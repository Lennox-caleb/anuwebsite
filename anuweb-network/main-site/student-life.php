<?php
require_once __DIR__ . '/includes/functions.php';
// Student Life now has its own dedicated site -- redirect there.
header('Location: ' . setting('url_student_life'));
exit;
