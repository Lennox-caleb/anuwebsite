<?php
require_once __DIR__ . '/clean_urls.php';
anu_clean_urls_boot(dirname(__DIR__));
// Never print PHP errors to visitors (config.php enables it for local dev);
// log them instead. Overridden here so config/config.php stays untouched.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (!ini_get('error_log')) {
    $__logdir = dirname(__DIR__) . '/logs';
    if (is_dir($__logdir) || @mkdir($__logdir, 0775, true)) {
        ini_set('error_log', $__logdir . '/php-errors.log');
    }
}
require_once __DIR__ . '/site_hardening.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/hero_render.php';
require_once __DIR__ . '/schema_guard.php';
require_once __DIR__ . '/asset_version.php';

/** Escape output for safe HTML rendering. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Fetch a single setting value by key, with a fallback default. */
function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

/**
 * Phase 3: read admin-editable content for a main-site page (currently
 * just Orientation) from the same `site_content` table the unit
 * sites already use, keyed by a "unit_slug" that's really just this
 * page's own slug ('orientation-pack'). Falls back to $default so the
 * page still reads sensibly before an admin has customised it -- same
 * behaviour as the unit sites' own unit_content().
 */
function page_content(string $unitSlug, string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$unitSlug])) {
        $cache[$unitSlug] = [];
        $stmt = db()->prepare('SELECT content_key, content_value FROM site_content WHERE unit_slug = ?');
        $stmt->execute([$unitSlug]);
        foreach ($stmt->fetchAll() as $row) {
            $cache[$unitSlug][$row['content_key']] = $row['content_value'];
        }
    }
    $val = $cache[$unitSlug][$key] ?? '';
    return $val !== '' ? $val : $default;
}

/** Generate + validate CSRF tokens for forms (public + admin). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * True when this POST request arrived with a body but PHP discarded it because
 * it exceeded post_max_size (or upload_max_filesize truncated a file upload).
 * When that happens, PHP empties $_POST and $_FILES entirely and raises no
 * catchable error -- so a form that only checks csrf_check() will wrongly
 * report "session expired" when the real problem is an oversized file.
 */
function post_too_large(): bool
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return false;
    }
    if (!empty($_POST) || !empty($_FILES)) {
        return false;
    }
    return (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

/** The right message for a failed csrf_check(): the real cause when it's an oversized upload, otherwise the generic session message. */
function csrf_error_message(): string
{
    if (post_too_large()) {
        return 'That file is too large for this server to accept right now (current limit: '
            . h(ini_get('upload_max_filesize')) . ' per file, ' . h(ini_get('post_max_size')) . ' per submission). '
            . 'Please choose a smaller file, or ask your system administrator to raise upload_max_filesize and post_max_size in php.ini.';
    }
    return 'Session expired, please resubmit.';
}

/** Build a URL-safe slug from a title. */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = strtolower(preg_replace('~[^-\w]+~', '', $text));
    return $text !== '' ? $text : 'n-a';
}

/** Human friendly date, e.g. "21 August 2026". */
function format_date(?string $date): string
{
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date('j F Y', $ts) : '';
}

/** Redirect helper. Pass a root-relative path like '/admin/dashboard.php'. */
function redirect(string $path): void
{
    // Admin actions that finish without an explicit message still tell the
    // user what happened (no more silent redirects after save/delete).
    if (strpos($path, '/admin/') === 0 && empty($_SESSION['flash'])
        && !in_array(basename(parse_url($path, PHP_URL_PATH) ?? ''), ['login.php', 'logout.php'], true)) {
        $self = basename($_SERVER['SCRIPT_NAME'] ?? '');
        if (substr($self, -11) === '_delete.php') {
            flash_set('success', 'Deleted.');
        } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && substr($self, -9) === '_form.php') {
            flash_set('success', 'Saved successfully.');
        }
    }
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** One-shot message shown on the next admin page. $type: success|danger|warning|info. */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f ?: null;
}

/** Turn a database exception into a message an editor can act on (details go to the log only). */
function friendly_db_error(Throwable $e): string
{
    $m = $e->getMessage();
    if (stripos($m, 'Data too long') !== false) {
        return 'One of the fields is too long for the database. Shorten the text and save again. (Nothing was saved.)';
    }
    if (stripos($m, 'Duplicate entry') !== false) {
        return 'That record already exists (a unique value such as a slug is already used). Change it and save again.';
    }
    if (stripos($m, "doesn't exist") !== false || stripos($m, 'Unknown column') !== false) {
        return 'The database is missing a table or column this page needs. A super admin should run database/migrate.php (or open Admin > System Check). Nothing was saved.';
    }
    if (stripos($m, 'Incorrect') !== false && stripos($m, 'value') !== false) {
        return 'One of the values has the wrong format (for example an invalid date or option). Check the form and save again.';
    }
    if (stripos($m, 'Illegal mix of collations') !== false) {
        return 'The database needs a one-time collation fix. A super admin should run database/migrate.php. Nothing was saved.';
    }
    return 'The change could not be saved because of a server problem. It has been logged for the administrator. Nothing was saved.';
}

/** Per-session token for destructive links such as admin delete buttons. */
function delete_token(): string
{
    return csrf_token();
}

/** Call at the top of every admin *_delete.php: refuses requests without the session's token. */
function require_delete_token(): void
{
    $t = $_GET['t'] ?? $_POST['csrf_token'] ?? '';
    if (!is_string($t) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $t)) {
        flash_set('danger', 'Delete was not performed: the link was missing its security token or your session expired. Please reload the list and try again.');
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        $dest = parse_url($back, PHP_URL_PATH) ?: '';
        if ($dest !== '' && strpos($dest, BASE_URL . '/admin/') === 0) {
            header('Location: ' . $dest);
            exit;
        }
        redirect('/admin/dashboard.php');
    }
}

/**
 * Resolve an admin-entered link (e.g. a hero slide's button URL) into a
 * usable href. Absolute URLs (https://..., mailto:, tel:, //cdn...) and
 * in-page anchors (#section) are left exactly as typed; anything else is
 * treated as a path relative to this site and gets BASE_URL prefixed, so
 * admins can type either "/admissions.php" or a full external link and
 * both work correctly.
 */
function resolve_link(string $url): string
{
    $url = trim($url);
    if ($url === '') return '';
    if (preg_match('/^(https?:)?\/\//i', $url) || preg_match('/^(mailto|tel):/i', $url) || $url[0] === '#') {
        return $url;
    }
    return BASE_URL . '/' . ltrim($url, '/');
}

/**
 * Turns a sliders.video_url value into a playable <source> URL. That
 * column holds either an uploaded filename (like the "image" column) or
 * a full external URL an admin pasted in -- this tells the two apart the
 * same way resolve_link() does for buttons, so callers don't double-prefix
 * an external link with UPLOADS_URL.
 */
function hero_video_src(?string $videoUrl): string
{
    $videoUrl = trim((string) $videoUrl);
    if ($videoUrl === '') return '';
    return is_external_link($videoUrl) ? $videoUrl : UPLOADS_URL . '/' . $videoUrl;
}

/** True when a link (as typed by an admin) points off-site, so callers can open it in a new tab. */
function is_external_link(string $url): bool
{
    return (bool) preg_match('/^(https?:)?\/\//i', trim($url));
}

/** Truncate text for cards/excerpts without cutting words mid-way. */
function excerpt(string $text, int $limit = 160): string
{
    $text = preg_replace('~<!--.*?-->~s', '', $text);                                   // editor marker
    $text = preg_replace('~</?(p|br|li|h[1-6]|div|blockquote|ul|ol)\b[^>]*>~i', ' ', $text);   // keep words apart
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8')));
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '…';
}

/** Human-readable file size, e.g. "2.4 MB". */
function format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

/**
 * Public-facing display guard: some records were originally seeded with an
 * internal placeholder like "To be appointed via admin panel" so an admin
 * would know to fill them in. Visitors should never see that wording, this
 * swaps it for a neutral, professional fallback wherever a title/bio is
 * printed on a public page.
 */
function public_text(?string $text, string $fallback = ''): string
{
    if ($text === null || trim($text) === '') return $fallback;
    if (stripos($text, 'admin panel') !== false) return $fallback;
    return $text;
}


/**
 * Safe handler for any uncaught exception: logs the real cause, shows the
 * user a plain message (admin pages get an actionable one) -- never a stack trace.
 */
set_exception_handler(function (Throwable $e): void {
    error_log('Uncaught ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
        . ' [' . ($_SERVER['REQUEST_URI'] ?? '') . ']');
    if (!headers_sent()) {
        http_response_code(500);
    }
    $isAdmin = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;
    $msg = $isAdmin ? friendly_db_error($e) : 'Sorry, something went wrong on our side. Please try again shortly.';
    $back = $isAdmin ? 'javascript:history.back()' : BASE_URL . '/index.php';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Something went wrong</title>'
       . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>'
       . '<body class="bg-light"><div class="container py-5" style="max-width:640px"><div class="alert alert-danger" role="alert">'
       . '<h1 class="h5">' . ($isAdmin ? 'Your change was not saved' : 'Something went wrong') . '</h1><p class="mb-0">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p></div>'
       . '<a class="btn btn-dark" href="' . htmlspecialchars($back, ENT_QUOTES, 'UTF-8') . '">' . ($isAdmin ? 'Go back and keep my input' : 'Back to home') . '</a></div></body></html>';
    exit;
});
