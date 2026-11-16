<?php
require_once __DIR__ . '/includes/functions.php';
// The Institute of Research now has its own dedicated site -- redirect there.
header('Location: ' . setting('url_research'));
exit;
