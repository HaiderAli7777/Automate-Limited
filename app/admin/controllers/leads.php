<?php
/* CRM leads: website enquiries and deals, the sales pipeline board, activity logging and follow-ups. */
declare(strict_types=1);

/** A lead the signed-in person is allowed to open. */
function scoped_lead(int $id): array
{
    $lead = lead_full($id);
    if (!$lead) {
        abort(404);
    }
    if (!user_can('crm.all') && (int) $lead['owner_id'] !== auth_id()) {
        abort(403, 'This lead is assigned to someone else.');
    }
    return $lead;
}

function lead_query(): array
{
    $where = [];
    $params = [];
    if (!user_can('crm.all')) {
        $where[] = 'l.owner_id = ' . (int) auth_id();
    }
    $q = input('q');
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        $where[] = '(l.title LIKE ? OR c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.phone LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $status = input('status', 'open');
    if (in_array($status, ['open', 'won', 'lost'], true)) {
        $where[] = 'l.status = ?';
        $params[] = $status;
    }
    if ($stage = input_int('stage')) {
        $where[] = 'l.stage_id = ?';
        $params[] = $stage;
    }
    $owner = input('owner');
    if ($owner === 'me') {
        $where[] = 'l.owner_id = ?';
        $params[] = auth_id();
    } elseif ($owner === 'none') {
        $where[] = 'l.owner_id IS NULL';
    } elseif ((int) $owner > 0) {
        $where[] = 'l.owner_id = ?';
        $params[] = (int) $owner;
    }
    if (($source = input('source')) !== '' && isset(LEAD_SOURCES[$source])) {
        $where[] = 'l.source = ?';
        $params[] = $source;
    }
    if (($service = input('service')) !== '') {
        $where[] = 'l.service = ?';
        $params[] = $service;
    }
    $follow = input('follow');
    if ($follow === 'overdue') {
        $where[] = 'l.next_follow_up < ?';
        $params[] = now();
    } elseif ($follow === 'today') {
        $where[] = 'l.next_follow_up BETWEEN ? AND ?';
        array_push($params, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
    } elseif ($follow === 'none') {
        $where[] = "l.next_follow_up IS NULL AND l.status = 'open'";
    }
    $sql = ' FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
    return [$sql, $params];
}

function leads_index(): void
{
    [$from, $params] = lead_query();
    $p = paginate((int) db()->value('SELECT COUNT(*)' . $from, $params), 30);
    $sort = ['created' => 'l.created_at DESC', 'value' => 'l.value IS NULL, l.value DESC', 'follow' => 'l.next_follow_up IS NULL, l.next_follow_up', 'updated' => 'l.updated_at DESC'];
    $order = $sort[input('sort')] ?? $sort['created'];
    $rows = db()->all('SELECT l.*, c.name AS contact_name, c.email AS contact_email, c.company AS contact_company' . $from . ' ORDER BY ' . $order . ' LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'], $params);
    $totals = db()->one('SELECT COUNT(*) AS n, SUM(l.value) AS total' . $from, $params);
    admin_view('leads/index', ['title' => 'Leads', 'nav' => 'leads', 'rows' => $rows, 'p' => $p, 'totals' => $totals]);
}

function leads_export(): void
{
    [$from, $params] = lead_query();
    $rows = db()->all('SELECT l.*, c.name AS contact_name, c.email AS contact_email, c.phone AS contact_phone, c.company AS contact_company' . $from . ' ORDER BY l.created_at DESC', $params);
    $stages = lead_stages();
    csv_download('leads-' . date('Y-m-d') . '.csv',
        ['Title', 'Contact', 'Email', 'Phone', 'Company', 'Service', 'Stage', 'Status', 'Value', 'Currency', 'Priority', 'Owner', 'Source', 'Next follow-up', 'Expected close', 'Lost reason', 'UTM source', 'UTM medium', 'UTM campaign', 'Created', 'Message'],
        array_map(static fn ($r) => [
            $r['title'], $r['contact_name'], $r['contact_email'], $r['contact_phone'], $r['contact_company'], $r['service'],
            $stages[(int) $r['stage_id']]['name'] ?? '', $r['status'], $r['value'], $r['currency'], $r['priority'], user_name($r['owner_id'] ? (int) $r['owner_id'] : null),
            LEAD_SOURCES[$r['source']] ?? $r['source'], $r['next_follow_up'], $r['expected_close'], $r['lost_reason'], $r['utm_source'], $r['utm_medium'], $r['utm_campaign'], $r['created_at'], $r['message'],
        ], $rows)
    );
}

function leads_board(): void
{
    $where = ['1 = 1' . crm_scope('l')];
    $params = [];
    $owner = input('owner');
    if ($owner === 'me') {
        $where[] = 'l.owner_id = ?';
        $params[] = auth_id();
    } elseif ((int) $owner > 0) {
        $where[] = 'l.owner_id = ?';
        $params[] = (int) $owner;
    }
    if (($service = input('service')) !== '') {
        $where[] = 'l.service = ?';
        $params[] = $service;
    }
    // closed deals stay on the board for 30 days
    $where[] = "(l.status = 'open' OR l.stage_changed_at >= ?)";
    $params[] = date('Y-m-d H:i:s', time() - 30 * 86400);
    $leads = db()->all(
        'SELECT l.*, c.name AS contact_name, c.company AS contact_company FROM leads l LEFT JOIN contacts c ON c.id = l.contact_id WHERE '
        . implode(' AND ', $where) . ' ORDER BY FIELD(l.priority, \'high\', \'normal\', \'low\'), l.updated_at DESC LIMIT 800',
        $params
    );
    admin_view('leads/board', ['title' => 'Sales pipeline', 'nav' => 'leadboard', 'leads' => $leads, 'wide' => true]);
}

function leads_form(): void
{
    $contact = input_int('contact') ? db()->one('SELECT * FROM contacts WHERE id = ?', [input_int('contact')]) : null;
    admin_view('leads/form', ['title' => 'New lead', 'nav' => 'leads', 'contact' => $contact]);
}

/** Lead fields shared by create and update. */
function lead_input(array &$errors): array
{
    $value = input('value') === '' ? null : (float) str_replace(',', '', input('value'));
    if (input('value') !== '' && !is_numeric(str_replace(',', '', input('value')))) {
        $errors['value'] = 'Enter a number, e.g. 450000.';
    }
    $follow = parse_dt(input('next_follow_up'));
    if (input('next_follow_up') !== '' && $follow === null) {
        $errors['next_follow_up'] = 'Enter a valid date and time.';
    }
    $data = [
        'title' => mb_substr(input('title'), 0, 190),
        'service' => nullable(mb_substr(input('service'), 0, 80)),
        'source' => isset(LEAD_SOURCES[input('source')]) ? input('source') : 'other',
        'value' => $value,
        'currency' => in_array(input('currency'), currencies(), true) ? input('currency') : (string) setting('default_currency', 'PKR'),
        'priority' => isset(LEAD_PRIORITIES[input('priority')]) ? input('priority') : 'normal',
        'owner_id' => user_can('crm.all') ? (input_int('owner_id') ?: null) : auth_id(),
        'topic' => nullable(mb_substr(input('topic'), 0, 120)),
        'next_follow_up' => $follow,
        'expected_close' => parse_dt(input('expected_close'), false),
        'updated_at' => now(),
    ];
    if ($data['title'] === '') {
        $errors['title'] = 'Give the lead a short title, e.g. "Odoo for Karachi Traders".';
    }
    return $data;
}

function leads_create(): void
{
    $errors = [];
    $data = lead_input($errors);
    $contactId = input_int('contact_id');
    $contactName = input('contact_name');
    $email = strtolower(input('contact_email'));
    if (!$contactId) {
        if ($contactName === '') {
            $errors['contact_name'] = 'Enter who the lead is with.';
        }
        if ($email !== '' && !valid_email($email)) {
            $errors['contact_email'] = 'Enter a valid email or leave it empty.';
        }
    }
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('leads/new') . ($contactId ? '?contact=' . $contactId : ''));
    }
    $id = db()->tx(static function (Db $db) use ($data, $contactId, $contactName, $email): int {
        if (!$contactId) {
            $existing = $email !== '' ? $db->one('SELECT id FROM contacts WHERE email = ?', [$email]) : null;
            $contactId = $existing ? (int) $existing['id'] : $db->insert('contacts', [
                'name' => mb_substr($contactName, 0, 120),
                'email' => $email ?: null,
                'phone' => nullable(mb_substr(input('contact_phone'), 0, 40)),
                'company' => nullable(mb_substr(input('contact_company'), 0, 120)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $stageId = input_int('stage_id');
        $stages = lead_stages();
        if (!isset($stages[$stageId])) {
            $stageId = first_stage_id($stages, 'open');
        }
        $kind = $stages[$stageId]['kind'];
        $id = $db->insert('leads', $data + [
            'contact_id' => $contactId,
            'stage_id' => $stageId,
            'status' => $kind === 'won' ? 'won' : ($kind === 'lost' ? 'lost' : 'open'),
            'message' => nullable((string) ($_POST['message'] ?? '')),
            'won_at' => $kind === 'won' ? now() : null,
            'lost_at' => $kind === 'lost' ? now() : null,
            'stage_changed_at' => now(),
            'created_at' => now(),
        ]);
        log_activity('lead', $id, 'created', 'Lead added by hand');
        return $id;
    });
    if (!empty($data['owner_id'])) {
        notify([(int) $data['owner_id']], 'Lead assigned to you: ' . $data['title'], admin_url('leads/' . $id), 'Added by ' . auth_user()['name'], 'funnel');
    }
    flash('success', 'Lead added.');
    redirect(admin_url('leads/' . $id));
}

function leads_show(int $id): void
{
    $lead = scoped_lead($id);
    $timeline = activities_for([['lead', [$id]], ['contact', $lead['contact_id'] ? [(int) $lead['contact_id']] : []]]);
    $others = $lead['contact_id'] ? db()->all('SELECT id, title, stage_id, status, value, currency, created_at FROM leads WHERE contact_id = ? AND id <> ?' . crm_scope() . ' ORDER BY created_at DESC', [(int) $lead['contact_id'], $id]) : [];
    $templates = db()->all("SELECT tkey, name FROM email_templates WHERE module = 'crm' ORDER BY name");
    admin_view('leads/show', [
        'title' => $lead['title'],
        'nav' => 'leads',
        'lead' => $lead,
        'timeline' => $timeline,
        'others' => $others,
        'tasks' => open_tasks_for('lead', $id),
        'templates' => $templates,
    ]);
}

function leads_update(int $id): void
{
    $lead = scoped_lead($id);
    $errors = [];
    $data = lead_input($errors);
    if ($errors) {
        remember_input($errors);
        redirect(admin_url('leads/' . $id));
    }
    $changes = [];
    foreach (['value' => 'Value', 'owner_id' => 'Owner', 'priority' => 'Priority', 'next_follow_up' => 'Next follow-up', 'service' => 'Service'] as $k => $label) {
        if ((string) ($lead[$k] ?? '') !== (string) ($data[$k] ?? '') && !($k === 'value' && (float) $lead[$k] === (float) $data[$k])) {
            $changes[] = $label;
        }
    }
    db()->update('leads', $data, 'id = ?', [$id]);
    if ($changes) {
        log_activity('lead', $id, 'updated', 'Updated ' . strtolower(implode(', ', $changes)));
    }
    if ($data['owner_id'] && (int) $data['owner_id'] !== (int) $lead['owner_id'] && (int) $data['owner_id'] !== auth_id()) {
        notify([(int) $data['owner_id']], 'Lead assigned to you: ' . $data['title'], admin_url('leads/' . $id), 'Assigned by ' . auth_user()['name'], 'funnel');
        $email = db()->value('SELECT email FROM users WHERE id = ? AND is_active = 1', [(int) $data['owner_id']]);
        if ($email) {
            Mailer::send((string) $email, 'Lead assigned to you: ' . $data['title'], auth_user()['name'] . " assigned you a lead.\n\n" . $data['title'] . "\n" . site_origin() . admin_url('leads/' . $id));
        }
    }
    flash('success', 'Lead saved.');
    redirect(admin_url('leads/' . $id));
}

function leads_stage(int $id): void
{
    $lead = scoped_lead($id);
    $to = lead_move($id, input_int('stage_id'), input('reason'));
    $msg = $to ? $lead['title'] . ' moved to ' . $to['name'] . '.' : '';
    if ($to && $to['kind'] === 'won') {
        $msg = 'Won. Nice work.';
    }
    if (is_ajax()) {
        json_out(['ok' => true, 'message' => $msg]);
    }
    if ($msg) {
        flash('success', $msg);
    }
    back(admin_url('leads/' . $id));
}

function leads_activity(int $id): void
{
    scoped_lead($id);
    $kind = isset(ACTIVITY_KINDS[input('kind')]) ? input('kind') : 'note';
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    if ($body === '') {
        flash('error', 'Write something first.');
        redirect(admin_url('leads/' . $id));
    }
    $titles = ['note' => 'Note', 'call' => 'Call logged', 'meeting' => 'Meeting logged', 'email' => 'Email logged', 'whatsapp' => 'WhatsApp logged'];
    log_activity('lead', $id, $kind, $titles[$kind], $body);
    $patch = ['updated_at' => now()];
    $follow = parse_dt(input('next_follow_up'));
    if ($follow) {
        $patch['next_follow_up'] = $follow;
    } elseif (input('clear_follow_up') === '1') {
        $patch['next_follow_up'] = null;
    }
    db()->update('leads', $patch, 'id = ?', [$id]);
    // the first contact moves a brand-new lead along
    $lead = lead_full($id);
    $stages = array_values(lead_stages());
    if ($kind !== 'note' && $lead && (int) $lead['stage_id'] === (int) ($stages[0]['id'] ?? 0) && isset($stages[1]) && $stages[1]['kind'] === 'open') {
        lead_move($id, (int) $stages[1]['id']);
    }
    flash('success', 'Logged.');
    redirect(admin_url('leads/' . $id) . '#timeline');
}

function leads_email_preview(int $id): void
{
    $lead = scoped_lead($id);
    $tpl = email_template(input('template'));
    if (!$tpl || $tpl['module'] !== 'crm') {
        json_out(['ok' => false, 'error' => 'Template not found.'], 404);
    }
    $vars = lead_vars($lead);
    json_out(['ok' => true, 'subject' => fill_template($tpl['subject'], $vars), 'body' => fill_template($tpl['body'], $vars)]);
}

function leads_email(int $id): void
{
    $lead = scoped_lead($id);
    if (!valid_email((string) $lead['contact_email'])) {
        flash('error', 'This contact has no email address. Add one first.');
        redirect(admin_url('leads/' . $id));
    }
    $subject = input('subject');
    $body = trim(str_replace("\r\n", "\n", (string) ($_POST['body'] ?? '')));
    if ($subject === '' || $body === '') {
        flash('error', 'Add a subject and a message.');
        redirect(admin_url('leads/' . $id));
    }
    $ok = Mailer::send((string) $lead['contact_email'], $subject, $body, ['to_name' => (string) $lead['contact_name'], 'reply_to' => (string) auth_user()['email']]);
    if ($ok) {
        log_activity('lead', $id, 'email', 'Email sent: ' . $subject, $body);
        db()->update('leads', ['updated_at' => now()], 'id = ?', [$id]);
        $stages = array_values(lead_stages());
        if ((int) $lead['stage_id'] === (int) ($stages[0]['id'] ?? 0) && isset($stages[1]) && $stages[1]['kind'] === 'open') {
            lead_move($id, (int) $stages[1]['id']);
        }
        flash('success', 'Email sent to ' . $lead['contact_email'] . '.');
    } else {
        flash('error', 'The email didn\'t send: ' . Mailer::$lastError);
    }
    redirect(admin_url('leads/' . $id) . '#timeline');
}

function leads_delete(int $id): void
{
    $lead = scoped_lead($id);
    db()->tx(static function (Db $db) use ($id): void {
        $db->delete('activities', "entity_type = 'lead' AND entity_id = ?", [$id]);
        $db->delete('tasks', "entity_type = 'lead' AND entity_id = ?", [$id]);
        $db->delete('leads', 'id = ?', [$id]);
    });
    flash('success', 'Deleted "' . $lead['title'] . '".');
    redirect(admin_url('leads'));
}

/** Change several leads at once from the list. */
function leads_bulk(): void
{
    $ids = array_values(array_unique(array_filter(array_map('intval', input_array('ids')))));
    $action = input('action');
    if (!$ids) {
        flash('error', 'Tick at least one lead first.');
        back(admin_url('leads'));
    }
    $done = 0;
    foreach ($ids as $id) {
        $lead = lead_full($id);
        if (!$lead || (!user_can('crm.all') && (int) $lead['owner_id'] !== auth_id())) {
            continue;
        }
        if ($action === 'owner' && user_can('crm.all')) {
            $owner = input_int('owner_id') ?: null;
            db()->update('leads', ['owner_id' => $owner, 'updated_at' => now()], 'id = ?', [$id]);
            log_activity('lead', $id, 'updated', $owner ? 'Owner set to ' . user_name($owner) : 'Owner removed');
            if ($owner) {
                notify([$owner], 'Lead assigned to you: ' . $lead['title'], admin_url('leads/' . $id), 'Assigned by ' . auth_user()['name'], 'funnel');
            }
            $done++;
        } elseif ($action === 'stage') {
            if (lead_move($id, input_int('stage_id'), input('reason'))) {
                $done++;
            }
        } elseif ($action === 'delete' && user_can('data.delete')) {
            db()->delete('activities', "entity_type = 'lead' AND entity_id = ?", [$id]);
            db()->delete('tasks', "entity_type = 'lead' AND entity_id = ?", [$id]);
            db()->delete('leads', 'id = ?', [$id]);
            $done++;
        }
    }
    flash($done ? 'success' : 'info', $done ? plural($done, 'lead') . ' updated.' : 'Nothing changed.');
    back(admin_url('leads'));
}
