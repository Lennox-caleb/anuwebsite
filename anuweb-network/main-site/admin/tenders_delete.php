<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_delete_token();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = db()->prepare('DELETE FROM tenders WHERE id = ?');
    $stmt->execute([$id]);
}
redirect('/admin/tenders.php');
