<?php
$pageTitle = 'Affiliation';
$pageDescription = "Africa Nazarene University is a chartered private Christian university sponsored by the Church of the Nazarene International.";
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>

<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">About Africa Nazarene University</span>
    <h1>Affiliation</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">A member of the worldwide family of Nazarene institutions, and
    chartered by Kenya's Commission for University Education.</p>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <div class="row g-4">
      <div class="col-md-6 reveal reveal-1">
        <div class="affiliation-card">
          <div class="icon-badge"><i class="bi bi-globe2"></i></div>
          <div>
            <h2 class="h5">Church of the Nazarene International</h2>
            <p class="small text-muted mb-0">Africa Nazarene University is a Chartered Private Christian
            University sponsored by the Church of the Nazarene International, and a member of the worldwide
            family of Nazarene institutions of higher education, the first such university outside
            North America.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 reveal reveal-2">
        <div class="affiliation-card">
          <div class="icon-badge"><i class="bi bi-patch-check"></i></div>
          <div>
            <h2 class="h5">Commission for University Education (CUE)</h2>
            <p class="small text-muted mb-0">The University is chartered by Kenya's Commission for University
            Education, the statutory body responsible for regulating university education in Kenya, and is the
            first private university in the country accredited under the University Act without external
            affiliation to another institution.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-12 reveal reveal-3">
        <div class="card-anu"><div class="body">
          <h2 class="h5">What This Means</h2>
          <p class="mb-0">Being sponsored by the Church of the Nazarene shapes ANU's Wesleyan-Holiness
          Christian identity, mission and values, while its Kenyan Government charter means every
          degree programme offered through the University's Schools is formally recognised and approved by
          CUE. Non-degree Certificate, Diploma and short courses are offered through ANU's Non-Degree
          Programmes. Read more on our <a href="<?= BASE_URL ?>/accreditation.php">Accreditation</a> page.</p>
        </div></div>
      </div>
    </div>

    <div class="d-flex flex-wrap gap-3 mt-4 reveal">
      <a href="<?= BASE_URL ?>/who-we-are.php" class="btn btn-outline-anu">Who We Are &rarr;</a>
      <a href="<?= BASE_URL ?>/our-history.php" class="btn btn-outline-anu">Our History &rarr;</a>
      <a href="<?= BASE_URL ?>/accreditation.php" class="btn btn-outline-anu">Accreditation &rarr;</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-dark">&larr; Back to About ANU</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
