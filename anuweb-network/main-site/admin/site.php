<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);

$sites = include __DIR__ . '/includes/sites_registry.php';
$slug  = $_GET['site'] ?? '';

if (!isset($sites[$slug])) {
    redirect('/admin/unit_sites.php');
}
$site = $sites[$slug];

$pageTitle = $site['label'];
require __DIR__ . '/includes/admin_header.php';

// Current hero image for this site's homepage.
$heroStmt = db()->prepare('SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1');
$heroStmt->execute([$slug]);
$hero = $heroStmt->fetch();
$heroCountStmt = db()->prepare('SELECT COUNT(*) FROM sliders WHERE unit_slug = ?');
$heroCountStmt->execute([$slug]);
$heroCount = (int) $heroCountStmt->fetchColumn();

// Staff on this site's regular listing (not counting the featured leader slot).
$staffStmt = db()->prepare('SELECT COUNT(*) FROM staff WHERE unit_slug = ?');
$staffStmt->execute([$slug]);
$staffCount = (int) $staffStmt->fetchColumn();

// This site's one featured leader message (Dean's Message, Registrar, etc.).
$leader = null;
if (!empty($site['leader_slug'])) {
    $leaderStmt = db()->prepare('SELECT * FROM staff WHERE unit_slug = ? LIMIT 1');
    $leaderStmt->execute([$site['leader_slug']]);
    $leader = $leaderStmt->fetch();
}

// Departments (Phase 5, academic schools only).
$departmentCount = null;
if (!empty($site['has_departments'])) {
    $dStmt = db()->prepare('SELECT COUNT(*) FROM departments WHERE unit_slug = ?');
    $dStmt->execute([$slug]);
    $departmentCount = (int) $dStmt->fetchColumn();
}

// This site's own News & Updates and Upcoming Events counts (Phase 5) --
// shown for every site, since any unit can now have its own scoped
// news/events, not just the 5 academic schools.
$newsCountStmt = db()->prepare('SELECT COUNT(*) FROM news WHERE unit_slug = ?');
$newsCountStmt->execute([$slug]);
$siteNewsCount = (int) $newsCountStmt->fetchColumn();
$eventsCountStmt = db()->prepare('SELECT COUNT(*) FROM events WHERE unit_slug = ?');
$eventsCountStmt->execute([$slug]);
$siteEventsCount = (int) $eventsCountStmt->fetchColumn();

// Programmes, if this site has any.
$programmeCount = null;
$programmesLink = null;
$newProgrammeLink = null;
if (!empty($site['programme_filter'])) {
    $pf = $site['programme_filter'];
    if ($pf['type'] === 'school_slug') {
        $schoolStmt = db()->prepare('SELECT id FROM schools WHERE slug = ?');
        $schoolStmt->execute([$pf['value']]);
        $schoolId = $schoolStmt->fetchColumn();
        if ($schoolId) {
            $cStmt = db()->prepare('SELECT COUNT(*) FROM programmes WHERE school_id = ?');
            $cStmt->execute([$schoolId]);
            $programmeCount = (int) $cStmt->fetchColumn();
            $programmesLink = BASE_URL . '/admin/programmes.php?school_id=' . (int)$schoolId;
            $newProgrammeLink = BASE_URL . '/admin/programmes_form.php?school_id=' . (int)$schoolId;
        }
    } else { // unit_slug
        $cStmt = db()->prepare('SELECT COUNT(*) FROM programmes WHERE unit_slug = ?');
        $cStmt->execute([$pf['value']]);
        $programmeCount = (int) $cStmt->fetchColumn();
        $programmesLink = BASE_URL . '/admin/programmes.php?unit=' . urlencode($pf['value']);
        $newProgrammeLink = BASE_URL . '/admin/programmes_form.php?unit=' . urlencode($pf['value']);
    }
}
?>
<nav class="small text-muted mb-2"><a href="<?= BASE_URL ?>/admin/unit_sites.php">Sites</a> &rsaquo; <?= h($site['label']) ?></nav>
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h1 class="h4 mb-1"><?= h($site['label']) ?></h1>
    <span class="text-muted small"><?= h($site['group']) ?></span>
  </div>
  <a href="<?= h($site['url']) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu btn-sm"><i class="bi bi-box-arrow-up-right me-1"></i>View Site</a>
</div>

<div class="row g-3">

  <!-- Hero image -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-image me-2"></i>Hero Image</h2>
        <?php if ($hero): ?>
          <div class="d-flex gap-3 align-items-start">
            <?php if ($hero['image']): ?>
              <img src="<?= BASE_URL ?>/assets/uploads/<?= h($hero['image']) ?>" class="img-preview-sm rounded border">
            <?php else: ?>
              <div class="text-muted small border rounded p-2">No image set</div>
            <?php endif; ?>
            <div>
              <div class="fw-semibold"><?= h($hero['headline'] ?: '(no headline)') ?></div>
              <?php if ($hero['subtext']): ?><div class="small text-muted"><?= h($hero['subtext']) ?></div><?php endif; ?>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/admin/sliders_form.php?id=<?= (int)$hero['id'] ?>" class="btn btn-sm btn-apply mt-3">Edit Hero Image</a>
        <?php else: ?>
          <p class="text-muted small mb-3">No hero image set yet, the homepage is showing its default design.</p>
          <a href="<?= BASE_URL ?>/admin/sliders_form.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-apply">Add Hero Image</a>
        <?php endif; ?>
        <?php if ($heroCount > 1): ?>
          <div class="mt-2"><a href="<?= BASE_URL ?>/admin/sliders.php?unit=<?= h($slug) ?>" class="small">View all <?= $heroCount ?> hero entries for this site &rarr;</a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Staff / Team -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-people me-2"></i>Staff &amp; Team</h2>
        <p class="text-muted small mb-3"><?= $staffCount ?> <?= $staffCount === 1 ? 'person' : 'people' ?> listed on this site.</p>
        <a href="<?= BASE_URL ?>/admin/staff.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-outline-anu">Manage Staff</a>
        <a href="<?= BASE_URL ?>/admin/staff_form.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-apply">+ Add Person</a>
      </div>
    </div>
  </div>

  <?php if ($site['leader_slug']): ?>
  <!-- Featured leader message -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-person-badge me-2"></i><?= h($site['leader_label']) ?></h2>
        <?php if ($leader && $leader['full_name']): ?>
          <div class="d-flex gap-3 align-items-start mb-3">
            <?php if ($leader['photo']): ?>
              <img src="<?= BASE_URL ?>/assets/uploads/<?= h($leader['photo']) ?>" class="img-preview-circle rounded-circle border">
            <?php endif; ?>
            <div>
              <div class="fw-semibold"><?= h($leader['full_name']) ?></div>
              <div class="small text-muted"><?= $leader['is_published'] ? 'Published' : 'Hidden' ?></div>
            </div>
          </div>
        <?php else: ?>
          <p class="text-muted small mb-3">No message set yet, this section is hidden on the site until one is added.</p>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/leader_form.php?unit=<?= h($site['leader_slug']) ?>" class="btn btn-sm btn-apply">Edit Message</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($departmentCount !== null): ?>
  <!-- Departments -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-diagram-3 me-2"></i>Departments</h2>
        <p class="text-muted small mb-3"><?= $departmentCount ?> department<?= $departmentCount === 1 ? '' : 's' ?> listed. <?= $departmentCount === 0 ? 'The "Departments" menu is hidden on the public site until at least one is added.' : '' ?></p>
        <a href="<?= BASE_URL ?>/admin/departments.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-outline-anu">Manage Departments</a>
        <a href="<?= BASE_URL ?>/admin/departments_form.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-apply">+ Add Department</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- News & Updates -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-newspaper me-2"></i>News &amp; Updates</h2>
        <p class="text-muted small mb-3"><?= $siteNewsCount ?> article<?= $siteNewsCount === 1 ? '' : 's' ?> posted to this site.</p>
        <a href="<?= BASE_URL ?>/admin/news.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-outline-anu">Manage Articles</a>
        <a href="<?= BASE_URL ?>/admin/news_form.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-apply">+ New Article</a>
      </div>
    </div>
  </div>

  <!-- Events -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-calendar-event me-2"></i>Upcoming Events</h2>
        <p class="text-muted small mb-3"><?= $siteEventsCount ?> event<?= $siteEventsCount === 1 ? '' : 's' ?> posted to this site.</p>
        <a href="<?= BASE_URL ?>/admin/events.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-outline-anu">Manage Events</a>
        <a href="<?= BASE_URL ?>/admin/events_form.php?unit=<?= h($slug) ?>" class="btn btn-sm btn-apply">+ New Event</a>
      </div>
    </div>
  </div>

  <?php if ($site['has_programmes']): ?>
  <!-- Programmes -->
  <div class="col-lg-6">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-mortarboard me-2"></i>Programmes</h2>
        <p class="text-muted small mb-3"><?= $programmeCount ?? 0 ?> programme<?= ($programmeCount ?? 0) === 1 ? '' : 's' ?> listed.</p>
        <a href="<?= h($programmesLink) ?>" class="btn btn-sm btn-outline-anu">Manage Programmes</a>
        <a href="<?= h($newProgrammeLink) ?>" class="btn btn-sm btn-apply">+ New Programme</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Page content -->
  <div class="col-12">
    <div class="card-anu h-100">
      <div class="body">
        <h2 class="h6 mb-3"><i class="bi bi-pencil-square me-2"></i>Page Content</h2>
        <div class="list-group list-group-flush">
          <?php foreach ($site['content'] as $groupKey => $group): ?>
            <a href="<?= BASE_URL ?>/admin/site_content.php?unit=<?= h($slug) ?>&group=<?= h($groupKey) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
              <?= h($group['label']) ?>
              <i class="bi bi-chevron-right text-muted small"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
