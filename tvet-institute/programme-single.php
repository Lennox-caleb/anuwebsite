<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$stmt = db()->prepare("SELECT p.*, d.name AS dept_name, d.slug AS dept_slug FROM programmes p LEFT JOIN departments d ON d.id = p.department_id WHERE p.slug = ? AND p.is_published = 1");
$stmt->execute([$slug]);
$programme = $stmt->fetch();

if (!$programme) {
    http_response_code(404);
    $pageTitle = 'Programme Not Found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="tvet-section"><div class="container text-center"><h1>Programme not found</h1><p class="text-muted">This programme may have been renamed or is no longer offered.</p><a href="' . BASE_URL . '/programmes.php" class="btn btn-tvet-cta">Browse all Programmes</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $programme['title'];
$pageDescription = excerpt($programme['overview'] ?? '', 155);

$relatedStmt = db()->prepare("SELECT * FROM programmes WHERE department_id <=> ? AND id != ? AND is_published = 1 ORDER BY RAND() LIMIT 3");
$relatedStmt->execute([$programme['department_id'], $programme['id']]);
$related = $relatedStmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= $programme['image'] ? ' has-image' : '' ?>">
  <?php if ($programme['image']): ?><img class="hero-bg-image" src="<?= h(upload_url('programmes', $programme['image'])) ?>" alt=""><?php endif; ?>
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-2">
      <ol class="breadcrumb small mb-0" style="--bs-breadcrumb-divider: '/';">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/programmes.php" class="<?= $programme['image'] ? 'text-white-50' : '' ?>">Programmes</a></li>
        <li class="breadcrumb-item active <?= $programme['image'] ? 'text-white' : '' ?>"><?= h($programme['dept_name'] ?? 'General') ?></li>
      </ol>
    </nav>
    <span class="level-badge <?= level_badge_class($programme['level']) ?> mb-2">&nbsp;<?= h($programme['level']) ?>&nbsp;</span>
    <h1><?= h($programme['title']) ?></h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <?php if ($programme['overview']): ?>
          <h2 class="h4">Programme Overview</h2>
          <p><?= nl2br(h($programme['overview'])) ?></p>
        <?php endif; ?>

        <?php if ($programme['entry_requirements']): ?>
          <h2 class="h4 mt-4">Entry Requirements</h2>
          <p><?= nl2br(h($programme['entry_requirements'])) ?></p>
        <?php endif; ?>

        <?php if ($programme['career_prospects']): ?>
          <h2 class="h4 mt-4">Career Prospects</h2>
          <p><?= nl2br(h($programme['career_prospects'])) ?></p>
        <?php endif; ?>

        <?php if ($related): ?>
          <h2 class="h4 mt-5">Related Programmes</h2>
          <div class="row g-3">
            <?php foreach ($related as $r): ?>
              <div class="col-md-4">
                <a class="tvet-programme-row d-block text-decoration-none" href="<?= BASE_URL ?>/programme-single.php?slug=<?= h($r['slug']) ?>">
                  <span class="level-badge <?= level_badge_class($r['level']) ?>"><?= h($r['level']) ?></span>
                  <strong class="d-block mt-2 text-dark"><?= h($r['title']) ?></strong>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-4">
        <div class="tvet-card" style="padding-top:1.7rem">
          <h3 class="h5 mb-3">Quick Facts</h3>
          <ul class="list-unstyled small mb-4">
            <li class="mb-2 d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Level</span><strong class="text-end"><?= h($programme['level']) ?></strong></li>
            <li class="mb-2 d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Duration</span><strong><?= h($programme['duration']) ?></strong></li>
            <li class="mb-2 d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Mode of Study</span><strong class="text-end"><?= h($programme['mode']) ?></strong></li>
            <li class="mb-2 d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Intakes</span><strong class="text-end"><?= h($programme['intake']) ?></strong></li>
            <li class="mb-2 d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Examining Body</span><strong class="text-end"><?= h($programme['examining_body']) ?></strong></li>
            <?php if ($programme['fee_per_year']): ?>
            <li class="mb-2 d-flex justify-content-between"><span class="text-muted">Fee Guide</span><strong><?= h($programme['fee_per_year']) ?>/yr</strong></li>
            <?php endif; ?>
          </ul>
          <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener" class="btn btn-tvet-cta w-100 mb-2">Apply for this Programme</a>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-tvet-outline w-100">Ask a Question</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
