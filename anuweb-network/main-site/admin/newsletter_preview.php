<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/../includes/newsletter_template.php';
current_admin();
$st = db()->prepare('SELECT * FROM newsletters WHERE id = ?'); $st->execute([(int)($_GET['id'] ?? 0)]);
$n = $st->fetch();
if (!$n) { http_response_code(404); exit('Newsletter not found.'); }
[$html] = anu_nl_render($n, ['full_name' => 'Sample Name'], '#', $n['include_news'] ? anu_nl_load_news(3) : [], $n['include_events'] ? anu_nl_load_events(3) : [], 'preview');
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: SAMEORIGIN');
echo $html;
