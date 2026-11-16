<?php
/**
 * includes/tracker.php
 * Call trackPage('page_key', 'Human Label', $optionalRefId) on any public page.
 * Silently ignored if table doesn't exist yet.
 */
function trackPage(string $page, string $label = '', int $refId = 0): void {
    try {
        $db  = getDB();
        $ip  = $_SERVER['HTTP_X_FORWARDED_FOR']
             ?? $_SERVER['REMOTE_ADDR']
             ?? null;
        // Take only first IP if comma-separated
        if ($ip) $ip = trim(explode(',', $ip)[0]);
        $ua  = isset($_SERVER['HTTP_USER_AGENT'])
             ? substr($_SERVER['HTTP_USER_AGENT'], 0, 300)
             : null;
        $ref = isset($_SERVER['HTTP_REFERER'])
             ? substr($_SERVER['HTTP_REFERER'], 0, 500)
             : null;
        $db->prepare(
            "INSERT INTO page_views (page, page_label, ref_id, ip, user_agent, referrer)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([$page, $label ?: $page, $refId ?: null, $ip, $ua, $ref]);
    } catch(Exception $e) {
        // Table may not exist yet — fail silently
    }
}
?>
