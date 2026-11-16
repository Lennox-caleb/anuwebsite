<?php
$pageTitle = 'Faculty & Staff';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
$stmt = db()->prepare('SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order, full_name');
$stmt->execute([UNIT_SLUG]);
$staff = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="rel-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">People</div><h1>Faculty &amp; Staff</h1></div></section>
<section class="rel-section">
  <div class="container">
    <?php if (!$staff): ?><p class="text-muted">Faculty profiles will appear here soon.</p><?php endif; ?>
    <div class="row g-4">
      <?php foreach ($staff as $i => $s): ?>
        <div class="col-md-4 col-lg-3">
          <div class="tb-card tb-c<?= $i % 4 ?>">
            <div class="tb-photo">
              <?php if ($s['photo']): ?><img src="<?= h(UPLOADS_URL . '/' . $s['photo']) ?>" alt="<?= h($s['full_name']) ?>" loading="lazy"><?php else: ?><span class="tb-ph"><i class="bi bi-person-fill"></i></span><?php endif; ?>
            </div>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
            <?php if ($s['bio']): ?><p class="tb-bio"><?= h(excerpt($s['bio'], 110)) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
