<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db      = getDB();
$s        = getAllSettings();
$applyUrl = $s['apply_url'] ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
try {
$events  = $db->query("SELECT * FROM events WHERE is_active=1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 6")->fetchAll();
$gallery = $db->query("SELECT * FROM gallery WHERE page='campus-life' AND is_active=1 ORDER BY sort_order LIMIT 8")->fetchAll();
$clubs   = $db->query("SELECT * FROM clubs WHERE is_active=1 ORDER BY sort_order LIMIT 6")->fetchAll();
} catch(Exception $e) {}

$heroSlides = getSlides('campus-life');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('campus-life');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

trackPage('campus-life', 'Student Life');
renderHead('Student Life'); renderNavbar('student-life');
?>
<!-- ══ HERO ══ -->
<div class="cl-page-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}')\"" : '' ?>>
  <div class="cl-page-hero-overlay"></div>
  <div class="container cl-page-hero-inner">
    <div data-aos="fade-up">
      <div class="sec-tag" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.8)">ANU Online</div>
      <h1 style="font-family:var(--fh);font-size:clamp(2.6rem,5vw,4rem);color:#fff;line-height:1.1;margin:14px 0 16px">
        Student Life at <span style="color:var(--gold)">ANU Online</span>
      </h1>
      <p style="color:rgba(255,255,255,.7);font-size:1rem;line-height:1.75;max-width:480px">
        Community, faith, and excellence — wherever you are in the world.
      </p>
      <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:28px">
        <a href="#highlights" class="btn-hero-solid">Explore <i class="bi bi-arrow-down"></i></a>
        <a href="<?=e($applyUrl)?>" target="_blank" rel="noopener" class="btn-hero-outline">Apply Now</a>
      </div>
    </div>
  </div>
  <div class="cl-hero-quicklinks">
    <div class="container">
      <div class="cl-hql-row">
        <a href="/anu/chaplaincy.php" class="cl-hql-item"><i class="bi bi-heart-fill"></i><span>Chaplaincy</span></a>
        <a href="/anu/clubs.php"      class="cl-hql-item"><i class="bi bi-people-fill"></i><span>Clubs</span></a>
        <a href="#events-sec"         class="cl-hql-item"><i class="bi bi-calendar-fill"></i><span>Events</span></a>
        <a href="#gallery"            class="cl-hql-item"><i class="bi bi-images"></i><span>Gallery</span></a>
        <a href="/anu/support.php"    class="cl-hql-item"><i class="bi bi-headset"></i><span>Support</span></a>
      </div>
    </div>
  </div>
</div>

<!-- ══ HIGHLIGHTS ══ -->
<section class="sec" id="highlights">
  <div class="container">
    <div class="cl-intro-grid">
      <div data-aos="fade-right">
        <div class="sec-tag">Virtual Campus</div>
        <h2 class="sec-title">More Than a <span class="hl">Degree</span></h2>
        <div class="rule"></div>
        <p class="body-lead">At ANU Online, student life travels with you. Our virtual community is built to replicate the energy, relationships, and growth of a physical campus — accessible from 15+ countries worldwide.</p>
        <div class="cl-intro-links">
          <a href="/anu/chaplaincy.php" class="cl-ilink"><i class="bi bi-heart-fill"></i> Chaplaincy</a>
          <a href="/anu/clubs.php"      class="cl-ilink"><i class="bi bi-people-fill"></i> Clubs</a>
          <a href="/anu/support.php"    class="cl-ilink"><i class="bi bi-headset"></i> Support</a>
          <a href="/anu/contact.php"    class="cl-ilink"><i class="bi bi-envelope-fill"></i> Contact</a>
        </div>
      </div>
      <div class="cl-feature-grid" data-aos="fade-left">
        <?php foreach([
          ['bi-camera-video-fill','#c1121f','Virtual Events',   'Live webinars, forums, and guest lectures open to every student.'],
          ['bi-book-fill',        '#9b2226','Digital Library',  '24/7 access to journals, e-books, and global research databases.'],
          ['bi-headset',          '#ae2012','Academic Support', 'One-on-one counselling, mentorship, and career guidance.'],
          ['bi-heart-pulse-fill', '#bb3e03','Wellness',         'Spiritual care and mental health resources for your whole self.'],
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
</section>

<!-- ══ STATS BAND ══ -->
<div class="cl-stats-band">
  <div class="container">
    <div class="cl-stats-row">
      <?php foreach([['5,000+','Students','bi-mortarboard-fill'],['15+','Countries','bi-globe2'],['50+','Programs','bi-book-fill'],['25+','Years','bi-award-fill']] as [$v,$l,$ic]): ?>
      <div class="cl-stat"><i class="bi <?=$ic?>"></i><strong><?=$v?></strong><span><?=$l?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ SPIRITUAL LIFE SPLIT ══ -->
<section class="cl-spirit-split">
  <div class="cl-spirit-img">
    <div class="cl-spirit-img-inner"><i class="bi bi-heart-fill"></i></div>
    <div class="cl-spirit-overlay-text">Faith &amp; Community</div>
  </div>
  <div class="cl-spirit-panel">
    <div class="cl-spirit-inner" data-aos="fade-left">
      <div class="sec-tag" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7)">Spiritual Life</div>
      <h2 class="cl-spirit-heading">More Than <span style="color:var(--gold)">Knowledge</span></h2>
      <div class="pp-dir-rule"></div>
      <p style="color:rgba(255,255,255,.72);line-height:1.85;font-size:.95rem;margin-bottom:28px">At ANU, learning and growing go hand in hand. You'll gain wisdom as you are stretched academically and spiritually. Our chaplaincy, devotions, and prayer ministry welcome every student, regardless of background.</p>
      <div class="cl-spirit-pillars">
        <?php foreach([
          ['bi-book-fill',       'Scripture & Devotion', 'Daily devotionals and weekly Scripture reflections.'],
          ['bi-chat-heart-fill', 'Prayer Ministry',      'One-on-one prayer sessions with our University Chaplain.'],
          ['bi-people-fill',     'Fellowship Groups',    'Virtual community meetings connecting students weekly.'],
        ] as [$ic,$ti,$tx]): ?>
        <div class="cl-pillar">
          <i class="bi <?=$ic?>"></i>
          <div><strong><?=$ti?></strong><p><?=$tx?></p></div>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="/anu/chaplaincy.php" class="split-btn-gold" style="margin-top:32px">Visit Chaplaincy <i class="bi bi-chevron-right"></i></a>
    </div>
  </div>
</section>

<!-- ══ CLUBS TEASER ══ -->
<section class="sec bg-light" id="clubs-sec">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-5">
      <div data-aos="fade-right">
        <div class="sec-tag">Student Organisations</div>
        <h2 class="sec-title mb-0">Clubs &amp; <span class="hl">Societies</span></h2>
      </div>
      <div data-aos="fade-left">
        <p class="sec-sub mb-2">There is a community for every passion and calling.</p>
        <a href="/anu/clubs.php" class="btn-ghost-red">See All Clubs <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
    <div class="cl-clubs-preview">
      <?php
      $fb=[['bi-people-fill','Christian Union','Faith community and fellowship for all ANU Online students.'],['bi-laptop-fill','Tech Society','Innovation, coding competitions, and hackathons.'],['bi-briefcase-fill','Business Club','Entrepreneurship, networking, and mentorship.'],['bi-mic-fill','Debating Society','Sharpening critical thinking and public speaking.'],['bi-globe2','Eco Club','Sustainability and environmental advocacy.'],['bi-heart-fill','Welfare Club','Peer support and student wellbeing.']];
      $list = !empty($clubs) ? $clubs : null;
      if($list){ foreach($list as $i=>$c): ?>
      <a href="/anu/clubs.php" class="cl-club-tile" data-aos="fade-up" data-aos-delay="<?=($i%3)*70?>">
        <div class="cl-club-ico"><i class="bi <?=e($c['icon'])?>"></i></div>
        <div class="cl-club-name"><?=e($c['name'])?></div>
        <div class="cl-club-desc"><?=e(mb_strimwidth($c['description']??'',0,70,'…'))?></div>
        <span class="cl-club-join">Join <i class="bi bi-arrow-right"></i></span>
      </a>
      <?php endforeach; }else{ foreach($fb as $i=>[$ic,$nm,$dc]): ?>
      <a href="/anu/clubs.php" class="cl-club-tile" data-aos="fade-up" data-aos-delay="<?=($i%3)*70?>">
        <div class="cl-club-ico"><i class="bi <?=$ic?>"></i></div>
        <div class="cl-club-name"><?=$nm?></div>
        <div class="cl-club-desc"><?=$dc?></div>
        <span class="cl-club-join">Join <i class="bi bi-arrow-right"></i></span>
      </a>
      <?php endforeach; } ?>
    </div>
  </div>
</section>

<!-- ══ DIRECTOR / LEADERSHIP MESSAGE — pulled from Admin → Team ══ -->
<?php renderDirectorMessage(); ?>

<!-- ══ EVENTS ══ -->
<?php if(!empty($events)): ?>
<section class="sec bg-light" id="events-sec">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-5">
      <div><div class="sec-tag">Calendar</div><h2 class="sec-title mb-0">Upcoming <span class="hl">Events</span></h2></div>
    </div>
    <div class="cl-events-grid">
      <?php foreach($events as $i=>$ev):
        $ts=$ev['event_date']?strtotime($ev['event_date']):time();
        $type=$ev['type']??'online';
        $cols=['online'=>'#2d6a4f','physical'=>'#c1121f','hybrid'=>'#bb3e03'];
        $col=$cols[$type]??'#555'; ?>
      <div class="cl-event-card" data-aos="fade-up" data-aos-delay="<?=($i%3)*60?>">
        <div class="cl-ev-date">
          <span class="cl-ev-day"><?=date('d',$ts)?></span>
          <span class="cl-ev-mon"><?=date('M',$ts)?></span>
          <span class="cl-ev-yr"><?=date('Y',$ts)?></span>
        </div>
        <div class="cl-ev-body">
          <div class="cl-ev-type" style="color:<?=$col?>;border-color:<?=$col?>28;background:<?=$col?>14"><?=ucfirst(e($type))?></div>
          <h5 class="cl-ev-title"><?=e($ev['title'])?></h5>
          <p class="cl-ev-desc"><?=e(mb_strimwidth($ev['description']??'',0,90,'…'))?></p>
          <div class="cl-ev-meta">
            <?php if($ev['location']): ?><span><i class="bi bi-geo-alt"></i><?=e($ev['location'])?></span><?php endif; ?>
            <?php if($ev['event_time']): ?><span><i class="bi bi-clock"></i><?=date('g:i A',strtotime($ev['event_time']))?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══ GALLERY ══ -->
<?php if(!empty($gallery)): ?>
<section class="sec" id="gallery">
  <div class="container">
    <div class="text-center mb-5"><div class="sec-tag">Photo Gallery</div><h2 class="sec-title">Student <span class="hl">Moments</span></h2></div>
    <div class="cl-gallery-mosaic">
      <?php foreach($gallery as $i=>$img): ?>
      <div class="cl-gallery-item <?=$i===0?'cl-gallery-featured':''?>" onclick="clGallery('<?=imgUrl($img['image'])?>')" data-aos="zoom-in">
        <img src="<?=imgUrl($img['image'])?>" alt="<?=e($img['title']??'')?>" loading="lazy">
        <?php if(!empty($img['title'])): ?><div class="cl-gallery-cap"><?=e($img['title'])?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<div id="clModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;cursor:pointer;align-items:center;justify-content:center" onclick="this.style.display='none'">
  <img id="clModalImg" alt="Enlarged campus photo" style="max-width:92vw;max-height:90vh;border-radius:8px;object-fit:contain">
</div>
<script>
function clGallery(s){document.getElementById('clModalImg').src=s;document.getElementById('clModal').style.display='flex';}
document.addEventListener('keydown',function(e){if(e.key==='Escape')document.getElementById('clModal').style.display='none';});
</script>
<?php endif; ?>

<!-- ══ CTA ══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <div class="sec-tag" style="background:rgba(250,200,35,.18);color:#fac823">Join Our Community</div>
    <h2 class="mt-3" style="color:#fff;font-family:var(--fh);font-size:clamp(2rem,4vw,3rem)">Experience the ANU Online Difference</h2>
    <p style="color:rgba(255,255,255,.68);max-width:460px;margin:14px auto 0;line-height:1.75">Community, faith, and academic excellence — all accessible wherever you are.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="<?=e($applyUrl)?>" target="_blank" rel="noopener" class="btn-cta-w">Apply Now <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/programs.php" class="btn-cta-o">Browse Programs</a>
    </div>
  </div>
</section>
<?php renderFooter(); ?>
