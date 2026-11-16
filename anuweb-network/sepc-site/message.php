<?php
$pageTitle = 'Message';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../main-site/includes/school_messages.php';
$smOpts = ['leader_slug' => 'head-sepc', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the Head of SEPC', 'eyebrow' => 'SEPC', 'heads' => false, 'soon' => 'The message from the Head of SEPC will be published here soon.'];
$smPerson = sm_find($smOpts);
if (!$smPerson) http_response_code(404);
require __DIR__ . '/includes/header.php';
sm_render_page($smOpts, $smPerson);
require __DIR__ . '/includes/footer.php';
