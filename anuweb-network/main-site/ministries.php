<?php
$pageTitle = 'Ministries';
require_once __DIR__ . '/config/db.php';
$ministries = db()->query("SELECT * FROM chaplaincy_programs WHERE is_published = 1 ORDER BY sort_order, id")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="chap-hero compact"><div class="container"><div class="eyebrow mb-2">Get involved</div><h1>Ministries</h1></div></section>
<section class="chap-section">
  <div class="container">
    <?php if (!$ministries): ?>
      <p class="text-muted text-center">Ministries will be listed here once added by the Chaplaincy office.</p>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($ministries as $m): ?>
          <div class="col-md-6 col-lg-4">
            <div class="chap-card">
              <div class="flame"></div>
              <h3><?= h($m['title']) ?></h3>
              <?php if ($m['description']): ?><p class="small text-muted mb-0"><?= nl2br(h($m['description'])) ?></p><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
