<?php
$pageTitle = 'Message from the University Registrar';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/school_messages.php';
$smOpts = ['leader_slug' => 'registrar', 'fallback_title' => 'Registrar', 'unit' => 'admissions', 'base' => BASE_URL, 'uploads' => UPLOADS_URL,
    'css' => BASE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the University Registrar', 'eyebrow' => 'Admissions',
    'heads' => false, 'message_page' => 'registrar-message.php', 'back_url' => BASE_URL . '/admissions.php', 'back_label' => 'Back to Admissions',
    'fallback_bio' => page_content('admissions', 'registrar_message', ''),
    'soon' => 'The Registrar&rsquo;s message will be published here soon.'];
$_GET['who'] = 'dean';
$smPerson = sm_find($smOpts);
require __DIR__ . '/includes/header.php';
sm_render_page($smOpts, $smPerson);
require __DIR__ . '/includes/footer.php';
