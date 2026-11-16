<?php
$pageTitle = 'About Alumni';
require_once __DIR__ . '/config/db.php';
$__hero = site_hero();
$leaderStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'head-alumni' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$leaderStmt->execute();
$leader = $leaderStmt->fetch();
require __DIR__ . '/includes/header.php';
?>
<section class="alum-hero compact<?= $__hero && $__hero['image'] ? ' has-image' : '' ?>"<?= $__hero && $__hero['image'] ? ' style="background-image: linear-gradient(180deg, rgba(10,10,10,.55) 0%, rgba(10,10,10,.72) 100%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $__hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <div class="eyebrow mb-2">About</div>
    <h1>Alumni</h1>
  </div>
</section>

<section class="alum-section">
  <div class="container container-narrow">
    <h2>Our Mission</h2>
    <p><?= h(main_setting('mission')) ?></p>
    <p class="text-muted"><?= nl2br(h(unit_content('about_body', "The Alumni office keeps every ANU graduate connected, to each other, to current students, and to the university's ongoing mission, through networking, mentorship and giving."))) ?></p>

    <div class="row g-4 g-lg-5 align-items-center leader-band mt-4">
      <div class="col-lg-5">
        <?php if ($leader && $leader['photo']): ?>
          <img class="photo-frame" src="<?= h(MAIN_SITE_URL . '/assets/uploads/' . $leader['photo']) ?>" alt="<?= h($leader['full_name']) ?>">
        <?php else: ?>
          <div class="photo-placeholder"><i class="bi bi-person"></i></div>
        <?php endif; ?>
      </div>
      <div class="col-lg-7">
        <h2>A Message from the Alumni Relations Officer</h2>
        <span class="quote-mark">&ldquo;</span>
        <p class="message"><?= $leader && $leader['bio'] ? nl2br(h($leader['bio'])) : "A welcome message will be published here soon." ?></p>
        <div class="signature">
          <strong><?= $leader ? h($leader['full_name']) : 'the Alumni Relations Officer' ?></strong>
          <span>Alumni</span>
        </div>
      </div>
    </div>
  </div>
</section><?php require __DIR__ . '/includes/footer.php'; ?>
