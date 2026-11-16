<?php
$pageTitle = 'Institute of Research, Innovation & Outreach';
require_once __DIR__ . '/config/db.php';

$staffStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order LIMIT 3");
$staffStmt->execute([UNIT_SLUG]);
$staff = $staffStmt->fetchAll();

$heroStmt = db()->prepare("SELECT * FROM sliders WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$heroStmt->execute([UNIT_SLUG]);
$hero = $heroStmt->fetch();

require __DIR__ . '/includes/header.php';
?>
<section class="res-hero<?= $hero && $hero['image'] ? ' has-image' : '' ?>"<?= $hero && $hero['image'] ? ' style="background-image: linear-gradient(90deg, rgba(0,0,0,.74) 0%, rgba(0,0,0,.46) 42%, rgba(0,0,0,.2) 78%), url(\'' . h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) . '\')"' : '' ?>>
  <img class="hero-bg-image" src="<?= $hero && $hero['image'] ? h(MAIN_SITE_URL . '/assets/uploads/' . $hero['image']) : 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1600&q=80' ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.18;" onerror="this.style.display='none';">
  <div class="container" style="position:relative;">
    <div class="icon-badge"><i class="bi bi-search"></i></div>
    <p class="lead"><?= $hero && $hero['subtext'] ? h($hero['subtext']) : 'advancing knowledge through research, innovation and community-engaged scholarship.' ?></p>
    <h1 class="mb-3"><?= $hero && $hero['headline'] ? h($hero['headline']) : 'Institute of Research, Innovation & Outreach' ?></h1>
    <div class="d-flex gap-3 mt-4 flex-wrap">
      <a href="<?= h(main_setting('repository_url')) ?>" class="btn btn-res-cta btn-lg" target="_blank" rel="noopener">View Publications</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-res-outline btn-lg">Learn More</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../main-site/includes/school_messages.php';
sm_render_home(['leader_slug' => 'head-research', 'unit' => UNIT_SLUG, 'base' => BASE_URL, 'uploads' => UPLOADS_URL, 'css' => MAIN_SITE_URL . '/assets/css/school-messages.css', 'heading' => 'Message from the Director, Institute of Research', 'eyebrow' => 'Institute of Research', 'heads' => false, 'soon' => 'The Director&rsquo;s message will be published here soon.']);
?>
<section class="res-section">
  <div class="container">
    <div class="res-section-head"><h2>What We Do</h2></div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="res-feature-card"><div class="icon-badge"><i class="bi bi-award"></i></div><h3>Research grants</h3><p class="small text-muted mb-0">Supporting faculty and student research through internal and external funding.</p></div>
      </div>
      <div class="col-md-4">
        <div class="res-feature-card"><div class="icon-badge"><i class="bi bi-people"></i></div><h3>Peer-reviewed publications</h3><p class="small text-muted mb-0">Journals, papers and proposals produced across ANU's five schools.</p></div>
      </div>
      <div class="col-md-4">
        <div class="res-feature-card"><div class="icon-badge"><i class="bi bi-globe2"></i></div><h3>Community-engaged outreach</h3><p class="small text-muted mb-0">Translating research into practical impact for communities across Kenya.</p></div>
      </div>
    </div>
  </div>
</section>

<?php if ($staff): ?>
<section class="res-section tint">
  <div class="container">
    <div class="res-section-head"><h2>Meet the Team</h2></div>
    <div class="row g-4">
      <?php foreach ($staff as $s): ?>
        <div class="col-md-4">
          <div class="faculty-card">
            <?php if ($s['photo']): ?>
              <img src="<?= h(UPLOADS_URL . '/' . $s['photo']) ?>" alt="<?= h($s['full_name']) ?>">
            <?php else: ?>
              <div class="faculty-placeholder"></div>
            <?php endif; ?>
            <h3><?= h($s['full_name']) ?></h3>
            <p class="role"><?= h($s['title']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
