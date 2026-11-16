<?php
/**
 * Admissions hub (Phase 7b, 2026-09) -- replaces the old separate
 * admissions-site micro-site as the live Admissions section, rebuilt
 * inside main-site, Daystar-style (structure only -- all content below
 * is ANU's own, sourced from anu.ac.ke or the existing admissions-site
 * pages it replaces, never copied from Daystar).
 *
 * Content is admin-editable via Admin -> Sites -> Admissions -> Overview
 * (site_content table, unit_slug='admissions', same pattern as every
 * other unit site and the Orientation page). Programme count comes
 * live from the `programmes` table, never typed in.
 */
$pageTitle = 'Admissions';
$pageDescription = 'Apply to Africa Nazarene University: application fees, how to apply, requirements, financial aid, international students and more.';
require_once __DIR__ . '/includes/functions.php';

$programmeCount = (int) db()->query('SELECT COUNT(*) FROM programmes WHERE is_published = 1')->fetchColumn();

$registrarStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'registrar' AND is_published = 1 ORDER BY sort_order LIMIT 1");
$registrarStmt->execute();
$registrar = $registrarStmt->fetch();
$registrarMessage = page_content('admissions', 'registrar_message', '');
require_once __DIR__ . '/includes/school_messages.php';
$smOpts = ['leader_slug' => 'registrar', 'fallback_title' => 'Registrar', 'unit' => 'admissions', 'base' => BASE_URL, 'uploads' => UPLOADS_URL,
    'css' => BASE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the University Registrar', 'eyebrow' => 'Admissions',
    'heads' => false, 'message_page' => 'registrar-message.php', 'fallback_bio' => $registrarMessage,
    'soon' => 'The Registrar&rsquo;s message will be published here soon.', 'back_url' => BASE_URL . '/admissions.php', 'back_label' => 'Back to Admissions'];
$goodToKnow = array_filter(array_map('trim', explode("\n", page_content('admissions', 'good_to_know', ''))));

require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Admissions</span>
    <h1><?= h(page_content('admissions', 'hero_headline', 'Your future starts with one application')) ?></h1>
    <p class="lead mb-0 max-ch-60 mx-auto"><?= h(page_content('admissions', 'hero_subtext', 'Applications for the current intake are open.')) ?></p>
  </div>
</section>

<?php sm_render_home($smOpts); ?>

<section class="py-4 border-bottom">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3">
        <div class="program-fact-icon mx-auto mb-2"><i class="bi bi-mortarboard"></i></div>
        <strong class="d-block h5 mb-0"><?= $programmeCount ?></strong>
        <span class="small text-muted">Programmes on offer</span>
      </div>
      <div class="col-6 col-md-3">
        <div class="program-fact-icon mx-auto mb-2"><i class="bi bi-cash-coin"></i></div>
        <strong class="d-block h5 mb-0"><?= h(page_content('admissions', 'fee_local', 'KES 1,500')) ?></strong>
        <span class="small text-muted">Application fee (East Africa)</span>
      </div>
      <div class="col-6 col-md-3">
        <div class="program-fact-icon mx-auto mb-2"><i class="bi bi-globe"></i></div>
        <strong class="d-block h5 mb-0"><?= h(page_content('admissions', 'fee_international', 'USD 35')) ?></strong>
        <span class="small text-muted">Application fee (International)</span>
      </div>
      <div class="col-6 col-md-3">
        <div class="program-fact-icon mx-auto mb-2"><i class="bi bi-clock-history"></i></div>
        <strong class="d-block h5 mb-0"><?= h(page_content('admissions', 'feedback_time', '5 working days')) ?></strong>
        <span class="small text-muted">Typical response time</span>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-5">Three steps to becoming a student</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card-anu h-100"><div class="body text-center">
          <div class="program-fact-icon mx-auto mb-3"><i class="bi bi-search"></i></div>
          <h3 class="h5">1. Choose a programme</h3>
          <p class="small text-muted mb-0">Browse programmes across our five schools, Non-Degree, ANU Online and Short Courses.</p>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu h-100"><div class="body text-center">
          <div class="program-fact-icon mx-auto mb-3"><i class="bi bi-pencil-square"></i></div>
          <h3 class="h5">2. Submit your application</h3>
          <p class="small text-muted mb-0">Apply online via the ANU records portal and pay the application fee to begin processing.</p>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card-anu h-100"><div class="body text-center">
          <div class="program-fact-icon mx-auto mb-3"><i class="bi bi-patch-check"></i></div>
          <h3 class="h5">3. Get admitted</h3>
          <p class="small text-muted mb-0">Once admitted, follow the fee payment guide in your admission letter to secure your place.</p>
        </div></div>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>/admissions-how-to-apply.php" class="btn btn-outline-anu btn-lg">See the Full Guide</a>
    </div>
  </div>
</section>

<?php if ($goodToKnow): ?>
<section class="py-5 bg-cream border-top">
  <div class="container container-narrow">
    <h2 class="text-center mb-4">Good to Know</h2>
    <ul class="ps-3">
      <?php foreach ($goodToKnow as $line): ?>
        <li class="mb-2"><i class="bi bi-check2-circle text-red me-1"></i> <?= h($line) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <?php
      $tiles = [
          ['How to Apply', 'admissions-how-to-apply.php', 'bi-pencil-square'],
          ['Requirements & Application Fees', 'admissions-requirements.php', 'bi-list-check'],
          ['International Students (ANUISA)', 'admissions-international.php', 'bi-globe'],
          ['Financial Aid', 'financial-aid.php', 'bi-cash-stack'],
          ['Tuition Payment Options', 'admissions-tuition-payment.php', 'bi-credit-card'],
          ['FAQs', 'admissions-faqs.php', 'bi-question-circle'],
      ];
      foreach ($tiles as [$label, $link, $icon]): ?>
        <div class="col-6 col-md-4">
          <a href="<?= BASE_URL . '/' . $link ?>" class="network-tile">
            <i class="bi <?= $icon ?> me-2"></i><span><?= h($label) ?></span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="cta-band reveal">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-8">
        <h2 class="h3 mb-2">Not sure what to study?</h2>
        <p class="mb-0">Explore all five schools, Non-Degree, ANU Online and our Centre for Short Courses &amp; Professional Studies.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?= BASE_URL ?>/programs.php" class="btn btn-apply btn-lg">Browse Programmes</a>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card-anu h-100"><div class="body">
          <i class="bi bi-cash-stack text-red mb-2" style="font-size:1.6rem;"></i>
          <h3 class="h5">Need Financial Aid?</h3>
          <p class="small text-muted mb-2">ANU offers financial aid packages to needy and deserving students.</p>
          <a href="<?= BASE_URL ?>/financial-aid.php" class="tile-go">Learn About Financial Aid &rarr;</a>
        </div></div>
      </div>
      <div class="col-md-6">
        <div class="card-anu h-100"><div class="body">
          <i class="bi bi-headset text-red mb-2" style="font-size:1.6rem;"></i>
          <h3 class="h5">Talk to Admissions</h3>
          <p class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i> <?= h(setting('email_admissions')) ?></p>
          <p class="small text-muted mb-2"><i class="bi bi-telephone me-1"></i> <?= h(setting('phone_primary')) ?></p>
          <a href="<?= BASE_URL ?>/admissions-enquiries.php" class="tile-go">Contact Admissions &rarr;</a>
        </div></div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
