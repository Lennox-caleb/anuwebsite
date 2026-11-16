<?php
$pageTitle = 'Gallery';
require_once __DIR__ . '/includes/functions.php';
$gallery = db()->query("SELECT * FROM gallery WHERE is_published = 1 ORDER BY sort_order DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('gallery') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('gallery')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Campus Life</div>
    <h1>Gallery</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <?php if (!$gallery): ?><p class="text-muted">Photos will appear here once uploaded via the Admin panel.</p><?php endif; ?>
    <div class="row g-3">
      <?php foreach ($gallery as $g): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="gallery-item">
            <img src="<?= h(upload_url('gallery', $g['image'])) ?>" alt="<?= h($g['title']) ?>" loading="lazy">
          </div>
          <?php if ($g['title']): ?><p class="small text-muted mt-1 mb-0"><?= h($g['title']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
