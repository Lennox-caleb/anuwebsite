<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','editor','super_admin']);
require_delete_token();
current_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    db()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
}
redirect('/admin/testimonials.php');
