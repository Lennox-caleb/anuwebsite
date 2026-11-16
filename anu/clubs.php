<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db    = getDB();
try {
$clubs = $db->query("SELECT * FROM clubs WHERE is_active=1 ORDER BY sort_order, name")->fetchAll();
} catch(Exception $e) {}

// Build category list from DB clubs; fallback categories for filter pills
$categories = [];
foreach ($clubs as $c) {
    $cat = $c['category'] ?? '';
    if ($cat) $categories[$cat] = true;
}
$categories = array_keys($categories);

$heroSlides = getSlides('clubs');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('clubs');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

// Fallback club data if DB is empty
$fallbackClubs = [
  ['id'=>'f1','name'=>'Christian Union (CU)',     'icon'=>'bi-people-fill',     'category'=>'Faith',          'description'=>'The largest student fellowship at ANU Online, welcoming all students regardless of denomination. Weekly virtual services, small groups, and mission opportunities.','leader_name'=>'Faith Wanjiku','is_featured'=>1],
  ['id'=>'f2','name'=>'ANU Tech Society',          'icon'=>'bi-laptop-fill',     'category'=>'Technology',     'description'=>'For students passionate about tech innovation. We run hackathons, coding competitions, and connect students with industry mentors in tech.','leader_name'=>'Brian Otieno','is_featured'=>1],
  ['id'=>'f3','name'=>'Business & Entrepreneurship Club','icon'=>'bi-briefcase-fill','category'=>'Business',  'description'=>'Fostering entrepreneurial spirit through business plan competitions, networking events, and mentorship from successful alumni entrepreneurs.','leader_name'=>'Sarah Kamau','is_featured'=>0],
  ['id'=>'f4','name'=>'ANU Debating Society',      'icon'=>'bi-mic-fill',        'category'=>'Leadership',     'description'=>'Developing critical thinking, research skills, and confident public speaking through weekly debates and inter-university competitions.','leader_name'=>'James Mwangi','is_featured'=>0],
  ['id'=>'f5','name'=>'Eco & Sustainability Club',  'icon'=>'bi-globe2',          'category'=>'Environment',    'description'=>'Advocating for environmental stewardship and sustainable living aligned with Christian values of caring for creation.','leader_name'=>'Grace Njeri','is_featured'=>0],
  ['id'=>'f6','name'=>'WECANU — Women in Leadership','icon'=>'bi-star-fill',     'category'=>'Leadership',     'description'=>'Empowering women at ANU Online through mentorship, leadership training, and community building across all programs.','leader_name'=>'Dr. Ruth Achieng','is_featured'=>1],
  ['id'=>'f7','name'=>'Creative Arts Society',     'icon'=>'bi-palette-fill',    'category'=>'Arts',           'description'=>'A home for writers, designers, musicians, and visual artists. We celebrate creativity as a form of worship and expression.','leader_name'=>'Victor Omondi','is_featured'=>0],
  ['id'=>'f8','name'=>'Student Welfare Club',      'icon'=>'bi-heart-fill',      'category'=>'Welfare',        'description'=>'Supporting the mental health, wellbeing, and financial welfare of ANU Online students through peer support networks and resources.','leader_name'=>'Alice Ndung\'u','is_featured'=>0],
  ['id'=>'f9','name'=>'Legal Aid Society',         'icon'=>'bi-shield-fill',     'category'=>'Law',            'description'=>'Law students providing free legal literacy sessions and community outreach, connecting classroom learning to real-world impact.','leader_name'=>'Peter Karanja','is_featured'=>0],
];

$displayClubs = !empty($clubs) ? $clubs : $fallbackClubs;

// Build filter categories from display data
$allCats = [];
foreach ($displayClubs as $c) { $cat = $c['category']??''; if($cat) $allCats[$cat]=true; }
$allCats = array_keys($allCats);
sort($allCats);

trackPage('clubs', 'Clubs & Societies');
renderHead('Clubs & Societies'); renderNavbar('student-life');
?>

<!-- ══ HERO ══ -->
<div class="clb-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="container clb-hero-inner">
    <div class="row align-items-center g-4">
      <div class="col-lg-7" data-aos="fade-right">
        <div class="sec-tag" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.8)">Student Life</div>
        <h1 class="clb-hero-title">Clubs &amp; <span style="color:var(--gold)">Societies</span></h1>
        <p class="clb-hero-sub">Journalists, entrepreneurs, faith leaders, and tech innovators — find your people and make your mark at ANU Online.</p>
        <div class="clb-hero-count">
          <i class="bi bi-people-fill" style="color:var(--gold)"></i>
          <?= count($displayClubs) ?>+ active clubs &amp; organisations
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:28px">
          <a href="#clubs-list"  class="btn-hero-solid">Find a Club <i class="bi bi-arrow-down"></i></a>
          <a href="#start-club" class="btn-hero-outline">Start a Club</a>
        </div>
      </div>
      <!-- Stats on right -->
      <div class="col-lg-5" data-aos="fade-left">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <?php foreach([
            ['bi-people-fill',    count($displayClubs).'+', 'Active Clubs',       'rgba(255,200,0,.12)','#fac823'],
            ['bi-mortarboard-fill','5,000+',             'Student Members',   'rgba(255,255,255,.08)','#fff'],
            ['bi-globe2',         '15+',                 'Countries',         'rgba(255,255,255,.08)','#fff'],
            ['bi-trophy-fill',    '25+',                 'Annual Events',     'rgba(255,200,0,.12)','#fac823'],
          ] as [$ic,$v,$l,$bg,$col]): ?>
          <div style="background:<?=$bg?>;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:22px 18px;text-align:center">
            <i class="bi <?=$ic?>" style="font-size:1.6rem;color:<?=$col?>;display:block;margin-bottom:8px"></i>
            <div style="font-family:var(--fh);font-size:1.8rem;color:#fff;line-height:1"><?=$v?></div>
            <div style="font-size:.68rem;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-top:4px"><?=$l?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ CLUBS LIST ══ -->
<section class="sec" id="clubs-list">
  <div class="container">

    <!-- Filter bar -->
    <div class="clb-filter" data-aos="fade-up">
      <button class="clb-pill active" onclick="clbFilter('all', this)">All Clubs</button>
      <?php foreach($allCats as $cat): ?>
      <button class="clb-pill" onclick="clbFilter('<?= e(strtolower($cat)) ?>', this)"><?= e($cat) ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Grid -->
    <div class="clb-grid" id="clbGrid">
      <?php foreach($displayClubs as $i => $c):
        $cat = strtolower($c['category'] ?? '');
        $isFeatured = !empty($c['is_featured']);
      ?>
      <div class="clb-card <?= $isFeatured ? 'clb-card-featured' : '' ?>"
           data-cat="<?= e($cat) ?>"
           data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 60 ?>">
        <div class="clb-card-header">
          <div class="clb-card-ico"><i class="bi <?= e($c['icon'] ?? 'bi-people') ?>"></i></div>
          <div class="clb-card-meta">
            <div class="clb-card-name"><?= e($c['name']) ?></div>
            <?php if (!empty($c['category'])): ?>
            <div class="clb-card-cat"><?= e($c['category']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="clb-card-body">
          <p class="clb-card-desc"><?= e($c['description'] ?? '') ?></p>
        </div>
        <div class="clb-card-footer">
          <span class="clb-card-leader">
            <?php if (!empty($c['leader_name'])): ?>
            <i class="bi bi-person-fill"></i><?= e($c['leader_name']) ?>
            <?php else: ?>
            <i class="bi bi-people-fill"></i>Open Leadership
            <?php endif; ?>
          </span>
          <a href="/anu/contact.php?subject=<?= urlencode('Join '.$c['name']) ?>" class="clb-join-btn">
            Join <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Empty state (shown via JS when filter matches nothing) -->
    <div id="clbEmpty" style="display:none;text-align:center;padding:60px 0;color:var(--muted)">
      <i class="bi bi-search" style="font-size:2.5rem;display:block;margin-bottom:12px;color:#ddd"></i>
      No clubs found in this category yet. <a href="#start-club" style="color:var(--red);font-weight:700">Start one!</a>
    </div>

    <!-- Start a club strip -->
    <div class="clb-start-strip" id="start-club" data-aos="fade-up">
      <div>
        <h4>Start Your Own Club</h4>
        <p>Have a passion that isn't represented yet? ANU Online encourages students to create new clubs and grow our community.</p>
      </div>
      <a href="/anu/contact.php?subject=Start+a+Club" class="split-btn-gold" style="flex-shrink:0">
        <i class="bi bi-plus-circle-fill me-2"></i>Propose a Club
      </a>
    </div>

  </div>
</section>

<!-- ══ TESTIMONIALS ══ -->
<section class="sec bg-light">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Student Voices</div>
      <h2 class="sec-title">What Our Members <span class="hl">Say</span></h2>
    </div>
    <div class="clb-testi-row">
      <?php
      $testis = [
        ['Grace K.', 'BTh Student',       'Christian Union changed my life at ANU. I found community, purpose, and leadership opportunities that shaped who I am as a student and in faith.'],
        ['Brian M.', 'BSc IT Student',    'The Tech Society gave me a platform to showcase my projects and connect with mentors in the industry. I landed my first internship through it.'],
        ['Alice N.', 'MBA Student',       'WECANU\'s seminars helped me find my voice as a woman in leadership. I walked away with confidence, a network, and a clear vision for my career.'],
      ];
      foreach($testis as $i=>[$name,$prog,$quote]): ?>
      <div class="clb-testi-card" data-aos="fade-up" data-aos-delay="<?=$i*80?>">
        <div class="clb-testi-quote"><?= e($quote) ?></div>
        <div class="clb-testi-author">
          <div class="clb-testi-avatar"><?= $name[0] ?></div>
          <div>
            <div class="clb-testi-name"><?= e($name) ?></div>
            <div class="clb-testi-prog"><?= e($prog) ?></div>
            <div class="clb-testi-stars">★★★★★</div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Get Involved</div>
    <h2 class="mt-3" style="color:#fff;font-family:var(--fh);font-size:clamp(2rem,4vw,3rem)">
      Find Your Community at ANU Online
    </h2>
    <p style="color:rgba(255,255,255,.68);max-width:440px;margin:14px auto 0;line-height:1.75">
      Join a club, lead a group, or start something new. Your story starts here.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="/anu/contact.php" class="btn-cta-w">Contact Student Life <i class="bi bi-arrow-right"></i></a>
      <a href="#clubs-list"      class="btn-cta-o">Browse All Clubs</a>
    </div>
  </div>
</section>

<!-- ══ FILTER JS ══ -->
<script>
function clbFilter(cat, btn) {
  // Update active pill
  document.querySelectorAll('.clb-pill').forEach(function(p){ p.classList.remove('active'); });
  btn.classList.add('active');

  var cards = document.querySelectorAll('#clbGrid .clb-card');
  var visible = 0;
  cards.forEach(function(card){
    var match = cat === 'all' || card.dataset.cat === cat;
    card.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('clbEmpty').style.display = visible === 0 ? 'block' : 'none';
}
</script>

<?php renderFooter(); ?>
