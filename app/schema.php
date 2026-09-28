<?php
/*
 * Database schema as numbered migrations. The installer runs them all; after
 * an update, the first admin page load runs any new ones (see migrate()).
 * Never edit a migration that has shipped: add a new number instead.
 */
declare(strict_types=1);

const SQL_TABLE_OPTS = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

function schema_migrations(): array
{
    return [
        1 => [
            'CREATE TABLE IF NOT EXISTS settings (
                skey VARCHAR(64) NOT NULL PRIMARY KEY,
                svalue MEDIUMTEXT NULL
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT \'recruiter\',
                title VARCHAR(120) NULL,
                phone VARCHAR(40) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                last_login_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_users_email (email)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS password_resets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                KEY idx_pr_token (token_hash),
                CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS throttle (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                bucket VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL,
                KEY idx_throttle (bucket, created_at)
            )' . SQL_TABLE_OPTS,

            /* ---------------- ATS ---------------- */
            'CREATE TABLE IF NOT EXISTS ats_stages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(60) NOT NULL,
                color VARCHAR(20) NOT NULL DEFAULT \'slate\',
                kind VARCHAR(20) NOT NULL DEFAULT \'active\',
                sort_order INT NOT NULL DEFAULT 0
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS jobs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(160) NOT NULL,
                slug VARCHAR(190) NOT NULL,
                department VARCHAR(80) NULL,
                location VARCHAR(120) NULL,
                employment_type VARCHAR(20) NOT NULL DEFAULT \'full_time\',
                workplace VARCHAR(20) NOT NULL DEFAULT \'onsite\',
                experience_level VARCHAR(20) NULL,
                openings INT NOT NULL DEFAULT 1,
                salary_min DECIMAL(14,2) NULL,
                salary_max DECIMAL(14,2) NULL,
                salary_currency VARCHAR(8) NULL,
                salary_period VARCHAR(10) NOT NULL DEFAULT \'month\',
                salary_visible TINYINT(1) NOT NULL DEFAULT 0,
                summary TEXT NULL,
                description MEDIUMTEXT NULL,
                requirements MEDIUMTEXT NULL,
                benefits MEDIUMTEXT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'draft\',
                listed TINYINT(1) NOT NULL DEFAULT 1,
                hiring_manager_id INT UNSIGNED NULL,
                closes_at DATE NULL,
                published_at DATETIME NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_jobs_slug (slug),
                KEY idx_jobs_status (status)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS job_questions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                job_id INT UNSIGNED NOT NULL,
                question VARCHAR(255) NOT NULL,
                qtype VARCHAR(20) NOT NULL DEFAULT \'text\',
                options TEXT NULL,
                required TINYINT(1) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                CONSTRAINT fk_jq_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS candidates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(80) NOT NULL,
                last_name VARCHAR(80) NOT NULL DEFAULT \'\',
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(40) NULL,
                location VARCHAR(120) NULL,
                current_title VARCHAR(120) NULL,
                current_company VARCHAR(120) NULL,
                experience_years DECIMAL(4,1) NULL,
                expected_salary VARCHAR(80) NULL,
                notice_period VARCHAR(80) NULL,
                linkedin_url VARCHAR(255) NULL,
                portfolio_url VARCHAR(255) NULL,
                source VARCHAR(40) NULL,
                tags VARCHAR(255) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_cand_email (email),
                KEY idx_cand_created (created_at)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS applications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                job_id INT UNSIGNED NOT NULL,
                candidate_id INT UNSIGNED NOT NULL,
                stage_id INT UNSIGNED NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'active\',
                cover_letter TEXT NULL,
                resume_file_id INT UNSIGNED NULL,
                source VARCHAR(40) NULL,
                owner_id INT UNSIGNED NULL,
                rejection_reason VARCHAR(120) NULL,
                offer_salary VARCHAR(80) NULL,
                offer_start_date DATE NULL,
                offer_status VARCHAR(20) NULL,
                offer_notes TEXT NULL,
                applied_at DATETIME NOT NULL,
                stage_changed_at DATETIME NOT NULL,
                hired_at DATETIME NULL,
                rejected_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_app_job_cand (job_id, candidate_id),
                KEY idx_app_stage (stage_id),
                KEY idx_app_status (status),
                KEY idx_app_applied (applied_at),
                CONSTRAINT fk_app_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
                CONSTRAINT fk_app_cand FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS application_answers (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                application_id INT UNSIGNED NOT NULL,
                question_id INT UNSIGNED NULL,
                question VARCHAR(255) NOT NULL,
                answer TEXT NULL,
                CONSTRAINT fk_ans_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS interviews (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                application_id INT UNSIGNED NOT NULL,
                title VARCHAR(160) NOT NULL,
                itype VARCHAR(20) NOT NULL DEFAULT \'video\',
                scheduled_at DATETIME NOT NULL,
                duration_minutes INT NOT NULL DEFAULT 45,
                location VARCHAR(255) NULL,
                meeting_url VARCHAR(500) NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'scheduled\',
                notes TEXT NULL,
                sequence INT NOT NULL DEFAULT 0,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_iv_when (scheduled_at),
                KEY idx_iv_status (status),
                CONSTRAINT fk_iv_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS interview_panel (
                interview_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (interview_id, user_id),
                KEY idx_panel_user (user_id),
                CONSTRAINT fk_panel_iv FOREIGN KEY (interview_id) REFERENCES interviews(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS interview_feedback (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                interview_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                rating TINYINT NOT NULL,
                recommendation VARCHAR(20) NOT NULL,
                strengths TEXT NULL,
                concerns TEXT NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_fb (interview_id, user_id),
                CONSTRAINT fk_fb_iv FOREIGN KEY (interview_id) REFERENCES interviews(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,

            /* ---------------- CRM ---------------- */
            'CREATE TABLE IF NOT EXISTS lead_stages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(60) NOT NULL,
                color VARCHAR(20) NOT NULL DEFAULT \'slate\',
                kind VARCHAR(20) NOT NULL DEFAULT \'open\',
                probability TINYINT UNSIGNED NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS contacts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NULL,
                phone VARCHAR(40) NULL,
                company VARCHAR(120) NULL,
                job_title VARCHAR(120) NULL,
                country VARCHAR(80) NULL,
                city VARCHAR(80) NULL,
                website VARCHAR(255) NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_contact_email (email),
                KEY idx_contact_company (company)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS leads (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                contact_id INT UNSIGNED NULL,
                title VARCHAR(190) NOT NULL,
                service VARCHAR(80) NULL,
                source VARCHAR(40) NOT NULL DEFAULT \'website\',
                stage_id INT UNSIGNED NOT NULL,
                status VARCHAR(10) NOT NULL DEFAULT \'open\',
                value DECIMAL(14,2) NULL,
                currency VARCHAR(8) NULL,
                priority VARCHAR(10) NOT NULL DEFAULT \'normal\',
                owner_id INT UNSIGNED NULL,
                message TEXT NULL,
                next_follow_up DATETIME NULL,
                expected_close DATE NULL,
                lost_reason VARCHAR(120) NULL,
                utm_source VARCHAR(120) NULL,
                utm_medium VARCHAR(120) NULL,
                utm_campaign VARCHAR(120) NULL,
                referrer VARCHAR(500) NULL,
                landing_page VARCHAR(500) NULL,
                ip VARCHAR(45) NULL,
                won_at DATETIME NULL,
                lost_at DATETIME NULL,
                stage_changed_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_lead_stage (stage_id),
                KEY idx_lead_status (status),
                KEY idx_lead_owner (owner_id),
                KEY idx_lead_created (created_at),
                KEY idx_lead_follow (next_follow_up),
                CONSTRAINT fk_lead_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL
            )' . SQL_TABLE_OPTS,

            /* ---------------- shared ---------------- */
            'CREATE TABLE IF NOT EXISTS activities (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(30) NOT NULL,
                entity_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NULL,
                type VARCHAR(20) NOT NULL,
                title VARCHAR(255) NOT NULL,
                body MEDIUMTEXT NULL,
                meta TEXT NULL,
                created_at DATETIME NOT NULL,
                KEY idx_act_entity (entity_type, entity_id, created_at),
                KEY idx_act_created (created_at)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS files (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(30) NOT NULL,
                entity_id INT UNSIGNED NOT NULL,
                kind VARCHAR(20) NOT NULL DEFAULT \'attachment\',
                original_name VARCHAR(190) NOT NULL,
                stored_name VARCHAR(190) NOT NULL,
                mime VARCHAR(120) NOT NULL,
                size INT UNSIGNED NOT NULL,
                uploaded_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                KEY idx_files_entity (entity_type, entity_id)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS tasks (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(190) NOT NULL,
                notes TEXT NULL,
                entity_type VARCHAR(30) NULL,
                entity_id INT UNSIGNED NULL,
                assigned_to INT UNSIGNED NULL,
                due_at DATETIME NULL,
                completed_at DATETIME NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_tasks_assignee (assigned_to, completed_at),
                KEY idx_tasks_entity (entity_type, entity_id)
            )' . SQL_TABLE_OPTS,

            'CREATE TABLE IF NOT EXISTS email_templates (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tkey VARCHAR(60) NOT NULL,
                name VARCHAR(120) NOT NULL,
                module VARCHAR(10) NOT NULL DEFAULT \'ats\',
                subject VARCHAR(255) NOT NULL,
                body MEDIUMTEXT NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_tpl_key (tkey)
            )' . SQL_TABLE_OPTS,
        ],

        // 2: per-user access rights, in-app notifications, the Odoo module an enquiry is about
        2 => [
            'ALTER TABLE users ADD COLUMN permissions TEXT NULL AFTER role',
            'ALTER TABLE leads ADD COLUMN topic VARCHAR(120) NULL AFTER service',
            'CREATE TABLE IF NOT EXISTS notifications (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                title VARCHAR(255) NOT NULL,
                body TEXT NULL,
                link VARCHAR(500) NULL,
                icon VARCHAR(40) NULL,
                read_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                KEY idx_notif_user (user_id, read_at, created_at)
            )' . SQL_TABLE_OPTS,
        ],

        // 3: talent pool, employees and departments, payroll
        3 => [
            'ALTER TABLE applications
                ADD COLUMN pool_reason VARCHAR(120) NULL AFTER rejection_reason,
                ADD COLUMN pool_note TEXT NULL AFTER pool_reason,
                ADD COLUMN revisit_on DATE NULL AFTER pool_note,
                ADD COLUMN pooled_at DATETIME NULL AFTER rejected_at',
            static function (Db $db): void {
                // existing sites get the new stage; new installs get it from seed_defaults()
                if ((int) $db->value('SELECT COUNT(*) FROM ats_stages') && !$db->value("SELECT id FROM ats_stages WHERE kind = 'pool'")) {
                    $db->insert('ats_stages', ['name' => 'Talent pool', 'color' => 'violet', 'kind' => 'pool', 'sort_order' => 75]);
                }
                foreach (['application_on_hold', 'payslip'] as $tkey) {
                    if ((int) $db->value('SELECT COUNT(*) FROM email_templates') && !$db->value('SELECT id FROM email_templates WHERE tkey = ?', [$tkey])) {
                        [$key, $name, $module, $subject, $body] = default_templates()[$tkey];
                        $db->insert('email_templates', ['tkey' => $key, 'name' => $name, 'module' => $module, 'subject' => $subject, 'body' => $body, 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                }
            },
            'CREATE TABLE IF NOT EXISTS departments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(80) NOT NULL,
                head_employee_id INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_dept_name (name)
            )' . SQL_TABLE_OPTS,
            'CREATE TABLE IF NOT EXISTS employees (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                employee_code VARCHAR(20) NOT NULL,
                candidate_id INT UNSIGNED NULL,
                application_id INT UNSIGNED NULL,
                first_name VARCHAR(80) NOT NULL,
                last_name VARCHAR(80) NOT NULL DEFAULT \'\',
                email VARCHAR(190) NULL,
                phone VARCHAR(40) NULL,
                cnic VARCHAR(30) NULL,
                date_of_birth DATE NULL,
                gender VARCHAR(12) NULL,
                marital_status VARCHAR(20) NULL,
                address TEXT NULL,
                city VARCHAR(80) NULL,
                emergency_name VARCHAR(120) NULL,
                emergency_relation VARCHAR(60) NULL,
                emergency_phone VARCHAR(40) NULL,
                department_id INT UNSIGNED NULL,
                designation VARCHAR(120) NULL,
                employment_type VARCHAR(20) NOT NULL DEFAULT \'full_time\',
                work_location VARCHAR(120) NULL,
                manager_id INT UNSIGNED NULL,
                join_date DATE NOT NULL,
                probation_end DATE NULL,
                confirmed_on DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'active\',
                exit_date DATE NULL,
                exit_reason VARCHAR(160) NULL,
                basic_salary DECIMAL(14,2) NOT NULL DEFAULT 0,
                currency VARCHAR(8) NOT NULL DEFAULT \'PKR\',
                bank_name VARCHAR(120) NULL,
                bank_account_title VARCHAR(120) NULL,
                bank_iban VARCHAR(60) NULL,
                tax_number VARCHAR(40) NULL,
                notes TEXT NULL,
                created_by INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_emp_code (employee_code),
                KEY idx_emp_status (status),
                KEY idx_emp_dept (department_id),
                KEY idx_emp_app (application_id)
            )' . SQL_TABLE_OPTS,
            'CREATE TABLE IF NOT EXISTS employee_components (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                employee_id INT UNSIGNED NOT NULL,
                type VARCHAR(12) NOT NULL,
                label VARCHAR(80) NOT NULL,
                amount DECIMAL(14,2) NOT NULL,
                KEY idx_comp_emp (employee_id),
                CONSTRAINT fk_comp_emp FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,
            'CREATE TABLE IF NOT EXISTS payroll_runs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                period CHAR(7) NOT NULL,
                title VARCHAR(160) NOT NULL,
                scope VARCHAR(20) NOT NULL DEFAULT \'all\',
                department_id INT UNSIGNED NULL,
                employee_id INT UNSIGNED NULL,
                pay_date DATE NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'draft\',
                notes TEXT NULL,
                created_by INT UNSIGNED NULL,
                confirmed_by INT UNSIGNED NULL,
                confirmed_at DATETIME NULL,
                paid_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY idx_run_period (period)
            )' . SQL_TABLE_OPTS,
            'CREATE TABLE IF NOT EXISTS payslips (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                run_id INT UNSIGNED NOT NULL,
                employee_id INT UNSIGNED NOT NULL,
                period CHAR(7) NOT NULL,
                emp_name VARCHAR(170) NOT NULL,
                emp_code VARCHAR(20) NOT NULL,
                designation VARCHAR(120) NULL,
                department VARCHAR(80) NULL,
                bank_iban VARCHAR(60) NULL,
                currency VARCHAR(8) NOT NULL,
                basic DECIMAL(14,2) NOT NULL DEFAULT 0,
                allowances DECIMAL(14,2) NOT NULL DEFAULT 0,
                deductions DECIMAL(14,2) NOT NULL DEFAULT 0,
                gross DECIMAL(14,2) NOT NULL DEFAULT 0,
                net DECIMAL(14,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT \'draft\',
                notes TEXT NULL,
                confirmed_at DATETIME NULL,
                emailed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uq_slip_emp_period (employee_id, period),
                KEY idx_slip_run (run_id),
                CONSTRAINT fk_slip_run FOREIGN KEY (run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,
            'CREATE TABLE IF NOT EXISTS payslip_lines (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                payslip_id INT UNSIGNED NOT NULL,
                type VARCHAR(12) NOT NULL,
                label VARCHAR(80) NOT NULL,
                amount DECIMAL(14,2) NOT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                KEY idx_line_slip (payslip_id),
                CONSTRAINT fk_line_slip FOREIGN KEY (payslip_id) REFERENCES payslips(id) ON DELETE CASCADE
            )' . SQL_TABLE_OPTS,
        ],
    ];
}

function schema_version(): int
{
    return max(array_keys(schema_migrations()));
}

/** Apply migrations newer than the stored version. Safe to call on every admin request. */
function migrate(Db $db): void
{
    $current = 0;
    try {
        $current = (int) $db->value("SELECT svalue FROM settings WHERE skey = 'schema_version'");
    } catch (Throwable $e) {
        $current = 0;
    }
    foreach (schema_migrations() as $version => $statements) {
        if ($version <= $current) {
            continue;
        }
        foreach ($statements as $sql) {
            if ($sql instanceof Closure) {
                $sql($db);
            } else {
                $db->pdo()->exec($sql);
            }
        }
        $db->run("INSERT INTO settings (skey, svalue) VALUES ('schema_version', ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)", [(string) $version]);
    }
}

/** Default stages, templates, settings and the open application posting. */
function seed_defaults(Db $db, array $opts): void
{
    $now = date('Y-m-d H:i:s');

    if (!(int) $db->value('SELECT COUNT(*) FROM ats_stages')) {
        $stages = [
            ['Applied', 'slate', 'active'], ['Screening', 'blue', 'active'], ['Assessment', 'violet', 'active'],
            ['Interview', 'cyan', 'active'], ['Final interview', 'teal', 'active'], ['Offer', 'amber', 'active'],
            ['Hired', 'green', 'hired'], ['Talent pool', 'violet', 'pool'], ['Rejected', 'red', 'rejected'],
        ];
        foreach ($stages as $i => [$name, $color, $kind]) {
            $db->insert('ats_stages', ['name' => $name, 'color' => $color, 'kind' => $kind, 'sort_order' => ($i + 1) * 10]);
        }
    }

    if (!(int) $db->value('SELECT COUNT(*) FROM lead_stages')) {
        $stages = [
            ['New', 'slate', 'open', 10], ['Contacted', 'blue', 'open', 20], ['Qualified', 'cyan', 'open', 40],
            ['Proposal sent', 'violet', 'open', 60], ['Negotiation', 'amber', 'open', 80],
            ['Won', 'green', 'won', 100], ['Lost', 'red', 'lost', 0],
        ];
        foreach ($stages as $i => [$name, $color, $kind, $p]) {
            $db->insert('lead_stages', ['name' => $name, 'color' => $color, 'kind' => $kind, 'probability' => $p, 'sort_order' => ($i + 1) * 10]);
        }
    }

    $templates = default_templates();
    foreach ($templates as [$key, $name, $module, $subject, $body]) {
        if (!$db->value('SELECT id FROM email_templates WHERE tkey = ?', [$key])) {
            $db->insert('email_templates', ['tkey' => $key, 'name' => $name, 'module' => $module, 'subject' => $subject, 'body' => $body, 'updated_at' => $now]);
        }
    }

    $email = (string) ($opts['notify_email'] ?? '');
    $defaults = [
        'company_name' => (string) ($opts['company_name'] ?? 'Automate Limited'),
        'timezone' => 'Asia/Karachi',
        'default_currency' => 'PKR',
        'hr_email' => $email,
        'sales_email' => $email,
        'mail_transport' => 'mail',
        'mail_from_email' => $email,
        'mail_from_name' => (string) ($opts['company_name'] ?? 'Automate Limited'),
        'smtp_host' => 'smtp.hostinger.com',
        'smtp_port' => '465',
        'smtp_encryption' => 'ssl',
        'smtp_username' => $email,
        'smtp_password' => '',
        'notify_new_application' => '1',
        'notify_new_inquiry' => '1',
        'autoreply_application' => '1',
        'autoreply_inquiry' => '1',
        'upload_max_mb' => '8',
        'lead_services' => "Odoo ERP\nWebsite development\nSEO\nDigital marketing\nGraphic design\nCustom solutions",
        'privacy_email' => $email,
    ];
    foreach ($defaults as $k => $v) {
        $db->run('INSERT IGNORE INTO settings (skey, svalue) VALUES (?, ?)', [$k, $v]);
    }

    if (!$db->value("SELECT id FROM jobs WHERE slug = 'open-application'")) {
        $db->insert('jobs', [
            'title' => 'Open application',
            'slug' => 'open-application',
            'department' => 'Any team',
            'location' => 'Pakistan',
            'employment_type' => 'full_time',
            'workplace' => 'hybrid',
            'summary' => 'Don\'t see the right role? Send your CV and tell us what you\'d like to work on. We look here first when a new role opens.',
            'description' => "We hire Odoo consultants and developers, web developers, designers and marketers.\n\nTell us which kind of work you want to do, and what you've done before. When a role opens that fits, we'll contact you.",
            'status' => 'open',
            'listed' => 0,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

/** Built-in email templates, keyed by tkey. */
function default_templates(): array
{
    return [
        'application_received' => ['application_received', 'Application received', 'ats', 'We received your application for {{job_title}}',
            "Hi {{candidate_first_name}},\n\nThank you for applying for the {{job_title}} role at {{company_name}}. Your application has reached our hiring team.\n\nWe read every application. If your experience matches what the role needs, we'll contact you about next steps.\n\nKind regards,\n{{company_name}}"],
        'interview_invite' => ['interview_invite', 'Interview invitation', 'ats', 'Interview for {{job_title}}: {{interview_date}}',
            "Hi {{candidate_first_name}},\n\nWe'd like to invite you to an interview for the {{job_title}} role.\n\nWhen: {{interview_date}} at {{interview_time}}\nLength: {{interview_duration}}\nFormat: {{interview_type}}\nWhere: {{interview_location}}\n\nA calendar invitation is attached. If the time doesn't work, reply to this email and we'll find another.\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'interview_update' => ['interview_update', 'Interview changed', 'ats', 'Updated interview details for {{job_title}}',
            "Hi {{candidate_first_name}},\n\nThe details of your interview for the {{job_title}} role have changed.\n\nWhen: {{interview_date}} at {{interview_time}}\nLength: {{interview_duration}}\nFormat: {{interview_type}}\nWhere: {{interview_location}}\n\nAn updated calendar invitation is attached.\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'application_rejected' => ['application_rejected', 'Not moving forward', 'ats', 'Your application for {{job_title}}',
            "Hi {{candidate_first_name}},\n\nThank you for your interest in the {{job_title}} role and for the time you put into your application.\n\nAfter careful consideration, we won't be moving forward with your application for this role. We'll keep your details on file, and you're welcome to apply for future openings at {{careers_url}}.\n\nWe wish you the best in your search.\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'offer' => ['offer', 'Offer', 'ats', 'An offer for the {{job_title}} role',
            "Hi {{candidate_first_name}},\n\nWe're pleased to offer you the {{job_title}} role at {{company_name}}.\n\nThe full offer letter follows separately. Reply to this email if you have any questions.\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'candidate_blank' => ['candidate_blank', 'Blank email to candidate', 'ats', '{{job_title}} at {{company_name}}',
            "Hi {{candidate_first_name}},\n\n\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'inquiry_received' => ['inquiry_received', 'Enquiry received', 'crm', 'Thanks for getting in touch with {{company_name}}',
            "Hi {{contact_first_name}},\n\nThanks for your message. It has reached our team and someone will reply personally.\n\nIf you have anything to add, such as documents, screenshots or the version of Odoo you're on, just reply to this email.\n\nKind regards,\n{{company_name}}"],
        'lead_follow_up' => ['lead_follow_up', 'Follow-up', 'crm', 'Following up on your enquiry',
            "Hi {{contact_first_name}},\n\nI'm following up on your enquiry about {{service}}. Would a short call this week be useful to go through what you need?\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'lead_blank' => ['lead_blank', 'Blank email to contact', 'crm', 'Your enquiry with {{company_name}}',
            "Hi {{contact_first_name}},\n\n\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'application_on_hold' => ['application_on_hold', 'Keeping you in mind', 'ats', 'Your application for {{job_title}}',
            "Hi {{candidate_first_name}},\n\nThank you for your time on the {{job_title}} role. We were impressed, but we can't move forward right now.\n\nWe'd like to keep your details on file and get in touch when a role that fits comes up, rather than close your application. If you'd prefer we didn't, just reply and let us know.\n\nKind regards,\n{{sender_name}}\n{{company_name}}"],
        'payslip' => ['payslip', 'Payslip', 'hr', 'Your payslip for {{period}}',
            "Hi {{employee_first_name}},\n\nYour payslip for {{period}} is below.\n\nBasic salary: {{basic}}\nAllowances: {{allowances}}\nDeductions: {{deductions}}\nNet pay: {{net}}\n\nPay date: {{pay_date}}\n\nIf anything looks wrong, reply to this email.\n\n{{company_name}}"],
    ];
}
