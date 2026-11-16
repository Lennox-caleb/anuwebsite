<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_delete_token();
$id = (int)($_GET['id'] ?? 0);
if ($id) db()->prepare('DELETE FROM publications_gallery WHERE id = ?')->execute([$id]);
redirect('/admin/magazines.php');
