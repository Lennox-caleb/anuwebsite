<?php
/**
 * Resolves this school's slug as it actually exists in the `schools` table.
 * Some servers' config.php still holds the old long form (e.g. "school-of-business")
 * while the database uses the short one ("sob"), or vice versa -- either works.
 */
if (!function_exists('school_slug_db')) {
    function school_slug_db(): string
    {
        static $slug = null;
        if ($slug !== null) return $slug;
        $pairs = ['sol' => 'school-of-law', 'sob' => 'school-of-business', 'sst' => 'school-of-science-and-technology',
                  'shss' => 'school-of-humanities-and-social-sciences', 'scrm' => 'school-of-religion-and-christian-ministry'];
        $configured = defined('SCHOOL_SLUG') ? (string) SCHOOL_SLUG : '';
        $cands = [$configured];
        foreach ($pairs as $short => $long) {
            if ($configured === $short) $cands[] = $long;
            if ($configured === $long) $cands[] = $short;
        }
        $slug = $configured;
        try {
            $st = db()->prepare('SELECT 1 FROM schools WHERE slug = ? LIMIT 1');
            foreach ($cands as $c) { $st->execute([$c]); if ($st->fetchColumn()) { $slug = $c; break; } }
        } catch (Throwable $e) { /* keep configured value */ }
        return $slug;
    }
}
