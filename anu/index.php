<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB(); $s = getAllSettings();
$applyUrl = $s['apply_url'] ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
try {
$news   = $db->query("SELECT * FROM news WHERE is_published=1 ORDER BY is_featured DESC, published_at DESC LIMIT 3")->fetchAll();
// Item 6: same `events` table admin/events.php already manages and
// livestream.php already reads from -- just also surfaced here, merged
// with News, instead of only living on the separate Live Events portal.
$events = $db->query("SELECT * FROM events WHERE is_active=1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3")->fetchAll();
// Item 9b: accreditation band now admin-managed (Admin -> Accreditations).
// Falls back to the same 4 real bodies that were hardcoded here before,
// so a server that hasn't run the migration yet still shows correct,
// real content instead of an empty band.
$accreditations = [];
try { $accreditations = $db->query("SELECT * FROM accreditations WHERE is_active=1 ORDER BY sort_order,name")->fetchAll(); } catch (Exception $e) {}
if (empty($accreditations)) {
    $accreditations = [
        ['name' => 'Commission for University Education (CUE)', 'icon' => 'bi-patch-check-fill', 'image' => '', 'url' => ''],
        ['name' => 'Kenya National Qualifications Authority',   'icon' => 'bi-globe2',            'image' => '', 'url' => ''],
        ['name' => 'Church of the Nazarene International',      'icon' => 'bi-award-fill',         'image' => '', 'url' => ''],
        ['name' => 'Higher Education Loans Board (HELB)',       'icon' => 'bi-building',           'image' => '', 'url' => ''],
    ];
}
$testis = $db->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY is_featured DESC, id ASC LIMIT 8")->fetchAll();
$progs  = $db->query("SELECT * FROM programs WHERE is_active=1 ORDER BY is_featured DESC, id ASC LIMIT 6")->fetchAll();
$faculty= getStaff(['is_featured' => 1], 3);
} catch(Exception $e) {}

trackPage('home', 'Home');
renderHead('Home'); renderNavbar('home'); renderHero('home');
?>

<!-- Director's message: first block under the hero, same card as the Dean messages -->
<?php renderDirectorMessage(); ?>

<!-- ═══════ ACCREDITATION STRIP — admin-managed (Admin -> Accreditations) ═══════ -->
<div class="accred-strip">
  <div class="container">
    <div class="accred-inner">
      <span class="accred-label">Recognised &amp; Accredited By</span>
      <div class="accred-logos">
        <?php foreach($accreditations as $ai => $acc):
          $inner = !empty($acc['image'])
            ? '<img src="'.e(imgUrl($acc['image'])).'" alt="'.e($acc['name']).'" style="height:28px;width:auto;object-fit:contain">'
            : '<i class="bi '.e($acc['icon'] ?: 'bi-patch-check-fill').'"></i> '.e($acc['name']);
        ?>
        <?php if ($ai > 0): ?><div class="accred-sep"></div><?php endif; ?>
        <?php if (!empty($acc['url'])): ?>
          <a class="accred-badge" href="<?=e($acc['url'])?>" target="_blank" rel="noopener"><?=$inner?></a>
        <?php else: ?>
          <div class="accred-badge"><?=$inner?></div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ═══════ STATS ═══════ -->
<section class="stats-band">
  <div class="container">
    <div class="row g-3 text-center">
      <?php foreach([
        [$s['students_count']??'5,000+','bi-mortarboard-fill','Students Enrolled'],
        [$s['programs_count']??'50+',   'bi-book-fill',       'Programs Offered'],
        [$s['countries_count']??'15+',  'bi-globe2',          'Countries Reached'],
        [$s['years_count']??'25+',      'bi-award-fill',      'Years of Excellence'],
      ] as [$v,$ic,$lb]): ?>
      <?php
        // D2: split the display value into a numeric part (for the
        // count-up animation) and a suffix (e.g. "+", ",000+") so the
        // animation counts up cleanly then the original label reappears.
        preg_match('/([\d,]+)(.*)$/', (string)$v, $m);
        $numPart = isset($m[1]) ? (int)str_replace(',', '', $m[1]) : 0;
        $suffix  = $m[2] ?? '';
      ?>
      <div class="col-6 col-md-3" data-aos="fade-up">
        <div class="stat-box">
          <i class="bi <?=$ic?> stat-ico"></i>
          <span class="stat-num" data-count="<?=$numPart?>" data-suffix="<?=e($suffix)?>">0<?=e($suffix)?></span>
          <span class="stat-lbl"><?=$lb?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════ ORIENTATION PACK — homepage card (item 5) ═══════ -->
<section class="sec" style="padding-top:32px;padding-bottom:32px">
  <div class="container">
    <div class="card-pop on-accent" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding:24px 28px">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(0,0,0,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="bi bi-file-earmark-text" style="font-size:1.6rem"></i>
      </div>
      <div style="flex:1;min-width:200px">
        <h5 style="margin-bottom:4px;font-family:var(--fb);font-size:1.05rem">New Student? Get the Orientation</h5>
        <p class="text-muted mb-0" style="font-size:.88rem">Everything you need to get started at ANU Online — in one downloadable guide.</p>
      </div>
      <a href="/orientation-pack.php" target="_blank" rel="noopener" class="btn-hero-solid" style="flex-shrink:0">
        Download Orientation <i class="bi bi-download"></i>
      </a>
    </div>
  </div>
</section>

<!-- ═══════ HOW IT WORKS ═══════ -->
<section class="sec" id="how">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Simple Process</div>
      <h2 class="sec-title">Your Journey Starts <span class="hl">in 4 Steps</span></h2>
      <p class="sec-sub mx-auto">From application to graduation — we've made it straightforward.</p>
    </div>
    <div class="how-grid">
      <?php foreach([
        ['01','bi-pencil-square',   'Apply Online',       'Fill out our simple application form. No entrance exam required — just your passion and commitment.'],
        ['02','bi-person-check-fill','Get Enrolled',      'Our admissions team reviews your application and contacts you within 48 hours to confirm your place.'],
        ['03','bi-laptop-fill',     'Study on eNaz',      'Access your lectures, assignments and resources 24/7 from any device on our modern learning platform.'],
        ['04','bi-mortarboard-fill','Graduate & Grow',    'Earn your accredited degree and join our growing network of over 5,000 ANU Online alumni.'],
      ] as [$num,$ic,$title,$desc]): ?>
      <div class="how-step" data-aos="fade-up">
        <div class="how-badge"><?=$num?></div>
        <div class="how-num"><?=$num?></div>
        <div class="how-ico"><i class="bi <?=$ic?>"></i></div>
        <h5 class="how-title"><?=$title?></h5>
        <p class="how-desc"><?=$desc?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="how-connector" aria-hidden="true">
      <div class="how-line"></div>
    </div>
    <div class="text-center mt-5" data-aos="fade-up">
      <a href="/anu/contact.php" class="btn-hero-solid" style="font-size:.9rem;padding:14px 34px">
        Start Your Application <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- ═══════ OUR FIVE SCHOOLS — same list and look as the university home page ═══════ -->
<section class="sec" id="schools">
  <div class="container">
    <div class="or-head" data-aos="fade-up"><h2>Our Five Schools</h2><a href="/anu/programs.php">Browse all programmes &rarr;</a></div>
    <div class="sc-row">
      <?php foreach ([
        ['law',        'School of Law',                              'Legal education grounded in justice, ethics and advocacy.',                              '/anuweb-network/law-school-site/'],
        ['business',   'School of Business',                         'Business, commerce and management programmes for East Africa and beyond.',               '/anuweb-network/business-school-site/'],
        ['science',    'School of Science and Technology',           'Computer science, IT and applied sciences.',                                             '/anuweb-network/scitech-school-site/'],
        ['humanities', 'School of Humanities and Social Sciences',   'Peace, counseling, community development, media and education.',                         '/anuweb-network/humanities-school-site/'],
        ['religion',   'School of Religion and Christian Ministry',  'Theology and ministry formation rooted in the Wesleyan-holiness tradition.',             '/anuweb-network/religion-school-site/'],
      ] as $i => [$img,$nm,$ds,$url]): ?>
      <a class="sc-card" href="<?=e($url)?>" data-aos="fade-up" data-aos-delay="<?=$i*50?>">
        <span class="sc-media"><img src="/anu/assets/images/schools/<?=e($img)?>.jpg" alt=""><span class="sc-num"><?=str_pad($i+1,2,'0',STR_PAD_LEFT)?></span></span>
        <span class="sc-body"><b><?=e($nm)?></b><span><?=e($ds)?></span><em>Explore school <i class="bi bi-arrow-right"></i></em></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════ SPLIT CTA BANNER ═══════ -->
<section class="split-cta">
  <div class="split-cta-left">
    <div class="split-cta-inner">
      <div class="split-cta-eyebrow">Where Your Future Begins</div>
      <h2 class="split-cta-heading">We Believe.<br>You Belong Here.</h2>
      <p class="split-cta-sub">Discover why ANU Online is where faith meets calling. Take the first step today and join thousands transforming their futures.</p>
      <div class="split-cta-btns">
        <a href="<?=e($applyUrl)?>" target="_blank" rel="noopener" class="split-btn-gold">Apply Now <i class="bi bi-chevron-right"></i></a>
        <a href="/anu/programs.php" class="split-btn-ghost">Explore Programs <i class="bi bi-chevron-right"></i></a>
      </div>
    </div>
  </div>
  <div class="split-cta-right">
    <img src="<?=SITE_URL?>/assets/images/anubanner.jpeg" alt="ANU Students"
         onerror="this.src='<?=SITE_URL?>/assets/images/default-hero.jpg'">
  </div>
</section>

<!-- ═══════ TESTIMONIALS — Item E: compact multi-card slider ═══════ -->
<?php if(!empty($testis)): ?>
<section class="ts-sec">
  <div class="container">
    <div class="ts-head" data-aos="fade-up">
      <div class="sec-tag">Student Stories</div>
      <h2 class="sec-title mb-0">What Our <span class="hl">Students Say</span></h2>
    </div>
    <div class="ts-viewport">
      <div class="ts-track" id="tsTrack">
        <?php foreach($testis as $t):
          $stars = min(5,max(1,(int)($t['rating']??5)));
          $initials = strtoupper(substr($t['name'],0,2));
        ?>
        <div class="ts-card">
          <i class="bi bi-quote ts-quote-ico"></i>
          <div class="ts-card-head">
            <?php if(!empty($t['photo'])): ?>
              <img class="ts-avatar" src="<?=imgUrl($t['photo'])?>" alt="<?=e($t['name'])?>">
            <?php else: ?>
              <div class="ts-avatar-fallback"><?=e($initials)?></div>
            <?php endif; ?>
            <div>
              <span class="ts-name"><?=e($t['name'])?></span>
              <span class="ts-prog"><?=e($t['program']??'')?></span>
            </div>
          </div>
          <div class="ts-stars">
            <?php for($si=1;$si<=5;$si++): ?><i class="bi bi-star<?=$si<=$stars?'-fill':''?>"></i><?php endfor; ?>
          </div>
          <blockquote class="ts-quote">"<?=e($t['quote'])?>"</blockquote>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="ts-dots" id="tsDots"></div>
  </div>
</section>
<script>
(function(){
  var track=document.getElementById('tsTrack');
  var dotsWrap=document.getElementById('tsDots');
  var cards=Array.prototype.slice.call(track.children);
  if(!cards.length) return;
  var page=0, timer=null;

  function perView(){
    var w=window.innerWidth;
    if(w<=575) return 1;
    if(w<=991) return 2;
    return 3;
  }
  function pageCount(){ return Math.max(1, Math.ceil(cards.length / perView())); }

  function renderDots(){
    dotsWrap.innerHTML='';
    var pc=pageCount();
    for(var i=0;i<pc;i++){
      var b=document.createElement('button');
      b.className='ts-dot'+(i===page?' active':'');
      b.setAttribute('aria-label','Testimonials page '+(i+1));
      b.addEventListener('click',function(idx){ return function(){ goTo(idx); restart(); }; }(i));
      dotsWrap.appendChild(b);
    }
  }
  function goTo(p){
    var pc=pageCount();
    page=((p%pc)+pc)%pc;
    var pv=perView();
    var cardWidth=cards[0].getBoundingClientRect().width;
    var gap=18;
    var offset=page*pv*(cardWidth+gap);
    track.style.transform='translateX(-'+offset+'px)';
    Array.prototype.forEach.call(dotsWrap.children,function(d,i){d.classList.toggle('active',i===page);});
  }
  function restart(){
    if(timer) clearInterval(timer);
    timer=setInterval(function(){ goTo(page+1); },6000);
  }
  renderDots();
  goTo(0);
  restart();
  window.addEventListener('resize',function(){
    renderDots();
    page=0;
    goTo(0);
  });
})();
</script>
<?php endif; ?>

<!-- ═══════ NEWS & EVENTS ═══════ -->
<?php if(!empty($news) || !empty($events)):
  $featured = $news[0] ?? null; $restNews = array_slice($news, 1, 3); ?>
<section class="sec bg-light" id="news-events">
  <div class="container">
    <div class="or-head" data-aos="fade-up"><h2>News &amp; Events</h2><a href="/anu/news.php">View all &rarr;</a></div>
    <div class="row g-4">
      <div class="<?= !empty($events) ? 'col-lg-8' : 'col-12' ?>">
        <?php if($featured):
          $fi = $featured['featured_image'] ? imgUrl($featured['featured_image']) : ''; ?>
        <a class="nx-feature" href="/anu/news.php?id=<?=$featured['id']?>" data-aos="fade-up"<?= $fi ? ' style="--nx-img:url(\''.e($fi).'\')"' : '' ?>>
          <span class="nx-cat"><?=e($featured['category'] ?: 'News')?></span>
          <h3><?=e($featured['title'])?></h3>
          <p><?=e(mb_strimwidth($featured['excerpt']??'',0,170,'…'))?></p>
          <span class="nx-meta"><span><i class="bi bi-calendar3"></i> <?=formatDate($featured['published_at']??date('Y-m-d'))?></span><b>Read story <i class="bi bi-arrow-right"></i></b></span>
        </a>
        <?php endif; ?>
        <?php foreach($restNews as $n): ?>
        <a class="nx-row" href="/anu/news.php?id=<?=$n['id']?>" data-aos="fade-up">
          <span class="nx-date"><b><?=e(date('d', strtotime($n['published_at']??'now')))?></b><?=e(date('M', strtotime($n['published_at']??'now')))?></span>
          <div><span class="nx-cat sm"><?=e($n['category'] ?: 'News')?></span><h4><?=e($n['title'])?></h4>
            <p><?=e(mb_strimwidth($n['excerpt']??'',0,120,'…'))?></p></div>
          <i class="bi bi-arrow-right nx-go"></i>
        </a>
        <?php endforeach; ?>
      </div>
      <?php if(!empty($events)): ?>
      <div class="col-lg-4">
        <div class="nx-events" data-aos="fade-up">
          <h3><i class="bi bi-calendar-event"></i> Upcoming Events</h3>
          <?php foreach($events as $ev):
            $isLive = (date('Y-m-d') === $ev['event_date']); $dObj = date_create($ev['event_date']); ?>
          <a class="nx-ev" href="<?=e($ev['link'] ?: '/anu/livestream.php')?>" <?=$ev['link'] && str_starts_with($ev['link'],'http') ? "target='_blank' rel='noopener'" : ''?>>
            <span class="nx-date gold"><b><?=e($dObj ? $dObj->format('d') : '')?></b><?=e($dObj ? $dObj->format('M') : '')?></span>
            <div><h4><?=e($ev['title'])?></h4>
              <p><i class="bi bi-geo-alt"></i> <?=e($ev['location'] ?: 'Online')?><?= $isLive ? ' <span class="nx-live">Live</span>' : '' ?></p></div>
          </a>
          <?php endforeach; ?>
          <a class="nx-all" href="/anu/livestream.php">All live events <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════ FINAL CTA ═══════ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Get Started Today</div>
    <h2 class="mt-3" style="color:#fff;font-family:'Roboto',Arial,sans-serif;font-size:clamp(2rem,4vw,3rem)">
      Ready to Transform Your Future?
    </h2>
    <p class="mt-3" style="color:rgba(255,255,255,.75);max-width:480px;margin:12px auto 0">
      Join thousands of students across Africa studying with ANU Online.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="<?=e($applyUrl)?>" target="_blank" rel="noopener" class="btn-cta-w">Apply Now <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/programs.php" class="btn-cta-o">Browse Programs</a>
    </div>
  </div>
</section>

<?php renderFooter(); ?>
