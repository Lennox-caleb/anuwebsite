<?php
/**
 * Board of Trustees ordering: the Chancellor is listed first, then the other members exactly as they were.
 * The Chancellor comes from Admin > Leadership (the same record that powers the Chancellor page), so
 * nothing is typed twice. If the Board list already has a member titled Chancellor, that member is moved to the front instead.
 */
if (!function_exists('board_chancellor_first')) {
    function board_chancellor_first(array $members): array
    {
        foreach ($members as $i => $m) {
            $t = (string)($m['title'] ?? '');
            if (stripos($t, 'Chancellor') !== false && stripos($t, 'Vice') === false) {
                array_splice($members, $i, 1);
                array_unshift($members, $m);
                return $members;
            }
        }
        try {
            $c = db()->query("SELECT full_name, title, photo FROM leadership WHERE role_type = 'chancellor' AND is_published = 1 ORDER BY sort_order, id LIMIT 1")->fetch();
        } catch (Throwable $e) { $c = false; }
        if ($c && trim((string)$c['full_name']) !== '') {
            array_unshift($members, ['full_name' => $c['full_name'], 'title' => $c['title'] ?: 'Chancellor', 'photo' => $c['photo'], 'bio' => '']);
        }
        return $members;
    }
}
