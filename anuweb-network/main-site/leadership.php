<?php
$pageTitle = 'University Leadership';
$pageDescription = "Meet the Vice Chancellor and Deputy Vice Chancellors of Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';

$rows = db()->query("SELECT * FROM leadership WHERE is_published = 1 ORDER BY FIELD(role_type,'vc','dvc'), sort_order")->fetchAll();
$vc = null;
$dvcs = [];
foreach ($rows as $r) {
    if ($r['role_type'] === 'vc' && !$vc) { $vc = $r; continue; }
    if ($r['role_type'] === 'dvc') $dvcs[] = $r;
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">Office of the Vice Chancellor</span>
    <h1>University Leadership</h1>
    <p class="lead text-muted max-ch-60 mx-auto mb-0">Meet the Vice Chancellor and Deputy Vice Chancellors guiding Africa Nazarene University.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (!$vc && !$dvcs): ?>
      <p class="text-muted text-center py-5">Leadership profiles will appear here once published.</p>
    <?php endif; ?>

    <?php if ($vc): ?>
      <div class="section-head reveal">
        <h2>Vice Chancellor</h2>
      </div>
      <a href="<?= BASE_URL ?>/leadership-message.php?id=<?= (int)$vc['id'] ?>" class="leader-card leader-card-featured reveal">
        <div class="leader-card-photo">
          <?php if ($vc['photo']): ?>
            <img src="<?= h(UPLOADS_URL . '/' . $vc['photo']) ?>" alt="<?= h($vc['full_name']) ?>">
          <?php else: ?>
            <span class="placeholder-icon"><i class="bi bi-person"></i></span>
          <?php endif; ?>
        </div>
        <div class="leader-card-body">
          <span class="message-card-eyebrow">Welcome Message from Vice Chancellor</span>
          <h3><?= h($vc['full_name']) ?></h3>
          <p class="role"><?= h(public_text($vc['title'], 'Vice Chancellor')) ?></p>
          <?php if ($vc['message']): ?><p class="excerpt"><?= h(excerpt($vc['message'], 200)) ?></p><?php endif; ?>
          <span class="see-all">Read full message &rarr;</span>
        </div>
      </a>
    <?php endif; ?>

    <?php if ($dvcs): ?>
      <div class="section-head mt-5 reveal">
        <h2>Deputy Vice Chancellors</h2>
      </div>
      <div class="row g-4">
        <?php foreach ($dvcs as $i => $m): ?>
          <div class="col-md-6 col-lg-4">
            <a href="<?= BASE_URL ?>/leadership-message.php?id=<?= (int)$m['id'] ?>" class="leader-card reveal <?= $i > 0 ? 'reveal-' . min($i + 1, 4) : '' ?>">
              <div class="leader-card-photo">
                <?php if ($m['photo']): ?>
                  <img src="<?= h(UPLOADS_URL . '/' . $m['photo']) ?>" alt="<?= h($m['full_name']) ?>">
                <?php else: ?>
                  <span class="placeholder-icon"><i class="bi bi-person"></i></span>
                <?php endif; ?>
              </div>
              <div class="leader-card-body">
                <h3><?= h($m['full_name']) ?></h3>
                <p class="role"><?= h(public_text($m['title'], 'Deputy Vice Chancellor')) ?></p>
                <span class="see-all">Read message &rarr;</span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
