<?php
/**
 * Shared hardening + performance helpers, included by the main site and by every
 * unit site's header (each unit site already requires main-site files the same way
 * it does for asset_version.php). Safe to include more than once.
 *
 *  - never prints PHP errors to visitors (config files enable it for local dev; this
 *    overrides it at runtime so the config files themselves stay untouched)
 *  - baseline security headers
 *  - adds loading="lazy" decoding="async" to images below the first few (page speed)
 */
if (defined('ANU_HARDENING_LOADED')) return;
define('ANU_HARDENING_LOADED', true);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

if (!function_exists('anu_lazy_images')) {
    /** Output filter: lazy-load every <img> except the first 3 (logo / above-the-fold) and hero images. */
    function anu_lazy_images(string $html): string
    {
        $n = 0;
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) use (&$n) {
            $tag = $m[0];
            $n++;
            if ($n <= 3 || preg_match('/\bloading\s*=/i', $tag) || preg_match('/hero|fetchpriority/i', $tag)) return $tag;
            return substr($tag, 0, 4) . ' loading="lazy" decoding="async"' . substr($tag, 4);
        }, $html) ?? $html;
    }
}

if (PHP_SAPI !== 'cli' && strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') === false
    && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'search-ajax.php') {
    ob_start(function (string $buf): string {
        foreach (headers_list() as $h) {
            if (stripos($h, 'content-type:') === 0 && stripos($h, 'text/html') === false) return $buf;
        }
        return stripos($buf, '<html') === false ? $buf : anu_lazy_images($buf);
    });
}

if (!function_exists('anu_contact_should_save')) {
    /**
     * Contact-form guard: false (silently skip saving) for bots that fill the hidden
     * "website" honeypot, and for the same email+message sent again within 10 minutes
     * (double-click / repeated submit) -- prevents duplicate rows in contact_messages.
     */
    function anu_contact_should_save(string $email, string $message): bool
    {
        if (!empty($_POST['website'])) return false;
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $key = hash('sha256', strtolower($email) . '|' . $message);
        $seen = $_SESSION['anu_contact_seen'] ?? [];
        $now = time();
        $seen = array_filter($seen, fn($t) => $now - $t < 600);
        if (isset($seen[$key])) { $_SESSION['anu_contact_seen'] = $seen; return false; }
        $seen[$key] = $now;
        $_SESSION['anu_contact_seen'] = $seen;
        return true;
    }
    /** Post/Redirect/Get: after a successful send, redirect so a browser refresh cannot re-submit the form. */
    function anu_prg_redirect(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['anu_sent'] = 1;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '', '?') . '?sent=1', true, 303);
        exit;
    }
    function anu_was_sent(): bool
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $ok = isset($_GET['sent']) && !empty($_SESSION['anu_sent']);
        if ($ok) unset($_SESSION['anu_sent']);
        return $ok;
    }
}

if (!function_exists('anu_seo_tags')) {
    /**
     * <link rel="canonical"> + Open Graph tags for unit sites. The URL is built from the CURRENT request
     * (scheme + host + path), so it is correct on whatever domain/sub-domain the site is served from -
     * nothing is guessed or hard-coded. Query strings are dropped (no duplicate-URL variants);
     * /index.php is canonicalised to the folder URL. Thank-you pages (?sent=1) are marked noindex.
     */
    function anu_seo_tags(string $title = '', string $description = '', string $image = ''): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!preg_match('/^[A-Za-z0-9.\-]+(:\d{1,5})?$/', $host)) return '';
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = preg_replace('#/index\.php$#', '/', $path);
        $url  = ($https ? 'https://' : 'http://') . $host . $path;
        // School message pages are told apart by their query (?who=dean / ?id=N), so keep it for those.
        if (preg_match('#/message(\.php)?$#', $path)) {
            if (($_GET['who'] ?? '') === 'dean') $url .= '?who=dean';
            elseif ((int)($_GET['id'] ?? 0) > 0) $url .= '?id=' . (int)$_GET['id'];
        }
        $e = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $out  = '<link rel="canonical" href="' . $e($url) . '">' . "\n";
        $out .= '<meta property="og:type" content="website"><meta property="og:site_name" content="Africa Nazarene University">' . "\n";
        $out .= '<meta property="og:url" content="' . $e($url) . '">' . "\n";
        if ($title !== '')       $out .= '<meta property="og:title" content="' . $e($title) . '">' . "\n";
        if ($description !== '') $out .= '<meta property="og:description" content="' . $e($description) . '">' . "\n";
        if ($image !== '')       $out .= '<meta property="og:image" content="' . $e($image) . '">' . "\n";
        if (isset($_GET['sent'])) $out .= '<meta name="robots" content="noindex,follow">' . "\n";
        return $out;
    }
}
