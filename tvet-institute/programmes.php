<?php
$pageTitle = 'Programmes';
require_once __DIR__ . '/includes/functions.php';

$departments = db()->query("SELECT * FROM departments ORDER BY sort_order")->fetchAll();
$levels = ['Certificate','Diploma','Higher Diploma','Short Course'];

$deptFilter = trim($_GET['department'] ?? '');
$levelFilter = trim($_GET['level'] ?? '');
$q = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, d.name AS dept_name, d.slug AS dept_slug FROM programmes p LEFT JOIN departments d ON d.id = p.department_id WHERE p.is_published = 1";
$params = [];
if ($deptFilter !== '') { $sql .= " AND d.slug = ?"; $params[] = $deptFilter; }
if ($levelFilter !== '') { $sql .= " AND p.level = ?"; $params[] = $levelFilter; }
if ($q !== '') { $sql .= " AND p.title LIKE ?"; $params[] = '%' . $q . '%'; }
$sql .= " ORDER BY p.sort_order, p.title";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$programmes = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('programmes') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('programmes')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Training</div>
    <h1>Our Programmes</h1>
    <p class="lead">Diploma and Certificate level courses offered by Africa Nazarene University.</p>
  </div>
</section>

<section class="tvet-section" style="padding-top:2.5rem">
  <div class="container">
    <form method="get" class="filter-bar row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label mb-1">Search programmes</label>
        <input type="text" id="programmeSearch" name="q" value="<?= h($q) ?>" class="form-control" placeholder="e.g. ICT, Electrical, Culinary&hellip;">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1">Department</label>
        <select id="programmeDept" name="department" class="form-select">
          <option value="">All Departments</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= h($d['slug']) ?>" <?= $deptFilter === $d['slug'] ? 'selected' : '' ?>><?= h($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1">Level</label>
        <select id="programmeLevel" name="level" class="form-select">
          <option value="">All Levels</option>
          <?php foreach ($levels as $lvl): ?>
            <option value="<?= h($lvl) ?>" <?= $levelFilter === $lvl ? 'selected' : '' ?>><?= h($lvl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-tvet-cta" type="submit"><i class="bi bi-search"></i> Filter</button>
      </div>
    </form>

    <p class="text-muted small mt-3 mb-4"><?= count($programmes) ?> programme<?= count($programmes) === 1 ? '' : 's' ?> found</p>

    <div id="programmeEmptyState" class="text-center py-5" style="display:<?= $programmes ? 'none' : 'block' ?>">
      <i class="bi bi-search" style="font-size:2.5rem; color:#ccc"></i>
      <p class="text-muted mt-2">No programmes match your search. Try clearing a filter.</p>
      <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-tvet-outline btn-sm">Clear filters</a>
    </div>

    <div class="row g-4">
      <?php foreach ($programmes as $p): ?>
        <div class="col-md-6 col-lg-4" data-programme-col>
          <div class="programme-card" data-programme-card data-title="<?= h(strtolower($p['title'])) ?>" data-level="<?= h($p['level']) ?>" data-dept="<?= h($p['dept_slug'] ?? '') ?>">
            <div class="thumb-area">
              <?php if ($p['image']): ?><img src="<?= h(upload_url('programmes', $p['image'])) ?>" alt="<?= h($p['title']) ?>"><?php endif; ?>
              <span class="level-badge <?= level_badge_class($p['level']) ?>"><?= h($p['level']) ?></span>
            </div>
            <div class="body">
              <h3><?= h($p['title']) ?></h3>
              <div class="meta"><i class="bi bi-clock"></i> <?= h($p['duration']) ?> &nbsp; <i class="bi bi-mortarboard"></i> <?= h($p['dept_name'] ?? 'General') ?></div>
              <p class="small text-muted"><?= h(excerpt($p['overview'] ?? '', 100)) ?></p>
              <div class="stretched-link-wrap">
                <a class="fw-bold small" href="<?= BASE_URL ?>/programme-single.php?slug=<?= h($p['slug']) ?>">View details <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
