<?php
$pageTitle = 'Programmes';
$pageDescription = 'Browse Certificate, Diploma, Undergraduate, Masters and PhD programmes at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

$level = $_GET['level'] ?? '';
// School filter is keyed by the short URL slug (e.g. ?school=sol), never by
// numeric id -- the dropdown and result cards still always show the full
// school name. A bare numeric value is accepted too, for any old link still
// pointing at an id, but slug is the canonical form going forward.
$schoolSlug = trim((string) ($_GET['school'] ?? ''));
$mode = $_GET['mode'] ?? '';
$intake = trim((string) ($_GET['intake'] ?? ''));
$q = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$allowedLevels = ['PhD','Masters','Undergraduate','Diploma','Certificate'];

$schools = db()->query('SELECT id, name, slug FROM schools WHERE is_published=1 ORDER BY sort_order')->fetchAll();

// Mode/Intake options are read from whatever real values already exist in
// the data -- never a hardcoded or invented list. Intake in particular
// stays unpopulated until confirmed from anu.ac.ke (see Phase 4's
// migration note), so its filter simply doesn't render until at least one
// programme actually has one set.
$modeOptions = db()->query("SELECT DISTINCT mode FROM programmes WHERE is_published=1 AND mode IS NOT NULL AND mode <> '' ORDER BY mode")->fetchAll(PDO::FETCH_COLUMN);
$intakeOptions = db()->query("SELECT DISTINCT intake FROM programmes WHERE is_published=1 AND intake IS NOT NULL AND intake <> '' ORDER BY intake")->fetchAll(PDO::FETCH_COLUMN);

$selectedSchool = null;
if ($schoolSlug !== '' && $schoolSlug !== '0') {
    foreach ($schools as $s) {
        if ($s['slug'] === $schoolSlug || (ctype_digit($schoolSlug) && (int) $s['id'] === (int) $schoolSlug)) {
            $selectedSchool = $s;
            break;
        }
    }
}
if (!in_array($mode, $modeOptions, true)) $mode = '';
if (!in_array($intake, $intakeOptions, true)) $intake = '';

$where = ['p.is_published = 1'];
$params = [];
if (in_array($level, $allowedLevels, true)) {
    $where[] = 'p.level = ?';
    $params[] = $level;
}
if ($selectedSchool) {
    $where[] = 'p.school_id = ?';
    $params[] = $selectedSchool['id'];
}
if ($mode !== '') {
    $where[] = 'p.mode = ?';
    $params[] = $mode;
}
if ($intake !== '') {
    $where[] = 'p.intake = ?';
    $params[] = $intake;
}
if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.overview LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM programmes p WHERE $whereSql");
$countStmt->execute($params);
$totalCount = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalCount / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, s.name AS school_name, s.slug AS school_slug FROM programmes p LEFT JOIN schools s ON s.id = p.school_id WHERE $whereSql ORDER BY p.level, p.title LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$programmes = $stmt->fetchAll();

// Carries every active filter except the one being changed, so links/forms
// below never silently drop a filter the visitor already picked.
function programs_qs(array $overrides, string $level, ?array $selectedSchool, string $mode, string $intake, string $q): string
{
    $parts = [
        'level' => $level,
        'school' => $selectedSchool['slug'] ?? '',
        'mode' => $mode,
        'intake' => $intake,
        'q' => $q,
    ];
    $parts = array_merge($parts, $overrides);
    $parts = array_filter($parts, fn($v) => $v !== '' && $v !== null);
    return $parts ? '?' . http_build_query($parts) : '';
}

$levelIcons = [
    'PhD' => 'bi-award', 'Masters' => 'bi-mortarboard', 'Undergraduate' => 'bi-book',
    'Diploma' => 'bi-journal-bookmark', 'Certificate' => 'bi-patch-check',
];

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Programmes Offered</h1>
    <p class="text-muted mb-0"><?= $totalCount ?> programme<?= $totalCount === 1 ? '' : 's' ?> found. Search or filter by level, school<?= ($modeOptions || $intakeOptions) ? ', mode' : '' ?><?= $intakeOptions ? ' or intake' : '' ?> below.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <form method="get" class="mb-4">
      <?php if ($level !== ''): ?><input type="hidden" name="level" value="<?= h($level) ?>"><?php endif; ?>
      <div class="row g-3 align-items-end">
        <div class="col-12">
          <label class="form-label small text-muted mb-1" for="programSearch">Search programmes</label>
          <input type="search" name="q" id="programSearch" class="form-control" placeholder="e.g. Business Administration" value="<?= h($q) ?>">
        </div>
        <div class="col-md-<?= ($modeOptions || $intakeOptions) ? 4 : 7 ?>">
          <label class="form-label small text-muted mb-1">Level</label>
          <div class="filter-bar">
            <a href="<?= BASE_URL ?>/programs.php<?= programs_qs(['level' => ''], $level, $selectedSchool, $mode, $intake, $q) ?>" class="btn btn-sm <?= $level === '' ? 'btn-apply' : 'btn-outline-anu' ?>">All Levels</a>
            <?php foreach ($allowedLevels as $lvl): ?>
              <a href="<?= BASE_URL ?>/programs.php<?= programs_qs(['level' => $lvl], $level, $selectedSchool, $mode, $intake, $q) ?>" class="btn btn-sm <?= $level === $lvl ? 'btn-apply' : 'btn-outline-anu' ?>">
                <i class="bi <?= $levelIcons[$lvl] ?? 'bi-mortarboard' ?>"></i> <?= h($lvl) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="col-md-<?= ($modeOptions || $intakeOptions) ? 4 : 5 ?>">
          <label class="form-label small text-muted mb-1" for="schoolFilter">School</label>
          <select name="school" id="schoolFilter" class="form-select" onchange="this.form.submit()">
            <option value="">All Schools</option>
            <?php foreach ($schools as $s): ?>
              <option value="<?= h($s['slug']) ?>" <?= ($selectedSchool && $selectedSchool['id'] === $s['id']) ? 'selected' : '' ?>><?= h($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($modeOptions): ?>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1" for="modeFilter">Mode</label>
          <select name="mode" id="modeFilter" class="form-select" onchange="this.form.submit()">
            <option value="">Any Mode</option>
            <?php foreach ($modeOptions as $m): ?>
              <option value="<?= h($m) ?>" <?= $mode === $m ? 'selected' : '' ?>><?= h($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <?php if ($intakeOptions): ?>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1" for="intakeFilter">Intake</label>
          <select name="intake" id="intakeFilter" class="form-select" onchange="this.form.submit()">
            <option value="">Any Intake</option>
            <?php foreach ($intakeOptions as $in): ?>
              <option value="<?= h($in) ?>" <?= $intake === $in ? 'selected' : '' ?>><?= h($in) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </div>
    </form>

    <div class="row g-4">
      <?php foreach ($programmes as $i => $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card-anu reveal reveal-<?= ($i % 4) + 1 ?>">
            <?php if ($p['image']): ?>
              <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $p['image']) ?>" alt="">
            <?php else: ?>
              <div class="thumb-placeholder"><i class="bi <?= $levelIcons[$p['level']] ?? 'bi-mortarboard' ?>"></i></div>
            <?php endif; ?>
            <div class="body">
              <span class="tag"><?= h($p['level']) ?></span>
              <h3><a class="text-decoration-none" href="<?= BASE_URL ?>/program-single.php?slug=<?= h($p['slug']) ?>"><?= h($p['title']) ?></a></h3>
              <?php if ($p['school_name']): ?><p class="small text-muted mb-0"><i class="bi bi-bank"></i> <?= h($p['school_name']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$programmes): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-mortarboard" style="font-size:2.2rem; color: var(--anu-line);"></i>
          <p class="text-muted mt-3">No programmes match this filter. <a href="<?= BASE_URL ?>/programs.php">Clear filters</a></p>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="d-flex justify-content-center mt-5" aria-label="Programme pages">
        <ul class="pagination mb-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/programs.php<?= programs_qs(['page' => max(1, $page - 1)], $level, $selectedSchool, $mode, $intake, $q) ?>">&laquo; Prev</a>
          </li>
          <?php for ($pn = 1; $pn <= $totalPages; $pn++): ?>
            <li class="page-item <?= $pn === $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= BASE_URL ?>/programs.php<?= programs_qs(['page' => $pn], $level, $selectedSchool, $mode, $intake, $q) ?>"><?= $pn ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= BASE_URL ?>/programs.php<?= programs_qs(['page' => min($totalPages, $page + 1)], $level, $selectedSchool, $mode, $intake, $q) ?>">Next &raquo;</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
