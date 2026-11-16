<?php
// This site's own admin panel (see main-site/site-admin/engine.php). Manages this site only.
require_once __DIR__ . '/../config/db.php';
$sa_unit = UNIT_SLUG;
require __DIR__ . '/../../main-site/site-admin/engine.php';
