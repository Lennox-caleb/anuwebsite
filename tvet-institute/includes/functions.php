<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/clean_urls.php';
anu_clean_urls_boot(dirname(__DIR__));

/** Escape for safe HTML output. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Truncate plain text to a word-safe excerpt. */
function excerpt(string $text, int $limit = 160): string
{
    $text = trim(strip_tags($text));
    $len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    if ($len <= $limit) return $text;
    return (function_exists('mb_substr') ? mb_substr($text, 0, $limit) : substr($text, 0, $limit)) . '…';
}

/** URL-safe slug from any string. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Cached read of a setting from the settings table. */
/** The one real application portal -- every "Apply" button on this site goes here. */
function apply_url(): string
{
    return 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

/** Nicely formatted date. */
function fdate(?string $datetime, string $format = 'd M Y'): string
{
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '';
}

/* ---------------------------------------------------------------------
 * Flash messages (session based, one-time read)
 * ------------------------------------------------------------------- */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

/* ---------------------------------------------------------------------
 * CSRF protection
 * ------------------------------------------------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ---------------------------------------------------------------------
 * File uploads
 * ------------------------------------------------------------------- */
/**
 * Handle a single <input type=file>, move it into uploads/{folder}, and
 * return the stored filename (not full path) or null if no file supplied.
 * Throws RuntimeException on validation/move errors.
 */
function handle_upload(string $fieldName, string $folder, array $allowedExt = ['jpg','jpeg','png','webp','gif']): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('File is too large. Maximum size is 5MB.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('File type .' . $ext . ' is not allowed.');
    }
    $destDir = UPLOADS_PATH . '/' . $folder;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    return $filename;
}

function upload_url(?string $folder, ?string $filename): string
{
    if (!$filename) return '';
    return UPLOADS_URL . '/' . $folder . '/' . $filename;
}

/** Optional uploaded hero/banner image for an inner page (About, Programmes, etc). */
function page_banner_url(string $pageKey): ?string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query('SELECT page_key, image FROM page_banners') as $row) {
                if (!empty($row['image'])) $cache[$row['page_key']] = $row['image'];
            }
        } catch (Throwable $e) {
            $cache = []; // table not migrated yet -- degrade gracefully, no banner shown
        }
    }
    return isset($cache[$pageKey]) ? upload_url('site', $cache[$pageKey]) : null;
}
/* ---------------------------------------------------------------------
 * Misc
 * ------------------------------------------------------------------- */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function generate_application_no(): string
{
    return 'TVET-' . date('y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function level_badge_class(string $level): string
{
    if (str_contains($level, 'Higher Diploma')) return 'lvl-diploma';
    if (str_contains($level, 'Diploma')) return 'lvl-diploma';
    if (str_contains($level, 'Certificate')) return 'lvl-craft';
    return 'lvl-short';
}
