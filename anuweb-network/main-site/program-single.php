<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT p.*, s.name AS school_name, s.slug AS school_slug
                        FROM programmes p LEFT JOIN schools s ON s.id = p.school_id
                        WHERE p.slug = ? AND p.is_published = 1 LIMIT 1');
$stmt->execute([$slug]);
$programme = $stmt->fetch();

if (!$programme) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><h1>Programme not found</h1><p><a href="' . BASE_URL . '/programs.php">Back to programmes</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $programme['title'];
$pageDescription = $programme['overview'] ? excerpt($programme['overview'], 160) : ($programme['title'] . ' at Africa Nazarene University.');

// Defensive defaults in case fee_structure_file hasn't been added yet on
// this server (i.e. database/schema_phase22.sql hasn't been run there).
$programme['fee_structure_file'] = $programme['fee_structure_file'] ?? null;
$programme['mode'] = $programme['mode'] ?: 'Physical';
// Defensive default in case this server hasn't run
// database/phase4-programmes-filters-2026-09.sql yet.
$programme['intake'] = $programme['intake'] ?? null;
$modeIcon = $programme['mode'] === 'Online' ? 'bi-globe2' : 'bi-building';
$levelIcons = ['PhD' => 'bi-mortarboard-fill', 'Masters' => 'bi-mortarboard', 'Undergraduate' => 'bi-book', 'Diploma' => 'bi-journal-bookmark', 'Certificate' => 'bi-award'];
$levelIcon = $levelIcons[$programme['level']] ?? 'bi-book';

$icons = [
    'pdf' => 'bi-file-earmark-pdf', 'doc' => 'bi-file-earmark-word', 'docx' => 'bi-file-earmark-word',
    'xls' => 'bi-file-earmark-excel', 'xlsx' => 'bi-file-earmark-excel',
];

require __DIR__ . '/includes/header.php';
?>
<section class="program-hero">
  <div class="container">
    <p class="mb-3"><a href="<?= BASE_URL ?>/programs.php" class="program-hero-back"><i class="bi bi-arrow-left"></i> All Programmes</a></p>
    <div class="row align-items-end g-4">
      <div class="col-lg-8">
        <span class="program-level-badge"><i class="bi <?= $levelIcon ?>"></i> <?= h($programme['level']) ?></span>
        <h1 class="mt-3 mb-2"><?= h($programme['title']) ?></h1>
        <?php if ($programme['school_name']): ?>
          <p class="mb-0 program-hero-school"><i class="bi bi-bank"></i> Offered by
          <a href="<?= BASE_URL ?>/schools/<?= h($programme['school_slug']) ?>"><?= h($programme['school_name']) ?></a></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <?php if ($programme['image']): ?>
      <img src="<?= h(UPLOADS_URL . '/' . $programme['image']) ?>" class="img-fluid rounded-4 mb-5 reveal program-hero-image" alt="<?= h($programme['title']) ?>">
    <?php endif; ?>

    <div class="row g-5">
      <div class="col-lg-8 reveal">
        <?php if ($programme['overview']): ?>
          <div class="program-section">
            <?= nl2br(h($programme['overview'])) ?>
          </div>
        <?php else: ?>
          <p class="text-muted">Full programme overview coming soon.</p>
        <?php endif; ?>

        <?php if ($programme['requirements']): ?>
          <div class="program-section">
            <h2 class="program-section-heading"><i class="bi bi-check2-square"></i> Admission Requirements</h2>
            <div class="program-section-body"><?= nl2br(h($programme['requirements'])) ?></div>
          </div>
        <?php endif; ?>

        <?php if ($programme['fee_structure'] || $programme['fee_structure_file']): ?>
          <div class="program-section">
            <h2 class="program-section-heading"><i class="bi bi-cash-coin"></i> Fee Structure</h2>
            <?php if ($programme['fee_structure']): ?>
              <div class="program-section-body"><?= nl2br(h($programme['fee_structure'])) ?></div>
            <?php endif; ?>
            <?php if ($programme['fee_structure_file']):
              $feeExt = strtolower(pathinfo($programme['fee_structure_file'], PATHINFO_EXTENSION));
              $feeIcon = $icons[$feeExt] ?? 'bi-file-earmark-text';
            ?>
              <a href="<?= h(UPLOADS_URL . '/' . $programme['fee_structure_file']) ?>" class="btn btn-outline-anu mt-2" download>
                <i class="bi <?= $feeIcon ?> me-1"></i> Download Fee Structure
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php
        $unitRows = [];
        if ($programme['course_units']) {
            foreach (preg_split('/\r\n|\r|\n/', trim($programme['course_units'])) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) >= 4) $unitRows[] = $parts;
            }
        }
        ?>
        <?php if ($unitRows): ?>
          <div class="program-section">
            <h2 class="program-section-heading"><i class="bi bi-list-columns"></i> Course Units</h2>
            <div class="table-responsive">
              <table class="table table-anu align-middle mb-0">
                <thead><tr><th>Year / Semester</th><th>Unit Code</th><th>Unit Name</th><th>Credit Hours</th></tr></thead>
                <tbody>
                  <?php foreach ($unitRows as $row): ?>
                    <tr><td><?= h($row[0]) ?></td><td><?= h($row[1]) ?></td><td><?= h($row[2]) ?></td><td><?= h($row[3]) ?></td></tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($programme['planner_file']): ?>
          <a href="<?= h(UPLOADS_URL . '/' . $programme['planner_file']) ?>" class="btn btn-outline-anu mt-2" download>
            <i class="bi bi-download"></i> Download Full Course Planner
          </a>
        <?php endif; ?>
      </div>
      <div class="col-lg-4">
        <div class="program-side-card reveal">
          <div class="program-fact">
            <span class="program-fact-icon"><i class="bi bi-clock-history"></i></span>
            <span>
              <strong>Duration</strong>
              <span><?= h($programme['duration'] ?: 'Contact Admissions') ?></span>
            </span>
          </div>
          <div class="program-fact">
            <span class="program-fact-icon"><i class="bi <?= $modeIcon ?>"></i></span>
            <span>
              <strong>Mode of Study</strong>
              <span class="program-mode-badge <?= $programme['mode'] === 'Online' ? 'is-online' : 'is-physical' ?>"><?= h($programme['mode']) ?></span>
            </span>
          </div>
          <div class="program-fact">
            <span class="program-fact-icon"><i class="bi bi-calendar-event"></i></span>
            <span>
              <strong>Intake</strong>
              <span><?= h($programme['intake'] ?: 'Contact Admissions') ?></span>
            </span>
          </div>
          <a href="<?= h(setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-apply w-100 mt-2">Apply Now</a>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-anu w-100 mt-2">Ask a Question</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
