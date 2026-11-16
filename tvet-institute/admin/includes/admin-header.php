<?php
require_once __DIR__ . '/auth.php';
require_login();
$admin = current_admin();
$here = basename($_SERVER['PHP_SELF']);
$entityHere = $_GET['entity'] ?? '';

function nav_active($files, $here) { return in_array($here, (array)$files, true); }

$navGroups = [
  'Overview' => [
    ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'href' => 'index.php', 'match' => ['index.php']],
  ],
  'Admissions' => [
    ['label' => 'Applications', 'icon' => 'bi-inbox', 'href' => 'applications.php', 'match' => ['applications.php']],
    ['label' => 'Contact Messages', 'icon' => 'bi-envelope', 'href' => 'messages.php', 'match' => ['messages.php']],
  ],
  'Content' => [
    ['label' => 'Programmes', 'icon' => 'bi-mortarboard', 'href' => 'programmes.php', 'match' => ['programmes.php', 'programme-edit.php']],
    ['label' => 'Page Banners', 'icon' => 'bi-image', 'href' => 'banners.php', 'match' => ['banners.php']],
    ['label' => 'Departments', 'icon' => 'bi-diagram-3', 'href' => 'crud.php?entity=departments', 'match' => ['crud.php'], 'entity' => 'departments'],
    ['label' => 'Board & Trainers', 'icon' => 'bi-people', 'href' => 'crud.php?entity=staff', 'match' => ['crud.php'], 'entity' => 'staff'],
    ['label' => 'News', 'icon' => 'bi-newspaper', 'href' => 'crud.php?entity=news', 'match' => ['crud.php'], 'entity' => 'news'],
    ['label' => 'Events', 'icon' => 'bi-calendar-event', 'href' => 'crud.php?entity=events', 'match' => ['crud.php'], 'entity' => 'events'],
    ['label' => 'Homepage Sliders', 'icon' => 'bi-images', 'href' => 'crud.php?entity=sliders', 'match' => ['crud.php'], 'entity' => 'sliders'],
    ['label' => 'Gallery', 'icon' => 'bi-camera', 'href' => 'crud.php?entity=gallery', 'match' => ['crud.php'], 'entity' => 'gallery'],
    ['label' => 'Testimonials', 'icon' => 'bi-chat-quote', 'href' => 'crud.php?entity=testimonials', 'match' => ['crud.php'], 'entity' => 'testimonials'],
    ['label' => 'Downloads', 'icon' => 'bi-file-earmark-arrow-down', 'href' => 'crud.php?entity=downloads', 'match' => ['crud.php'], 'entity' => 'downloads'],
    ['label' => 'FAQs', 'icon' => 'bi-question-circle', 'href' => 'crud.php?entity=faqs', 'match' => ['crud.php'], 'entity' => 'faqs'],
    ['label' => 'Partners', 'icon' => 'bi-patch-check', 'href' => 'crud.php?entity=partners', 'match' => ['crud.php'], 'entity' => 'partners'],
  ],
  'System' => [
    ['label' => 'Site Settings', 'icon' => 'bi-gear', 'href' => 'settings.php', 'match' => ['settings.php']],
    ['label' => 'Admin Users', 'icon' => 'bi-person-lock', 'href' => 'users.php', 'match' => ['users.php', 'user-edit.php'], 'role' => ['super_admin']],
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>Admin | ANU Non-Degree Programmes</title>
<link rel="icon" href="<?= BASE_URL ?>/assets/images/anu-logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
<link rel="stylesheet" href="/anuweb-network/main-site/assets/css/admin-theme.css?v=2">
<script src="/anuweb-network/main-site/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="<?= ADMIN_URL ?>/index.php" class="admin-brand">
      <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="ANU logo">
      <span>Non-Degree Programmes<small>Admin Panel</small></span>
    </a>
    <nav class="admin-nav">
      <?php foreach ($navGroups as $groupLabel => $items): ?>
        <div class="nav-group-label"><?= h($groupLabel) ?></div>
        <?php foreach ($items as $item):
          if (!empty($item['role']) && !in_array($admin['role'], $item['role'], true)) continue;
          $isActive = nav_active($item['match'], $here) && (empty($item['entity']) || $item['entity'] === $entityHere);
        ?>
          <a href="<?= ADMIN_URL . '/' . $item['href'] ?>" class="admin-nav-link <?= $isActive ? 'active' : '' ?>">
            <i class="bi <?= $item['icon'] ?>"></i> <?= h($item['label']) ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-footer">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-nav-link"><i class="bi bi-box-arrow-up-right"></i> View Site</a>
      <a href="<?= ADMIN_URL ?>/logout.php" class="admin-nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
      <h1 class="admin-page-title"><?= h($pageTitle ?? 'Dashboard') ?></h1>
      <div class="admin-user-chip">
        <i class="bi bi-person-circle"></i>
        <span><?= h($admin['full_name']) ?> <small class="d-block text-muted"><?= h(ucfirst(str_replace('_',' ',$admin['role']))) ?></small></span>
      </div>
    </header>
    <main class="admin-content">
      <?php $flashes = flash_get(); if ($flashes): ?>
        <?php foreach ($flashes as $f): ?>
          <div class="alert alert-<?= h($f['type']) ?> alert-dismissible fade show">
            <?= h($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
