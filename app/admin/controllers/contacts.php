<?php
/* CRM contacts: the people behind leads. Website enquiries create them automatically. */
declare(strict_types=1);

function contacts_index(): void
{
    $q = input('q');
    $where = '';
    $params = [];
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
        $where = ' WHERE c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.phone LIKE ?';
        $params = [$like, $like, $like, $like];
    }
    $p = paginate((int) db()->value('SELECT COUNT(*) FROM contacts c' . $where, $params), 30);
    $rows = db()->all(
        "SELECT c.*,
            (SELECT COUNT(*) FROM leads l WHERE l.contact_id = c.id) AS lead_count,
            (SELECT COUNT(*) FROM leads l WHERE l.contact_id = c.id AND l.status = 'open') AS open_count,
            (SELECT SUM(l.value) FROM leads l WHERE l.contact_id = c.id AND l.status = 'won') AS won_value,
            (SELECT MAX(l.updated_at) FROM leads l WHERE l.contact_id = c.id) AS last_activity
         FROM contacts c" . $where . ' ORDER BY c.updated_at DESC LIMIT ' . $p['per'] . ' OFFSET ' . $p['offset'],
        $params
    );
    admin_view('contacts/index', ['title' => 'Contacts', 'nav' => 'contacts', 'rows' => $rows, 'p' => $p, 'q' => $q]);
}

function contacts_form(): void
{
    admin_view('contacts/show', ['title' => 'New contact', 'nav' => 'contacts', 'contact' => null, 'leads' => [], 'timeline' => [], 'tasks' => []]);
}

function contacts_show(int $id): void
{
    $contact = db()->one('SELECT * FROM contacts WHERE id = ?', [$id]) ?? abort(404);
    $leads = db()->all('SELECT * FROM leads WHERE contact_id = ? ORDER BY created_at DESC', [$id]);
    $timeline = activities_for([['contact', [$id]], ['lead', array_map('intval', array_column($leads, 'id'))]], 100);
    admin_view('contacts/show', ['title' => $contact['name'], 'nav' => 'contacts', 'contact' => $contact, 'leads' => $leads, 'timeline' => $timeline, 'tasks' => open_tasks_for('contact', $id)]);
}

function contacts_save(int $id = 0): void
{
    $contact = $id ? (db()->one('SELECT * FROM contacts WHERE id = ?', [$id]) ?? abort(404)) : null;
    $errors = [];
    $email = strtolower(input('email'));
    $data = [
        'name' => mb_substr(input('name'), 0, 120),
        'email' => $email ?: null,
        'phone' => nullable(mb_substr(input('phone'), 0, 40)),
        'company' => nullable(mb_substr(input('company'), 0, 120)),
        'job_title' => nullable(mb_substr(input('job_title'), 0, 120)),
        'country' => nullable(mb_substr(input('country'), 0, 80)),
        'city' => nullable(mb_substr(input('city'), 0, 80)),
        'website' => nullable(mb_substr(input('website') !== '' ? normalise_url(input('website')) : '', 0, 255)),
        'notes' => nullable((string) ($_POST['notes'] ?? '')),
        'updated_at' => now(),
    ];
    if ($data['name'] === '') {
        $errors['name'] = 'Enter a name.';
    }
    if ($email !== '' && !valid_email($email)) {
        $errors['email'] = 'Enter a valid email.';
    } elseif ($email !== '' && db()->value('SELECT id FROM contacts WHERE email = ? AND id <> ?', [$email, $id])) {
        $errors['email'] = 'Another contact already uses this email.';
    }
    if ($errors) {
        remember_input($errors);
        redirect($id ? admin_url('contacts/' . $id) : admin_url('contacts/new'));
    }
    if ($contact) {
        db()->update('contacts', $data, 'id = ?', [$id]);
    } else {
        $id = db()->insert('contacts', $data + ['created_at' => now()]);
    }
    flash('success', 'Contact saved.');
    redirect(admin_url('contacts/' . $id));
}

function contacts_delete(int $id): void
{
    $contact = db()->one('SELECT * FROM contacts WHERE id = ?', [$id]) ?? abort(404);
    $n = (int) db()->value('SELECT COUNT(*) FROM leads WHERE contact_id = ?', [$id]);
    if ($n) {
        flash('error', $contact['name'] . ' has ' . plural($n, 'lead') . '. Delete those first, or keep the contact for the history.');
        redirect(admin_url('contacts/' . $id));
    }
    db()->delete('activities', "entity_type = 'contact' AND entity_id = ?", [$id]);
    db()->delete('tasks', "entity_type = 'contact' AND entity_id = ?", [$id]);
    db()->delete('contacts', 'id = ?', [$id]);
    flash('success', 'Contact deleted.');
    redirect(admin_url('contacts'));
}
