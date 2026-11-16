<?php
$pageTitle = 'About ANU';
$pageDescription = "Africa Nazarene University's mission, leadership, history and governance.";
require_once __DIR__ . '/includes/functions.php';

$facts = db()->query('SELECT * FROM fun_facts ORDER BY sort_order')->fetchAll();
$vc = db()->query("SELECT * FROM leadership WHERE role_type = 'vc' AND is_published = 1 ORDER BY sort_order LIMIT 1")->fetch();

require __DIR__ . '/includes/header.php';

$mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode(setting('address'));
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">About Africa Nazarene University</span>
    <h1>Faith, Character and Excellence Since 1994</h1>
    <p class="lead text-muted mb-0"><?= h(setting('tagline')) ?></p>
  </div>
</section>

<!-- QUICK STATS -->
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

<!-- VC MESSAGE CARD -->
<section class="py-5">
  <div class="container">
    <div class="section-head reveal">
      <h2>A Message from Our Leadership</h2>
      <a href="<?= BASE_URL ?>/<?= $vc ? 'leadership-message.php?id=' . (int)$vc['id'] : 'leadership.php' ?>" class="see-all">Read full message &rarr;</a>
    </div>
    <div class="message-card reveal">
      <div class="message-card-photo">
        <?php if ($vc && $vc['photo']): ?>
          <img src="<?= h(UPLOADS_URL . '/' . $vc['photo']) ?>" alt="<?= h($vc['full_name']) ?>">
        <?php else: ?>
          <span class="placeholder-icon"><i class="bi bi-person"></i></span>
        <?php endif; ?>
      </div>
      <div class="message-card-body">
        <i class="bi bi-quote"></i>
        <span class="message-card-eyebrow">Welcome Message from Vice Chancellor</span>
        <p class="message is-excerpt"><?= $vc && $vc['message']
            ? h(excerpt($vc['message'], 260))
            : "Africa Nazarene University forms graduates of character and competence for a life of service, read our full welcome message to prospective students, parents and partners." ?></p>
        <div class="signature">
          <strong><?= $vc ? h($vc['full_name']) : 'The Vice Chancellor' ?></strong>
          <span><?= $vc ? h(public_text($vc['title'], 'Vice Chancellor')) : 'Vice Chancellor' ?>, Africa Nazarene University</span>
        </div>
        <a href="<?= BASE_URL ?>/<?= $vc ? 'leadership-message.php?id=' . (int)$vc['id'] : 'leadership.php' ?>" class="btn btn-outline-anu mt-3 align-self-start">Read Full Message &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- MISSION & VISION -->
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

<!-- EXPLORE ABOUT ANU (hub tiles) -->
<section class="py-5">
  <div class="container">
    <div class="section-head reveal"><h2>Explore About ANU</h2></div>
    <div class="row g-4">
      <?php
      $tiles = [
        ['bi-person-lines-fill', 'Who We Are', 'Our identity as a Christian university sponsored by the Church of the Nazarene.', 'who-we-are.php'],
        ['bi-clock-history', 'Our History', 'From 62 students in 1994 to a chartered, multi-school university.', 'our-history.php'],
        ['bi-globe2', 'Affiliation', 'Our sponsorship by the Church of the Nazarene and our CUE charter.', 'affiliation.php'],
        ['bi-gem', 'Core Values', 'Character, Competence and Community, what shapes an ANU graduate.', 'core-values.php'],
        ['bi-patch-check', 'Accreditation', 'Our charter status and recognition by the Commission for University Education.', 'accreditation.php'],
        ['bi-diagram-3', 'Governance', 'Board of Trustees, University Council and Management.', 'governance.php'],
        ['bi-person-badge', 'Chancellor', "The University's Chancellor, provided through the Church of the Nazarene.", 'chancellor.php'],
        ['bi-chat-square-quote', 'Leadership', 'Welcome messages from the Vice Chancellor and Deputy Vice Chancellors.', 'leadership.php'],
        ['bi-heart', 'Corporate Social Responsibility', 'How we live out our faith and mission in the wider community.', 'csr.php'],
        ['bi-geo-alt', 'Main Campus', '124 acres bordering Nairobi National Park, off Magadi Road.', 'about.php#campus'],
      ];
      foreach ($tiles as $i => [$icon, $title, $desc, $link]): ?>
        <div class="col-sm-6 col-lg-3">
          <a class="about-tile reveal reveal-<?= ($i % 4) + 1 ?>" href="<?= BASE_URL ?>/<?= $link ?>">
            <div class="icon-badge"><i class="bi <?= $icon ?>"></i></div>
            <h3><?= $title ?></h3>
            <p><?= $desc ?></p>
            <span class="tile-go">Learn more &rarr;</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CAMPUS -->
<section class="py-5 bg-cream border-top" id="campus">
  <div class="container container-narrow reveal">
    <div class="section-head"><h2>Main Campus</h2></div>
    <p><?= h(setting('address')) ?>. The University offers a peaceful, tranquil environment with
    well-manicured gardens on 124 acres bordering Nairobi National Park.</p>
    <a href="<?= h($mapUrl) ?>" target="_blank" rel="noopener" class="btn btn-outline-anu">
      <i class="bi bi-signpost-2 me-1"></i> Get Directions
    </a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
