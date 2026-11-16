<?php
$pageTitle = 'Programmes';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/school_slug.php';
$__hero = site_hero();

// Phase 5: category chips with real counts, like Daystar's SAHS
// programme listing -- counts come straight from the same query that
// builds the list below, never hardcoded.
$allowedLevels = ['PhD','Masters','Undergraduate','Diploma','Certificate','Other'];
$level = $_GET['level'] ?? '';
if (!in_array($level, $allowedLevels, true)) $level = '';

$countStmt = db()->prepare("SELECT p.level, COUNT(*) AS c FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1 GROUP BY p.level");
$countStmt->execute([school_slug_db()]);
$levelCounts = [];
foreach ($countStmt->fetchAll() as $row) { $levelCounts[$row['level']] = (int) $row['c']; }
$totalCount = array_sum($levelCounts);

// Phase 5b: keyword search (from the homepage search bar, or typed here
// directly). Single WHERE clause on one row's own columns -- no JOIN, no
// UNION -- so a match can never appear twice in the results.
$q = trim($_GET['q'] ?? '');

$sql = "SELECT DISTINCT p.* FROM programmes p WHERE p.school_id = (SELECT id FROM schools WHERE slug = ?) AND p.is_published = 1";
$params = [school_slug_db()];
if ($level !== '') { $sql .= " AND p.level = ?"; $params[] = $level; }
if ($q !== '') { $sql .= " AND (p.title LIKE ? OR p.overview LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
$sql .= " ORDER BY p.level, p.title";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$programmes = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="biz-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Academics</div>
    <h1>Business Programmes</h1>
  </div>
</section>

<section class="biz-section">
  <div class="container">
    <form action="<?= BASE_URL ?>/programmes.php" method="get" class="biz-programme-search mb-4">
      <input type="text" name="q" class="form-control" placeholder="Search programmes (e.g. a name or keyword)&hellip;" value="<?= h($q) ?>">
      <?php if ($level !== ''): ?><input type="hidden" name="level" value="<?= h($level) ?>"><?php endif; ?>
      <button type="submit" class="btn btn-biz-cta">Search</button>
    </form>

    <?php if ($totalCount > 0): ?>
    <div class="biz-category-bar mb-4">
      <a href="<?= BASE_URL ?>/programmes.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="biz-category-chip <?= $level==='' ? 'active' : '' ?>">All <span>(<?= $totalCount ?>)</span></a>
      <?php foreach ($allowedLevels as $lvl): if (empty($levelCounts[$lvl])) continue; ?>
        <a href="<?= BASE_URL ?>/programmes.php?level=<?= urlencode($lvl) ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="biz-category-chip <?= $level===$lvl ? 'active' : '' ?>"><?= h($lvl === 'Other' ? 'Short Courses' : $lvl) ?> <span>(<?= $levelCounts[$lvl] ?>)</span></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($q !== ''): ?>
      <p class="text-muted small mb-4"><?= count($programmes) ?> result<?= count($programmes) === 1 ? '' : 's' ?> for &ldquo;<?= h($q) ?>&rdquo; &middot; <a href="<?= BASE_URL ?>/programmes.php">Clear search</a></p>
    <?php endif; ?>
    <?php if (!$programmes): ?>
      <p class="text-muted"><?= $q !== '' ? 'No programmes match your search.' : ($totalCount > 0 ? 'No programmes match this filter.' : 'Programme listings for the School of Business will appear here soon.') ?></p>
    <?php endif; ?>
    <?php foreach ($programmes as $p): ?>
      <div class="biz-programme-row">
        <div class="d-flex align-items-center">
          <?php if ($p['image']): ?>
            <img class="thumb" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $p['image']) ?>" alt="">
          <?php else: ?>
            <span class="thumb-placeholder"></span>
          <?php endif; ?>
          <div>
            <span class="level"><?= h($p['level'] === 'Other' ? 'Short course' : $p['level']) ?></span>
            <strong><?= h($p['title']) ?></strong>
            <?php if ($p['duration']): ?><span class="text-muted small ms-2"><?= h($p['duration']) ?></span><?php endif; ?>
            <?php if (!empty($p['intake'])): ?><span class="text-muted small ms-2">Intake: <?= h($p['intake']) ?></span><?php endif; ?>
          </div>
        </div>
        <a href="<?= h(MAIN_SITE_URL) ?>/program-single.php?slug=<?= h($p['slug']) ?>" class="text-decoration-none">Details &rarr;</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
