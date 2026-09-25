<?php
/*
 * CV and attachment uploads. Files are checked by extension AND by their
 * first bytes, renamed to random names, and stored outside the web root when
 * the host allows it (otherwise in app/storage, which the web server refuses
 * to serve). They are only ever sent back through /admin/files/{id}, which
 * requires a signed-in user.
 */
declare(strict_types=1);

const UPLOAD_TYPES = [
    'pdf' => ['mime' => 'application/pdf', 'magic' => ['%PDF-']],
    'docx' => ['mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'magic' => ["PK\x03\x04"]],
    'doc' => ['mime' => 'application/msword', 'magic' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"]],
    'rtf' => ['mime' => 'application/rtf', 'magic' => ['{\\rtf']],
    'png' => ['mime' => 'image/png', 'magic' => ["\x89PNG"]],
    'jpg' => ['mime' => 'image/jpeg', 'magic' => ["\xFF\xD8\xFF"]],
    'jpeg' => ['mime' => 'image/jpeg', 'magic' => ["\xFF\xD8\xFF"]],
    'xlsx' => ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'magic' => ["PK\x03\x04"]],
    'pptx' => ['mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'magic' => ["PK\x03\x04"]],
    'zip' => ['mime' => 'application/zip', 'magic' => ["PK\x03\x04"]],
];

const RESUME_EXTENSIONS = ['pdf', 'doc', 'docx', 'rtf'];
const ATTACHMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'rtf', 'png', 'jpg', 'jpeg', 'xlsx', 'pptx', 'zip'];

function upload_max_bytes(): int
{
    return max(1, (int) setting('upload_max_mb', '8')) * 1024 * 1024;
}

function upload_dir(): string
{
    $dir = storage_path('uploads');
    if (!@is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $guard = $dir . '/.htaccess';
    if (!@is_file($guard)) {
        @file_put_contents($guard, "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }
    return $dir;
}

/** Whether a field actually carries a file (as opposed to being left empty). */
function has_upload(string $field): bool
{
    return isset($_FILES[$field]) && is_array($_FILES[$field]) && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

/**
 * Validate and move an uploaded file.
 * @return array{ok:bool,error?:string,original?:string,stored?:string,mime?:string,size?:int}
 */
function store_upload(string $field, array $allowed = RESUME_EXTENSIONS): array
{
    if (!has_upload($field)) {
        return ['ok' => false, 'error' => 'Choose a file to upload.'];
    }
    $f = $_FILES[$field];
    if (is_array($f['name'])) {
        return ['ok' => false, 'error' => 'Upload one file at a time.'];
    }
    $err = (int) $f['error'];
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'error' => 'That file is too large. The limit is ' . (upload_max_bytes() / 1048576) . ' MB.'];
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $f['tmp_name'])) {
        return ['ok' => false, 'error' => 'The upload didn\'t complete. Please try again.'];
    }
    $size = (int) $f['size'];
    if ($size <= 0) {
        return ['ok' => false, 'error' => 'That file is empty.'];
    }
    if ($size > upload_max_bytes()) {
        return ['ok' => false, 'error' => 'That file is too large. The limit is ' . (upload_max_bytes() / 1048576) . ' MB.'];
    }
    $original = basename(str_replace('\\', '/', (string) $f['name']));
    $original = (string) preg_replace('/[^\p{L}\p{N}\s._()\-]+/u', '_', $original);
    $original = mb_substr($original, -150) ?: 'file';
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true) || !isset(UPLOAD_TYPES[$ext])) {
        return ['ok' => false, 'error' => 'Please upload a ' . strtoupper(implode(', ', array_slice($allowed, 0, -1))) . ' or ' . strtoupper((string) end($allowed)) . ' file.'];
    }
    $head = (string) @file_get_contents((string) $f['tmp_name'], false, null, 0, 16);
    $matches = false;
    foreach (UPLOAD_TYPES[$ext]['magic'] as $magic) {
        if (str_starts_with($head, $magic)) {
            $matches = true;
            break;
        }
    }
    if (!$matches) {
        return ['ok' => false, 'error' => 'That file doesn\'t look like a real ' . strtoupper($ext) . '. Please export it again and retry.'];
    }
    $stored = date('Y/m/') . bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = upload_dir() . '/' . $stored;
    if (!@is_dir(dirname($dest)) && !@mkdir(dirname($dest), 0750, true)) {
        return ['ok' => false, 'error' => 'The server could not save the file. Please email it to us instead.'];
    }
    if (!move_uploaded_file((string) $f['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'The server could not save the file. Please email it to us instead.'];
    }
    @chmod($dest, 0640);
    return ['ok' => true, 'original' => $original, 'stored' => $stored, 'mime' => UPLOAD_TYPES[$ext]['mime'], 'size' => $size];
}

function file_record(string $entityType, int $entityId, array $upload, string $kind = 'attachment'): int
{
    return db()->insert('files', [
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'kind' => $kind,
        'original_name' => $upload['original'],
        'stored_name' => $upload['stored'],
        'mime' => $upload['mime'],
        'size' => $upload['size'],
        'uploaded_by' => auth_id(),
        'created_at' => now(),
    ]);
}

function file_path_for(array $file): string
{
    $stored = str_replace(['..', '\\'], '', (string) $file['stored_name']);
    return upload_dir() . '/' . ltrim($stored, '/');
}

function delete_file_record(array $file): void
{
    @unlink(file_path_for($file));
    db()->delete('files', 'id = ?', [(int) $file['id']]);
}

function human_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    }
    return max(1, (int) round($bytes / 1024)) . ' KB';
}
