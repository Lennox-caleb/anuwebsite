<?php
require_once __DIR__ . '/includes/functions.php';
// Alumni now has its own dedicated site -- redirect there.
header('Location: ' . setting('alumni_url'));
exit;
