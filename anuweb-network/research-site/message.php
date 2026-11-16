<?php
$pageTitle = 'Message';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../main-site/includes/school_messages.php';
$smOpts = ['leader_slug' => 'head-research', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the Director, Institute of Research', 'eyebrow' => 'Institute of Research', 'heads' => false, 'soon' => 'The Director&rsquo;s message will be published here soon.'];
$smPerson = sm_find($smOpts);
if (!$smPerson) http_response_code(404);
require __DIR__ . '/includes/header.php';
sm_render_page($smOpts, $smPerson);
require __DIR__ . '/includes/footer.php';
