<?php
$pageTitle = 'Courses';
require_once __DIR__ . '/config/db.php';
$stmt = db()->prepare("SELECT * FROM programmes WHERE unit_slug = ? AND is_published = 1 ORDER BY title");
$stmt->execute([UNIT_SLUG]);
$programmes = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="sepc-hero compact"><div class="container"><div class="eyebrow mb-2">Catalog</div><h1>Short Courses</h1></div></section>
<section class="sepc-section">
  <div class="container">
    <?php if (!$programmes): ?><p class="text-muted">Course listings will appear here once added via the shared admin panel at the main site's <code>/admin/programmes.php</code> (unit: Centre for Short Courses &amp; Professional Studies).</p><?php endif; ?>
    <div class="row g-4">
      <?php foreach ($programmes as $p): ?>
        <div class="col-md-6 col-lg-4">
          <div class="sepc-card">
            <?php if ($p['image']): ?><img class="thumb" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $p['image']) ?>" alt=""><?php else: ?><div class="thumb-placeholder"><i class="bi bi-journal-bookmark"></i></div><?php endif; ?>
            <div class="body">
              <?php if ($p['duration']): ?><span class="duration-badge"><?= h($p['duration']) ?></span><?php endif; ?>
              <h3><?= h($p['title']) ?></h3>
              <a href="<?= h(MAIN_SITE_URL) ?>/program-single.php?slug=<?= h($p['slug']) ?>" class="text-decoration-none small">Details &rarr;</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
