<?php
$__hard = __DIR__ . '/../../main-site/includes/site_hardening.php';
if (is_file($__hard)) { require_once $__hard; }
$__cu = __DIR__ . '/../../main-site/includes/clean_urls.php';
if (is_file($__cu)) { require_once $__cu; anu_clean_urls_boot(dirname(__DIR__)); }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/school_slug.php';
$current = basename($_SERVER['PHP_SELF']);

// Phase 5b redesign: real programmes for this school, grouped by level,
// to power the "Programmes" nav dropdown (Daystar-style submenu by
// level) -- never hardcoded, and the dropdown simply doesn't appear if
// this school has no published programmes yet.
$__navProgrammesByLevel = [];
$__navProgStmt = db()->prepare("SELECT p.title, p.slug, p.level FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 ORDER BY p.level, p.title");
$__navProgStmt->execute([school_slug_db()]);
foreach ($__navProgStmt->fetchAll() as $__p) {
    $__navProgrammesByLevel[$__p['level']][] = $__p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>School of Religion & Christian Ministry | Africa Nazarene University</title>
<link href="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/anu-logo.png">
<meta name="description" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>School of Religion & Christian Ministry, Africa Nazarene University">
<?= function_exists('anu_seo_tags') ? anu_seo_tags((isset($pageTitle) ? $pageTitle . ' | ' : '') . 'Africa Nazarene University', '', '') : '' ?>
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset_v(MAIN_SITE_URL, 'assets/css/hero-fullscreen.css', main_site_fs_root()) ?>">
<script src="<?= asset_v(MAIN_SITE_URL, 'assets/js/hero-fullscreen.js', main_site_fs_root()) ?>" defer></script>
<script src="<?= asset_v(MAIN_SITE_URL, 'assets/js/hero-media-fallback.js', main_site_fs_root()) ?>" defer></script>
<link rel="stylesheet" href="<?= asset_v(MAIN_SITE_URL, 'assets/css/priority-nav.css', main_site_fs_root()) ?>">
<script src="<?= asset_v(MAIN_SITE_URL, 'assets/js/priority-nav.js', main_site_fs_root()) ?>" defer></script>
<script src="<?= asset_v(MAIN_SITE_URL, 'assets/js/hero-controls.js', main_site_fs_root()) ?>" defer></script>
<link rel="stylesheet" href="<?= MAIN_SITE_URL ?>/assets/css/a11y.css">
<link rel="stylesheet" href="<?= MAIN_SITE_URL ?>/assets/css/unit-daystar.css">
</head>
<body class="unit-daystar">
<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="rel-topbar site-header-bar">
  <div class="container d-flex flex-wrap justify-content-between">
    <?php if (is_file(__DIR__ . '/../../main-site/includes/contact_bar.php')) { require_once __DIR__ . '/../../main-site/includes/contact_bar.php'; anu_contact_bar(defined('UNIT_SLUG') ? UNIT_SLUG : ''); } ?>
    <div>
      <a target="_blank" rel="noopener" href="<?= h(MAIN_SITE_URL) ?>/index.php"><i class="bi bi-house"></i> ANU Main Website</a>
      &nbsp;|&nbsp;
      <a target="_blank" rel="noopener" href="<?= h(main_setting('enaz_url')) ?>" target="_blank" rel="noopener">eNaz</a>
      &nbsp;|&nbsp;
      <a target="_blank" rel="noopener" href="<?= h(main_setting('student_portal_url')) ?>">Student Portal</a>
    </div>
  </div>
</div>

<nav class="rel-nav navbar navbar-expand-lg site-header-bar">
  <div class="container">
    <a class="brand-mark navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#relNav" aria-controls="relNav" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end offcanvas-lg mobile-nav-panel" tabindex="-1" id="relNav" aria-labelledby="relNavLabel">
      <div class="offcanvas-header d-lg-none">
        <a class="mobile-nav-brand" href="<?= BASE_URL ?>/index.php" id="relNavLabel">
          <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column">
      <ul class="nav flex-wrap align-items-lg-center ms-auto mt-2 mt-lg-0">
        <li class="nav-item"><a class="nav-link <?= $current==='index.php'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='about.php'?'active':'' ?>" href="<?= BASE_URL ?>/about.php">About</a></li>
        <li class="nav-item">
          <a class="nav-link prog-link <?= $current==='programmes.php'?'active':'' ?>" href="<?= BASE_URL ?>/programmes.php" id="relProgDropdown">Programmes</a>
        </li>
        <?php if (department_count() > 0): ?>
        <li class="nav-item"><a class="nav-link <?= $current==='departments.php'?'active':'' ?>" href="<?= BASE_URL ?>/departments.php">Departments</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link <?= $current==='faculty.php'?'active':'' ?>" href="<?= BASE_URL ?>/faculty.php">Faculty &amp; Staff</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='news.php'?'active':'' ?>" href="<?= BASE_URL ?>/news.php">News &amp; Updates</a></li>
        <li class="nav-item"><a class="nav-link <?= $current==='contact.php'?'active':'' ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" target="_blank" rel="noopener" href="<?= h(MAIN_SITE_URL) ?>/index.php">Main Web</a></li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0 d-none d-lg-block"><a target="_blank" rel="noopener" class="btn btn-rel-cta btn-sm" href="<?= h(main_setting('apply_url')) ?>">Apply Now</a></li>
      </ul>
      <div class="mobile-nav-footer d-lg-none mt-auto">
        <a target="_blank" rel="noopener" class="btn btn-rel-cta mobile-nav-apply" href="<?= h(main_setting('apply_url')) ?>">Apply Now</a>
        <div class="mobile-nav-quicklinks">
          <a href="<?= h(main_setting('student_portal_url')) ?>" target="_blank" rel="noopener">Student Portal</a>
          <a href="<?= h(main_setting('enaz_url')) ?>" target="_blank" rel="noopener">eNaz</a>
          <a href="<?= h(MAIN_SITE_URL) ?>/index.php" target="_blank" rel="noopener">Main Site</a>
        </div>
      </div>
      </div>
    </div>
  </div>
</nav>
<main id="main-content">
