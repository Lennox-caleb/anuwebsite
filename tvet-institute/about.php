<?php
$pageTitle = 'About Non-Degree Programmes';
require_once __DIR__ . '/includes/functions.php';
$head = db()->query("SELECT * FROM staff WHERE category = 'leadership' AND is_published = 1 ORDER BY sort_order LIMIT 1")->fetch();
$board = db()->query("SELECT COUNT(*) c FROM staff WHERE category='board' AND is_published=1")->fetch();
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact<?= page_banner_url('about') ? ' has-image' : '' ?>">
  <?php if ($b = page_banner_url('about')): ?><img class="hero-bg-image" src="<?= h($b) ?>" alt=""><?php endif; ?>
  <div class="container">
    <div class="eyebrow mb-2">About</div>
    <h1>Non-Degree Programmes</h1>
    <p class="lead">Practical, career-focused Diploma and Certificate programmes offered by Africa Nazarene University within a Christ-centred learning community.</p>
  </div>
</section>

<section class="tvet-section">
  <div class="container container-narrow">
    <h2>Our Mission</h2>
    <p><?= nl2br(h(setting('mission'))) ?></p>
    <h2 class="mt-4">Our Vision</h2>
    <p><?= nl2br(h(setting('vision'))) ?></p>

    <h2 class="mt-5">Accreditation</h2>
    <p>Non-Degree Programmes are offered by <strong>Africa Nazarene University</strong>, chartered by the Commission for University Education (CUE) under the Universities Act, 2012. Diploma and Certificate qualifications are awarded by the University; where a programme leads to a professional or regulatory qualification, this is indicated on the individual programme page. Prospective students are encouraged to confirm current accreditation details with the Admissions Office before enrolling.</p>

    <div class="row g-4 mt-2 mb-5">
      <div class="col-md-4">
        <div class="tvet-card"><span class="index">01</span><h3>Certificate</h3><p class="small text-muted mb-0">Foundational, career-entry qualification, typically 1 year.</p></div>
      </div>
      <div class="col-md-4">
        <div class="tvet-card"><span class="index">02</span><h3>Diploma</h3><p class="small text-muted mb-0">Advanced, career-focused qualification, typically 2 years.</p></div>
      </div>
      <div class="col-md-4">
        <div class="tvet-card"><span class="index">03</span><h3>Short Course</h3><p class="small text-muted mb-0">Focused, shorter-duration skills training.</p></div>
      </div>
    </div>

    <div class="tvet-card d-md-flex align-items-center justify-content-between gap-4" style="padding-top:1.7rem">
      <div>
        <h2 class="h5 mb-2">A message from our Head of Non-Degree Programmes</h2>
        <p class="text-muted mb-0 mb-md-0"><?= $head ? h($head['full_name']) : 'Office of the Head of Non-Degree Programmes' ?> shares a welcome message and vision for every learner joining the Institute.</p>
      </div>
      <a href="<?= BASE_URL ?>/leadership.php" class="btn btn-tvet-cta text-nowrap mt-3 mt-md-0">Read the Message &rarr;</a>
    </div>

    <h2 class="mt-5">Governance</h2>
    <p class="text-muted">Non-Degree Programmes is guided by a <?= (int)($board['c'] ?? 0) ?>-member Board of Management drawn from University leadership and industry partners, responsible for quality assurance, curriculum alignment and strategic direction. See the <a href="<?= BASE_URL ?>/board.php">Board &amp; Trainers</a> page for member profiles.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
