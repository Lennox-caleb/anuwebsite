<?php
/**
 * Campus Tour helpers: video link, places (chapters) and the table behind them.
 * Safe to include many times. Creates its own table on first use.
 */
if (!function_exists('ct_ensure')) {

function ct_ensure(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS campus_tour_places (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(120) NOT NULL,
            description VARCHAR(255) NOT NULL DEFAULT '',
            start_seconds INT UNSIGNED NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) { /* page still renders the video */ }
}

function ct_video_url(): string
{
    return trim(setting('campus_tour_video', 'https://youtu.be/lBitPZ1iQfc'));
}

/** Returns the 11-character YouTube id from any normal YouTube link, or ''. */
function ct_video_id(string $url): string
{
    $url = trim($url);
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) return $url;
    if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    return '';
}

function ct_places(bool $publishedOnly = true): array
{
    ct_ensure();
    try {
        $sql = 'SELECT * FROM campus_tour_places' . ($publishedOnly ? ' WHERE is_published = 1' : '')
             . ' ORDER BY start_seconds, sort_order, id';
        return db()->query($sql)->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** "1:25" or "01:02:03" or "85" -> seconds. Returns null if it is not a time. */
function ct_parse_time(string $s): ?int
{
    $s = trim($s);
    if ($s === '') return null;
    if (ctype_digit($s)) return (int)$s;
    if (!preg_match('/^(?:(\d{1,2}):)?(\d{1,3}):([0-5]\d)$/', $s, $m)) return null;
    return ((int)$m[1]) * 3600 + ((int)$m[2]) * 60 + (int)$m[3];
}

function ct_fmt_time(int $sec): string
{
    $h = intdiv($sec, 3600); $m = intdiv($sec % 3600, 60); $s = $sec % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
}

}
