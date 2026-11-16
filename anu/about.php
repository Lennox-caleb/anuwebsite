<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();
$settings = getAllSettings();
$applyUrl = $settings['apply_url'] ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';

$heroSlides = getSlides('about');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('about');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';
$heroOverlay= !empty($heroData['overlay_color']) ? $heroData['overlay_color'] : 'rgba(10,0,0,0.6)';

trackPage('about', 'About ANU Online');
renderHead('About ANU Online'); renderNavbar('about');
?>

<!-- ═══ HERO ═══ -->
<div class="pp-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}')\"" : '' ?>>
  <div class="pp-hero-overlay" style="background:<?=e($heroOverlay)?>"></div>
  <div class="container pp-hero-content">
    <div class="pp-hero-text" data-aos="fade-up">
      <div class="sec-tag" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.8)">Who We Are</div>
      <h1 class="pp-hero-title">About ANU Online</h1>
      <p class="pp-hero-sub">The distance-learning arm of Africa Nazarene University — quality, faith-centered education delivered wherever you are.</p>
    </div>
  </div>
</div>

<!-- ═══ INTRO ═══ -->
<section class="sec">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="sec-tag">Our Story</div>
        <h2 class="sec-title">Learning Without <span class="hl">Boundaries</span></h2>
        <div class="rule"></div>
        <p class="body-lead">ANU Online extends Africa Nazarene University's Christ-centered academic tradition to students anywhere in the world. Through a modern learning platform, our programs bring together rigorous scholarship, flexible scheduling, and genuine pastoral care — so distance is never a barrier to a quality education.</p>
        <p class="body-lead" style="margin-top:16px">Every course is designed for working professionals, parents, ministers and anyone balancing life's demands with the pursuit of a degree — without compromising on academic standards or spiritual formation.</p>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <div class="cl-feature-grid">
          <?php foreach([
            ['bi-mortarboard-fill','#c1121f','Accredited Programs',   'Recognised by the Commission for University Education (CUE).'],
            ['bi-laptop-fill',     '#9b2226','Flexible Delivery',     'Study on your schedule from anywhere with an internet connection.'],
            ['bi-people-fill',     '#ae2012','Real Community',        'Chaplaincy, clubs and peer support built for distance learners.'],
            ['bi-heart-pulse-fill','#bb3e03','Faith-Centered',        'Christian values woven through every program and interaction.'],
          ] as [$ic,$cl,$tt,$tx]): ?>
          <div class="cl-feat-card">
            <div class="cl-feat-ico" style="background:<?=$cl?>1a;color:<?=$cl?>"><i class="bi <?=$ic?>"></i></div>
            <h5 class="cl-feat-title"><?=$tt?></h5>
            <p class="cl-feat-text"><?=$tx?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ MISSION / VISION ═══ -->
<section class="sec bg-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6" data-aos="fade-up">
        <div class="admin-card" style="height:100%">
          <div class="sec-tag">Our Mission</div>
          <h3 class="sec-title mb-3" style="font-size:1.6rem"><?= e($settings['about_mission'] ?? 'To provide accessible, quality, Christ-centered higher education to learners across Africa and beyond through flexible online delivery.') ?></h3>
        </div>
      </div>
      <div class="col-md-6" data-aos="fade-up" data-aos-delay="80">
        <div class="admin-card" style="height:100%">
          <div class="sec-tag">Our Vision</div>
          <h3 class="sec-title mb-3" style="font-size:1.6rem"><?= e($settings['about_vision'] ?? 'To be a leading provider of transformative online Christian education in Africa.') ?></h3>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ ACCREDITATION STRIP (reuse homepage strip) ═══ -->
<div class="accred-strip">
  <div class="container">
    <div class="accred-inner">
      <span class="accred-label">Recognised &amp; Accredited By</span>
      <div class="accred-logos">
        <div class="accred-badge"><i class="bi bi-patch-check-fill"></i> Commission for University Education (CUE)</div>
        <div class="accred-sep"></div>
        <div class="accred-badge"><i class="bi bi-globe2"></i> Kenya National Qualifications Authority</div>
        <div class="accred-sep"></div>
        <div class="accred-badge"><i class="bi bi-award-fill"></i> Church of the Nazarene International</div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ DIRECTOR / LEADERSHIP MESSAGE — pulled from Admin → Team ═══ -->
<?php renderDirectorMessage(); ?>

<!-- ═══ TEAM TEASER ═══ -->
<section class="sec">
  <div class="container text-center" data-aos="fade-up">
    <div class="sec-tag">Our People</div>
    <h2 class="sec-title">Meet The <span class="hl">ANU Online Team</span></h2>
    <p class="sec-sub mx-auto mb-4">The leadership, faculty and support staff behind your learning experience.</p>
    <a href="/anu/team.php" class="btn-ghost-red">Meet Our Team <i class="bi bi-arrow-right"></i></a>
  </div>
</section>

<!-- ═══ CTA ═══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Ready to Begin?</div>
    <h2 class="mt-3" style="color:#fff;font-family:'Roboto',Arial,sans-serif;font-size:clamp(2rem,4vw,3rem)">Join ANU Online Today</h2>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="<?=e($applyUrl)?>" target="_blank" rel="noopener" class="btn-cta-w">Apply Now <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/programs.php" class="btn-cta-o">Browse Programs</a>
    </div>
  </div>
</section>

<?php renderFooter(); ?>
