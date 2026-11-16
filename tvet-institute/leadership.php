<?php
$pageTitle = 'Message from the Head';
require_once __DIR__ . '/includes/functions.php';
$head = db()->query("SELECT * FROM staff WHERE category = 'leadership' AND is_published = 1 ORDER BY sort_order LIMIT 1")->fetch();
$pageDescription = 'A welcome message from the Head of Non-Degree Programmes at Africa Nazarene University.';
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('leadership') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('leadership')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-2">
      <ol class="breadcrumb small mb-0" style="--bs-breadcrumb-divider: '/';">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/about.php" class="<?= page_banner_url('leadership') ? 'text-white-50' : '' ?>">About</a></li>
        <li class="breadcrumb-item active <?= page_banner_url('leadership') ? 'text-white' : '' ?>">Leadership Message</li>
      </ol>
    </nav>
    <div class="eyebrow mb-2">Leadership</div>
    <h1>Message from the Head</h1>
  </div>
</section>

<section class="tvet-section">
  <div class="container">
    <div class="row g-5 align-items-center leader-band">
      <div class="col-lg-5">
        <?php if ($head && $head['photo']): ?>
          <img class="photo-frame" src="<?= h(upload_url('staff', $head['photo'])) ?>" alt="<?= h($head['full_name']) ?>">
        <?php else: ?>
          <div class="photo-placeholder"><i class="bi bi-person"></i></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-7">
        <span class="quote-mark">&ldquo;</span>
        <p class="message"><?= $head && $head['bio'] ? nl2br(h($head['bio'])) : 'A message from the Head of Non-Degree Programmes will be added here once provided via the Admin panel (Board & Trainers).' ?></p>
        <div class="signature">
          <strong><?= $head ? h($head['full_name']) : 'Office of the Head of Non-Degree Programmes' ?></strong>
          <span><?= $head && $head['title'] ? h($head['title']) : 'Africa Nazarene University' ?></span>
        </div>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-lg-9 mx-auto text-center">
        <div class="p-5 rounded-4" style="background: var(--tvet-surface);">
          <h2 class="h4">Ready to begin your journey?</h2>
          <p class="text-muted mb-4">Explore our programmes or start your application today.</p>
          <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>/programmes.php" class="btn btn-tvet-outline">View Programmes</a>
            <a href="<?= h(apply_url()) ?>" target="_blank" rel="noopener" class="btn btn-tvet-cta">Apply Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
