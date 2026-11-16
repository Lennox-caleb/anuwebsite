<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/admin_header.php';

$counts = [
    ['News articles',   db()->query('SELECT COUNT(*) c FROM news')->fetch()['c'],              'bi-newspaper',    BASE_URL . '/admin/news.php'],
    ['Events',          db()->query('SELECT COUNT(*) c FROM events')->fetch()['c'],             'bi-calendar-event', BASE_URL . '/admin/events.php'],
    ['Programmes',      db()->query('SELECT COUNT(*) c FROM programmes')->fetch()['c'],         'bi-mortarboard',  BASE_URL . '/admin/programmes.php'],
    ['Unread messages', db()->query('SELECT COUNT(*) c FROM contact_messages WHERE is_read = 0')->fetch()['c'], 'bi-envelope', BASE_URL . '/admin/messages.php'],
];

// Each of these is one clearly separate area of the site -- a "hub" of
// destinations rather than a wall of sidebar links, so it's obvious at a
// glance where a given task lives and nothing feels tangled together.
// Same admin['role'] gating as the sidebar, so nobody sees a card for a
// section they can't open anyway.
$sections = [
    ['Site Look', 'Homepage hero images, video and slideshow.', 'bi-image', BASE_URL . '/admin/sliders.php', ['editor','marketing','super_admin']],
    ['Leadership', 'Vice Chancellor and Deputy VC profiles.', 'bi-person-vcard', BASE_URL . '/admin/leadership.php', ['editor','super_admin']],
    ['Academic Content', 'News, events, schools, programmes, staff, governance and research.', 'bi-mortarboard', BASE_URL . '/admin/schools.php', ['editor','super_admin']],
    ['Unit Sites', 'Manage the 12 school and department mini-sites.', 'bi-diagram-2', BASE_URL . '/admin/unit_sites.php', ['editor','marketing','super_admin']],
    ['Marketing & Comms', 'Magazines, tenders, vacancies, resources, announcements.', 'bi-megaphone', BASE_URL . '/admin/magazines.php', ['marketing','super_admin']],
    ['Administration', 'Site-wide settings and admin user accounts.', 'bi-gear', BASE_URL . '/admin/settings.php', ['super_admin']],
];
?>
<div class="row g-3 mb-4">
  <?php foreach ($counts as [$label, $value, $icon, $url]): ?>
    <div class="col-6 col-lg-3">
      <a href="<?= h($url) ?>" class="card-anu feature-card d-block h-100 text-decoration-none"><div class="body">
        <div class="icon-badge"><i class="bi <?= $icon ?>"></i></div>
        <div class="h3 mb-0 text-dark"><?= (int)$value ?></div>
        <div class="text-muted small"><?= h($label) ?></div>
      </div></a>
    </div>
  <?php endforeach; ?>
</div>

<div class="section-head reveal" style="border-bottom:none; margin-bottom:1rem;"><h2 class="h5 mb-0">Jump to a section</h2></div>
<div class="row g-3 mb-4">
  <?php foreach ($sections as [$title, $desc, $icon, $url, $roles]): if (!in_array($admin['role'], $roles, true)) continue; ?>
    <div class="col-md-6 col-lg-4">
      <a href="<?= h($url) ?>" class="card-anu d-block h-100 text-decoration-none admin-hub-card"><div class="body">
        <div class="icon-badge"><i class="bi <?= $icon ?>"></i></div>
        <h3 class="h6 text-dark mb-1"><?= h($title) ?></h3>
        <p class="small text-muted mb-0"><?= h($desc) ?></p>
      </div></a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card-anu">
  <div class="body">
    <h2 class="h5">Quick actions</h2>
    <a href="<?= BASE_URL ?>/admin/news_form.php" class="btn btn-apply btn-sm me-2 mb-2">+ New Article</a>
    <a href="<?= BASE_URL ?>/admin/events_form.php" class="btn btn-outline-anu btn-sm me-2 mb-2">+ New Event</a>
    <a href="<?= BASE_URL ?>/admin/programmes_form.php" class="btn btn-outline-anu btn-sm me-2 mb-2">+ New Programme</a>
    <a href="<?= BASE_URL ?>/admin/messages.php" class="btn btn-outline-anu btn-sm mb-2">View Messages</a>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
