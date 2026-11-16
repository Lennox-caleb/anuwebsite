<?php
$pageTitle = 'Who We Are';
$pageDescription = "Africa Nazarene University is a chartered, Christian, Wesleyan-Holiness university sponsored by the Church of the Nazarene, this is who we are.";
require_once __DIR__ . '/includes/functions.php';
$facts = db()->query('SELECT * FROM fun_facts ORDER BY sort_order LIMIT 4')->fetchAll();
require __DIR__ . '/includes/header.php';
?>

<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">About Africa Nazarene University</span>
    <h1>Who We Are</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">A chartered, Christian university sponsored by the Church of the Nazarene, 
    forming graduates of character and competence for a life of service.</p>
    <div class="pillar-strip justify-content-center">
      <span class="pillar-chip"><i class="bi bi-person-heart"></i> Character</span>
      <span class="pillar-chip"><i class="bi bi-mortarboard"></i> Competence</span>
      <span class="pillar-chip"><i class="bi bi-people"></i> Community</span>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <div class="row g-4 align-items-start reveal">
      <div class="col-lg-8">
        <p class="lead">Africa Nazarene University (ANU) is a private, Christian university built on the
        Wesleyan-Holiness tradition of the international Church of the Nazarene. Since opening its doors in
        1994, ANU has grown from 62 pioneering students into a fully chartered, multi-school university
        educating men and women for lives of Christian faith, scholarship and service across Africa and
        beyond.</p>
        <p>As part of the worldwide family of Nazarene institutions, ANU delivers holistic, values-centred
        education that integrates academic excellence with spiritual formation and community engagement
, equipping every graduate not just with a qualification, but with character.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a href="<?= BASE_URL ?>/our-history.php" class="btn btn-outline-anu">Our History &rarr;</a>
          <a href="<?= BASE_URL ?>/affiliation.php" class="btn btn-outline-anu">Our Affiliation &rarr;</a>
          <a href="<?= BASE_URL ?>/core-values.php" class="btn btn-outline-anu">Core Values &rarr;</a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="motto-banner">&ldquo;<?= h(setting('tagline')) ?>&rdquo;</div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-cream border-top">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card-anu reveal reveal-1 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-bullseye"></i></div>
          <h2 class="h4">Our Mission</h2>
          <p class="mb-0"><?= h(setting('mission')) ?></p>
        </div></div>
      </div>
      <div class="col-md-6">
        <div class="card-anu reveal reveal-2 h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-eye"></i></div>
          <h2 class="h4">Our Vision</h2>
          <p class="mb-0"><?= h(setting('vision')) ?></p>
        </div></div>
      </div>
    </div>
  </div>
</section>

<?php if ($facts): ?>
<section class="facts-strip">
  <div class="container">
    <div class="row">
      <?php foreach ($facts as $f):
        $num = null; $suffix = '';
        if (preg_match('/^([\d,]+)(.*)$/', trim($f['stat_value']), $m)) {
            $num = (int) str_replace(',', '', $m[1]);
            $suffix = trim($m[2]);
        }
      ?>
        <div class="col-6 col-lg-3 fact">
          <?php if ($num !== null): ?>
            <div class="value" data-count="<?= $num ?>" data-suffix="<?= h($suffix) ?>"><?= h($f['stat_value']) ?></div>
          <?php else: ?>
            <div class="value"><?= h($f['stat_value']) ?></div>
          <?php endif; ?>
          <div class="label"><?= h($f['stat_label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5">
  <div class="container container-narrow text-center reveal">
    <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu">&larr; Back to About ANU</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
