<?php
$pageTitle = 'About the School of Religion & Christian Ministry';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
$leaderStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'dean-religion' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$leaderStmt->execute();
$leader = $leaderStmt->fetch();
require __DIR__ . '/includes/header.php';
?>
<section class="rel-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container"><div class="eyebrow mb-2">About</div><h1>School of Religion & Christian Ministry</h1></div>
</section>
<section class="rel-section">
  <div class="container container-narrow">
    <h2 class="text-center">Our Mission</h2>
    <p class="text-center"><?= h(main_setting('mission')) ?></p>
    <p class="text-muted text-center"><?= nl2br(h(unit_content('about_body', 'Applied here: the School forms pastors, chaplains, missionaries and religion scholars grounded in the Wesleyan-holiness tradition of the Church of the Nazarene.'))) ?></p>
    <div class="row g-4 g-lg-5 align-items-center leader-band mt-4">
      <div class="col-lg-5">
        <?php if ($leader && $leader['photo']): ?>
          <img class="photo-frame" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $leader['photo']) ?>" alt="<?= h($leader['full_name']) ?>">
        <?php else: ?>
          <div class="photo-placeholder"><i class="bi bi-person"></i></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-7">
        <h2>Dean's Message</h2>
        <span class="quote-mark">&ldquo;</span>
        <p class="message"><?= $leader && $leader['bio'] ? nl2br(h($leader['bio'])) : "A message from the Dean will be published here soon." ?></p>
        <div class="signature">
          <strong><?= $leader ? h($leader['full_name']) : 'Office of the Dean' ?></strong>
          <span><?= $leader && $leader['title'] ? h($leader['title']) : 'School of Religion & Christian Ministry' ?></span>
        </div>
      </div>
    </div>
  </div>
</section><?php require __DIR__ . '/includes/footer.php'; ?>
