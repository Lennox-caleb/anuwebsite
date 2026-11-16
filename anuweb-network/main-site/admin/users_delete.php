<?php
require_once __DIR__ . '/includes/auth.php';
require_super_admin();

$id = (int)($_GET['id'] ?? 0);
$me = current_admin();
require_delete_token();

if ($id && $id !== (int)$me['id']) {
    db()->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$id]);
}
redirect('/admin/users.php');
