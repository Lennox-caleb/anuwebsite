<?php
$pageTitle = 'Admissions';
require_once __DIR__ . '/config/db.php';
$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute(['admissions']);
$hero = $heroStmt->fetch();

$registrarStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'registrar' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$registrarStmt->execute();
$registrar = $registrarStmt->fetch();

$feeLocal = unit_content('fee_local', 'KSh 1,500');
$feeIntl = unit_content('fee_international', 'USD 35');
$feedbackTime = unit_content('feedback_time', '5 working days');
$goodToKnow = array_filter(array_map('trim', explode("\n", unit_content('good_to_know', ''))));

require __DIR__ . '/includes/header.php';
?>
<section class="adm-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <div class="container">
    <p class="lead mb-4"><?= h(unit_content('hero_subtext', 'Applications for the current intake are open.')) ?></p>
    <h1 class="mb-3"><?= h(unit_content('hero_headline', 'Your future starts with one application')) ?></h1>
    <div class="d-flex gap-3 flex-wrap">
      <a href="<?= h(main_setting('apply_url')) ?>" class="btn btn-adm-cta btn-lg">Apply Now</a>
      <a href="<?= BASE_URL ?>/requirements.php" class="btn btn-adm-outline on-dark btn-lg">Check Requirements</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'registrar', 'fallback_title' => 'Registrar', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the University Registrar', 'eyebrow' => 'Admissions', 'heads' => false, 'soon' => 'The Registrar&rsquo;s message will be published here soon.']);
?>
<section class="adm-section pt-4 pb-4">
  <div class="container">
    <div class="row g-4 text-center adm-fact-row">
      <div class="col-6 col-md-3">
        <div class="adm-fact"><i class="bi bi-cash-coin"></i><strong><?= h($feeLocal) ?></strong><span>Application fee (East Africa)</span></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="adm-fact"><i class="bi bi-globe"></i><strong><?= h($feeIntl) ?></strong><span>Application fee (International)</span></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="adm-fact"><i class="bi bi-clock-history"></i><strong><?= h($feedbackTime) ?></strong><span>Typical response time</span></div>
      </div>
      <div class="col-6 col-md-3">
        <div class="adm-fact"><i class="bi bi-calendar-check"></i><strong>Rolling</strong><span>First-come, first-served</span></div>
      </div>
    </div>
  </div>
</section>

<section class="adm-section">
  <div class="container">
    <div class="adm-section-head text-center">
      <h2>Three steps to becoming a student</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="adm-step"><div class="num">1</div><h3>Choose a programme</h3><p class="small text-muted">Browse programmes across our five schools, Non-Degree, ANU Online and Short Courses.</p></div>
      </div>
      <div class="col-md-4">
        <div class="adm-step"><div class="num">2</div><h3>Submit your application</h3><p class="small text-muted">Apply online via the ANU records portal and pay the application fee to begin processing.</p></div>
      </div>
      <div class="col-md-4">
        <div class="adm-step"><div class="num">3</div><h3>Get admitted</h3><p class="small text-muted">Once admitted, follow the fee payment guide in your admission letter to secure your place.</p></div>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/how-to-apply.php" class="btn btn-adm-outline btn-lg">See the Full Guide</a>
    </div>
  </div>
</section>

<?php if ($goodToKnow): ?>
<section class="adm-section bg-cream">
  <div class="container container-narrow">
    <div class="adm-section-head text-center">
      <h2>Good to Know</h2>
    </div>
    <ul class="adm-good-to-know">
      <?php foreach ($goodToKnow as $line): ?>
        <li><i class="bi bi-check2-circle"></i> <?= h($line) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>


<section class="adm-section dark">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-8">
        <h2>Not sure what to study?</h2>
        <p class="mb-0">Explore all five schools, Non-Degree, ANU Online and our Centre for Short Courses &amp; Professional Studies from the main site.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?= h(MAIN_SITE_URL) ?>/programs.php" class="btn btn-adm-cta btn-lg">Browse Programmes</a>
      </div>
    </div>
  </div>
</section>

<section class="adm-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="adm-contact-card">
          <i class="bi bi-cash-stack"></i>
          <h3 class="h5">Need Financial Aid?</h3>
          <p class="small text-muted mb-2">ANU offers financial aid packages to needy and deserving students.</p>
          <a href="<?= h(MAIN_SITE_URL) ?>/financial-aid.php" class="tile-go">Learn About Financial Aid &rarr;</a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="adm-contact-card">
          <i class="bi bi-headset"></i>
          <h3 class="h5">Talk to Admissions</h3>
          <p class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i> <?= h(unit_content('contact_email', 'admissions@anu.ac.ke')) ?></p>
          <p class="small text-muted mb-2"><i class="bi bi-telephone me-1"></i> <?= h(unit_content('contact_phone', '+254 703 970 520')) ?></p>
          <a href="<?= BASE_URL ?>/contact.php" class="tile-go">Contact Admissions &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
