<?php
/*
 * The enquiry page (GET) and its handler (POST). Every valid submission lands in the CRM as a lead
 * (or as a new message on the sender's open lead from the last week), the
 * sales inbox is notified, and the sender gets an acknowledgement.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

/** The enquiry page, with the form pre-filled from ?service= and ?module=. */
function contact_page(array $errors = [], array $values = [], bool $sent = false): never
{
    $services = app_installed() ? lead_services() : array_column(site_services(), 'name');
    $pre = $values ?: [
        'service' => in_array(input('service'), $services, true) ? input('service') : '',
        'module' => contact_module(input('module')) ?? '',
    ];
    render('site/contact-page', ['errors' => $errors, 'values' => $pre, 'sent' => $sent]);
    exit;
}

/** A module name from the modules list, or null. */
function contact_module(string $name): ?string
{
    foreach (site_modules() as $m) {
        if (strcasecmp($m['name'], trim($name)) === 0) {
            return $m['name'];
        }
    }
    return null;
}

if (!is_post()) {
    contact_page([], [], input('sent') === '1');
}

$ajax = is_ajax();
$values = [];
foreach (['name', 'email', 'company', 'phone', 'service', 'module', 'message'] as $k) {
    $values[$k] = input($k);
}
$values['email'] = strtolower($values['email']);

$respond = static function (bool $ok, array $errors = []) use ($ajax, $values): never {
    if ($ajax) {
        json_out($ok
            ? ['ok' => true, 'message' => 'Thanks, your message is with our team. We\'ll reply by email.']
            : ['ok' => false, 'errors' => $errors, 'error' => $errors['form'] ?? 'Please check the highlighted fields.'], $ok ? 200 : 422);
    }
    if ($ok) {
        redirect(url('contact/') . '?sent=1#enquiry', 303);
    }
    contact_page($errors, $values);
};

// Bots fill the hidden field or submit instantly: act as if it worked, store nothing.
if (input('website') !== '') {
    $respond(true);
}
$token = form_token_problem(input('_t'));
if ($token === 'too_fast') {
    $respond(true);
}

$errors = [];
if ($token === 'expired') {
    $errors['form'] = 'This page was open for a long time. Please refresh it and send your message again.';
}
if ($values['name'] === '' || mb_strlen($values['name']) > 120) {
    $errors['name'] = 'Please tell us your name.';
}
if (!valid_email($values['email'])) {
    $errors['email'] = 'Please enter an email address we can reply to.';
}
if (mb_strlen($values['message']) < 5) {
    $errors['message'] = 'Please tell us a little about what you need.';
} elseif (mb_strlen($values['message']) > 5000) {
    $errors['message'] = 'Please keep the message under 5,000 characters.';
}
if ($errors) {
    $respond(false, $errors);
}

if (!app_installed()) {
    $respond(false, ['form' => 'Our form is being set up. Please email info@automateltd.com in the meantime.']);
}
if (!throttle('contact|' . client_ip(), 6, 600)) {
    $respond(false, ['form' => 'We\'ve received several messages from you in a short time. Please wait a few minutes, or email info@automateltd.com.']);
}

$service = in_array($values['service'], array_merge(lead_services(), ['Not sure yet']), true) ? $values['service'] : 'Not sure yet';
// the Odoo module they picked, kept as the lead's topic
$topic = $service === 'Odoo ERP' ? contact_module($values['module']) : null;
$now = now();
$clip = static fn (string $v, int $n): ?string => $v === '' ? null : mb_substr($v, 0, $n);

$leadId = db()->tx(static function (Db $db) use ($values, $service, $topic, $now, $clip): int {
    $contact = $db->one('SELECT * FROM contacts WHERE email = ? ORDER BY id LIMIT 1', [$values['email']]);
    if ($contact) {
        $contactId = (int) $contact['id'];
        $patch = ['updated_at' => $now];
        if (!$contact['phone'] && $values['phone'] !== '') {
            $patch['phone'] = $clip($values['phone'], 40);
        }
        if (!$contact['company'] && $values['company'] !== '') {
            $patch['company'] = $clip($values['company'], 120);
        }
        $db->update('contacts', $patch, 'id = ?', [$contactId]);
    } else {
        $contactId = $db->insert('contacts', [
            'name' => mb_substr($values['name'], 0, 120),
            'email' => $values['email'],
            'phone' => $clip($values['phone'], 40),
            'company' => $clip($values['company'], 120),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    // A second message within a week joins the open lead instead of duplicating it.
    $recent = $db->one(
        "SELECT id FROM leads WHERE contact_id = ? AND status = 'open' AND created_at >= ? ORDER BY id DESC LIMIT 1",
        [$contactId, date('Y-m-d H:i:s', time() - 7 * 86400)]
    );
    if ($recent) {
        $db->update('leads', ['updated_at' => $now], 'id = ?', [(int) $recent['id']]);
        log_activity('lead', (int) $recent['id'], 'email', 'New message from the website form' . ($topic ? ' about ' . $topic : ''), $values['message'], ['service' => $service], null);
        return (int) $recent['id'];
    }

    $who = $values['company'] !== '' ? $values['company'] : $values['name'];
    $title = $service === 'Not sure yet' ? 'Enquiry from ' . $who : $service . ($topic ? ' (' . $topic . ')' : '') . ' for ' . $who;
    $owner = (int) setting('lead_default_owner', '0');
    $leadId = $db->insert('leads', [
        'contact_id' => $contactId,
        'title' => mb_substr($title, 0, 190),
        'service' => $service,
        'topic' => $topic,
        'source' => 'website',
        'stage_id' => first_stage_id(lead_stages(), 'open'),
        'status' => 'open',
        'currency' => (string) setting('default_currency', 'PKR'),
        'priority' => 'normal',
        'owner_id' => $owner ?: null,
        'message' => $values['message'],
        'utm_source' => $clip(input('utm_source'), 120),
        'utm_medium' => $clip(input('utm_medium'), 120),
        'utm_campaign' => $clip(input('utm_campaign'), 120),
        'referrer' => $clip(input('referrer'), 500),
        'landing_page' => $clip(input('landing'), 500),
        'ip' => client_ip(),
        'stage_changed_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    log_activity('lead', $leadId, 'created', 'Enquiry received from the website form', '', [], null);
    return $leadId;
});

$lead = lead_full($leadId);
if ($lead) {
    notify(lead_audience($lead['owner_id'] ? (int) $lead['owner_id'] : null), 'New enquiry: ' . $lead['title'], admin_url('leads/' . $leadId), $values['name'] . ($values['company'] !== '' ? ', ' . $values['company'] : ''), 'funnel');
}
if ($lead && setting('notify_new_inquiry', '1') === '1') {
    $body = "A new enquiry came in through the website.\n\n"
        . 'Name: ' . $values['name'] . "\n"
        . 'Email: ' . $values['email'] . "\n"
        . ($values['company'] !== '' ? 'Company: ' . $values['company'] . "\n" : '')
        . ($values['phone'] !== '' ? 'Phone: ' . $values['phone'] . "\n" : '')
        . 'Interested in: ' . $service . ($topic ? ', ' . $topic . ' module' : '') . "\n\n"
        . $values['message'] . "\n\n"
        . 'Open it in the CRM: ' . site_origin() . admin_url('leads/' . $leadId);
    notify_team((string) setting('sales_email', ''), 'New enquiry: ' . $lead['title'], $body, ['reply_to' => $values['email']]);
}
if ($lead && setting('autoreply_inquiry', '1') === '1') {
    send_template('inquiry_received', $values['email'], lead_vars($lead), [
        'to_name' => $values['name'],
        'reply_to' => (string) (explode(',', (string) setting('sales_email', ''))[0] ?? ''),
    ]);
}

$respond(true);
