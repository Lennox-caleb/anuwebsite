<?php
$pageTitle = 'Corporate Social Responsibility';
$pageDescription = "How Africa Nazarene University lives out its faith and mission through community outreach, environmental stewardship and student-led service.";
require_once __DIR__ . '/includes/functions.php';
$extraHead = '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/csr-hero.css') . '">';
$photos = db()->query('SELECT * FROM csr_gallery WHERE is_published = 1 ORDER BY sort_order, created_at DESC')->fetchAll();
require __DIR__ . '/includes/header.php';
?>

<section class="csr-hero2">
  <div class="container">
    <div class="row g-0 g-lg-4 align-items-stretch">
      <div class="col-lg-7 csr-hero2-text">
        <span class="csr-eyebrow">Corporate Social Responsibility</span>
        <h1>Faith in Action, <span>Beyond the Classroom</span></h1>
        <p class="csr-lead">As a Church of the Nazarene sponsored university, ANU's commitment to Christian
        values extends past our lecture halls into the communities around us, through health outreach,
        education, environmental stewardship and student-led service.</p>
        <ul class="csr-chips" aria-label="Our CSR focus areas">
          <li><i class="bi bi-heart-pulse"></i>Health &amp; Wellness</li>
          <li><i class="bi bi-book"></i>Education</li>
          <li><i class="bi bi-tree"></i>Environment</li>
          <li><i class="bi bi-people"></i>Community</li>
          <li><i class="bi bi-hand-thumbs-up"></i>Volunteerism</li>
        </ul>
        <div class="csr-actions">
          <a href="#csr-pillars" class="btn csr-btn-gold">Explore our pillars</a>
          <a href="mailto:<?= h(setting('email_csr', 'csr@anu.ac.ke')) ?>" class="btn csr-btn-ghost">Partner with us</a>
        </div>
      </div>
      <div class="col-lg-5 csr-hero2-media">
        <img src="<?= BASE_URL ?>/assets/images/hero-people-2.jpg" alt="A smiling ANU student carrying a laptop" width="1600" height="900">
      </div>
    </div>
  </div>
</section>

<!-- PILLARS -->
<section class="py-5" id="csr-pillars">
  <div class="container">
    <div class="section-head reveal">
      <h2>Our CSR Pillars</h2>
      <span class="see-all see-all-muted">How we give back</span>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-1">
          <div class="icon-badge"><i class="bi bi-heart-pulse"></i></div>
          <h3 class="h5">Health &amp; Wellness Outreach</h3>
          <p class="small text-muted mb-0">Medical and wellness camps, health awareness drives and
          partnerships with local health facilities that extend care to neighbouring communities near
          our Magadi Road campus.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-2">
          <div class="icon-badge"><i class="bi bi-book"></i></div>
          <h3 class="h5">Education &amp; Mentorship</h3>
          <p class="small text-muted mb-0">Mentorship visits to local schools, scholarship support for
          disadvantaged students, and volunteer tutoring led by ANU staff and students.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-3">
          <div class="icon-badge"><i class="bi bi-tree"></i></div>
          <h3 class="h5">Environmental Stewardship</h3>
          <p class="small text-muted mb-0">Tree-planting drives, waste-reduction initiatives and
          conservation awareness that reflect our privileged position next to Nairobi National Park.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-1">
          <div class="icon-badge"><i class="bi bi-people"></i></div>
          <h3 class="h5">Church &amp; Community Partnerships</h3>
          <p class="small text-muted mb-0">Working alongside Church of the Nazarene congregations and
          local partners on relief, discipleship and community-development projects.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-2">
          <div class="icon-badge"><i class="bi bi-hand-thumbs-up"></i></div>
          <h3 class="h5">Student-Led Volunteerism</h3>
          <p class="small text-muted mb-0">Chaplaincy and student clubs organise service days, charity
          drives and outreach trips as part of student life at ANU.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="csr-pillar reveal reveal-3">
          <div class="icon-badge"><i class="bi bi-briefcase"></i></div>
          <h3 class="h5">Staff &amp; Alumni Giving</h3>
          <p class="small text-muted mb-0">Staff-driven fundraising and alumni-supported initiatives
          that channel resources into causes the ANU community cares about.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PHOTO GALLERY -->
<section class="py-5 bg-cream border-top">
  <div class="container">
    <div class="section-head reveal">
      <h2>CSR in Pictures</h2>
      <span class="see-all see-all-muted">Moments from our outreach &amp; community work</span>
    </div>
    <?php if (!$photos): ?>
      <p class="text-muted">Photos from CSR activities will be published here as they become available.</p>
    <?php else: ?>
      <div class="csr-gallery-grid">
        <?php foreach ($photos as $i => $p): ?>
          <figure class="csr-gallery-item reveal <?= 'reveal-' . (($i % 4) + 1) ?>">
            <img src="<?= h(UPLOADS_URL . '/' . $p['photo']) ?>" alt="<?= h($p['title']) ?>" loading="lazy">
            <figcaption>
              <?php if ($p['category']): ?><span class="csr-gallery-tag"><?= h($p['category']) ?></span><?php endif; ?>
              <strong><?= h($p['title']) ?></strong>
              <?php if ($p['caption']): ?><span class="csr-gallery-caption"><?= h($p['caption']) ?></span><?php endif; ?>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- GET INVOLVED -->
<section class="py-5 bg-cream border-top">
  <div class="container container-narrow text-center reveal">
    <h2>Get Involved</h2>
    <p class="text-muted mb-4 max-ch-60 mx-auto">Whether you're a student looking to volunteer, an
    alumnus wanting to give back, or an organisation seeking a community partner, we'd love to hear
    from you.</p>
    <div class="d-flex flex-wrap gap-3 justify-content-center">
      <a href="mailto:<?= h(setting('email_csr', 'csr@anu.ac.ke')) ?>" class="btn btn-apply btn-lg">
        <i class="bi bi-envelope me-1"></i> Partner With Us
      </a>
      <a href="<?= h(setting('url_chaplaincy')) ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-lg">Chaplaincy &amp; Volunteering</a>
      <a href="<?= h(setting('url_student_life')) ?>" target="_blank" rel="noopener" class="btn btn-outline-dark btn-lg">Student Life</a>
    </div>
    <p class="text-muted small mt-4 mb-0">Programme reports and impact figures are maintained by the
    University's Community Engagement office and will be published here as they become available.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
