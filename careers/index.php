<?php
/*
 * Careers. /careers/ lists open roles; /careers/{slug} shows one role and its
 * application form. Applications land in the ATS at the first pipeline stage,
 * the hiring inbox is notified and the candidate gets an acknowledgement.
 * .htaccess rewrites /careers/{slug} to this file with ?job={slug}.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$slug = trim((string) ($_GET['job'] ?? ''), '/');
if ($slug !== '' && !preg_match('/^[a-z0-9-]{1,190}$/', $slug)) {
    $slug = '#invalid';
}

/* ------------------------------------------------------------ list */
if ($slug === '') {
    $jobs = [];
    $openApp = null;
    if (app_installed()) {
        $jobs = public_jobs();
        $openApp = db()->one("SELECT slug FROM jobs WHERE slug = 'open-application' AND status = 'open'");
    }
    render('site/careers-list', ['jobs' => $jobs, 'openApp' => $openApp]);
    exit;
}

$job = app_installed() ? db()->one('SELECT * FROM jobs WHERE slug = ?', [$slug]) : null;
if (!$job) {
    http_response_code(404);
    require APP_ROOT . '/404.php';
    exit;
}
$isOpen = $job['status'] === 'open' && ($job['closes_at'] === null || $job['closes_at'] >= today());
$questions = db()->all('SELECT * FROM job_questions WHERE job_id = ? ORDER BY sort_order, id', [(int) $job['id']]);

if (!$isOpen) {
    render('site/careers-closed', ['job' => $job]);
    exit;
}

if (input('applied') === '1' && !is_post()) {
    render('site/careers-done', ['job' => $job]);
    exit;
}

/* ------------------------------------------------------------ apply */
$errors = [];
$values = [];
if (is_post()) {
    $fields = ['first_name', 'last_name', 'email', 'phone', 'location', 'current_title', 'current_company', 'experience_years',
        'expected_salary', 'notice_period', 'linkedin_url', 'portfolio_url', 'cover_letter'];
    foreach ($fields as $f) {
        $values[$f] = input($f);
    }
    $values['email'] = strtolower($values['email']);
    $values['linkedin_url'] = $values['linkedin_url'] !== '' ? normalise_url($values['linkedin_url']) : '';
    $values['portfolio_url'] = $values['portfolio_url'] !== '' ? normalise_url($values['portfolio_url']) : '';
    $answersIn = input_array('q');
    $values['q'] = $answersIn;

    $done = static function () use ($job): never {
        redirect(url('careers/' . $job['slug']) . '?applied=1', 303);
    };
    if (input('website') !== '') {
        $done();
    }
    $token = form_token_problem(input('_t'));
    if ($token === 'too_fast') {
        $done();
    }
    if ($token === 'expired') {
        $errors['form'] = 'This page was open for a long time. Please check your details, attach your CV again and resubmit.';
    }

    $need = ['first_name' => 'Enter your first name.', 'last_name' => 'Enter your last name.', 'phone' => 'Enter a phone number we can reach you on.'];
    foreach ($need as $f => $msg) {
        if ($values[$f] === '') {
            $errors[$f] = $msg;
        }
    }
    foreach (['first_name' => 80, 'last_name' => 80, 'phone' => 40, 'location' => 120, 'current_title' => 120, 'current_company' => 120, 'expected_salary' => 80, 'notice_period' => 80] as $f => $max) {
        if (mb_strlen($values[$f]) > $max) {
            $errors[$f] = 'Please shorten this to ' . $max . ' characters.';
        }
    }
    if (!valid_email($values['email'])) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($values['experience_years'] !== '' && (!is_numeric($values['experience_years']) || (float) $values['experience_years'] < 0 || (float) $values['experience_years'] > 60)) {
        $errors['experience_years'] = 'Enter a number of years, for example 3.';
    }
    foreach (['linkedin_url', 'portfolio_url'] as $f) {
        if (!valid_url($values[$f]) || mb_strlen($values[$f]) > 255) {
            $errors[$f] = 'Enter a full web address, for example https://linkedin.com/in/yourname';
        }
    }
    if (mb_strlen($values['cover_letter']) > 8000) {
        $errors['cover_letter'] = 'Please keep this under 8,000 characters.';
    }
    $answers = [];
    foreach ($questions as $q) {
        $qid = (int) $q['id'];
        $a = trim((string) (is_array($answersIn[$qid] ?? null) ? '' : ($answersIn[$qid] ?? '')));
        if ($a === '' && (int) $q['required']) {
            $errors['q' . $qid] = 'Please answer this question.';
            continue;
        }
        if ($a !== '') {
            if ($q['qtype'] === 'yesno' && !in_array($a, ['Yes', 'No'], true)) {
                $errors['q' . $qid] = 'Choose yes or no.';
            } elseif ($q['qtype'] === 'select' && !in_array($a, array_map('trim', explode("\n", (string) $q['options'])), true)) {
                $errors['q' . $qid] = 'Choose one of the options.';
            } elseif ($q['qtype'] === 'number' && !is_numeric($a)) {
                $errors['q' . $qid] = 'Enter a number.';
            } elseif (mb_strlen($a) > 4000) {
                $errors['q' . $qid] = 'Please keep this under 4,000 characters.';
            }
        }
        $answers[] = ['question_id' => $qid, 'question' => $q['question'], 'answer' => $a];
    }
    if (input('consent') !== '1') {
        $errors['consent'] = 'Please confirm you\'re happy for us to keep your details for this application.';
    }
    if (!has_upload('resume')) {
        $errors['resume'] = 'Please attach your CV.';
    }
    if (!$errors && !throttle('apply|' . client_ip(), 10, 3600)) {
        $errors['form'] = 'We\'ve received several applications from you in a short time. Please wait a while and try again.';
    }

    $upload = null;
    if (!$errors) {
        $upload = store_upload('resume', RESUME_EXTENSIONS);
        if (!$upload['ok']) {
            $errors['resume'] = $upload['error'];
            $upload = null;
        }
    }

    if (!$errors && $upload) {
        $now = now();
        $nul = static fn (string $v): ?string => $v === '' ? null : $v;
        try {
            $appId = db()->tx(static function (Db $db) use ($job, $values, $answers, $upload, $now, $nul): int {
                $profile = [
                    'first_name' => $values['first_name'],
                    'last_name' => $values['last_name'],
                    'phone' => $nul($values['phone']),
                    'location' => $nul($values['location']),
                    'current_title' => $nul($values['current_title']),
                    'current_company' => $nul($values['current_company']),
                    'experience_years' => $values['experience_years'] === '' ? null : round((float) $values['experience_years'], 1),
                    'expected_salary' => $nul($values['expected_salary']),
                    'notice_period' => $nul($values['notice_period']),
                    'linkedin_url' => $nul($values['linkedin_url']),
                    'portfolio_url' => $nul($values['portfolio_url']),
                    'updated_at' => $now,
                ];
                $cand = $db->one('SELECT id FROM candidates WHERE email = ? ORDER BY id DESC LIMIT 1', [$values['email']]);
                if ($cand) {
                    $candId = (int) $cand['id'];
                    // keep what we already know when a field is left blank this time
                    $db->update('candidates', array_filter($profile, static fn ($v) => $v !== null), 'id = ?', [$candId]);
                } else {
                    $candId = $db->insert('candidates', $profile + ['email' => $values['email'], 'source' => 'careers', 'created_at' => $now]);
                }
                $fileId = $db->insert('files', [
                    'entity_type' => 'candidate', 'entity_id' => $candId, 'kind' => 'resume',
                    'original_name' => $upload['original'], 'stored_name' => $upload['stored'], 'mime' => $upload['mime'],
                    'size' => $upload['size'], 'uploaded_by' => null, 'created_at' => $now,
                ]);

                $existing = $db->one('SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?', [(int) $job['id'], $candId]);
                if ($existing) {
                    $appId = (int) $existing['id'];
                    $db->update('applications', ['resume_file_id' => $fileId, 'cover_letter' => $nul($values['cover_letter']), 'updated_at' => $now], 'id = ?', [$appId]);
                    $db->delete('application_answers', 'application_id = ?', [$appId]);
                    log_activity('application', $appId, 'system', 'Candidate sent an updated application', '', [], null);
                } else {
                    $appId = $db->insert('applications', [
                        'job_id' => (int) $job['id'],
                        'candidate_id' => $candId,
                        'stage_id' => first_stage_id(ats_stages(), 'active'),
                        'status' => 'active',
                        'cover_letter' => $nul($values['cover_letter']),
                        'resume_file_id' => $fileId,
                        'source' => 'careers',
                        'owner_id' => $job['hiring_manager_id'] ?: null,
                        'applied_at' => $now,
                        'stage_changed_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    log_activity('application', $appId, 'created', 'Applied through the careers page', '', [], null);
                }
                foreach ($answers as $a) {
                    $db->insert('application_answers', ['application_id' => $appId] + $a);
                }
                return $appId;
            });
        } catch (Throwable $e) {
            @unlink(upload_dir() . '/' . $upload['stored']);
            throw $e;
        }

        $app = application_full($appId);
        if ($app && setting('notify_new_application', '1') === '1') {
            $to = (string) setting('hr_email', '');
            if ($job['hiring_manager_id']) {
                $hm = db()->value('SELECT email FROM users WHERE id = ? AND is_active = 1', [(int) $job['hiring_manager_id']]);
                if ($hm && !str_contains($to, (string) $hm)) {
                    $to .= ($to !== '' ? ',' : '') . $hm;
                }
            }
            $body = 'New application for ' . $job['title'] . ".\n\n"
                . 'Name: ' . candidate_name($app) . "\n"
                . 'Email: ' . $app['email'] . "\n"
                . 'Phone: ' . $app['phone'] . "\n"
                . ($app['location'] ? 'Location: ' . $app['location'] . "\n" : '')
                . ($app['current_title'] ? 'Current role: ' . $app['current_title'] . ($app['current_company'] ? ' at ' . $app['current_company'] : '') . "\n" : '')
                . "\nReview it in the ATS: " . site_origin() . admin_url('applications/' . $appId);
            notify_team($to, 'New application: ' . candidate_name($app) . ' for ' . $job['title'], $body, ['reply_to' => $app['email']]);
        }
        if ($app && setting('autoreply_application', '1') === '1') {
            send_template('application_received', $app['email'], candidate_vars($app), [
                'to_name' => candidate_name($app),
                'reply_to' => (string) (explode(',', (string) setting('hr_email', ''))[0] ?? ''),
            ]);
        }
        redirect(url('careers/' . $job['slug']) . '?applied=1', 303);
    }
}

render('site/careers-job', ['job' => $job, 'questions' => $questions, 'errors' => $errors, 'values' => $values]);
