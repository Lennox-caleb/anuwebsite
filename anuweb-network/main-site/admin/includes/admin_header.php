<?php
require_once __DIR__ . '/auth.php';
$admin = current_admin();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle ?? 'Dashboard') ?> | ANU Website Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<style>
  body{ background: #F4F2ED; }
  .admin-shell{ display:flex; min-height:100vh; background:linear-gradient(90deg,#000 250px,#F4F2ED 250px); }  /* sidebar colour continues to the bottom of the page */
  .admin-sidebar{
    width:250px; background: var(--anu-black); color: rgba(255,255,255,.75); flex-shrink:0;
    position: sticky; top: 0; height: 100vh; overflow-y: auto;
  }
  @media (max-width: 767px){
    .admin-shell{ flex-direction: column; }
    .admin-shell{ background:#F4F2ED; }
    .admin-sidebar{ width:100%; position: static; height: auto; overflow-y: visible; }
    .admin-sidebar nav{ display:flex; flex-wrap:wrap; }
    .admin-sidebar nav a{ margin: 2px; }
    .admin-sidebar .nav-group-label{ width:100%; }
    .admin-main{ padding: 1.2rem; }
    .admin-topbar{ flex-direction:column; align-items:flex-start; gap:.5rem; }
  }
  .admin-sidebar a{ color: rgba(255,255,255,.78); text-decoration:none; display:flex; align-items:center; padding:.55rem 1.2rem; font-size:.9rem; border-radius:4px; margin:1px 8px; }
  .admin-sidebar a.active{ background: var(--anu-red); color: #fff; }
  .admin-sidebar a:hover:not(.active){ background: rgba(255,255,255,.08); color:#fff; }
  .admin-sidebar .brand{ padding:1.4rem 1.2rem; border-bottom:1px solid rgba(255,255,255,.15); margin-bottom:.5rem; }
  .admin-sidebar .brand .site-name{ font-family: var(--font-display); font-size:1.05rem; color:#fff; line-height:1.25; display:block; }
  .admin-sidebar .brand .sub-name{ font-size:.68rem; letter-spacing:.06em; text-transform:uppercase; color: var(--anu-gold); font-weight:700; }
  .admin-sidebar .nav-group-label{ font-size:.68rem; text-transform:uppercase; letter-spacing:.06em; color: var(--anu-gold); font-weight:700; padding:1rem 1.2rem .3rem; border-top:1px solid rgba(255,255,255,.1); margin-top:.4rem; }
  .admin-sidebar nav > a:first-child + .nav-group-label,
  .admin-sidebar .brand + nav .nav-group-label:first-of-type{ border-top:none; margin-top:0; }
  .admin-hub-card{ transition: box-shadow .2s, transform .2s, border-color .2s; }
  .admin-hub-card:hover{ box-shadow: 0 12px 24px rgba(0,0,0,.1); transform: translateY(-3px); border-color: var(--anu-gold); }
  .admin-main{ flex:1; padding: 1.8rem 2.2rem; background: #F4F2ED; }
  .admin-topbar{
    display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;
    background:#fff; border-radius:8px; padding: 1rem 1.4rem; box-shadow: 0 2px 8px rgba(0,0,0,.05);
  }
  .admin-topbar h1{ font-family: var(--font-display); }
  .admin-user-pill{ background: var(--anu-panel); color: var(--anu-black); font-size:.82rem; padding:.4rem .9rem; border-radius:20px; }
  .card-anu{ box-shadow: 0 2px 8px rgba(0,0,0,.05); border-radius:8px; }
  .admin-sidebar .logout-block{ border-top:1px solid rgba(255,255,255,.15); }
  .table-anu thead{ background: var(--anu-panel); }
  .card-anu .body.p-0{ overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .table-anu{ min-width: 640px; }

  /* ---- Admin redesign (Part 6): mobile menu, #FAC823 accents, readable tables/forms ---- */
  :root{ --accent:#FAC823; }
  .admin-sidebar a.active{ background: var(--accent); color:#111; font-weight:700; }
  .admin-sidebar a:focus-visible, .admin-menu-btn:focus-visible{ outline:3px solid var(--accent); outline-offset:2px; }
  .admin-topbar{ position: sticky; top: 0; z-index: 20; }
  .admin-menu-btn{ display:none; border:2px solid #111; background:#fff; border-radius:8px; padding:.35rem .7rem; font-weight:700; }
  .table-anu tbody tr:hover{ background: rgba(250, 200, 35,.14); }
  .form-control:focus, .form-select:focus{ border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(250, 200, 35,.35); }
  .card-anu .body{ padding:1.25rem; }
  /* cards placed directly in the page must fit their content (site style gives .card-anu height:100%, which stretched them to the full page height) */
  .admin-main > .card-anu, .admin-main > form > .card-anu{ height:auto; }
  .admin-main > .card-anu:hover{ transform:none; }
  .btn-apply:focus-visible{ outline:3px solid var(--accent); outline-offset:2px; }
  @media (max-width: 767px){
    .admin-menu-btn{ display:inline-flex; align-items:center; gap:.4rem; }
    .admin-sidebar{ position: fixed !important; z-index: 1040; inset: 0 25% 0 0; width:auto; max-width:320px; height:100vh !important; overflow-y:auto !important;
      transform: translateX(-105%); transition: transform .25s ease; box-shadow: 8px 0 24px rgba(0,0,0,.3); }
    body.admin-menu-open .admin-sidebar{ transform:none; }
    body.admin-menu-open::after{ content:''; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1030; }
    .admin-sidebar nav{ display:block !important; }
    .admin-sidebar nav a{ margin:1px 8px; }
    .admin-topbar{ flex-direction:row !important; align-items:center !important; flex-wrap:wrap; }
  }
  @media (prefers-reduced-motion: reduce){ .admin-sidebar{ transition:none; } }

  /* ---- v24: gold (#FAC823) sidebar + top bar with black text, background photo ---- */
  .admin-shell{ background:linear-gradient(90deg,#FAC823 250px,#F4F2ED 250px); }
  .admin-sidebar{ background:#FAC823; color:#000; }
  .admin-sidebar .brand{ padding:1rem .9rem; border-bottom:1px solid rgba(0,0,0,.18); }
  .admin-sidebar .brand .brand-logo{ display:block; background:#fff; border-radius:10px; padding:.35rem .5rem; margin:0; box-shadow:0 2px 6px rgba(0,0,0,.15); }
  .admin-sidebar .brand .brand-logo img{ display:block; width:100%; height:auto; }
  .admin-sidebar a, .admin-sidebar nav a{ color:#000; font-weight:500; }
  .admin-sidebar nav a:hover:not(.active){ background:rgba(0,0,0,.10); color:#000; }
  .admin-sidebar nav a.active{ background:#000; color:#FAC823; font-weight:700; }
  .admin-sidebar .nav-group-label{ color:#000; opacity:.75; border-top:1px solid rgba(0,0,0,.18); }
  .admin-sidebar .logout-block{ border-top:1px solid rgba(0,0,0,.18); }
  .admin-sidebar .logout-block a{ color:#000; font-weight:700; }
  .admin-sidebar a:focus-visible{ outline:3px solid #000; outline-offset:2px; }
  .admin-topbar{ background:#FAC823; box-shadow:0 2px 8px rgba(0,0,0,.12); }
  .admin-topbar, .admin-topbar h1, .admin-topbar a{ color:#000; }
  .admin-user-pill{ background:#000; color:#FAC823; }
  .admin-menu-btn{ border-color:#000; background:#FAC823; color:#000; }
  .admin-main{ background:linear-gradient(rgba(244,242,237,.82),rgba(244,242,237,.82)), url('<?= BASE_URL ?>/assets/images/admin-bg.jpg') center/cover fixed no-repeat; }
  @media (max-width:767px){ .admin-shell{ background:#F4F2ED; } }
</style>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin-theme.css?v=2">
<script src="<?= BASE_URL ?>/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="brand">
      <a href="<?= BASE_URL ?>/admin/dashboard.php" class="brand-logo" aria-label="Dashboard"><img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University"></a>
    </div>
    <nav class="pb-3">
      <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <?php if (in_array($admin['role'], ['editor','marketing','super_admin'], true)): ?>
        <p class="nav-group-label mb-0">Site Look</p>
        <a href="<?= BASE_URL ?>/admin/sliders.php" class="<?= $current==='sliders.php' || $current==='sliders_form.php' ?'active':'' ?>"><i class="bi bi-image me-2"></i>Hero Images</a>
      <?php endif; ?>
      <?php if (in_array($admin['role'], ['editor','super_admin'], true)): ?>
        <p class="nav-group-label mb-0">Leadership</p>
        <a href="<?= BASE_URL ?>/admin/leadership.php" class="<?= $current==='leadership.php' || $current==='leadership_form.php' ?'active':'' ?>"><i class="bi bi-person-vcard me-2"></i>VC &amp; Deputy VCs</a>
      <?php endif; ?>
      <?php if (in_array($admin['role'], ['editor','super_admin'], true)): ?>
        <p class="nav-group-label mb-0">Academic Content</p>
        <a href="<?= BASE_URL ?>/admin/news.php" class="<?= $current==='news.php'?'active':'' ?>"><i class="bi bi-newspaper me-2"></i>News</a>
        <a href="<?= BASE_URL ?>/admin/events.php" class="<?= $current==='events.php'?'active':'' ?>"><i class="bi bi-calendar-event me-2"></i>Events</a>
        <a href="<?= BASE_URL ?>/admin/photo_gallery.php" class="<?= $current==='photo_gallery.php'?'active':'' ?>"><i class="bi bi-images me-2"></i>Photo Gallery</a>
        <a href="<?= BASE_URL ?>/admin/schools.php" class="<?= $current==='schools.php'?'active':'' ?>"><i class="bi bi-bank me-2"></i>Schools</a>
        <a href="<?= BASE_URL ?>/admin/programmes.php" class="<?= $current==='programmes.php'?'active':'' ?>"><i class="bi bi-mortarboard me-2"></i>Programmes</a>
        <a href="<?= BASE_URL ?>/admin/staff.php" class="<?= $current==='staff.php'?'active':'' ?>"><i class="bi bi-people-fill me-2"></i>Faculty &amp; Staff</a>
        <a href="<?= BASE_URL ?>/admin/departments.php" class="<?= $current==='departments.php'?'active':'' ?>"><i class="bi bi-diagram-3 me-2"></i>Departments</a>
        <a href="<?= BASE_URL ?>/admin/governance.php" class="<?= $current==='governance.php'?'active':'' ?>"><i class="bi bi-diagram-3 me-2"></i>Governance</a>
        <a href="<?= BASE_URL ?>/admin/research.php" class="<?= $current==='research.php'?'active':'' ?>"><i class="bi bi-journal-text me-2"></i>Research Publications</a>
        <a href="<?= BASE_URL ?>/admin/proposals.php" class="<?= $current==='proposals.php'?'active':'' ?>"><i class="bi bi-file-earmark-text me-2"></i>Research Proposals</a>
      <?php endif; ?>
      <?php if (in_array($admin['role'], ['editor','marketing','super_admin'], true)): ?>
        <p class="nav-group-label mb-0">Unit Sites</p>
        <a href="<?= BASE_URL ?>/admin/unit_sites.php" class="<?= $current==='unit_sites.php' || $current==='site_content.php' ?'active':'' ?>"><i class="bi bi-diagram-2 me-2"></i>Manage Unit Sites</a>
        <a href="<?= BASE_URL ?>/admin/site.php?site=orientation-pack" class="<?= ($_GET['site'] ?? '')==='orientation-pack' && $current==='site.php' ?'active':'' ?>"><i class="bi bi-signpost-split me-2"></i>Orientation</a>
      <?php endif; ?>
      <?php if (in_array($admin['role'], ['marketing','super_admin'], true)): ?>
        <p class="nav-group-label mb-0">Marketing &amp; Comms</p>
        <a href="<?= BASE_URL ?>/admin/magazines.php" class="<?= $current==='magazines.php' || $current==='magazines_form.php' ?'active':'' ?>"><i class="bi bi-journal-richtext me-2"></i>ASPIRE &amp; ANU Voice</a>
        <a href="<?= BASE_URL ?>/admin/tenders.php" class="<?= $current==='tenders.php' || $current==='tenders_form.php' ?'active':'' ?>"><i class="bi bi-file-earmark-ruled me-2"></i>Tenders &amp; Procurement</a>
        <a href="<?= BASE_URL ?>/admin/csr_gallery.php" class="<?= $current==='csr_gallery.php' || $current==='csr_gallery_form.php' ?'active':'' ?>"><i class="bi bi-images me-2"></i>CSR Photo Gallery</a>
        <a href="<?= BASE_URL ?>/admin/chaplaincy.php" class="<?= $current==='chaplaincy.php' || $current==='chaplaincy_form.php' ?'active':'' ?>"><i class="bi bi-heart me-2"></i>Chaplaincy Ministries</a>
        <a href="<?= BASE_URL ?>/admin/vacancies.php" class="<?= $current==='vacancies.php' || $current==='vacancies_form.php' ?'active':'' ?>"><i class="bi bi-briefcase me-2"></i>Vacancies</a>
        <a href="<?= BASE_URL ?>/admin/testimonials.php" class="<?= $current==='testimonials.php' || $current==='testimonials_form.php' ?'active':'' ?>"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
        <a href="<?= BASE_URL ?>/admin/partners.php" class="<?= $current==='partners.php'?'active':'' ?>"><i class="bi bi-handshake me-2"></i>Partners</a>
        <a href="<?= BASE_URL ?>/admin/resources.php" class="<?= $current==='resources.php'?'active':'' ?>"><i class="bi bi-folder2-open me-2"></i>Resources Library</a>
        <a href="<?= BASE_URL ?>/admin/announcements.php" class="<?= $current==='announcements.php'?'active':'' ?>"><i class="bi bi-megaphone me-2"></i>Popup Announcements</a>
        <a href="<?= BASE_URL ?>/admin/campus_tour.php" class="<?= $current==='campus_tour.php'?'active':'' ?>"><i class="bi bi-camera-video me-2"></i>Campus Tour</a>
        <a href="<?= BASE_URL ?>/admin/messages.php" class="<?= $current==='messages.php'?'active':'' ?>"><i class="bi bi-envelope me-2"></i>Messages</a>
        <a href="<?= BASE_URL ?>/admin/newsletters.php" class="<?= in_array($current, ['newsletters.php','newsletter_form.php','newsletter_send.php'], true) ? 'active' : '' ?>"><i class="bi bi-send me-2"></i>Newsletters</a>
        <a href="<?= BASE_URL ?>/admin/subscribers.php" class="<?= $current==='subscribers.php'?'active':'' ?>"><i class="bi bi-people me-2"></i>Subscribers</a>
      <?php endif; ?>
      <?php if ($admin['role'] === 'super_admin'): ?>
        <p class="nav-group-label mb-0">Administration</p>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="<?= $current==='settings.php'?'active':'' ?>"><i class="bi bi-gear me-2"></i>Site Settings</a>
        <a href="<?= BASE_URL ?>/admin/site_admins.php" class="<?= $current==='site_admins.php' ?'active':'' ?>"><i class="bi bi-person-gear me-2"></i>Site Admin Accounts</a>
        <a href="<?= BASE_URL ?>/admin/users.php" class="<?= $current==='users.php' || $current==='users_form.php' ?'active':'' ?>"><i class="bi bi-person-badge me-2"></i>Admin Users</a>
        <a href="<?= BASE_URL ?>/admin/system_check.php" class="<?= $current==='system_check.php'?'active':'' ?>"><i class="bi bi-heart-pulse me-2"></i>System Check<?= (function_exists('schema_check_report') && count(array_filter(schema_check_report(), fn($r) => !$r['ok']))) ? ' <span class="badge bg-danger ms-1">!</span>' : '' ?></a>
      <?php endif; ?>
      <div class="mt-3 pt-2 logout-block">
        <a href="<?= BASE_URL ?>/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log Out</a>
      </div>
    </nav>
  </aside>
  <main class="admin-main">
    <div class="admin-topbar">
      <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-controls="adminSidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i> Menu</button>
      <h1 class="h4 mb-0"><?= h($pageTitle ?? 'Dashboard') ?></h1>
      <div class="d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/index.php" target="_blank" rel="noopener" class="btn btn-outline-anu btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i>View Live Site</a>
        <div class="admin-user-pill">Signed in as <strong><?= h($admin['full_name']) ?></strong> &middot; <?= h($admin['role']) ?></div>
      </div>
    </div>
    <?php if ($__flash = flash_get()): ?>
      <div class="alert alert-<?= h($__flash['type']) ?> alert-dismissible fade show" role="alert" aria-live="polite">
        <?= h($__flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
