<?php
require_once __DIR__ . '/functions.php';
$current = basename($_SERVER['PHP_SELF']);
$navActive = function (array $files) use ($current) {
    return in_array($current, $files, true) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= h($pageDescription ?? 'Africa Nazarene University Non-Degree Programmes -- accredited, competency-based technical and vocational education and training in Kenya.') ?>">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= h(setting('site_name', 'ANU Non-Degree Programmes')) ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/images/anu-logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/navbar-fix.css">
</head>
<body>

<div class="tvet-topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="topbar-left">
      <i class="bi bi-telephone"></i> <a href="tel:<?= h(preg_replace('/[^0-9+]/', '', setting('phone_primary'))) ?>"><?= h(setting('phone_primary')) ?></a>
      <?php if (setting('phone_secondary') !== ''): ?>&nbsp;|&nbsp;<a href="tel:<?= h(preg_replace('/[^0-9+]/', '', setting('phone_secondary'))) ?>"><?= h(setting('phone_secondary')) ?></a><?php endif; ?>
      &nbsp;|&nbsp;<i class="bi bi-envelope"></i> <a href="mailto:<?= h(setting('email_dept', 'nondegree@anu.ac.ke')) ?>"><?= h(setting('email_dept', 'nondegree@anu.ac.ke')) ?></a>
    </div>
    <div class="topbar-right">
      <a target="_blank" rel="noopener" href="<?= h(setting('main_site_url')) ?>"><i class="bi bi-house-door"></i> ANU Main Website</a>
      <span>|</span>
      <a target="_blank" rel="noopener" href="<?= h(setting('enaz_url')) ?>"><i class="bi bi-mortarboard"></i> eNaz Portal</a>
      <span>|</span>
      <a target="_blank" rel="noopener" href="<?= h(setting('student_portal_url')) ?>"><i class="bi bi-person-badge"></i> Student Portal</a>
    </div>
  </div>
</div>

<nav class="tvet-nav navbar navbar-expand-lg sticky-top" aria-label="Main navigation">
  <div class="container">
    <a class="brand-mark navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#tvetMenu" aria-controls="tvetMenu" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas-lg offcanvas-end tvet-offcanvas" tabindex="-1" id="tvetMenu" aria-labelledby="tvetMenuLabel">
      <div class="offcanvas-header">
        <span class="visually-hidden" id="tvetMenuLabel">Menu</span>
        <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University logo">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#tvetMenu" aria-label="Close menu"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav tvet-menu">
          <li class="nav-item"><a class="nav-link <?= $navActive(['index.php']) ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['about.php']) ?>" href="<?= BASE_URL ?>/about.php">About</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['programmes.php','programme-single.php']) ?>" href="<?= BASE_URL ?>/programmes.php">Programmes</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['admissions.php']) ?>" href="<?= BASE_URL ?>/admissions.php">Admissions</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['board.php']) ?>" href="<?= BASE_URL ?>/board.php">Board &amp; Trainers</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['news.php','news-single.php']) ?>" href="<?= BASE_URL ?>/news.php">News</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['events.php']) ?>" href="<?= BASE_URL ?>/events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link <?= $navActive(['contact.php']) ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
          <li class="nav-item cta"><a class="btn btn-tvet-cta" href="<?= h(apply_url()) ?>" target="_blank" rel="noopener">Apply Now</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php $flashes = flash_get(); if ($flashes): ?>
  <div class="container mt-3">
    <?php foreach ($flashes as $f): ?>
      <div class="alert alert-<?= h($f['type']) ?> alert-dismissible fade show" role="alert">
        <?= h($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
