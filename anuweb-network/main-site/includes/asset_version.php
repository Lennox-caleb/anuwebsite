<?php
/**
 * Phase 7 (2026-09): cache-busting for every CSS/JS URL, so a deploy that
 * changes a stylesheet or script is never masked by a browser (or CDN/
 * proxy) serving the old cached file under the same URL.
 *
 * Included directly by main-site/includes/functions.php. Each unit site's
 * config/db.php requires it via a relative path across to main-site/ --
 * same pattern already used for hero_render.php and schema_guard.php,
 * safe because every site is always deployed as a sibling folder under
 * anuweb-network/.
 */

if (!function_exists('asset_v')) {
    /**
     * Builds a versioned asset URL: "$urlBase/$relPath?v=<filemtime>".
     * $fsBase is the filesystem directory $relPath is resolved against to
     * read its real mtime -- defaults to SITE_ROOT (this site's own
     * assets), but a unit site linking to a shared main-site asset (e.g.
     * hero-fullscreen.css, served from MAIN_SITE_URL) passes the main
     * site's filesystem root explicitly, since that's a different
     * directory than this site's own SITE_ROOT.
     *
     * Falls back to the current timestamp (still cache-busts, just isn't
     * stable across requests) if the file can't be found on disk -- never
     * fatals, and never serves an unversioned URL silently.
     */
    function asset_v(string $urlBase, string $relPath, ?string $fsBase = null): string
    {
        $fsBase = $fsBase ?? (defined('SITE_ROOT') ? SITE_ROOT : __DIR__);
        $file = rtrim($fsBase, '/') . '/' . ltrim($relPath, '/');
        $v = is_file($file) ? filemtime($file) : time();
        return rtrim($urlBase, '/') . '/' . ltrim($relPath, '/') . '?v=' . $v;
    }
}

if (!function_exists('main_site_fs_root')) {
    /**
     * Filesystem path to the sibling main-site/ folder, from any unit
     * site -- same sibling-folder assumption used everywhere else in this
     * codebase (MAIN_SITE_URL, hero_render.php's own require path). Used
     * with asset_v() as $fsBase when a unit site versions a shared
     * main-site asset URL (MAIN_SITE_URL + '/assets/...').
     */
    function main_site_fs_root(): string
    {
        return defined('SITE_ROOT') ? dirname(SITE_ROOT) . '/main-site' : __DIR__;
    }
}
