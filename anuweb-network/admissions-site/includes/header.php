<?php
$__hard = __DIR__ . '/../../main-site/includes/site_hardening.php';
if (is_file($__hard)) { require_once $__hard; }
$__cu = __DIR__ . '/../../main-site/includes/clean_urls.php';
if (is_file($__cu)) { require_once $__cu; anu_clean_urls_boot(dirname(__DIR__)); }
require_once __DIR__ . '/../config/db.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>Admissions | Africa Nazarene University</title>
<meta name="description" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>Admissions, Africa Nazarene University">
<?= function_exists('anu_seo_tags') ? anu_seo_tags((isset($pageTitle) ? $pageTitle . ' | ' : '') . 'Africa Nazarene University', '', '') : '' ?>
<link href="<?= BASE_URL ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= MAIN_SITE_URL ?>/assets/css/a11y.css">
<link rel="stylesheet" href="<?= MAIN_SITE_URL ?>/assets/css/unit-daystar.css">
<link rel="stylesheet" href="<?= MAIN_SITE_URL ?>/assets/css/priority-nav.css">
<script src="<?= MAIN_SITE_URL ?>/assets/js/priority-nav.js" defer></script>
</head>
<body class="unit-chrome">

<div class="adm-topbar">
  <div class="container d-flex flex-wrap justify-content-between">
    <div><i class="bi bi-house"></i> <a target="_blank" rel="noopener" href="<?= h(MAIN_SITE_URL) ?>/index.php">Africa Nazarene University main site</a></div>
    <div><a href="mailto:<?= h(main_setting('email_admissions')) ?>"><?= h(main_setting('email_admissions')) ?></a></div>
  </div>
</div>

<nav class="adm-nav navbar navbar-expand-lg">
  <div class="container">
    <a class="brand-mark navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#admNav" aria-controls="admNav" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end offcanvas-lg mobile-nav-panel" tabindex="-1" id="admNav" aria-labelledby="admNavLabel">
      <div class="offcanvas-header d-lg-none">
        <a class="mobile-nav-brand" href="<?= BASE_URL ?>/index.php" id="admNavLabel">
          <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column">
      <ul class="nav flex-wrap align-items-lg-center ms-auto mt-2 mt-lg-0">
        <li class="nav-item"><a class="nav-link <?= $current==='index.php'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='requirements.php'?'active':'' ?>" href="<?= BASE_URL ?>/requirements.php">Requirements</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='how-to-apply.php'?'active':'' ?>" href="<?= BASE_URL ?>/how-to-apply.php">How to Apply</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='meet-the-team.php'?'active':'' ?>" href="<?= BASE_URL ?>/meet-the-team.php">Meet the Team</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='contact.php'?'active':'' ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a target="_blank" rel="noopener" class="btn btn-adm-cta btn-sm" href="<?= h(main_setting('apply_url')) ?>">Apply Now</a></li>
      </ul>
      </div>
    </div>
  </div>
</nav>
