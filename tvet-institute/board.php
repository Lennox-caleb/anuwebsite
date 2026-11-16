<?php
$pageTitle = 'Board & Trainers';
require_once __DIR__ . '/includes/functions.php';

$board = db()->query("SELECT * FROM staff WHERE category = 'board' AND is_published = 1 ORDER BY sort_order, full_name")->fetchAll();
$trainers = db()->query("SELECT * FROM staff WHERE category = 'trainer' AND is_published = 1 ORDER BY sort_order, full_name")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('board') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('board')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">People</div>
    <h1>Board &amp; Trainers</h1>
    <p class="lead">Meet the governance board and the experienced trainers who deliver our programmes.</p>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="tvet-section-head"><div><span class="eyebrow">Governance</span><h2>Institute Board</h2></div></div>
    <?php if (!$board): ?><p class="text-muted">Board member profiles will appear here once added via the Admin panel.</p><?php endif; ?>
    <div class="row g-4 mb-4">
      <?php foreach ($board as $s): ?>
        <div class="col-md-4 col-lg-3">
          <div class="faculty-card">
            <?php if ($s['photo']): ?>
              <img src="<?= h(upload_url('staff', $s['photo'])) ?>" alt="<?= h($s['full_name']) ?>">
            <?php else: ?>
              <div class="faculty-placeholder"><i class="bi bi-person"></i></div>
            <?php endif; ?>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
            <?php if ($s['bio']): ?><p class="small text-muted"><?= h(excerpt($s['bio'], 110)) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="tvet-section alt">
  <div class="container">
    <div class="tvet-section-head"><div><span class="eyebrow">Faculty</span><h2>Our Trainers</h2></div></div>
    <?php if (!$trainers): ?><p class="text-muted">Trainer profiles will appear here once added via the Admin panel.</p><?php endif; ?>
    <div class="row g-4">
      <?php foreach ($trainers as $s): ?>
        <div class="col-md-4 col-lg-3">
          <div class="faculty-card">
            <?php if ($s['photo']): ?>
              <img src="<?= h(upload_url('staff', $s['photo'])) ?>" alt="<?= h($s['full_name']) ?>">
            <?php else: ?>
              <div class="faculty-placeholder"><i class="bi bi-person"></i></div>
            <?php endif; ?>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
            <?php if ($s['bio']): ?><p class="small text-muted"><?= h(excerpt($s['bio'], 110)) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
