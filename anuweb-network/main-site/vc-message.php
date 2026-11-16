<?php
// The Vice Chancellor's welcome message now lives on the Leadership pages,
// managed from admin panel -> Leadership. This old URL is kept working by
// redirecting straight to the current VC's page (or the Leadership
// directory if none is published yet), so existing links/bookmarks and
// search results don't break.
require_once __DIR__ . '/includes/functions.php';

$stmt = db()->query("SELECT id FROM leadership WHERE role_type = 'vc' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$vc = $stmt->fetch();

redirect($vc ? '/leadership-message.php?id=' . (int)$vc['id'] : '/leadership.php');
