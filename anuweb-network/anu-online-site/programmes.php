<?php
$pageTitle = 'Online Programmes';
require_once __DIR__ . '/config/db.php';

// Programmes tagged for this unit OR delivered online (mode = Online).
$stmt = db()->prepare("SELECT p.*, s.name AS school_name FROM programmes p LEFT JOIN schools s ON s.id = p.school_id WHERE (p.unit_slug = ? OR p.mode = 'Online') AND p.is_published = 1 ORDER BY FIELD(p.level,'PhD','Masters','Undergraduate','Diploma','Certificate','Other'), p.title");
$stmt->execute([UNIT_SLUG]);
$listings = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="online-hero compact">
  <div class="container">
    <div class="eyebrow mb-2">Academics</div>
    <h1>Online Programmes</h1>
  </div>
</section>

<section class="online-section">
  <div class="container">
    <?php if (!$listings): ?>
      <p class="text-muted">Listings for ANU Online will appear here soon.</p>
    <?php endif; ?>
    <?php foreach ($listings as $item): ?>
      <div class="online-programme-row">
        <div class="d-flex align-items-center">
          <?php if ($item['image']): ?>
            <img class="thumb" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $item['image']) ?>" alt="">
          <?php else: ?>
            <span class="thumb-placeholder"></span>
          <?php endif; ?>
          <div>
            <span class="level"><?= h($item['level'] === 'Other' ? 'Short course' : $item['level']) ?><?= !empty($item['school_name']) ? ' &middot; ' . h($item['school_name']) : '' ?></span>
            <strong><?= h($item['title']) ?></strong>
            <?php if ($item['duration']): ?><span class="text-muted small ms-2"><?= h($item['duration']) ?></span><?php endif; ?>
            <?php if (!empty($item['intake'])): ?><span class="text-muted small ms-2">Intake: <?= h($item['intake']) ?></span><?php endif; ?>
          </div>
        </div>
        <a href="<?= h(MAIN_SITE_URL) ?>/program-single.php?slug=<?= h($item['slug']) ?>" class="text-decoration-none">Details &rarr;</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
