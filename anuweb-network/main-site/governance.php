<?php
$pageTitle = 'Governance';
require_once __DIR__ . '/includes/functions.php';
$extraHead = '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/board-cards.css') . '">';

$rows = db()->query("SELECT * FROM governance_members WHERE is_published = 1 ORDER BY FIELD(category,'Board of Trustees','University Council','Management'), sort_order")->fetchAll();
$groups = ['Board of Trustees' => [], 'University Council' => [], 'Management' => []];
foreach ($rows as $r) {
    // The Vice Chancellor, Deputy Vice Chancellors and Chancellor are
    // managed in their own Leadership section and already have their own
    // pages there -- skip any stray old entry for them here so they don't
    // also appear (and repeat themselves) in this Management list.
    if ($r['category'] === 'Management' && (stripos($r['title'] ?? '', 'Deputy') !== false || stripos($r['full_name'] ?? '', 'Vice Chancellor') !== false || stripos($r['full_name'] ?? '', 'Chancellor') !== false)) {
        continue;
    }
    $groups[$r['category']][] = $r;
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Governance</h1>
    <p class="lead text-muted max-ch-60 mx-auto mb-0">The University is guided by a Board of Trustees, a University Council, and a Management team, in keeping with its status as a Church of the Nazarene sponsored institution.</p>
  </div>
</section>
<section class="py-5">
  <div class="container">
    <?php
    $anchors = ['Board of Trustees' => 'board-of-trustees', 'University Council' => 'university-council', 'Management' => 'management'];
    foreach ($groups as $category => $members): ?>
      <div class="section-head mt-5" id="<?= h($anchors[$category] ?? '') ?>">
        <h2><?= h($category) ?></h2>
        <?php if ($category === 'Management'): ?>
          <a href="<?= BASE_URL ?>/management.php" class="small">View Management page &rarr;</a>
        <?php endif; ?>
      </div>
      <?php if ($category === 'Management'): ?>
        <p class="text-muted small mb-4">The Vice Chancellor, Deputy Vice Chancellors and Chancellor have their
        own pages with full welcome messages, see <a href="<?= BASE_URL ?>/leadership.php">University Leadership</a>
        and the <a href="<?= BASE_URL ?>/chancellor.php">Chancellor</a>.</p>
      <?php endif; ?>
      <?php if (!$members): ?>
        <p class="text-muted">Members of the <?= h($category) ?> will be listed here once added.</p>
      <?php endif; ?>
      <div class="row g-4 board-grid">
        <?php foreach ($members as $m): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="board-card">
            <div class="board-card-media">
              <?php if ($m['photo']): ?>
                <img src="<?= h(UPLOADS_URL . '/' . $m['photo']) ?>" alt="<?= h($m['full_name']) ?>">
              <?php else: ?>
                <div class="board-card-placeholder" aria-hidden="true"><i class="bi bi-person"></i></div>
              <?php endif; ?>
              <div class="board-card-caption">
                <h3><?= h($m['full_name']) ?></h3>
                <p class="board-card-role"><?= h(public_text($m['title'])) ?></p>
              </div>
            </div>
            <?php if ($m['bio']): ?><p class="board-card-bio"><?= h(excerpt($m['bio'], 110)) ?></p><?php endif; ?>
          </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
