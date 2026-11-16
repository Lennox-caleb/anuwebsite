<?php
/**
 * Outgoing email for the newsletter and test emails.
 *
 * Settings come from config/mail.php (copy includes/mail.sample.php there and
 * fill it in). That file is created by the site owner on the server and is
 * never part of any update package, because it holds a password. Without it
 * this falls back to PHP's mail(), which needs a mail program on the server.
 *
 * No external libraries: a small SMTP client (AUTH LOGIN/PLAIN, STARTTLS or
 * SSL) is built in.
 */

if (!function_exists('anu_mail_config')) {

    /** Mail settings, or [] when config/mail.php is missing/invalid. */
    function anu_mail_config(): array
    {
        static $cfg = null;
        if ($cfg !== null) return $cfg;
        $cfg = [];
        $file = __DIR__ . '/../config/mail.php';
        if (is_file($file)) {
            $c = include $file;
            if (is_array($c)) $cfg = $c;
        }
        return $cfg;
    }

    /** 'smtp' | 'mail' */
    function anu_mail_driver(): string
    {
        $c = anu_mail_config();
        if (($c['driver'] ?? '') === 'smtp' && !empty($c['host'])) return 'smtp';
        return 'mail';
    }

    function anu_mail_from(): array
    {
        $c = anu_mail_config();
        $email = trim((string)($c['from_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = trim(setting('email_marketing')) ?: 'no-reply@localhost';
        }
        $name = trim((string)($c['from_name'] ?? '')) ?: 'Africa Nazarene University';
        return [$email, $name];
    }

    function anu_mail_clean(string $v): string
    {
        return trim(str_replace(["\r", "\n", "\0"], ' ', $v));
    }

    function anu_mail_encode_header(string $v): string
    {
        $v = anu_mail_clean($v);
        return preg_match('/[^\x20-\x7E]/', $v) ? '=?UTF-8?B?' . base64_encode($v) . '?=' : $v;
    }

    function anu_mail_address(string $email, string $name = ''): string
    {
        $email = anu_mail_clean($email);
        $name = anu_mail_clean($name);
        if ($name === '') return '<' . $email . '>';
        return anu_mail_encode_header(str_replace('"', '', $name)) . ' <' . $email . '>';
    }

    /**
     * Build the full message (headers + multipart/alternative body).
     * Returns [headersString, bodyString] (CRLF line endings).
     */
    function anu_mail_build(string $to, string $toName, string $subject, string $html, string $text, array $extra = [], array $inline = []): array
    {
        [$fromEmail, $fromName] = anu_mail_from();
        $c = anu_mail_config();
        $domain = substr(strrchr($fromEmail, '@') ?: '@localhost', 1);
        $boundary = 'anu_' . bin2hex(random_bytes(12));

        $h = [];
        $h[] = 'Date: ' . date('r');
        $h[] = 'From: ' . anu_mail_address($fromEmail, $fromName);
        $h[] = 'To: ' . anu_mail_address($to, $toName);
        $reply = trim((string)($c['reply_to'] ?? ''));
        if ($reply !== '' && filter_var($reply, FILTER_VALIDATE_EMAIL)) $h[] = 'Reply-To: <' . anu_mail_clean($reply) . '>';
        $h[] = 'Subject: ' . anu_mail_encode_header($subject);
        $h[] = 'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>';
        $h[] = 'MIME-Version: 1.0';
        foreach ($extra as $k => $v) $h[] = anu_mail_clean($k) . ': ' . anu_mail_clean($v);
        $alt  = '--' . $boundary . "\r\n";
        $alt .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $alt .= chunk_split(base64_encode($text), 76, "\r\n");
        $alt .= '--' . $boundary . "\r\n";
        $alt .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $alt .= chunk_split(base64_encode($html), 76, "\r\n");
        $alt .= '--' . $boundary . "--\r\n";

        $files = [];
        foreach ($inline as $img) {
            $data = is_file($img['path'] ?? '') ? file_get_contents($img['path']) : false;
            if ($data !== false) $files[] = [$img, $data];
        }
        if (!$files) {
            $h[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $body = $alt;
        } else {
            // images that belong to the message itself (logo, pictures from the editor)
            $rel = 'anu_rel_' . bin2hex(random_bytes(10));
            $h[] = 'Content-Type: multipart/related; type="multipart/alternative"; boundary="' . $rel . '"';
            $body  = '--' . $rel . "\r\n";
            $body .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n\r\n";
            $body .= $alt . "\r\n";
            foreach ($files as [$img, $data]) {
                $name = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)($img['name'] ?? 'image'));
                $body .= '--' . $rel . "\r\n";
                $body .= 'Content-Type: ' . anu_mail_clean((string)($img['mime'] ?? 'application/octet-stream')) . '; name="' . $name . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= 'Content-ID: <' . anu_mail_clean((string)$img['cid']) . ">\r\n";
                $body .= 'Content-Disposition: inline; filename="' . $name . "\"\r\n\r\n";
                $body .= chunk_split(base64_encode($data), 76, "\r\n");
            }
            $body .= '--' . $rel . "--\r\n";
        }
        return [implode("\r\n", $h), $body];
    }

    /** Minimal SMTP client. One instance can send many messages over one connection. */
    class AnuSmtp
    {
        private $fp = null;
        private array $cfg;
        public string $error = '';

        public function __construct(array $cfg) { $this->cfg = $cfg; }

        private function read(): string
        {
            $data = '';
            while (($line = fgets($this->fp, 1024)) !== false) {
                $data .= $line;
                if (strlen($line) < 4 || $line[3] !== '-') break;   // last line of a reply has a space after the code
            }
            return $data;
        }

        private function cmd(string $line, array $okCodes): bool
        {
            if ($line !== '') fwrite($this->fp, $line . "\r\n");
            $reply = $this->read();
            $code = (int)substr($reply, 0, 3);
            if (!in_array($code, $okCodes, true)) {
                $this->error = trim(preg_replace('/\s+/', ' ', $reply)) ?: 'No reply from mail server';
                return false;
            }
            return true;
        }

        public function connect(): bool
        {
            $host   = (string)$this->cfg['host'];
            $port   = (int)($this->cfg['port'] ?? 587);
            $secure = strtolower((string)($this->cfg['secure'] ?? ''));
            $target = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
            $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
            $this->fp = @stream_socket_client($target, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
            if (!$this->fp) { $this->error = "Could not connect to $host:$port ($errstr)"; return false; }
            stream_set_timeout($this->fp, 20);
            if (!$this->cmd('', [220])) return false;
            $ehlo = 'EHLO ' . (parse_url(defined('SITE_URL') ? SITE_URL : '', PHP_URL_HOST) ?: 'localhost');
            if (!$this->cmd($ehlo, [250])) return false;
            if ($secure === 'tls') {
                if (!$this->cmd('STARTTLS', [220])) return false;
                if (!@stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { $this->error = 'Could not start a secure (TLS) connection'; return false; }
                if (!$this->cmd($ehlo, [250])) return false;
            }
            $user = (string)($this->cfg['username'] ?? '');
            if ($user !== '') {
                $pass = (string)($this->cfg['password'] ?? '');
                fwrite($this->fp, "AUTH LOGIN\r\n");
                if ((int)substr($this->read(), 0, 3) !== 334) { $this->error = 'Mail server does not accept login'; return false; }
                fwrite($this->fp, base64_encode($user) . "\r\n");
                if ((int)substr($this->read(), 0, 3) !== 334) { $this->error = 'Mail login rejected'; return false; }
                fwrite($this->fp, base64_encode($pass) . "\r\n");
                $r = $this->read();
                if ((int)substr($r, 0, 3) !== 235) { $this->error = 'Mail login failed: ' . trim(preg_replace('/\s+/', ' ', $r)); return false; }
            }
            return true;
        }

        public function send(string $fromEmail, string $toEmail, string $headers, string $body): bool
        {
            if (!$this->fp) return false;
            if (!$this->cmd('RSET', [250])) return false;
            if (!$this->cmd('MAIL FROM:<' . anu_mail_clean($fromEmail) . '>', [250])) return false;
            if (!$this->cmd('RCPT TO:<' . anu_mail_clean($toEmail) . '>', [250, 251])) return false;
            if (!$this->cmd('DATA', [354])) return false;
            $msg = $headers . "\r\n\r\n" . $body;
            $msg = preg_replace('/\r\n|\r|\n/', "\r\n", $msg);
            $msg = preg_replace('/^\./m', '..', $msg);          // dot-stuffing
            fwrite($this->fp, $msg . "\r\n.\r\n");
            $r = $this->read();
            if ((int)substr($r, 0, 3) !== 250) { $this->error = trim(preg_replace('/\s+/', ' ', $r)) ?: 'Message not accepted'; return false; }
            return true;
        }

        public function close(): void
        {
            if ($this->fp) { @fwrite($this->fp, "QUIT\r\n"); @fclose($this->fp); $this->fp = null; }
        }
    }

    /**
     * Send many/one emails. $messages: list of ['to','name','subject','html','text','headers'=>[]].
     * Returns list of ['ok'=>bool,'error'=>string] in the same order.
     */
    function anu_mail_send_many(array $messages): array
    {
        $results = [];
        [$fromEmail] = anu_mail_from();
        if (anu_mail_driver() === 'smtp') {
            $smtp = new AnuSmtp(anu_mail_config());
            if (!$smtp->connect()) {
                $err = $smtp->error;
                $smtp->close();
                foreach ($messages as $m) $results[] = ['ok' => false, 'error' => $err];
                return $results;
            }
            foreach ($messages as $m) {
                [$hd, $bd] = anu_mail_build($m['to'], $m['name'] ?? '', $m['subject'], $m['html'], $m['text'], $m['headers'] ?? [], $m['inline'] ?? []);
                $ok = $smtp->send($fromEmail, $m['to'], $hd, $bd);
                if (!$ok && stripos($smtp->error, 'connect') === false && stripos($smtp->error, 'timed out') === false) {
                    // single recipient problem (bad address etc.): keep going
                }
                $results[] = ['ok' => $ok, 'error' => $ok ? '' : mb_substr($smtp->error, 0, 240)];
            }
            $smtp->close();
            return $results;
        }
        // PHP mail() fallback
        foreach ($messages as $m) {
            [$hd, $bd] = anu_mail_build($m['to'], $m['name'] ?? '', $m['subject'], $m['html'], $m['text'], $m['headers'] ?? [], $m['inline'] ?? []);
            // mail() adds To:/Subject: itself, so strip them from the extra headers.
            $hd = preg_replace('/^(To|Subject): .*\r\n?/mi', '', $hd);
            $ok = @mail(anu_mail_clean($m['to']), anu_mail_encode_header($m['subject']), $bd, $hd, '-f' . $fromEmail);
            $results[] = ['ok' => (bool)$ok, 'error' => $ok ? '' : 'PHP mail() could not send. This server probably has no mail program (e.g. postfix) set up -- create config/mail.php to send through your email provider instead.'];
        }
        return $results;
    }

    function anu_mail_send(string $to, string $name, string $subject, string $html, string $text, array $headers = [], array $inline = []): array
    {
        return anu_mail_send_many([['to' => $to, 'name' => $name, 'subject' => $subject, 'html' => $html, 'text' => $text, 'headers' => $headers, 'inline' => $inline]])[0];
    }
}
