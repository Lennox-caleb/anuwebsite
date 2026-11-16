<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin']);
session_regenerate_id(true);
redirect(ADMIN_URL . '/login.php');
