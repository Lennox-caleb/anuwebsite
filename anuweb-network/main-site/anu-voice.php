<?php
$pageTitle = 'ANU Voice';
$pageDescription = "ANU Voice is Africa Nazarene University's campus newsletter, published each semester.";
require_once __DIR__ . '/includes/functions.php';
$issues = db()->query("SELECT * FROM publications_gallery WHERE is_published = 1 AND publication_type = 'ANU Voice' ORDER BY published_date DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-gold);">Publications</span>
    <h1>ANU Voice</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">Our campus newsletter, published every semester, news,
    student stories and updates from across the University.</p>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <?php if (!$issues): ?>
      <div class="text-center py-5">
        <i class="bi bi-journal-richtext" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3">No issues have been published yet.</p>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($issues as $i => $mag): $cardClass = 'mag-card mag-card-voice reveal ' . ($i > 0 ? 'reveal-' . min($i + 1, 4) : ''); ?>
          <div class="col-6 col-md-3 col-lg-2">
            <?php if ($mag['pdf_file']): ?>
              <a href="<?= h(UPLOADS_URL . '/' . $mag['pdf_file']) ?>" class="<?= h($cardClass) ?>" target="_blank" rel="noopener">
            <?php else: ?>
              <div class="<?= h($cardClass) ?>">
            <?php endif; ?>
              <?php if ($mag['thumbnail']): ?>
                <img src="<?= h(UPLOADS_URL . '/' . $mag['thumbnail']) ?>" alt="<?= h($mag['title']) ?>" class="cover">
              <?php else: ?>
                <span class="cover-placeholder"><i class="bi bi-journal-richtext"></i></span>
              <?php endif; ?>
              <div class="cover-body">
                <span class="cover-type">ANU VOICE</span>
                <p class="cover-title"><?= h($mag['title']) ?></p>
                <?php if ($mag['issue_label']): ?><p class="cover-issue"><?= h($mag['issue_label']) ?></p><?php endif; ?>
              </div>
            <?php if ($mag['pdf_file']): ?></a><?php else: ?></div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/resources.php" class="btn btn-outline-anu mt-5">&larr; Back to Resources Library</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
