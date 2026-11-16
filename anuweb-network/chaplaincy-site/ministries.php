<?php
$pageTitle = 'Ministries';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
$ministries = db()->query("SELECT * FROM chaplaincy_programs WHERE is_published = 1 ORDER BY sort_order, id")->fetchAll();
// Each ministry: the first line of its description may be the schedule, e.g. "Monday | 4:00 pm - 6:00 pm". The rest is the blurb.
foreach ($ministries as &$m) {
    $m['day'] = ''; $m['time'] = ''; $m['blurb'] = (string)$m['description'];
    $lines = preg_split('/\R/', trim((string)$m['description']), 2);
    if (preg_match('/^(Mon|Tues|Wednes|Thurs|Fri|Satur|Sun)day\b\s*(?:\|\s*(.*))?$/i', trim($lines[0]), $mm)) {
        $m['day'] = ucfirst(strtolower($mm[1])) . 'day'; $m['time'] = trim($mm[2] ?? ''); $m['blurb'] = trim($lines[1] ?? '');
    }
}
unset($m);
require __DIR__ . '/includes/header.php';
?>
<section class="chap-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>><div class="container"><div class="eyebrow mb-2">Get involved</div><h1>Ministries</h1><p class="lead mt-2 mb-0" style="color:rgba(255,255,255,.85)">Join us through the week. Every ministry is open to all students.</p></div></section>
<section class="chap-section">
  <div class="container">
    <?php if (!$ministries): ?>
      <p class="text-muted text-center">Ministries will be listed here once added by the Chaplaincy office.</p>
    <?php else: ?>
      <div class="mn-head"><span class="mn-eyebrow">Weekly programme</span><h2>Our Ministries</h2></div>
      <div class="mn-grid">
        <?php foreach ($ministries as $m): ?>
          <article class="mn-card">
            <div class="mn-day"><?= $m['day'] !== '' ? '<span>' . h(substr($m['day'], 0, 3)) . '</span><small>' . h($m['day']) . '</small>' : '<i class="bi ' . h($m['icon'] ?: 'bi-heart') . '"></i>' ?></div>
            <div class="mn-body">
              <h3><?= h($m['title']) ?></h3>
              <?php if ($m['time'] !== ''): ?><p class="mn-time"><i class="bi bi-clock"></i> <?= h($m['time']) ?></p><?php endif; ?>
              <?php if ($m['blurb'] !== ''): ?><p class="mn-text"><?= nl2br(h($m['blurb'])) ?></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="mn-note"><i class="bi bi-info-circle"></i> Questions about any ministry? <a href="<?= BASE_URL ?>/contact.php">Contact the Chaplaincy</a>.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
