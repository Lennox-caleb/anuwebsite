<?php
$pageTitle = 'Management';
require_once __DIR__ . '/includes/functions.php';
$extraHead = '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/board-cards.css') . '">';
$allMembers = db()->query("SELECT * FROM governance_members WHERE category = 'Management' AND is_published = 1 ORDER BY sort_order")->fetchAll();
// Entries whose NAME field is just the office (e.g. "Vice Chancellor") are
// skipped -- those have their own Leadership pages. Everyone else, including
// Deputy Vice Chancellors and the Interim Vice Chancellor, is listed.
$members = array_filter($allMembers, function ($m) {
    return stripos($m['full_name'] ?? '', 'Vice Chancellor') === false
        && stripos($m['full_name'] ?? '', 'Chancellor') === false;
});
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="section-head"><h1>Management</h1></div>
    <p class="text-muted small mb-4">The Vice Chancellor and Chancellor have their own
    pages with full welcome messages, see <a href="<?= BASE_URL ?>/leadership.php">University Leadership</a>
    and the <a href="<?= BASE_URL ?>/chancellor.php">Chancellor</a>. The Deputy Vice Chancellors and other
    members of the Management team are listed below.</p>
    <?php if (!$members): ?>
      <p class="text-muted">Members of the Management team will be listed here once added.</p>
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
                <h2><?= h($m['full_name']) ?></h2>
                <p class="board-card-role"><?= h(public_text($m['title'])) ?></p>
              </div>
            </div>
            <?php if ($m['bio']): ?><p class="board-card-bio"><?= h(excerpt($m['bio'], 110)) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
