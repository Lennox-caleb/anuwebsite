<?php
// admin/includes/admin-layout.php

function adminHead(string $title): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . htmlspecialchars($title) . ' | ANU Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* ── ANU ADMIN PANEL STYLES ── */
:root {
  --primary:#cc0000;
  --primary-dark:#a00000;
  --accent:#fac823;
  --border:#eaeaee;
  --text-dark:#1e1e2e;
  --text-mid:#666;
  --bg:#f4f5f7;
  /* aliases matching public site vars */
  --red:#cc0000;
  --gold:#fac823;
  --dark:#1e1e2e;
  --light:#f4f5f7;
  --muted:#888;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"Roboto",system-ui,sans-serif;background:#f4f5f7;color:#1e1e2e;min-height:100vh;display:flex;flex-direction:column}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}

/* Layout */
.admin-wrap{display:flex;min-height:100vh}

/* Sidebar */
.admin-sidebar{
  width:248px;min-width:248px;background:#1c0303;
  display:flex;flex-direction:column;
  position:fixed;top:0;left:0;bottom:0;z-index:200;
  transition:transform .25s ease;overflow-y:auto;overflow-x:hidden;
}
.admin-sidebar::-webkit-scrollbar{width:4px}
.admin-sidebar::-webkit-scrollbar-track{background:transparent}
.admin-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:2px}

.sidebar-brand{
  padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;justify-content:flex-start;flex-shrink:0;
  background:rgba(0,0,0,.25);
}
.sidebar-brand-icon{
  width:38px;height:38px;border-radius:10px;
  background:linear-gradient(135deg,#cc0000,#880000);
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0;
  font-family:Georgia,serif;
}
.sidebar-brand-text{display:flex;flex-direction:column;line-height:1}
.sidebar-brand-name{font-size:.95rem;font-weight:800;color:#fff;letter-spacing:-.3px}
.sidebar-brand-sub{font-size:.62rem;color:rgba(255,255,255,.35);letter-spacing:1.2px;text-transform:uppercase;margin-top:2px}

.sidebar-nav{flex:1;padding:12px 10px}
.sidebar-section{
  font-size:.6rem;font-weight:700;letter-spacing:1.8px;
  text-transform:uppercase;color:rgba(255,255,255,.25);
  padding:14px 10px 6px;
}
.sidebar-link{
  display:flex;align-items:center;gap:10px;
  padding:9px 12px;border-radius:8px;
  font-size:.82rem;font-weight:500;color:rgba(255,255,255,.6);
  transition:.18s ease;margin-bottom:1px;white-space:nowrap;
}
.sidebar-link i{font-size:.95rem;flex-shrink:0;width:18px;text-align:center}
.sidebar-link:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.9)}
.sidebar-link.active{background:rgba(204,0,0,.25);color:#ff6666;font-weight:600}
.sidebar-link.active i{color:#ff4444}
.sidebar-link.logout{color:rgba(255,80,80,.6)}
.sidebar-link.logout:hover{background:rgba(204,0,0,.15);color:#ff6666}

.sidebar-user{
  padding:14px 16px;border-top:1px solid rgba(255,255,255,.06);
  display:flex;align-items:center;gap:10px;flex-shrink:0;
}
.sidebar-avatar{
  width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,#cc0000,#fac823);
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:.85rem;color:#fff;flex-shrink:0;
}
.sidebar-uname{font-size:.82rem;font-weight:600;color:#fff;line-height:1}
.sidebar-urole{font-size:.68rem;color:rgba(255,255,255,.35);margin-top:2px}

/* Main content */
.admin-main{
  margin-left:248px;flex:1;display:flex;flex-direction:column;
  min-height:100vh;transition:margin .25s ease;
}

/* Topbar */
.admin-topbar{
  background:#fff;border-bottom:1px solid #e8e8ec;
  padding:0 24px;height:56px;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(0,0,0,.05);
}
.topbar-left{display:flex;align-items:center;gap:12px}
.topbar-page-title{font-size:1rem;font-weight:700;color:#1e1e2e}
.sidebar-toggle{
  display:none;background:none;border:none;cursor:pointer;
  padding:6px;border-radius:6px;color:#555;font-size:1.3rem;
  transition:.15s;
}
.sidebar-toggle:hover{background:#f0f0f0}
.topbar-right{display:flex;align-items:center;gap:12px}
.topbar-date{font-size:.75rem;color:#aaa;font-weight:500}
.btn-view-site{
  display:inline-flex;align-items:center;gap:6px;
  background:#f4f5f7;border:1px solid #e0e0e0;
  padding:6px 14px;border-radius:50px;
  font-size:.75rem;font-weight:600;color:#555;
  transition:.18s;
}
.btn-view-site:hover{background:#cc0000;color:#fff;border-color:#cc0000}

/* Content area */
.admin-body{padding:24px;flex:1}

/* Cards */
.admin-card{
  background:#fff;border-radius:14px;
  border:1px solid #eaeaee;
  box-shadow:0 1px 6px rgba(0,0,0,.04);
  overflow:hidden;margin-bottom:20px;
}
.admin-card-header{
  padding:16px 20px;border-bottom:1px solid #f0f0f4;
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:8px;
}
.admin-card-header h5{font-size:.95rem;font-weight:700;margin:0;color:#1e1e2e}
.admin-card-body{padding:20px}
.admin-card-body.p-0{padding:0}

/* Stats */
.stat-card{
  background:#fff;border-radius:14px;padding:20px;
  border:1px solid #eaeaee;box-shadow:0 1px 6px rgba(0,0,0,.04);
  display:flex;align-items:center;gap:16px;
}
.stat-card-icon{
  width:50px;height:50px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.3rem;flex-shrink:0;
}
.stat-card-num{font-size:1.7rem;font-weight:800;color:#1e1e2e;line-height:1}
.stat-card-label{font-size:.75rem;color:#888;font-weight:500;margin-top:3px}

/* Tables */
.table{font-size:.85rem}
.table th{font-size:.72rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#888;background:#fafafa;border-bottom:1px solid #eee;padding:10px 16px}
.table td{padding:12px 16px;border-bottom:1px solid #f5f5f5;vertical-align:middle}
.table tbody tr:hover{background:#fafbff}
.table tbody tr:last-child td{border-bottom:none}

/* Badges */
.anu-badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:.7rem;font-weight:700;letter-spacing:.4px}
.badge-success{background:#e8f5e9;color:#2e7d32}
.badge-warning{background:#fff8e1;color:#e65100}
.badge-danger{background:#fce4ec;color:#c62828}
.badge-info{background:#e3f2fd;color:#1565c0}
.badge-muted{background:#f5f5f5;color:#777}

/* Form styles */
.form-label{font-size:.8rem;font-weight:600;color:#444;margin-bottom:5px}
.form-control,.form-select{
  border-radius:8px !important;
  border:1.5px solid #e0e0e8 !important;
  padding:9px 13px !important;
  font-size:.875rem !important;
  font-family:"Roboto",system-ui,sans-serif;
  transition:.18s;
}
.form-control:focus,.form-select:focus{
  border-color:#cc0000 !important;
  box-shadow:0 0 0 3px rgba(204,0,0,.1) !important;
  outline:none;
}
textarea.form-control{min-height:100px}
.form-text{font-size:.74rem;color:#aaa;margin-top:4px}

/* Alerts */
.alert{border-radius:10px;border:none;padding:12px 16px;font-size:.875rem}
.alert-success{background:#e8f5e9;color:#2e7d32}
.alert-danger{background:#fce4ec;color:#c62828}
.alert-warning{background:#fff8e1;color:#e65100}
.alert-info{background:#e3f2fd;color:#1565c0}

/* Buttons */
.btn{border-radius:8px;font-size:.83rem;font-weight:600;font-family:"Roboto",system-ui,sans-serif}
.btn-danger{background:#cc0000;border-color:#cc0000}
.btn-danger:hover{background:#a00000;border-color:#a00000}
.btn-sm{padding:5px 12px;font-size:.78rem;border-radius:6px}

/* Image preview */
.img-preview{width:100%;height:110px;object-fit:cover;border-radius:8px;border:1px solid #eee}

/* Overlay for mobile */
.sidebar-overlay{
  display:none;position:fixed;inset:0;
  background:rgba(0,0,0,.5);z-index:199;
}

/* Responsive */
@media(max-width:991px){
  .admin-sidebar{transform:translateX(-100%)}
  .admin-sidebar.open{transform:translateX(0)}
  .admin-main{margin-left:0}
  .sidebar-toggle{display:flex;align-items:center;justify-content:center}
  .sidebar-overlay.show{display:block}
  .admin-body{padding:16px}
}
@media(max-width:575px){
  .admin-topbar{padding:0 14px}
  .admin-body{padding:12px}
  .topbar-date{display:none}
  .admin-card-header{padding:12px 14px}
  .admin-card-body{padding:14px}
  .stat-card{padding:14px}
  .stat-card-num{font-size:1.4rem}
}

/* ── Programs admin: school tabs + grouped table ── */
.prog-admin-tabs {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 10px 14px;
}
.pat-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 50px;
  font-size: .8rem;
  font-weight: 600;
  color: var(--text-mid);
  text-decoration: none;
  transition: background .2s,color .2s;
  border: 1.5px solid transparent;
}
.pat-tab:hover { background: rgba(255,0,0,.06); color: var(--primary); }
.pat-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.pat-n {
  background: rgba(255,255,255,.25);
  border-radius: 50px;
  padding: 1px 7px;
  font-size: .7rem;
}
.pat-tab:not(.active) .pat-n { background: rgba(0,0,0,.07); color: inherit; }
.pat-add {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 18px;
  border-radius: 50px;
  background: var(--primary);
  color: #fff !important;
  font-size: .8rem;
  font-weight: 700;
  text-decoration: none;
  transition: opacity .2s;
}
.pat-add:hover { opacity: .88; }
/* School group header row inside card */
.prog-school-hdr {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 20px;
  background: rgba(255,0,0,.04);
  border-bottom: 1px solid var(--border);
  font-weight: 700;
  font-size: .85rem;
  color: var(--text-dark);
}
.prog-school-hdr i { color: var(--primary); font-size: 1rem; }
.prog-school-hdr em { font-style: normal; font-size: .74rem; color: var(--text-mid); font-weight: 500; margin-left: auto; }

/* ── Program image upload widget ── */
.prog-img-upload {
  position: relative;
  width: 100%; max-width: 360px;
  height: 180px;
  border: 2px dashed var(--border);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: border-color .2s;
  background: #fafafa;
}
.prog-img-upload:hover { border-color: var(--primary); }
.prog-img-upload img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
}
.prog-img-placeholder {
  width: 100%; height: 100%;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 6px; color: #aaa;
}
.prog-img-placeholder i  { font-size: 2rem; }
.prog-img-placeholder span { font-size: .82rem; font-weight: 600; }
.prog-img-placeholder small { font-size: .72rem; }
.prog-img-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.45);
  color: #fff; font-size: .82rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  gap: 6px; opacity: 0;
  transition: opacity .2s;
}
.prog-img-upload:hover .prog-img-overlay { opacity: 1; }
/* Courses count badge in table */
.courses-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: .78rem; font-weight: 700;
  color: var(--primary);
  background: rgba(255,0,0,.07);
  padding: 3px 10px; border-radius: 50px;
  text-decoration: none; transition: background .2s;
}
.courses-badge:hover { background: rgba(255,0,0,.15); }

/* ── Missing utility classes used across admin pages ── */
.btn-primary-anu {
  background: var(--primary);
  color: #fff !important;
  border: none;
  font-weight: 700;
  font-family: "Roboto",system-ui,sans-serif;
  transition: background .18s;
}
.btn-primary-anu:hover { background: var(--primary-dark); color:#fff !important; }

.badge-status {
  display: inline-block;
  padding: 3px 12px;
  border-radius: 50px;
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .4px;
}
.badge-active   { background: #e8f5e9; color: #2e7d32; }
.badge-inactive { background: #f5f5f5; color: #999; }
.badge-new      { background: #e3f2fd; color: #1565c0; }

.anu-form .form-label { font-size:.8rem;font-weight:600;color:#444;margin-bottom:5px; }
.anu-form .form-control, .anu-form .form-select {
  border-radius:8px !important;
  border:1.5px solid #e0e0e8 !important;
  padding:9px 13px !important;
  font-size:.875rem !important;
}
.anu-form .form-control:focus, .anu-form .form-select:focus {
  border-color:var(--primary) !important;
  box-shadow:0 0 0 3px rgba(204,0,0,.1) !important;
  outline:none;
}

.alert-anu-success {
  background:#e8f5e9; color:#2e7d32;
  border-radius:10px; padding:12px 16px; font-size:.875rem;
  display:flex; align-items:center;
}
.alert-anu-error {
  background:#fce4ec; color:#c62828;
  border-radius:10px; padding:12px 16px; font-size:.875rem;
  display:flex; align-items:center;
}

.btn-submit {
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  background:var(--primary); color:#fff;
  border:none; border-radius:50px;
  padding:12px 36px; font-size:.9rem; font-weight:700;
  font-family:"Roboto",system-ui,sans-serif;
  cursor:pointer; transition:background .18s;
}
.btn-submit:hover { background:var(--primary-dark); }

.admin-card > .admin-card-body { padding:24px; }
.admin-card > h5 { padding:24px 24px 0; font-size:.95rem; font-weight:700; color:var(--text-dark); }
</style>
<link rel="stylesheet" href="/anuweb-network/main-site/assets/css/admin-theme.css?v=2">
<script src="/anuweb-network/main-site/assets/js/admin-theme.js?v=2" defer></script>
</head>
<body>
<div class="admin-wrap">
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
';
}

function adminSidebar(string $active = ''): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
    $role = htmlspecialchars(ucfirst($_SESSION['admin_role'] ?? 'editor'));
    $ini  = strtoupper(($_SESSION['admin_name'] ?? 'A')[0]);

    $items = [
        ['icon'=>'bi-speedometer2',        'label'=>'Dashboard',     'href'=>'/anu/admin/dashboard.php',    'key'=>'dashboard'],
        ['section'=>'Content'],
        ['icon'=>'bi-collection-fill',     'label'=>'Hero Slides',   'href'=>'/anu/admin/slides.php',       'key'=>'slides'],
        ['icon'=>'bi-house-door-fill',     'label'=>'Hero Sections', 'href'=>'/anu/admin/heroes.php',       'key'=>'heroes'],
        ['icon'=>'bi-images',              'label'=>'Gallery',       'href'=>'/anu/admin/gallery.php',      'key'=>'gallery'],
        ['icon'=>'bi-newspaper',           'label'=>'News',          'href'=>'/anu/admin/news.php',         'key'=>'news'],
        ['icon'=>'bi-calendar-event-fill', 'label'=>'Events',        'href'=>'/anu/admin/events.php',       'key'=>'events'],
        ['section'=>'Academics'],
        ['icon'=>'bi-mortarboard-fill',    'label'=>'Programs',      'href'=>'/anu/admin/programs.php',     'key'=>'programs'],
        ['icon'=>'bi-patch-question-fill', 'label'=>'FAQs',          'href'=>'/anu/admin/faqs.php',         'key'=>'faqs'],
        ['section'=>'Campus Life'],
        ['icon'=>'bi-people-fill',         'label'=>'Clubs',         'href'=>'/anu/admin/clubs.php',        'key'=>'clubs'],
        ['icon'=>'bi-chat-quote-fill',     'label'=>'Testimonials',  'href'=>'/anu/admin/testimonials.php', 'key'=>'testimonials'],
        ['section'=>'Support Centre'],
        ['icon'=>'bi-collection-play-fill','label'=>'Tutorials & Resources','href'=>'/anu/admin/support_resources.php','key'=>'support_resources'],
        ['icon'=>'bi-broadcast-pin',        'label'=>'Live Events',          'href'=>'/anu/admin/livestream.php',       'key'=>'livestream'],
        ['section'=>'Inbox'],
        ['icon'=>'bi-envelope-fill',       'label'=>'Messages',      'href'=>'/anu/admin/messages.php',     'key'=>'messages'],
        ['icon'=>'bi-heart-fill',          'label'=>'Prayer Requests','href'=>'/anu/admin/prayers.php',     'key'=>'prayers'],
        ['icon'=>'bi-person-plus-fill',    'label'=>'Applications',  'href'=>'/anu/admin/applications.php', 'key'=>'applications'],
        ['section'=>'Settings'],
        ['icon'=>'bi-sliders',             'label'=>'Site Settings', 'href'=>'/anu/admin/settings.php',    'key'=>'settings'],
        ['icon'=>'bi-person-circle',       'label'=>'Admin Users',   'href'=>'/anu/admin/users.php',        'key'=>'users'],
    ];

    echo '<aside class="admin-sidebar" id="adminSidebar">';
    echo '<div class="sidebar-brand">';
    echo '<img src="/anu/assets/images/anu-logo-white.png" alt="ANU Online" style="height:46px;width:auto;max-width:180px;object-fit:contain;filter:brightness(1.1)">';
    echo '</div>';
    echo '<nav class="sidebar-nav">';
    foreach ($items as $item) {
        if (isset($item['section'])) {
            echo '<div class="sidebar-section">' . htmlspecialchars($item['section']) . '</div>';
        } else {
            $cls = ($active === $item['key']) ? 'sidebar-link active' : 'sidebar-link';
            echo '<a href="' . $item['href'] . '" class="' . $cls . '">';
            echo '<i class="bi ' . $item['icon'] . '"></i>' . htmlspecialchars($item['label']);
            echo '</a>';
        }
    }
    echo '<div class="sidebar-section" style="margin-top:8px"></div>';
    echo '<a href="/anu/admin/logout.php" class="sidebar-link logout"><i class="bi bi-box-arrow-left"></i>Logout</a>';
    echo '</nav>';
    echo '<div class="sidebar-user">';
    echo '<div class="sidebar-avatar">' . $ini . '</div>';
    echo '<div><div class="sidebar-uname">' . $name . '</div><div class="sidebar-urole">' . $role . '</div></div>';
    echo '</div>';
    echo '</aside>';
    echo '<div class="admin-main">';
}

function adminTopBar(string $title, string $sub = ''): void {
    echo '<div class="admin-topbar">';
    echo '<div class="topbar-left">';
    echo '<button class="sidebar-toggle" onclick="openSidebar()"><i class="bi bi-list"></i></button>';
    echo '<div class="topbar-page-title">' . htmlspecialchars($title) . '</div>';
    if ($sub) echo '<span style="color:#ccc;font-size:.8rem">/ ' . htmlspecialchars($sub) . '</span>';
    echo '</div>';
    echo '<div class="topbar-right">';
    echo '<span class="topbar-date">' . date('D, M j Y') . '</span>';
    echo '<a href="/anu/index.php" target="_blank" class="btn-view-site"><i class="bi bi-eye"></i> View Site</a>';
    echo '</div>';
    echo '</div>';
    echo '<div class="admin-body">';
}

function adminFoot(): void {
    echo '</div>'; // admin-body
    echo '</div>'; // admin-main
    echo '</div>'; // admin-wrap
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<script>
function openSidebar(){
  document.getElementById("adminSidebar").classList.add("open");
  document.getElementById("sidebarOverlay").classList.add("show");
}
function closeSidebar(){
  document.getElementById("adminSidebar").classList.remove("open");
  document.getElementById("sidebarOverlay").classList.remove("show");
}
// Close sidebar on nav link click (mobile)
document.querySelectorAll(".sidebar-link").forEach(function(l){
  l.addEventListener("click",function(){
    if(window.innerWidth<992) closeSidebar();
  });
});
</script>';
    echo '<script>
function confirmDelete(url, name){
  if(confirm("Delete \"" + name + "\"?\nThis cannot be undone.")){
    window.location.href = url;
  }
}
</script>';
    echo '</body></html>';
}
?>
