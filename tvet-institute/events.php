<?php
$pageTitle = 'Events';
require_once __DIR__ . '/includes/functions.php';

$upcoming = db()->query("SELECT * FROM events WHERE is_published = 1 AND start_date >= NOW() ORDER BY start_date ASC")->fetchAll();
$past = db()->query("SELECT * FROM events WHERE is_published = 1 AND start_date < NOW() ORDER BY start_date DESC LIMIT 6")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('events') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('events')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">Calendar</div>
    <h1>Events</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="tvet-section-head"><div><span class="eyebrow">Coming Up</span><h2>Upcoming Events</h2></div></div>
    <?php if (!$upcoming): ?><p class="text-muted">No upcoming events at the moment -- check back soon.</p><?php endif; ?>
    <div class="row g-4">
      <?php foreach ($upcoming as $e): ?>
        <div class="col-md-4">
          <div class="tvet-card" style="padding-top:1.7rem">
            <span class="badge-soft-gold px-2 py-1 rounded mb-2 d-inline-block small fw-bold"><?= fdate($e['start_date'], 'd M Y') ?></span>
            <h3><?= h($e['title']) ?></h3>
            <p class="small text-muted"><i class="bi bi-geo-alt"></i> <?= h($e['location']) ?></p>
            <p class="small text-muted"><?= h(excerpt($e['description'] ?? '', 120)) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($past): ?>
<section class="tvet-section alt">
  <div class="container">
    <div class="tvet-section-head"><div><span class="eyebrow">Archive</span><h2>Past Events</h2></div></div>
    <div class="row g-3">
      <?php foreach ($past as $e): ?>
        <div class="col-md-4">
          <div class="tvet-programme-row">
            <div><strong class="d-block"><?= h($e['title']) ?></strong><span class="small text-muted"><?= fdate($e['start_date'], 'd M Y') ?> &middot; <?= h($e['location']) ?></span></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
