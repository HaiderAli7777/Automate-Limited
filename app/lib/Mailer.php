<?php
/*
 * Outgoing email. Uses SMTP when it is configured in Settings (Hostinger:
 * smtp.hostinger.com, port 465, SSL, the mailbox address and its password),
 * otherwise PHP's mail(). Messages go out as text + HTML, with optional
 * attachments (interview invitations carry an .ics calendar file).
 */
declare(strict_types=1);

final class Mailer
{
    public static string $lastError = '';

    /**
     * @param array{html?:string,reply_to?:string,to_name?:string,attachments?:array<int,array{name:string,content:string,mime:string}>} $opts
     */
    public static function send(string $to, string $subject, string $text, array $opts = []): bool
    {
        self::$lastError = '';
        $to = trim($to);
        if (!valid_email($to)) {
            self::$lastError = 'Invalid recipient address.';
            return false;
        }
        $fromEmail = (string) setting('mail_from_email', '');
        if (!valid_email($fromEmail)) {
            $host = preg_replace('/^www\./', '', (string) parse_url(site_origin(), PHP_URL_HOST));
            $fromEmail = 'no-reply@' . ($host ?: 'automateltd.com');
        }
        $fromName = (string) setting('mail_from_name', company_name());
        $html = $opts['html'] ?? self::htmlFromText($text);

        [$headers, $body] = self::build($fromEmail, $fromName, $to, (string) ($opts['to_name'] ?? ''), $subject, $text, $html, $opts);

        try {
            if (setting('mail_transport', 'mail') === 'smtp' && setting('smtp_host', '') !== '') {
                self::smtp($fromEmail, $to, $headers, $body);
            } else {
                $headerLines = $headers;
                unset($headerLines['To'], $headerLines['Subject']);
                $flat = '';
                foreach ($headerLines as $k => $v) {
                    $flat .= $k . ': ' . $v . "\r\n";
                }
                $ok = @mail($to, $headers['Subject'], $body, rtrim($flat), '-f' . $fromEmail);
                if (!$ok) {
                    throw new RuntimeException('mail() returned false. Configure SMTP in Settings.');
                }
            }
            return true;
        } catch (Throwable $e) {
            self::$lastError = $e->getMessage();
            error_log('[automate] mail to ' . $to . ' failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function htmlFromText(string $text): string
    {
        $paras = preg_split("/\n{2,}/", trim(str_replace("\r\n", "\n", $text))) ?: [];
        $body = '';
        foreach ($paras as $p) {
            $p = e($p);
            $p = (string) preg_replace('~(https?://[^\s<]+)~', '<a href="$1" style="color:#0072AE">$1</a>', $p);
            $body .= '<p style="margin:0 0 16px">' . nl2br($p, false) . '</p>';
        }
        $company = e(company_name());
        return '<!doctype html><html><body style="margin:0;background:#F4F7FB;padding:24px 12px">'
            . '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:14px;padding:32px 28px;'
            . 'font:15px/1.6 -apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#0B2240">'
            . $body
            . '</div><p style="max-width:600px;margin:14px auto 0;font:12px/1.5 -apple-system,Segoe UI,Helvetica,Arial,sans-serif;color:#5E7189;text-align:center">'
            . $company . '</p></body></html>';
    }

    private static function encodeHeader(string $v): string
    {
        return preg_match('/[^\x20-\x7E]/', $v) ? '=?UTF-8?B?' . base64_encode($v) . '?=' : $v;
    }

    private static function address(string $email, string $name): string
    {
        $name = trim(str_replace(["\r", "\n", '"'], '', $name));
        return $name === '' ? $email : self::encodeHeader($name) . ' <' . $email . '>';
    }

    private static function build(string $fromEmail, string $fromName, string $to, string $toName, string $subject, string $text, string $html, array $opts): array
    {
        $subject = str_replace(["\r", "\n"], ' ', $subject);
        $domain = substr(strrchr($fromEmail, '@') ?: '@localhost', 1);
        $headers = [
            'Date' => date('r'),
            'From' => self::address($fromEmail, $fromName),
            'To' => self::address($to, $toName),
            'Subject' => self::encodeHeader($subject),
            'Message-ID' => '<' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
            'MIME-Version' => '1.0',
        ];
        $replyTo = (string) ($opts['reply_to'] ?? '');
        if ($replyTo !== '' && valid_email($replyTo)) {
            $headers['Reply-To'] = $replyTo;
        }

        $alt = 'alt_' . bin2hex(random_bytes(8));
        $altBody = "--{$alt}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode(str_replace(["\r\n", "\n"], ["\n", "\r\n"], $text)) . "\r\n"
            . "--{$alt}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
            . quoted_printable_encode($html) . "\r\n"
            . "--{$alt}--\r\n";

        $attachments = $opts['attachments'] ?? [];
        if (!$attachments) {
            $headers['Content-Type'] = 'multipart/alternative; boundary="' . $alt . '"';
            return [$headers, $altBody];
        }
        $mixed = 'mix_' . bin2hex(random_bytes(8));
        $headers['Content-Type'] = 'multipart/mixed; boundary="' . $mixed . '"';
        $body = "--{$mixed}\r\nContent-Type: multipart/alternative; boundary=\"{$alt}\"\r\n\r\n" . $altBody;
        foreach ($attachments as $a) {
            $name = str_replace(['"', "\r", "\n"], '', (string) $a['name']);
            $body .= "--{$mixed}\r\n"
                . 'Content-Type: ' . $a['mime'] . '; name="' . $name . "\"\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . 'Content-Disposition: attachment; filename="' . $name . "\"\r\n\r\n"
                . chunk_split(base64_encode((string) $a['content']), 76, "\r\n");
        }
        $body .= "--{$mixed}--\r\n";
        return [$headers, $body];
    }

    private static function smtp(string $from, string $to, array $headers, string $body): void
    {
        $host = (string) setting('smtp_host');
        $port = (int) setting('smtp_port', '465');
        $enc = (string) setting('smtp_encryption', 'ssl');
        $user = (string) setting('smtp_username', '');
        $pass = decrypt_secret((string) setting('smtp_password', ''));

        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host, 'SNI_enabled' => true]]);
        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            throw new RuntimeException("Could not connect to {$host}:{$port} ({$errstr})");
        }
        stream_set_timeout($fp, 20);
        try {
            self::expect($fp, [220]);
            $ehloHost = preg_replace('/[^a-z0-9.\-]/i', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
            self::cmd($fp, 'EHLO ' . $ehloHost, [250]);
            if ($enc === 'tls') {
                self::cmd($fp, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('STARTTLS negotiation failed.');
                }
                self::cmd($fp, 'EHLO ' . $ehloHost, [250]);
            }
            if ($user !== '') {
                self::cmd($fp, 'AUTH LOGIN', [334]);
                self::cmd($fp, base64_encode($user), [334]);
                self::cmd($fp, base64_encode($pass), [235], 'Authentication failed. Check the SMTP username and password.');
            }
            self::cmd($fp, 'MAIL FROM:<' . $from . '>', [250]);
            self::cmd($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::cmd($fp, 'DATA', [354]);
            $data = '';
            foreach ($headers as $k => $v) {
                $data .= $k . ': ' . $v . "\r\n";
            }
            $data .= "\r\n" . $body;
            $data = (string) preg_replace('/^\./m', '..', str_replace(["\r\n", "\n"], ["\n", "\r\n"], $data));
            fwrite($fp, $data . "\r\n.\r\n");
            self::expect($fp, [250]);
            fwrite($fp, "QUIT\r\n");
        } finally {
            fclose($fp);
        }
    }

    private static function cmd($fp, string $line, array $ok, string $failMessage = ''): string
    {
        fwrite($fp, $line . "\r\n");
        return self::expect($fp, $ok, $failMessage);
    }

    private static function expect($fp, array $ok, string $failMessage = ''): string
    {
        $resp = '';
        while (($line = fgets($fp, 1024)) !== false) {
            $resp .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($resp, 0, 3);
        if (!in_array($code, $ok, true)) {
            throw new RuntimeException($failMessage !== '' ? $failMessage : 'SMTP error: ' . trim($resp));
        }
        return $resp;
    }
}

/** An .ics calendar file for an interview. Times are sent in UTC. */
function build_ics(array $ev): string
{
    $fmt = static fn (int $t): string => gmdate('Ymd\THis\Z', $t);
    $esc = static fn (string $s): string => str_replace(["\\", ';', ',', "\r\n", "\n"], ["\\\\", '\;', '\,', '\n', '\n'], $s);
    $start = (int) $ev['start'];
    $end = (int) $ev['end'];
    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Automate Limited//Hiring//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . $ev['uid'],
        'DTSTAMP:' . $fmt(time()),
        'DTSTART:' . $fmt($start),
        'DTEND:' . $fmt($end),
        'SUMMARY:' . $esc((string) $ev['summary']),
        'DESCRIPTION:' . $esc((string) ($ev['description'] ?? '')),
        'LOCATION:' . $esc((string) ($ev['location'] ?? '')),
        'STATUS:' . (($ev['cancelled'] ?? false) ? 'CANCELLED' : 'CONFIRMED'),
        'SEQUENCE:' . (int) ($ev['sequence'] ?? 0),
        'END:VEVENT',
        'END:VCALENDAR',
    ];
    $out = '';
    foreach ($lines as $line) {
        // fold long lines at 74 octets
        while (strlen($line) > 74) {
            $cut = 74;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $out .= substr($line, 0, $cut) . "\r\n";
            $line = ' ' . substr($line, $cut);
        }
        $out .= $line . "\r\n";
    }
    return $out;
}
