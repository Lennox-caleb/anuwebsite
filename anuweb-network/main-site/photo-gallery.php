<?php
$pageTitle = 'Photo Gallery';
$pageDescription = 'Photos from events, graduations and life at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

// Real ANU photo albums link out to where the photos actually live
// (typically Facebook) -- mirrors anu.ac.ke's own Photo Gallery, which
// works the same way. Nothing self-hosted is invented here.
try {
    $albums = db()->query('SELECT * FROM photo_gallery WHERE is_published=1 ORDER BY sort_order, id')->fetchAll();
} catch (Throwable $e) {
    error_log('photo-gallery: ' . $e->getMessage());   // table missing -> show the empty state instead of an error page
    $albums = [];
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Photo Gallery</h1>
    <p class="text-muted mb-0">Moments from graduations, events and life across ANU.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (!$albums): ?>
      <p class="text-muted">No photo albums posted yet.</p>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($albums as $a): ?>
          <div class="col-md-6 col-lg-4">
            <a class="card-anu reveal d-block h-100 text-decoration-none" href="<?= h($a['external_url']) ?>" target="_blank" rel="noopener">
              <?php if ($a['cover_image']): ?>
                <img class="thumb" src="<?= h(UPLOADS_URL . '/' . $a['cover_image']) ?>" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="bi bi-images"></i></div>
              <?php endif; ?>
              <div class="body">
                <h3 class="text-black" style="font-size:1.05rem;"><?= h($a['title']) ?></h3>
                <span class="small text-muted"><i class="bi bi-box-arrow-up-right me-1"></i>View album</span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
