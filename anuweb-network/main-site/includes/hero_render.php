<?php
/**
 * Shared hero-media renderer (Phase 2) -- ONE component used by the main
 * site, Orientation, and the school/unit sites, so image/video/
 * YouTube hero support only has to be built and fixed in one place.
 *
 * Included directly by main-site/includes/functions.php. Each unit site's
 * config/db.php requires it via a relative path across to main-site/ --
 * safe because every site is always deployed as a sibling folder under
 * anuweb-network/, the same assumption MAIN_SITE_URL/UPLOADS_URL already
 * make elsewhere in this codebase. If that file can't be found (a site
 * deployed on its own, without main-site alongside it), each unit site's
 * require uses a guarded fallback -- see the comment where it's required.
 */

if (!function_exists('hero_youtube_id')) {
    /**
     * Extract a YouTube video ID from a youtu.be or youtube.com URL,
     * ignoring ?si= and any other query params (e.g. a link copied from
     * the YouTube share sheet). Returns null if the URL isn't a
     * recognisable YouTube link, so callers can fall back to treating it
     * as a direct video file URL instead.
     */
    function hero_youtube_id(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if (preg_match('~^https?://(?:www\.)?youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~^https?://(?:www\.)?(?:m\.)?youtube\.com/watch\?[^#]*\bv=([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~^https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return $m[1];
        }
        return null;
    }
}

if (!function_exists('hero_media_kind')) {
    /**
     * 'youtube' | 'video' | 'image' | 'none' -- what a hero/slider row will
     * actually render as. Deliberately untyped/no-?array hint: PDOStatement
     * ::fetch() returns false (not null) when there's no row, and every
     * caller across this codebase passes that fetch() result straight
     * through -- typing this ?array would fatal-error on every page with
     * no hero row set yet, which is the normal, common case.
     */
    function hero_media_kind($hero): string
    {
        if (!is_array($hero)) return 'none';
        $video = trim((string)($hero['video_url'] ?? ''));
        if ($video !== '') {
            return hero_youtube_id($video) ? 'youtube' : 'video';
        }
        if (!empty($hero['image'])) return 'image';
        return 'none';
    }
}

if (!function_exists('render_hero_media')) {
    /**
     * Renders the hero's background media as one element (image / video /
     * YouTube iframe), or '' if there's no media at all -- callers should
     * fall back to a plain photo-less hero in that case, never an empty
     * box. $uploadsUrl is the absolute or root-relative URL to the shared
     * uploads folder (served from the main site for every unit site too).
     * prefers-reduced-motion and video-load-failure handling live in each
     * site's own main.js (see the .hero-bg-video / .hero-bg-iframe hook),
     * not here -- this only ever emits markup, no inline JS.
     */
    function render_hero_media($hero, string $uploadsUrl, string $cssClass = 'hero-bg-media'): string
    {
        $kind = hero_media_kind($hero);
        if ($kind === 'none') return '';

        $esc = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

        // A poster/fallback <img> is emitted BEFORE the video/iframe for both
        // 'video' and 'youtube' kinds whenever an image is also set on the
        // hero row -- it sits behind the moving media in normal document
        // order, and hero-media-fallback.js (see that file) reveals it (by
        // hiding the video/iframe) if playback can't be confirmed within a
        // few seconds, or if the video/iframe fails to load at all. It's
        // also what prefers-reduced-motion shows once the moving media is
        // hidden by CSS. No poster image set -> emits nothing extra; the
        // hero's own background-color is the fallback in that case, same
        // as before.
        $posterHtml = '';
        if (($kind === 'video' || $kind === 'youtube') && !empty($hero['image'])) {
            $posterSrc = rtrim($uploadsUrl, '/') . '/' . $hero['image'];
            $posterHtml = '<img class="' . $esc($cssClass) . ' hero-bg-poster" src="' . $esc($posterSrc) . '" alt="">';
        }

        if ($kind === 'youtube') {
            $id = hero_youtube_id($hero['video_url']);
            // youtube-nocookie.com is YouTube's privacy-enhanced embed
            // domain -- same player, but not blocked by the tracker/ad
            // blockers and corporate network filters that commonly block
            // plain youtube.com/embed iframes outright (a well-documented
            // real-world cause of "the embedded video just doesn't show").
            // loop=1 only loops a single video when playlist is set to that
            // same id. enablejsapi=1 + origin lets hero-media-fallback.js
            // listen for the player's real onStateChange="playing" event
            // via postMessage, rather than guessing from the iframe's own
            // (unrelated) load event.
            $origin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
                    . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $src = 'https://www.youtube-nocookie.com/embed/' . $esc($id)
                 . '?autoplay=1&mute=1&loop=1&playlist=' . $esc($id)
                 . '&controls=0&playsinline=1&modestbranding=1&rel=0&enablejsapi=1&origin=' . $esc($origin);
            return $posterHtml . '<iframe class="' . $esc($cssClass) . ' hero-video hero-bg-iframe" src="' . $src . '" title="Background video" '
                 . 'frameborder="0" allow="autoplay; encrypted-media" aria-hidden="true" tabindex="-1"></iframe>';
        }

        if ($kind === 'video') {
            $videoUrl = $hero['video_url'];
            $src = (bool)preg_match('/^(https?:)?\/\//i', $videoUrl) ? $videoUrl : rtrim($uploadsUrl, '/') . '/' . $videoUrl;
            $poster = !empty($hero['image']) ? rtrim($uploadsUrl, '/') . '/' . $hero['image'] : '';
            return $posterHtml . '<video class="' . $esc($cssClass) . ' hero-video hero-bg-video" autoplay muted loop playsinline'
                 . ($poster ? ' poster="' . $esc($poster) . '"' : '') . '>'
                 . '<source src="' . $esc($src) . '">'
                 . '</video>';
        }

        // image
        $src = rtrim($uploadsUrl, '/') . '/' . $hero['image'];
        return '<img class="' . $esc($cssClass) . '" src="' . $esc($src) . '" alt="">';
    }
}
