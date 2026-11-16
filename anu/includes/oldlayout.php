<?php

function renderHead(string $title='ANU Online', string $extra=''): void {
    $s = getAllSettings();
    $primary = $s['primary_color'] ?? '#ff0000';
    $accent  = $s['accent_color']  ?? '#fac823';
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>{$title} | ANU Online</title>
<meta name="description" content="Quality Christian online education from Africa Nazarene University.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="/anu/assets/css/main.css">
{$extra}
<style>:root{--primary:{$primary};--accent:{$accent};}</style>
</head><body>
HTML;
}

function renderNavbar(string $active=''): void {
    $s      = getAllSettings();
    $portal = $s['portal_url'] ?? 'https://records.anu.ac.ke/';
    $enaz   = $s['enaz_url']    ?? 'https://enaz.anu.ac.ke/';
    $apply  = $s['apply_url']  ?? '/anu/contact.php';
    $phone  = $s['contact_phone'] ?? '+254 700 000 000';
    $email  = 'distancelearning@anu.ac.ke';

    $nav = [
        ['home',        '/anu/index.php',       'Home'],
        ['programs',    '/anu/programs.php',     'Programs'],
        ['chaplaincy',  '/anu/chaplaincy.php', 'Chaplaincy'],
        ['support',  '/anu/support.php',  'Support Center'],
        ['campus-life', '/anu/campus-life.php',  'Campus Life'],
        ['news',        '/anu/news.php',         'News'],
        ['livestream',  '/anu/livestream.php',   'Live Events'],
        ['faq',         '/anu/faq.php',          'FAQs'],
        ['contact',     '/anu/contact.php',      'Contact'],
    ];
    $sub = [
       // ['/anu/chaplaincy.php',  'bi-heart-fill',        'Chaplaincy'],
        ['/anu/clubs.php',       'bi-people-fill',        'Clubs & Societies'],
       // ['/anu/support.php',     'bi-headset',            'Support Center'],
        ['/anu/livestream.php',  'bi-broadcast-pin',      'Live Events'],
    ];

    echo '<div class="topbar">';
    echo '<div class="container-xl topbar-inner">';
    echo '<div class="topbar-left">';
    echo '<a href="tel:'.$phone.'" class="topbar-link"><i class="bi bi-telephone-fill"></i>'.$phone.'</a>';
    echo '<a href="mailto:'.$email.'" class="topbar-link d-none d-md-inline-flex"><i class="bi bi-envelope-fill"></i>'.$email.'</a>';
    echo '</div><div class="topbar-right">';
    echo '<a href="'.$portal.'" class="topbar-link" target="_blank"><i class="bi bi-person-circle"></i>Student Portal</a>';
    echo '<a href="'.$apply.'" class="topbar-cta">Apply Now</a>';
    echo '</div></div></div>';

    echo '<nav class="site-nav" id="siteNav">';
    echo '<div class="container-xl nav-inner">';
    // Logo — real ANU logo image only, no fallback text that competes
    echo '<a class="nav-brand" href="/anu/index.php">';
    echo '<img src="/anu/assets/images/anu-logo-white.png" alt="Africa Nazarene University Online" height="52">';
    echo '</a>';
    echo '<button class="nav-toggle" id="navToggle"><i class="bi bi-list"></i></button>';
    echo '<ul class="nav-menu" id="navMenu">';
    foreach ($nav as [$key, $href, $label]) {
        $cls = $active===$key ? 'nav-link active' : 'nav-link';
        if ($key==='campus-life') {
            echo '<li class="nav-item has-drop">';
            echo "<a class='{$cls}' href='{$href}'>{$label} <i class='bi bi-chevron-down nav-caret'></i></a>";
            echo '<ul class="nav-drop">';
            foreach ($sub as [$dh,$ic,$dl])
                echo "<li><a href='{$dh}'><i class='bi {$ic}'></i>{$dl}</a></li>";
            echo '</ul></li>';
        } else {
            echo "<li class='nav-item'><a class='{$cls}' href='{$href}'>{$label}</a></li>";
        }
    }
    echo '<li class="nav-item nav-portal-item"><a href="'.$enaz.'" class="nav-portal-btn" target="_blank"><i class="bi bi-person-fill"></i> eNaz</a></li>';
    echo '</ul></div></nav>';
}

function renderHero(string $slug): void {
    $slides = getSlides($slug);
    if (empty($slides)) {
        $h = getHero($slug);
        if (!empty($h)) {
            $slides = [[
                'title'=>$h['title'],'subtitle'=>$h['subtitle'],
                'cta_text'=>$h['cta_text'],'cta_link'=>$h['cta_link'],
                'cta2_text'=>$h['cta2_text']??'','cta2_link'=>$h['cta2_link']??'',
                'bg_image'=>$h['bg_image']??'','overlay_color'=>$h['overlay_color']??'rgba(10,20,60,0.72)',
                'badge_text'=>'Accredited · Flexible · Faith-Centered',
            ]];
        }
    }
    if (empty($slides)) {
        $slides = [[
            'title'=>'Transforming Lives Through Online Learning',
            'subtitle'=>'Quality Christian education from Africa Nazarene University — study anywhere, anytime.',
            'cta_text'=>'Explore Programs','cta_link'=>'/anu/programs.php',
            'cta2_text'=>'Apply Now','cta2_link'=>'/anu/contact.php',
            'bg_image'=>'','overlay_color'=>'rgba(10,20,60,0.72)',
            'badge_text'=>'Accredited · Flexible · Faith-Centered',
        ]];
    }

    $single   = count($slides)===1;
    $overlays = ['rgba(10,20,60,0.72)','rgba(120,0,0,0.68)','rgba(0,50,30,0.70)'];
    $cid      = 'heroCarousel';

    if (!$single) {
        echo '<div id="'.$cid.'" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">';
    }
    echo '<div class="'.($single ? 'hero-single' : 'carousel-inner').'">';

    foreach ($slides as $i => $sl) {
        $bg  = !empty($sl['bg_image']) ? "style=\"background-image:url('".imgUrl($sl['bg_image'])."')\"" : '';
        $ov  = !empty($sl['overlay_color']) ? $sl['overlay_color'] : $overlays[$i%3];
        $act = $i===0 ? 'active' : '';

        if (!$single) echo "<div class='carousel-item {$act}'>";
        echo "<div class='hero-slide' {$bg}>";
        echo "<div class='hero-overlay' style='background:{$ov}'></div>";
        echo "<div class='container hero-body'><div class='hero-content'>";
        if (!empty($sl['badge_text']))
            echo "<div class='hero-pill'>".e($sl['badge_text'])."</div>";
        echo "<h1 class='hero-title'>".e($sl['title'])."</h1>";
        if (!empty($sl['subtitle']))
            echo "<p class='hero-sub'>".e($sl['subtitle'])."</p>";
        echo "<div class='hero-btns'>";
        if (!empty($sl['cta_text']))
            echo "<a href='".e($sl['cta_link'])."' class='btn-hero-solid'>".e($sl['cta_text'])." <i class='bi bi-arrow-right'></i></a>";
        if (!empty($sl['cta2_text']))
            echo "<a href='".e($sl['cta2_link'])."' class='btn-hero-outline'>".e($sl['cta2_text'])."</a>";
        echo "</div></div></div></div>";
        if (!$single) echo '</div>';
    }
    echo '</div>';

    if (!$single) {
        echo '<div class="hero-dots">';
        foreach ($slides as $i => $sl)
            echo "<button type='button' data-bs-target='#{$cid}' data-bs-slide-to='{$i}' class='".($i===0?'active':'')."'></button>";
        echo '</div>';
        echo "<button class='hero-ctrl prev' type='button' data-bs-target='#{$cid}' data-bs-slide='prev'><i class='bi bi-chevron-left'></i></button>";
        echo "<button class='hero-ctrl next' type='button' data-bs-target='#{$cid}' data-bs-slide='next'><i class='bi bi-chevron-right'></i></button>";
        echo '</div>';
    }
}

function renderFooter(): void {
    $s    = getAllSettings();
    $addr = $s['contact_address'] ?? 'Ongata Rongai, Kajiado North, Kenya';
    $tel  = $s['contact_phone']   ?? '+254 700 000 000';
    $mail = 'distancelearning@anu.ac.ke';
    $fb   = $s['facebook_url']    ?? '#';
    $tw   = $s['twitter_url']     ?? '#';
    $li   = $s['linkedin_url']    ?? '#';
    $ig   = $s['instagram_url']   ?? '#';
    $yr   = date('Y');
    echo <<<HTML
<footer class="site-footer">
  <div class="footer-body">
    <div class="container">
      <div class="row gy-5">
        <div class="col-lg-4">
          <a href="/anu/index.php" class="footer-logo-link">
            <img src="/anu/assets/images/anu-logo-white.png" alt="ANU Online" height="64" class="footer-logo">
          </a>
          <p class="footer-about mt-3">Transforming lives through quality, faith-centered online education accessible to all.</p>
          <div class="footer-socials">
            <a href="{$fb}" target="_blank" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="{$tw}" target="_blank" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="{$li}" target="_blank" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="{$ig}" target="_blank" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="footer-head">Explore</h6>
          <ul class="footer-list">
            <li><a href="/anu/index.php">Home</a></li>
            <li><a href="/anu/programs.php">Programs</a></li>
            <li><a href="/anu/campus-life.php">Campus Life</a></li>
            <li><a href="/anu/news.php">News</a></li>
            <li><a href="/anu/faq.php">FAQs</a></li>
            <li><a href="/anu/contact.php">Contact</a></li>
          </ul>
        </div>
        <div class="col-6 col-lg-3">
          <h6 class="footer-head">Programs</h6>
          <ul class="footer-list">
            <li><a href="/anu/programs.php?level=Masters">Masters Programs</a></li>
            <li><a href="/anu/programs.php?level=Undergraduate">Undergraduate</a></li>
            <li><a href="/anu/programs.php?school=Business">Business School</a></li>
            <li><a href="/anu/programs.php?school=Religion">School of Theology</a></li>
            <li><a href="/anu/programs.php?school=Science">School of Science</a></li>
          </ul>
        </div>
        <div class="col-lg-3">
          <h6 class="footer-head">Contact Us</h6>
          <ul class="footer-contact">
            <li><i class="bi bi-geo-alt-fill"></i><span>{$addr}</span></li>
            <li><i class="bi bi-telephone-fill"></i><a href="tel:{$tel}">{$tel}</a></li>
            <li><i class="bi bi-envelope-fill"></i><a href="mailto:{$mail}">{$mail}</a></li>
          </ul>
          <div class="footer-nl-label">Stay Updated</div>
          <form class="footer-nl" onsubmit="handleNewsletter(event)">
            <input type="email" placeholder="Your email address" required>
            <button type="submit"><i class="bi bi-send-fill"></i></button>
          </form>
          <div id="newsletter-msg"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <p>&copy; {$yr} Africa Nazarene University Online. All rights reserved.</p>
      <div class="footer-links">
        <a href="#">Privacy</a><a href="#">Terms</a><a href="/anu/support.php">Support</a>
        <a href="/anu/admin/login.php">Admin</a>
      </div>
    </div>
  </div>
</footer>
<button id="toTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="/anu/assets/js/main.js"></script>
<script>
AOS.init({once:true,duration:700,offset:60});
window.addEventListener('scroll',function(){
  document.getElementById('siteNav').classList.toggle('scrolled',window.scrollY>40);
  document.getElementById('toTop').classList.toggle('show',window.scrollY>500);
});
document.getElementById('toTop').addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
document.getElementById('navToggle').addEventListener('click',function(){
  var m=document.getElementById('navMenu');
  m.classList.toggle('open');
  this.querySelector('i').className=m.classList.contains('open')?'bi bi-x':'bi bi-list';
});
document.querySelectorAll('.has-drop>.nav-link').forEach(function(el){
  el.addEventListener('click',function(e){
    if(window.innerWidth<992){e.preventDefault();this.closest('.has-drop').classList.toggle('open');}
  });
});
</script>
HTML;
}
?>
