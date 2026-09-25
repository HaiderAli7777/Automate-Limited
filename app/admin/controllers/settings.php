<?php
/* Settings: company, notifications, email delivery, pipeline stages and email templates. Admin only. */
declare(strict_types=1);

const SETTING_TEXT = ['company_name', 'timezone', 'default_currency', 'hr_email', 'sales_email', 'mail_transport', 'mail_from_email',
    'mail_from_name', 'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'lead_services', 'upload_max_mb', 'lead_default_owner', 'privacy_email'];
const SETTING_FLAGS = ['notify_new_application', 'notify_new_inquiry', 'autoreply_application', 'autoreply_inquiry'];

function settings_page(): void
{
    admin_view('settings/general', ['title' => 'Settings', 'nav' => 'settings', 'tab' => 'general']);
}

function settings_save(): void
{
    $errors = [];
    $in = [];
    foreach (SETTING_TEXT as $k) {
        $in[$k] = input($k);
    }
    if ($in['company_name'] === '') {
        $errors['company_name'] = 'Enter the company name.';
    }
    if (!in_array($in['timezone'], timezone_identifiers_list(), true)) {
        $errors['timezone'] = 'Choose a time zone.';
    }
    foreach (['hr_email', 'sales_email'] as $k) {
        foreach (array_filter(array_map('trim', explode(',', $in[$k]))) as $addr) {
            if (!valid_email($addr)) {
                $errors[$k] = '"' . $addr . '" isn\'t a valid email. Separate several addresses with commas.';
            }
        }
    }
    foreach (['mail_from_email', 'privacy_email'] as $k) {
        if ($in[$k] !== '' && !valid_email($in[$k])) {
            $errors[$k] = 'Enter a valid email.';
        }
    }
    if (!in_array($in['mail_transport'], ['mail', 'smtp'], true)) {
        $in['mail_transport'] = 'mail';
    }
    if ($in['mail_transport'] === 'smtp' && $in['smtp_host'] === '') {
        $errors['smtp_host'] = 'Enter the SMTP server, for Hostinger smtp.hostinger.com.';
    }
    if (!in_array($in['smtp_encryption'], ['ssl', 'tls', 'none'], true)) {
        $in['smtp_encryption'] = 'ssl';
    }
    $in['smtp_port'] = (string) max(1, min(65535, (int) $in['smtp_port'] ?: 465));
    $in['upload_max_mb'] = (string) max(1, min(50, (int) $in['upload_max_mb'] ?: 8));
    $in['lead_default_owner'] = (string) (int) $in['lead_default_owner'];
    $in['default_currency'] = in_array($in['default_currency'], currencies(), true) ? $in['default_currency'] : 'PKR';
    $in['lead_services'] = implode("\n", array_slice(array_filter(array_map(static fn ($s) => mb_substr(trim($s), 0, 80), explode("\n", $in['lead_services']))), 0, 30));
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('settings'));
    }
    foreach ($in as $k => $v) {
        set_setting($k, $v);
    }
    foreach (SETTING_FLAGS as $k) {
        set_setting($k, input($k) === '1' ? '1' : '0');
    }
    $pw = (string) ($_POST['smtp_password'] ?? '');
    if ($pw !== '') {
        set_setting('smtp_password', encrypt_secret($pw));
    }
    if (input('smtp_password_clear') === '1') {
        set_setting('smtp_password', '');
    }
    flash('success', 'Settings saved.');
    redirect(admin_url('settings'));
}

function settings_test_email(): void
{
    $me = auth_user();
    $ok = Mailer::send((string) $me['email'], 'Test email from ' . company_name(), "This is a test from the team area.\n\nIf you're reading this, notifications for new applications and enquiries will reach you too.", ['to_name' => $me['name']]);
    flash($ok ? 'success' : 'error', $ok ? 'Test email sent to ' . $me['email'] . '. Check the inbox (and the spam folder).' : 'The test email failed: ' . Mailer::$lastError);
    redirect(admin_url('settings') . '#email');
}

/* ------------------------------------------------------------------ stages */
function stages_page(): void
{
    $atsCounts = db()->pairs('SELECT stage_id, COUNT(*) FROM applications GROUP BY stage_id');
    $leadCounts = db()->pairs('SELECT stage_id, COUNT(*) FROM leads GROUP BY stage_id');
    admin_view('settings/stages', ['title' => 'Pipeline stages', 'nav' => 'settings', 'tab' => 'stages', 'atsCounts' => $atsCounts, 'leadCounts' => $leadCounts]);
}

function stages_save(): void
{
    $which = input('pipeline') === 'crm' ? 'crm' : 'ats';
    $table = $which === 'crm' ? 'lead_stages' : 'ats_stages';
    $kinds = $which === 'crm' ? ['open', 'won', 'lost'] : ['active', 'hired', 'rejected'];
    $usage = $which === 'crm' ? db()->pairs('SELECT stage_id, COUNT(*) FROM leads GROUP BY stage_id') : db()->pairs('SELECT stage_id, COUNT(*) FROM applications GROUP BY stage_id');

    $ids = input_array('id');
    $names = input_array('name');
    $colors = input_array('color');
    $kindIn = input_array('kind');
    $probs = input_array('probability');
    $deletes = array_map('intval', input_array('delete'));

    $rows = [];
    foreach ($names as $i => $name) {
        $name = mb_substr(trim((string) $name), 0, 60);
        $id = (int) ($ids[$i] ?? 0);
        if ($name === '') {
            if ($id) {
                $deletes[] = $id;
            }
            continue;
        }
        if ($id && in_array($id, $deletes, true)) {
            continue;
        }
        $rows[] = [
            'id' => $id,
            'name' => $name,
            'color' => in_array($colors[$i] ?? '', STAGE_COLORS, true) ? $colors[$i] : 'slate',
            'kind' => in_array($kindIn[$i] ?? '', $kinds, true) ? $kindIn[$i] : $kinds[0],
            'probability' => max(0, min(100, (int) ($probs[$i] ?? 0))),
        ];
    }
    $errors = [];
    if (!in_array($kinds[0], array_column($rows, 'kind'), true)) {
        $errors[] = 'Keep at least one ' . ($which === 'crm' ? 'open' : 'active') . ' stage.';
    }
    foreach (array_unique($deletes) as $del) {
        if (!empty($usage[$del])) {
            $errors[] = 'A stage still holds ' . plural((int) $usage[$del], $which === 'crm' ? 'lead' : 'application') . '. Move them to another stage before deleting it.';
        }
    }
    if ($errors) {
        flash('error', implode(' ', array_unique($errors)));
        redirect(admin_url('settings/stages'));
    }
    db()->tx(static function (Db $db) use ($rows, $deletes, $table, $which): void {
        foreach (array_unique($deletes) as $del) {
            $db->delete($table, 'id = ?', [$del]);
        }
        foreach ($rows as $i => $r) {
            $data = ['name' => $r['name'], 'color' => $r['color'], 'kind' => $r['kind'], 'sort_order' => ($i + 1) * 10];
            if ($which === 'crm') {
                $data['probability'] = $r['kind'] === 'won' ? 100 : ($r['kind'] === 'lost' ? 0 : $r['probability']);
            }
            if ($r['id']) {
                $db->update($table, $data, 'id = ?', [$r['id']]);
            } else {
                $db->insert($table, $data);
            }
        }
    });
    // keep status in step with any change of stage kind
    if ($which === 'crm') {
        db()->run("UPDATE leads l JOIN lead_stages s ON s.id = l.stage_id SET l.status = CASE s.kind WHEN 'won' THEN 'won' WHEN 'lost' THEN 'lost' ELSE 'open' END");
    } else {
        db()->run("UPDATE applications a JOIN ats_stages s ON s.id = a.stage_id SET a.status = CASE s.kind WHEN 'hired' THEN 'hired' WHEN 'rejected' THEN 'rejected' ELSE 'active' END");
    }
    flash('success', ($which === 'crm' ? 'Sales' : 'Hiring') . ' pipeline saved.');
    redirect(admin_url('settings/stages'));
}

/* ------------------------------------------------------------------ templates */
function templates_index(): void
{
    $templates = db()->all('SELECT * FROM email_templates ORDER BY module, name');
    admin_view('settings/templates', ['title' => 'Email templates', 'nav' => 'settings', 'tab' => 'templates', 'templates' => $templates]);
}

function templates_form(int $id): void
{
    $tpl = db()->one('SELECT * FROM email_templates WHERE id = ?', [$id]) ?? abort(404);
    admin_view('settings/template-form', ['title' => $tpl['name'], 'nav' => 'settings', 'tab' => 'templates', 'tpl' => $tpl]);
}

function templates_save(int $id): void
{
    $tpl = db()->one('SELECT * FROM email_templates WHERE id = ?', [$id]) ?? abort(404);
    $subject = input('subject');
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    $errors = [];
    if ($subject === '') {
        $errors['subject'] = 'Enter a subject.';
    }
    if ($body === '') {
        $errors['body'] = 'Enter the message.';
    }
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('settings/templates/' . $id));
    }
    db()->update('email_templates', ['name' => input('name') ?: $tpl['name'], 'subject' => mb_substr($subject, 0, 255), 'body' => $body, 'updated_at' => now()], 'id = ?', [$id]);
    flash('success', 'Template saved.');
    redirect(admin_url('settings/templates'));
}
