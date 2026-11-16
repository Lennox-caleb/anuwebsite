<?php
$pageTitle = 'Meet the Library Team';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();

$stmt = db()->prepare("SELECT * FROM staff WHERE unit_slug IN ('library','head-library') AND is_published = 1 ORDER BY sort_order, full_name");
$stmt->execute();
$staff = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="lib-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">People</div>
    <h1>Meet the Library Team</h1>
  </div>
</section>

<section class="lib-section">
  <div class="container">
    <?php if (!$staff): ?>
      <p class="text-muted">Team profiles will appear here soon.</p>
    <?php endif; ?>
    <div class="row g-4">
      <?php foreach ($staff as $s): ?>
        <div class="col-md-4 col-lg-3">
          <div class="team-card">
            <?php if ($s['photo']): ?>
              <img src="<?= h(UPLOADS_URL . '/' . $s['photo']) ?>" alt="<?= h($s['full_name']) ?>">
            <?php else: ?>
              <div class="team-placeholder"><i class="bi bi-person"></i></div>
            <?php endif; ?>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
            <?php if ($s['bio']): ?><p class="small text-muted"><?= h(excerpt($s['bio'], 110)) ?></p><?php endif; ?>
            <?php if ($s['email']): ?><a href="mailto:<?= h($s['email']) ?>" class="small"><i class="bi bi-envelope me-1"></i><?= h($s['email']) ?></a><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
      <a href="<?= BASE_URL ?>/contact.php" class="btn btn-lib-cta btn-lg">Contact the Library</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
