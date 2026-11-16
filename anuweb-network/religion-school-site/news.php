<?php
$pageTitle = 'News & Updates';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();

// Phase 5: this school's own News & Updates, sourced from the same
// shared `news` table the main site's News page uses, scoped to just
// this unit (unit_slug), so nothing posted elsewhere leaks in here and
// nothing posted here leaks onto the main site's News page.
// Phase 5b fix: don't fatal if unit_slug hasn't been migrated onto
// the news table yet -- show the honest "coming soon" fallback instead.
$articles = [];
if (schema_has_column('news', 'unit_slug')) {
    $stmt = db()->prepare("SELECT * FROM news WHERE unit_slug = ? AND is_published = 1 ORDER BY published_at DESC");
    $stmt->execute([UNIT_SLUG]);
    $articles = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<section class="rel-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">News</div>
    <h1>News &amp; Updates</h1>
  </div>
</section>

<section class="rel-section">
  <div class="container container-narrow">
    <?php if (!$articles): ?>
      <p class="text-muted">News and updates from the School of Religion and Christian Ministry will appear here soon.</p>
    <?php else: ?>
      <?php foreach ($articles as $a): ?>
        <div class="rel-card mb-4">
          <span class="text-muted small"><?= date('d M Y', strtotime($a['published_at'])) ?></span>
          <h3 class="mt-1"><?= h($a['title']) ?></h3>
          <?php if ($a['cover_image']): ?>
            <img src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $a['cover_image']) ?>" class="img-fluid rounded mb-3" alt="">
          <?php endif; ?>
          <?php if ($a['excerpt']): ?>
            <p class="mb-0"><?= h($a['excerpt']) ?></p>
          <?php elseif ($a['body']): ?>
            <p class="mb-0"><?= nl2br(h(excerpt($a['body'], 280))) ?></p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
