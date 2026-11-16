<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();
$settings = getAllSettings();

// Load resources
$resources = [];
try {
    $resources = $db->query("SELECT * FROM support_resources WHERE is_active=1 ORDER BY is_featured DESC, sort_order, id")->fetchAll();
} catch(Exception $e){ $resources = []; }

$resCats = [];
foreach($resources as $r){ if($r['category']) $resCats[$r['category']]=true; }
$resCats = array_keys($resCats);

// Featured resources (top 3)
$featuredRes = array_slice(array_filter($resources, fn($r)=>$r['is_featured']), 0, 3);

// Learning paths — curated category groupings
$learningPaths = [
  ['icon'=>'bi-rocket-takeoff-fill','color'=>'#c1121f','bg'=>'rgba(193,18,31,.07)',
   'title'=>'Getting Started',  'desc'=>'New student? Start here. Set up your account, navigate eNaz, and attend your first virtual class.',
   'cat'=>'Getting Started'],
  ['icon'=>'bi-journal-check',     'color'=>'#1565C0','bg'=>'rgba(21,101,192,.07)',
   'title'=>'Academic Success',   'desc'=>'Submit assignments, track grades, use the library, and manage your academic calendar.',
   'cat'=>'Academic'],
  ['icon'=>'bi-cash-coin',         'color'=>'#2e7d32','bg'=>'rgba(46,125,50,.07)',
   'title'=>'Fees & Finance',     'desc'=>'Pay fees via M-Pesa, apply for HELB, download receipts, and manage your payment plan.',
   'cat'=>'Finance'],
  ['icon'=>'bi-tools',             'color'=>'#6d4c41','bg'=>'rgba(109,76,65,.07)',
   'title'=>'Tech & Troubleshooting','desc'=>'Fix login issues, browser problems, LMS errors, and video conference setup.',
   'cat'=>'Technical'],
];

function ytEmbed(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/', $url, $m))
        return 'https://www.youtube.com/embed/'.$m[1].'?autoplay=1&rel=0';
    return $url;
}
function ytThumb(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/', $url, $m))
        return 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
    return '';
}

$heroSlides = getSlides('support');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('support');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

trackPage('support', 'Support Center');
renderHead('Support Centre'); renderNavbar('support');
?>

<!-- ══ HERO ══ -->
<div class="sup-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="sup-hero-noise"></div>
  <div class="container sup-hero-inner">
    <div class="row align-items-end g-4">
      <div class="col-lg-7" data-aos="fade-right">
        <div class="sup-hero-eyebrow"><span class="sup-hero-dot"></span>ANU Online Help Centre</div>
        <h1 class="sup-hero-title">Student <span style="color:var(--gold)">Support</span> Centre</h1>
        <p style="color:rgba(255,255,255,.7);font-size:1rem;line-height:1.8;max-width:500px;margin-bottom:28px">
          Find tutorials, step-by-step guides, and direct support — everything you need to succeed at ANU Online, in one place.
        </p>
        <div class="sup-search-bar">
          <input type="text" id="supSearch" placeholder="Search tutorials, guides, FAQs…" oninput="supDoSearch(this.value)" autocomplete="off">
          <button onclick="supDoSearch(document.getElementById('supSearch').value)">
            <i class="bi bi-search"></i> Search
          </button>
        </div>
        <div class="sup-search-hints">
          <?php foreach(['eNaz Login','Pay Fees via M-Pesa','Submitting Assignments','Library Access','Forgot Password'] as $hint): ?>
          <button onclick="document.getElementById('supSearch').value='<?=$hint?>';supDoSearch('<?=$hint?>')"><?= $hint ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-5" data-aos="fade-left">
        <div class="sup-hero-stats">
          <?php foreach([
            ['bi-play-circle-fill', count($resources).'+ Resources', 'Videos, PDFs & Guides'],
            ['bi-clock-fill',       '24/7',          'Self-help Access'],
            ['bi-headset',          'Live Help',      'Mon–Fri, 8am–5pm'],
            ['bi-star-fill',        '4.8★',           'Student Rating'],
          ] as [$ic,$v,$l]): ?>
          <div class="sup-hero-stat-tile">
            <i class="bi <?= $ic ?>"></i>
            <span class="sup-hero-stat-val"><?= $v ?></span>
            <span class="sup-hero-stat-lbl"><?= $l ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ LEARNING PATHS ══ -->
<section class="sec">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Guided Learning</div>
      <h2 class="sec-title">Learning <span class="hl">Paths</span></h2>
      <p class="sec-sub mx-auto">Not sure where to start? Follow a curated path built for your needs.</p>
    </div>
    <div class="sup-paths-grid">
      <?php foreach($learningPaths as $i=>$path):
        // Count resources in this category
        $pathCount = count(array_filter($resources, fn($r)=> strtolower($r['category'])==strtolower($path['cat'])));
      ?>
      <div class="sup-path-card" data-aos="fade-up" data-aos-delay="<?= $i*70 ?>">
        <div class="sup-path-ico" style="background:<?=$path['bg']?>;color:<?=$path['color']?>">
          <i class="bi <?= $path['icon'] ?>"></i>
        </div>
        <div class="sup-path-body">
          <h5 class="sup-path-title"><?= $path['title'] ?></h5>
          <p class="sup-path-desc"><?= $path['desc'] ?></p>
          <?php if($pathCount): ?>
          <div class="sup-path-count"><?= $pathCount ?> resource<?= $pathCount!==1?'s':'' ?></div>
          <?php endif; ?>
        </div>
        <button onclick="resFilter('<?= strtolower($path['cat']) ?>',null);document.getElementById('resources').scrollIntoView({behavior:'smooth'})"
                class="sup-path-btn" style="color:<?=$path['color']?>">
          Start Path <i class="bi bi-arrow-right"></i>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ FEATURED TUTORIALS ══ -->
<?php if(!empty($featuredRes)): ?>
<section class="sec" style="background:var(--dark);padding:72px 0">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-5" data-aos="fade-up">
      <div>
        <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Must-Watch</div>
        <h2 class="sec-title" style="color:#fff;margin-bottom:0">Featured <span style="color:var(--gold)">Tutorials</span></h2>
      </div>
      <a href="#resources" style="color:rgba(255,255,255,.55);font-size:.82rem;font-weight:700">View All Resources <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="sup-featured-row">
      <?php foreach($featuredRes as $i=>$r):
        $thumb    = !empty($r['thumbnail']) ? imgUrl($r['thumbnail']) : ($r['type']==='video' ? ytThumb($r['url']) : '');
        $embedUrl = $r['type']==='video' ? ytEmbed($r['url']) : '';
      ?>
      <div class="sup-feat-card" data-aos="fade-up" data-aos-delay="<?= $i*80 ?>">
        <div class="sup-feat-thumb" onclick="<?= $r['type']==='video' ? "supOpenVideo('".e($embedUrl)."')" : "window.open('".e($r['url'])."','_blank')" ?>" style="cursor:pointer">
          <?php if($thumb): ?>
          <img src="<?= e($thumb) ?>" alt="<?= e($r['title']) ?>" loading="lazy" onerror="this.style.display='none'">
          <?php else: ?>
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(255,255,255,.2)">
            <i class="bi bi-play-circle"></i>
          </div>
          <?php endif; ?>
          <div class="sup-feat-thumb-overlay">
            <div class="sup-feat-play-btn">
              <i class="bi bi-<?= $r['type']==='video'?'play-fill':'arrow-up-right' ?>"></i>
            </div>
          </div>
          <span class="sup-res-type-badge sup-res-type-<?= e($r['type']) ?>"><?= ucfirst($r['type']) ?></span>
          <?php if($r['duration']): ?>
          <span class="sup-res-duration"><?= e($r['duration']) ?></span>
          <?php endif; ?>
        </div>
        <div class="sup-feat-body">
          <div class="sup-res-cat" style="color:var(--gold)"><?= e($r['category']) ?></div>
          <div class="sup-feat-title"><?= e($r['title']) ?></div>
          <?php if($r['description']): ?>
          <p class="sup-feat-desc"><?= e(mb_strimwidth($r['description'],0,100,'…')) ?></p>
          <?php endif; ?>
          <?php if($r['type']==='video'): ?>
          <button onclick="supOpenVideo('<?= e($embedUrl) ?>')" class="sup-feat-btn">
            <i class="bi bi-play-fill me-1"></i>Watch Now
          </button>
          <?php else: ?>
          <a href="<?= e($r['url']) ?>" target="_blank" class="sup-feat-btn">
            <i class="bi bi-arrow-up-right me-1"></i>Open Resource
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══ SUPPORT CATEGORIES ══ -->
<section class="sec" style="background:var(--light)">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Support Areas</div>
      <h2 class="sec-title">How Can We <span class="hl">Help?</span></h2>
      <p class="sec-sub mx-auto">Choose a support area or search above to find what you need quickly.</p>
    </div>
    <div class="sup-grid" id="supCardsGrid">
      <?php foreach([
        ['bi-mortarboard-fill', '#c1121f','rgba(193,18,31,.08)', 'Student Support',     'Help with eNaz login, class navigation, assignment submission, and enrollment.'],
        ['bi-laptop-fill',      '#1565C0','rgba(21,101,192,.08)','Technical Support',   'System errors, browser issues, and LMS troubleshooting from our IT team.'],
        ['bi-cash-stack',       '#2e7d32','rgba(46,125,50,.08)', 'Fees & Finance',      'Tuition, M-Pesa payment guides, HELB applications, and payment plans.'],
        ['bi-book-fill',        '#6d4c41','rgba(109,76,65,.08)', 'Library Services',    'Access digital journals, e-books, research databases, and citation guides.'],
        ['bi-journal-text',     '#6a1b9a','rgba(106,27,154,.08)','Academic Affairs',    'Transcripts, registration, deferrals, grade appeals, and registry queries.'],
        ['bi-heart-pulse-fill', '#c1121f','rgba(193,18,31,.08)', 'Chaplaincy & Wellness','Spiritual care, mental health resources, and peer counselling support.'],
      ] as $i=>[$ic,$col,$bg,$ti,$tx]): ?>
      <div class="sup-card" data-search="<?= strtolower($ti.' '.$tx) ?>" data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
        <div class="sup-card-ico" style="background:<?=$bg?>;color:<?=$col?>"><i class="bi <?=$ic?>"></i></div>
        <div class="sup-card-title"><?=$ti?></div>
        <p class="sup-card-text"><?=$tx?></p>
        <a href="/anu/contact.php#contact-form" class="sup-card-link">Get Help <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Live helpdesk CTA -->
    <div class="sup-helpdesk-cta" data-aos="fade-up">
      <div>
        <div style="font-family:var(--fh);font-size:1.4rem;color:#fff;margin-bottom:6px">Need Live Help Right Now?</div>
        <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin:0">Our helpdesk team is available Monday – Friday, 8am – 5pm EAT.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="https://enaz.anu.ac.ke/local/helpdesk/" target="_blank" class="split-btn-gold">
          <i class="bi bi-life-preserver me-2"></i>Open Helpdesk
        </a>
        <a href="https://wa.me/<?= preg_replace('/\D/','',$settings['phone']??'254714785249') ?>" target="_blank" class="split-btn-ghost">
          <i class="bi bi-whatsapp me-2"></i>WhatsApp Us
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ══ FULL RESOURCE LIBRARY ══ -->
<section class="sec" id="resources">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4" data-aos="fade-up">
      <div>
        <div class="sec-tag">Resource Library</div>
        <h2 class="sec-title mb-0">Tutorials &amp; <span class="hl">Resources</span></h2>
      </div>
      <p class="sec-sub" style="max-width:380px;margin:0;text-align:right">Step-by-step guides, how-to videos, and downloadable resources.</p>
    </div>

    <!-- Tabs: category + type filters -->
    <div class="sup-lib-toolbar" data-aos="fade-up">
      <div class="sup-cat-tabs" id="resFilterTabs">
        <button class="sup-cat-tab active" onclick="resFilter('all',this)">
          <i class="bi bi-grid me-1"></i>All
        </button>
        <?php foreach($resCats as $rc): ?>
        <button class="sup-cat-tab" onclick="resFilter('<?= e(strtolower($rc)) ?>',this)"><?= e($rc) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="sup-type-btns">
        <button class="sup-type-btn" onclick="resFilterType('video',this)" title="Videos">
          <i class="bi bi-play-circle-fill" style="color:#c1121f"></i> Videos
        </button>
        <button class="sup-type-btn" onclick="resFilterType('pdf',this)" title="PDFs">
          <i class="bi bi-file-pdf-fill" style="color:#1565C0"></i> PDFs
        </button>
        <button class="sup-type-btn" onclick="resFilterType('link',this)" title="Links">
          <i class="bi bi-link-45deg" style="color:#2e7d32"></i> Links
        </button>
      </div>
    </div>

    <?php if(!empty($resources)): ?>
    <div class="sup-res-grid" id="resGrid">
      <?php foreach($resources as $i=>$r):
        $type     = $r['type'] ?? 'video';
        $isVideo  = $type === 'video';
        $isPdf    = $type === 'pdf';
        $thumb    = !empty($r['thumbnail']) ? imgUrl($r['thumbnail']) : ($isVideo ? ytThumb($r['url']) : '');
        $embedUrl = $isVideo ? ytEmbed($r['url']) : '';
        $typeIcons= ['video'=>'bi-play-fill','pdf'=>'bi-file-pdf-fill','link'=>'bi-link-45deg','article'=>'bi-newspaper'];
        $icon     = $typeIcons[$type] ?? 'bi-play-fill';
      ?>
      <div class="sup-res-card"
           data-cat="<?= e(strtolower($r['category']??'')) ?>"
           data-type="<?= e($type) ?>"
           data-search="<?= e(strtolower($r['title'].' '.$r['category'].' '.$r['description'])) ?>"
           data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">

        <div class="sup-res-thumb">
          <?php if($thumb): ?>
          <img src="<?= e($thumb) ?>" alt="<?= e($r['title']) ?>" loading="lazy" onerror="this.style.display='none'">
          <?php else: ?>
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,#1a0000,#3a0000)"></div>
          <?php endif; ?>
          <!-- Play/open overlay -->
          <?php if($isVideo): ?>
          <div class="sup-res-play" onclick="supOpenVideo('<?= e($embedUrl) ?>')" style="cursor:pointer">
            <i class="bi bi-play-circle-fill"></i>
          </div>
          <?php elseif($isPdf): ?>
          <div class="sup-res-play" style="background:rgba(21,101,192,.55)">
            <i class="bi bi-file-pdf-fill" style="font-size:2.4rem;color:#fff"></i>
          </div>
          <?php else: ?>
          <div class="sup-res-play" style="background:rgba(46,125,50,.55)">
            <i class="bi bi-<?= $icon ?>" style="font-size:2.4rem;color:#fff"></i>
          </div>
          <?php endif; ?>
          <span class="sup-res-type-badge sup-res-type-<?= e($type) ?>"><?= ucfirst($type) ?></span>
          <?php if($r['duration']): ?>
          <span class="sup-res-duration"><?= e($r['duration']) ?></span>
          <?php endif; ?>
          <?php if($r['is_featured']): ?>
          <span style="position:absolute;top:10px;right:10px;background:var(--gold);color:var(--dark);font-size:.58rem;font-weight:700;padding:3px 8px;border-radius:50px;letter-spacing:.5px">
            <i class="bi bi-star-fill me-1"></i>Featured
          </span>
          <?php endif; ?>
        </div>

        <div class="sup-res-body">
          <div class="sup-res-cat"><?= e($r['category']??'') ?></div>
          <div class="sup-res-title"><?= e($r['title']) ?></div>
          <?php if($r['description']): ?>
          <p class="sup-res-desc"><?= e(mb_strimwidth($r['description'],0,90,'…')) ?></p>
          <?php endif; ?>
        </div>

        <div class="sup-res-footer">
          <span class="sup-res-audience">
            <i class="bi bi-person-fill"></i>
            <?php $aud=['all'=>'Everyone','students'=>'Students','staff'=>'Staff','new'=>'New Students'];
            echo $aud[$r['audience']??'all']??'Everyone'; ?>
          </span>
          <?php if($isVideo): ?>
          <button onclick="supOpenVideo('<?= e($embedUrl) ?>')" class="sup-res-btn">
            <i class="bi bi-play-fill"></i>Watch
          </button>
          <?php elseif($isPdf): ?>
          <a href="<?= e($r['url']) ?>" target="_blank" class="sup-res-btn">
            <i class="bi bi-download"></i>Download
          </a>
          <?php else: ?>
          <a href="<?= e($r['url']) ?>" target="_blank" class="sup-res-btn">
            <i class="bi bi-arrow-up-right"></i>Open
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div id="resEmpty" style="display:none;text-align:center;padding:60px 0;color:var(--muted)">
      <i class="bi bi-search" style="font-size:2.5rem;display:block;margin-bottom:12px;color:#ddd"></i>
      No resources found. Try a different keyword or category.
    </div>

    <?php else: ?>
    <!-- No resources yet — placeholder cards -->
    <div class="sup-res-grid">
      <?php foreach([
        ['Getting Started','video','How to Log Into eNaz','3:45'],
        ['Getting Started','video','Navigating Your Dashboard','5:12'],
        ['Academic','video','Submitting Assignments','4:30'],
        ['Finance','video','Paying Fees via M-Pesa','3:20'],
        ['Technical','video','Troubleshooting Login','2:55'],
        ['Library','video','Accessing Digital Journals','6:10'],
      ] as $i=>[$cat,$type,$title,$dur]): ?>
      <div class="sup-res-card" data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
        <div class="sup-res-thumb" style="background:linear-gradient(135deg,#0d0000,#3a0000)">
          <div class="sup-res-play"><i class="bi bi-play-circle-fill"></i></div>
          <span class="sup-res-type-badge sup-res-type-video">Video</span>
          <span class="sup-res-duration"><?= $dur ?></span>
        </div>
        <div class="sup-res-body">
          <div class="sup-res-cat"><?= $cat ?></div>
          <div class="sup-res-title"><?= $title ?></div>
        </div>
        <div class="sup-res-footer">
          <span class="sup-res-audience"><i class="bi bi-person-fill"></i>Students</span>
          <span class="sup-res-btn" style="opacity:.5;cursor:default">Coming Soon</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="text-align:center;color:var(--muted);font-size:.8rem;margin-top:16px">
      These are sample resources. Add real ones in <a href="/anu/admin/support_resources" style="color:var(--red);font-weight:700">Admin → Tutorials & Resources</a>.
    </p>
    <?php endif; ?>
  </div>
</section>

<!-- ══ DIRECT CONTACT STRIP ══ -->
<section class="sec" style="background:var(--light)">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Direct Contact</div>
      <h2 class="sec-title">Still Need <span class="hl">Help?</span></h2>
      <p class="sec-sub mx-auto">Our team is ready to assist you through any channel you prefer.</p>
    </div>
    <div class="sup-contact-band">
      <?php foreach([
        ['bi-telephone-fill', 'var(--red)',  'Call Us',      $settings['phone']??'+254 714 785 249',          'Mon–Fri, 8am–5pm EAT',     'tel:'.preg_replace('/\D/','',$settings['phone']??'254714785249')],
        ['bi-whatsapp',       '#25D366',     'WhatsApp',     'Chat With Us',                                   'Quick responses, anytime',  'https://wa.me/'.preg_replace('/\D/','',$settings['phone']??'254714785249')],
        ['bi-envelope-fill',  '#1565C0',     'Email Us',     $settings['email']??'distancelearning@anu.ac.ke','Response within 24 hrs',    'mailto:'.($settings['email']??'distancelearning@anu.ac.ke')],
        ['bi-life-preserver', '#6a1b9a',     'IT Helpdesk',  'eNaz Support Portal',                            'Live chat during office hours','https://enaz.anu.ac.ke/local/helpdesk/'],
      ] as [$ic,$col,$lb,$vl,$sub,$href]): ?>
      <a href="<?= e($href) ?>" <?= str_starts_with($href,'http')?'target="_blank"':'' ?> class="sup-contact-card" data-aos="fade-up">
        <div class="sup-contact-ico" style="background:<?=$col?>18;color:<?=$col?>"><i class="bi <?=$ic?>"></i></div>
        <div class="sup-contact-label"><?= $lb ?></div>
        <div class="sup-contact-val"><?= e($vl) ?></div>
        <div class="sup-contact-sub"><?= $sub ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">We're Here For You</div>
    <h2 class="mt-3" style="color:#fff;font-family:var(--fh);font-size:clamp(2rem,4vw,3rem)">Your Success Is Our Priority</h2>
    <p style="color:rgba(255,255,255,.68);max-width:440px;margin:14px auto 0;line-height:1.75">From your first login to graduation, ANU Online support is with you every step.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="/anu/contact.php" class="btn-cta-w">Contact Us <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/faq.php"     class="btn-cta-o">Browse FAQs</a>
    </div>
  </div>
</section>

<!-- ══ VIDEO MODAL ══ -->
<div id="supModal" onclick="if(event.target===this)supCloseVideo()">
  <div class="modal-inner">
    <button class="modal-close" onclick="supCloseVideo()"><i class="bi bi-x-lg"></i></button>
    <iframe id="supModalFrame" src="" allow="autoplay; fullscreen" allowfullscreen></iframe>
  </div>
</div>

<script>
function supOpenVideo(url) {
  document.getElementById('supModalFrame').src = url;
  document.getElementById('supModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function supCloseVideo() {
  document.getElementById('supModal').classList.remove('open');
  document.getElementById('supModalFrame').src = '';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') supCloseVideo(); });

function resFilter(cat, btn) {
  if(btn) {
    document.querySelectorAll('#resFilterTabs .sup-cat-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
  }
  document.querySelectorAll('.sup-type-btn').forEach(b=>b.classList.remove('active'));
  var cards=document.querySelectorAll('#resGrid .sup-res-card'), shown=0;
  cards.forEach(function(c){
    var match = cat==='all' || c.dataset.cat===cat;
    c.style.display = match ? '' : 'none';
    if(match) shown++;
  });
  var empty = document.getElementById('resEmpty');
  if(empty) empty.style.display = shown===0 ? 'block' : 'none';
}
function resFilterType(type, btn) {
  document.querySelectorAll('#resFilterTabs .sup-cat-tab').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.sup-type-btn').forEach(b=>b.classList.remove('active'));
  if(btn) btn.classList.add('active');
  var cards=document.querySelectorAll('#resGrid .sup-res-card'), shown=0;
  cards.forEach(function(c){
    var match = c.dataset.type===type;
    c.style.display = match ? '' : 'none';
    if(match) shown++;
  });
  var empty = document.getElementById('resEmpty');
  if(empty) empty.style.display = shown===0 ? 'block' : 'none';
}
function supDoSearch(q) {
  q = q.trim().toLowerCase();
  document.querySelectorAll('#supCardsGrid .sup-card').forEach(function(c){
    c.style.display = (!q || c.dataset.search.includes(q)) ? '' : 'none';
  });
  var resCards = document.querySelectorAll('#resGrid .sup-res-card');
  if(resCards.length) {
    var shown=0;
    resCards.forEach(function(c){
      var match = !q || c.dataset.search.includes(q);
      c.style.display = match ? '' : 'none';
      if(match) shown++;
    });
    var empty = document.getElementById('resEmpty');
    if(empty) empty.style.display = shown===0&&q ? 'block' : 'none';
  }
  if(q) setTimeout(function(){ document.getElementById('resources').scrollIntoView({behavior:'smooth',block:'start'}); },200);
}
</script>

<?php renderFooter(); ?>
