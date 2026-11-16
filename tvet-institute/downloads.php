<?php
$pageTitle = 'Downloads';
require_once __DIR__ . '/includes/functions.php';
$downloads = db()->query("SELECT * FROM downloads WHERE is_published = 1 ORDER BY category, title")->fetchAll();
$grouped = [];
foreach ($downloads as $d) { $grouped[$d['category']][] = $d; }
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('downloads') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('downloads')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Resources</div>
    <h1>Downloads &amp; Forms</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <?php if (!$downloads): ?>
      <p class="text-muted">Downloadable prospectus, application and fee forms will appear here once uploaded via the Admin panel.</p>
    <?php endif; ?>
    <?php foreach ($grouped as $category => $items): ?>
      <h2 class="h5 mb-3"><?= h($category) ?></h2>
      <div class="row g-3 mb-5">
        <?php foreach ($items as $d): ?>
          <div class="col-md-6">
            <a class="tvet-programme-row text-decoration-none" href="<?= h(upload_url('downloads', $d['file'])) ?>" target="_blank" rel="noopener">
              <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-arrow-down fs-3 text-red me-3"></i>
                <div><strong class="d-block text-dark"><?= h($d['title']) ?></strong><span class="small text-muted"><?= h($d['description']) ?></span></div>
              </div>
              <i class="bi bi-download text-muted"></i>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
