<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_delete_token();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = db()->prepare('DELETE FROM csr_gallery WHERE id = ?');
    $stmt->execute([$id]);
}
redirect('/admin/csr_gallery.php');
