<?php
$pageTitle = 'Publications';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();

$stmt = db()->prepare("SELECT * FROM research_publications WHERE is_published = 1 ORDER BY publication_year DESC, title");
$stmt->execute();
$pubs = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="res-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">Research</div>
    <h1>Publications</h1>
  </div>
</section>

<section class="res-section">
  <div class="container container-narrow">
    <?php if (!$pubs): ?>
      <p class="text-muted">Publications will appear here soon.</p>
    <?php endif; ?>
    <?php foreach ($pubs as $pub): ?>
      <div class="res-programme-row" style="align-items:flex-start;">
        <div>
          <strong><?= h($pub['title']) ?></strong>
          <?php if ($pub['authors']): ?><div class="small text-muted"><?= h($pub['authors']) ?><?= $pub['publication_year'] ? ' &middot; ' . h((string)$pub['publication_year']) : '' ?></div><?php endif; ?>
          <?php if ($pub['summary']): ?><p class="small text-muted mt-2 mb-0"><?= h(excerpt($pub['summary'], 220)) ?></p><?php endif; ?>
        </div>
        <?php if ($pub['external_link']): ?><a href="<?= h($pub['external_link']) ?>" target="_blank" rel="noopener" class="text-decoration-none">View &rarr;</a><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
