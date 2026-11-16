<?php

function renderHead(string $title='ANU Online', string $extra=''): void {
    require_once __DIR__ . '/clean_urls.php';
    anu_clean_urls_boot(dirname(__DIR__));   // addresses without .php once enabled (see anu-clean-urls)
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
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="/anu/assets/css/main.css">
<link rel="stylesheet" href="/anu/assets/css/anu-v2.css?v=6">
<link rel="icon" type="image/png" sizes="32x32" href="/anu/assets/images/favicon-32.png?v=2">
<link rel="icon" type="image/png" sizes="48x48" href="/anu/assets/images/favicon-48.png?v=2">
<link rel="shortcut icon" href="/anu/assets/images/favicon.ico?v=2">
<link rel="apple-touch-icon" href="/anu/assets/images/favicon-180.png?v=2">
{$extra}
<style>:root{--primary:{$primary};--accent:{$accent};}</style>
</head><body>
HTML;
}

function renderNavbar(string $active=''): void {
    $s      = getAllSettings();
    $portal = $s['portal_url'] ?? 'https://records.anu.ac.ke/';
    $enaz   = $s['enaz_url']    ?? 'https://enaz.anu.ac.ke/';
    $apply  = $s['apply_url']  ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
    $phone  = $s['contact_phone'] ?? '+254 700 000 000';
    $email  = 'distancelearning@anu.ac.ke';
    // Item 4/5: utility bar links, admin-editable via Settings -> new
    // "Utility Bar & Orientation Pack" fields. Falls back to a clearly
    // marked placeholder (#) + TODO when no real URL has been set yet --
    // never a guessed/invented link.
    $myloft       = $s['myloft_url']       ?? '';   // TODO: confirm real MyLoft URL with ANU IT if not set in Settings
    $libraryUrl   = $s['library_url']      ?? '';   // TODO: confirm real digital library URL if not set in Settings
    $repository   = $s['repository_url']   ?? '';   // TODO: confirm real institutional repository URL if not set in Settings
    $orientation  = $s['orientation_pack_url'] ?? ''; // TODO: upload orientation-pack.pdf and set its URL in Settings

    $nav = [
        ['home',        '/anu/index.php',       'Home'],
        ['about',       '/anu/about.php',        'About'],
        ['programs',    '/anu/programs.php',     'Programs'],
        ['student-life','/anu/student-life.php', 'Student Life'],
        ['news',        '/anu/news.php',         'News'],
        ['faq',         '/anu/faq.php',          'FAQs'],
        ['contact',     '/anu/contact.php',      'Contact'],
    ];
    // Dropdown shown under "Student Life" — keeps Chaplaincy easy to find
    // without giving it its own top-level slot, per request.
    $sub = [
        ['/anu/chaplaincy.php',  'bi-heart-fill',        'Chaplaincy'],
        ['/anu/clubs.php',       'bi-people-fill',        'Clubs & Societies'],
        ['/anu/support.php',     'bi-headset',            'Support Center'],
        ['/anu/livestream.php',  'bi-broadcast-pin',      'Live Events'],
    ];
    // Dropdown shown under "About"
    $aboutSub = [
        ['/anu/about.php', 'bi-info-circle-fill', 'About ANU Online'],
        ['/anu/team.php',  'bi-people-fill',       'Meet Our Team'],
    ];

    echo '<div class="topbar">';
    echo '<div class="container-xl topbar-inner">';
    echo '<div class="topbar-left">';
    echo '<a href="tel:'.preg_replace('/[^0-9+]/','',$phone).'" class="topbar-link"><i class="bi bi-telephone-fill"></i>'.$phone.'</a>';
    if (!empty($s['contact_phone_2'])) echo '<a href="tel:'.preg_replace('/[^0-9+]/','',$s['contact_phone_2']).'" class="topbar-link d-none d-md-inline-flex">'.e($s['contact_phone_2']).'</a>';
    echo '<a href="mailto:'.$email.'" class="topbar-link d-none d-md-inline-flex"><i class="bi bi-envelope-fill"></i>'.$email.'</a>';
    echo '</div><div class="topbar-right">';
    echo '<a href="'.e($portal).'" class="topbar-link" target="_blank" rel="noopener"><i class="bi bi-person-circle"></i>Student Portal</a>';
    echo '<a href="'.e($myloft ?: '#').'" class="topbar-link" target="_blank" rel="noopener">'.($myloft ? '' : '<!-- TODO: real MyLoft URL -->').'<i class="bi bi-mortarboard"></i>MyLoft</a>';
    echo '<a href="'.e($libraryUrl ?: '#').'" class="topbar-link" target="_blank" rel="noopener">'.($libraryUrl ? '' : '<!-- TODO: real Library URL -->').'<i class="bi bi-book"></i>Library</a>';
    echo '<a href="'.e($repository ?: '#').'" class="topbar-link" target="_blank" rel="noopener">'.($repository ? '' : '<!-- TODO: real Repository URL -->').'<i class="bi bi-archive"></i>Repository</a>';
    echo '<a href="/anu/orientation-pack.php" class="topbar-link op-link" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i>Orientation</a>';
    echo '<a href="'.e($apply).'" class="topbar-cta" target="_blank" rel="noopener">Apply Now</a>';
    echo '</div></div></div>';

    echo '<nav class="site-nav" id="siteNav">';
    echo '<div class="container-xl nav-inner">';
    // Logo — real ANU logo image only, no fallback text that competes
    echo '<a class="nav-brand" href="/anu/index.php">';
    echo '<img src="/anu/assets/images/anu-logo-white.png" alt="Africa Nazarene University Online" height="68">';
    echo '</a>';
    echo '<button class="nav-toggle" id="navToggle" aria-expanded="false" aria-label="Open menu" aria-controls="navMenu"><i class="bi bi-list"></i></button>';
    echo '<div class="nav-overlay" id="navOverlay"></div>';
    echo '<ul class="nav-menu" id="navMenu">';
    // Daystar-style off-canvas panel head (logo + close button) -- a real
    // <li> so it travels with the same sliding #navMenu element; hidden on
    // desktop via CSS (.nav-panel-head{display:none}) and shown only
    // inside the mobile slide-in panel.
    echo '<li class="nav-panel-head">';
    echo '<img src="/anu/assets/images/anu-logo-white.png" alt="ANU Online" height="36">';
    echo '<button class="nav-panel-close" id="navClose" aria-label="Close menu" type="button"><i class="bi bi-x-lg"></i></button>';
    echo '</li>';
    foreach ($nav as [$key, $href, $label]) {
        $cls = $active===$key ? 'nav-link active' : 'nav-link';
        if ($key==='student-life') {
            echo '<li class="nav-item has-drop">';
            echo "<a class='{$cls}' href='{$href}'>{$label} <i class='bi bi-chevron-down nav-caret'></i></a>";
            echo '<ul class="nav-drop">';
            foreach ($sub as [$dh,$ic,$dl])
                echo "<li><a href='{$dh}'><i class='bi {$ic}'></i>{$dl}</a></li>";
            echo '</ul></li>';
        } elseif ($key==='about') {
            echo '<li class="nav-item has-drop">';
            echo "<a class='{$cls}' href='{$href}'>{$label} <i class='bi bi-chevron-down nav-caret'></i></a>";
            echo '<ul class="nav-drop">';
            foreach ($aboutSub as [$dh,$ic,$dl])
                echo "<li><a href='{$dh}'><i class='bi {$ic}'></i>{$dl}</a></li>";
            echo '</ul></li>';
        } else {
            echo "<li class='nav-item'><a class='{$cls}' href='{$href}'>{$label}</a></li>";
        }
    }
    echo '<li class="nav-item nav-portal-item"><a href="'.$enaz.'" class="nav-portal-btn" target="_blank"><i class="bi bi-person-fill"></i> eNaz</a></li>';
    // Daystar-style off-canvas panel foot: utility links + contact/socials
    // + Apply Now, all hidden on desktop (that content already lives in
    // the topbar/footer there) and shown only inside the mobile panel,
    // per item F of the follow-up spec.
    $fb = $s['facebook_url'] ?? '#'; $tw = $s['twitter_url'] ?? '#';
    $li = $s['linkedin_url'] ?? '#'; $ig = $s['instagram_url'] ?? '#';
    echo '<li class="nav-panel-foot">';
    echo '<div class="nav-panel-utility">';
    echo '<a href="'.e($portal).'" target="_blank" rel="noopener"><i class="bi bi-person-circle"></i>Student Portal</a>';
    echo '<a href="'.e($myloft ?: '#').'" target="_blank" rel="noopener"><i class="bi bi-mortarboard"></i>MyLoft</a>';
    echo '<a href="'.e($libraryUrl ?: '#').'" target="_blank" rel="noopener"><i class="bi bi-book"></i>Library</a>';
    echo '<a href="'.e($repository ?: '#').'" target="_blank" rel="noopener"><i class="bi bi-archive"></i>Repository</a>';
    echo '<a href="/anu/orientation-pack.php" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text"></i>Orientation</a>';
    echo '</div>';
    echo '<a href="'.e($apply).'" target="_blank" rel="noopener" class="nav-panel-apply">Apply Now <i class="bi bi-arrow-right"></i></a>';
    echo '<div class="nav-panel-contact">';
    echo '<a href="tel:'.e($phone).'"><i class="bi bi-telephone-fill"></i>'.e($phone).'</a>';
    echo '<a href="mailto:'.e($email).'"><i class="bi bi-envelope-fill"></i>'.e($email).'</a>';
    echo '</div>';
    echo '<div class="nav-panel-socials">';
    echo '<a href="'.e($fb).'" target="_blank" aria-label="Facebook"><i class="bi bi-facebook"></i></a>';
    echo '<a href="'.e($tw).'" target="_blank" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>';
    echo '<a href="'.e($li).'" target="_blank" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>';
    echo '<a href="'.e($ig).'" target="_blank" aria-label="Instagram"><i class="bi bi-instagram"></i></a>';
    echo '</div>';
    echo '</li>';
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
                // Item 8: optional background video. bg_video/poster_image
                // are new columns (added by this phase's migration) --
                // both simply empty until an admin uploads one via
                // Admin -> Heroes, so every existing hero keeps using its
                // bg_image exactly as before.
                'bg_video'=>$h['bg_video']??'','poster_image'=>$h['poster_image']??'',
                'badge_text'=>'Accredited · Flexible · Faith-Centered',
            ]];
        }
    }
    if (empty($slides)) {
        $slides = [[
            'title'=>'Transforming Lives Through Online Learning',
            'subtitle'=>'Quality Christian education from Africa Nazarene University — study anywhere, anytime.',
            'cta_text'=>'Explore Programs','cta_link'=>'/anu/programs.php',
            'cta2_text'=>'Apply Now','cta2_link'=>'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp',
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
        // Item 8: background video (single src, easy to change via the
        // hero's own bg_video field in Admin -> Heroes) with poster-image
        // fallback and dark overlay. Hidden entirely on small screens and
        // when the visitor has requested reduced motion (CSS handles
        // both), where the poster/bg-image shows instead.
        if (!empty($sl['bg_video'])) {
            $poster = !empty($sl['poster_image']) ? " poster=\"".e(imgUrl($sl['poster_image']))."\"" : '';
            echo "<video class='hero-video' autoplay muted loop playsinline preload='metadata'{$poster}>";
            echo "<source src=\"".e(imgUrl($sl['bg_video']))."\" type='video/mp4'>";
            echo "</video>";
        }
        echo "<div class='hero-overlay' style='background:{$ov}'></div>";
        echo "<div class='container hero-body'><div class='hero-content'>";
        if (!empty($sl['badge_text']))
            echo "<div class='hero-pill'>".e($sl['badge_text'])."</div>";
        echo "<h1 class='hero-title'>".e($sl['title'])."</h1>";
        if (!empty($sl['subtitle']))
            echo "<p class='hero-sub'>".e($sl['subtitle'])."</p>";
        echo "<div class='hero-btns'>";
        if (!empty($sl['cta_text'])) {
            $ext1 = str_starts_with($sl['cta_link'], 'http') ? " target='_blank' rel='noopener'" : '';
            echo "<a href='".e($sl['cta_link'])."'{$ext1} class='btn-hero-solid'>".e($sl['cta_text'])." <i class='bi bi-arrow-right'></i></a>";
        }
        if (!empty($sl['cta2_text'])) {
            $ext2 = str_starts_with($sl['cta2_link'], 'http') ? " target='_blank' rel='noopener'" : '';
            echo "<a href='".e($sl['cta2_link'])."'{$ext2} class='btn-hero-outline'>".e($sl['cta2_text'])."</a>";
        }
        $heroPortal = getAllSettings()['portal_url'] ?? 'https://records.anu.ac.ke/';
        echo "<a href='".e($heroPortal)."' target='_blank' rel='noopener' class='btn-hero-outline'><i class='bi bi-person-circle me-1'></i>Student Portal Login</a>";
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
    $portal = e($s['portal_url'] ?? 'https://records.anu.ac.ke/');
    $enaz   = e($s['enaz_url']   ?? 'https://enaz.anu.ac.ke/');
    $apply  = e($s['apply_url']  ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp');
    require_once __DIR__ . '/hubspot_consent.php';
    $hsId = anu_hubspot_id($s['hubspot_portal_id'] ?? '');
    if (!array_key_exists('hubspot_portal_id', $s)) {   // create the empty setting once, so it shows in Admin > Site Settings
        try { getDB()->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, label, page) VALUES ('hubspot_portal_id', '', 'text', 'HubSpot Hub ID (chat, numbers only)', 'global')")->execute(); } catch (Throwable $e) {}
    }
    echo <<<HTML
<footer class="site-footer">
  <div class="footer-body">
    <div class="container">
      <div class="row gy-5 gx-lg-5">
        <div class="col-lg-3 col-md-12">
          <a href="/anu/index.php" class="footer-logo-link">
            <img src="/anu/assets/images/anu-logo-white.png" alt="ANU Online" height="80" class="footer-logo">
          </a>
          <p class="footer-about mt-3">Transforming lives through quality, faith-centered online education accessible to all.</p>
          <a href="{$apply}" target="_blank" rel="noopener" class="footer-apply">Apply now <i class="bi bi-arrow-right"></i></a>
          <div class="footer-socials mt-3">
            <a href="{$fb}" target="_blank" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="{$tw}" target="_blank" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="{$li}" target="_blank" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="{$ig}" target="_blank" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="footer-head">About</h6>
          <ul class="footer-list">
            <li><a href="/anu/about.php">About ANU Online</a></li>
            <li><a href="/anu/team.php">Meet Our Team</a></li>
            <li><a href="/anu/news.php">News</a></li>
            <li><a href="/anu/faq.php">FAQs</a></li>
            <li><a href="/anu/contact.php">Contact</a></li>
          </ul>
        </div>
        <div class="col-6 col-md-3 col-lg-3">
          <h6 class="footer-head">Our Schools</h6>
          <ul class="footer-list">
            <li><a href="/anu/programs.php">All Programs</a></li>
            <li><a href="/anu/programs.php?school=Law">School of Law</a></li>
            <li><a href="/anu/programs.php?school=Business">School of Business</a></li>
            <li><a href="/anu/programs.php?school=Science">School of Science and Technology</a></li>
            <li><a href="/anu/programs.php?school=Humanities">School of Humanities and Social Sciences</a></li>
            <li><a href="/anu/programs.php?school=Religion">School of Religion and Christian Ministry</a></li>
          </ul>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <h6 class="footer-head">Students</h6>
          <ul class="footer-list">
            <li><a href="{$portal}" target="_blank" rel="noopener">Student Portal</a></li>
            <li><a href="{$enaz}" target="_blank" rel="noopener">eNaz</a></li>
            <li><a href="/anu/orientation-pack.php">Orientation</a></li>
            <li><a href="/anu/student-life.php">Student Life</a></li>
            <li><a href="/anu/chaplaincy.php">Chaplaincy</a></li>
            <li><a href="/anu/support.php">Support</a></li>
          </ul>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
          <h6 class="footer-head">Contact Us</h6>
          <ul class="footer-contact">
            <li><i class="bi bi-geo-alt-fill"></i><span>{$addr}</span></li>
            <li><i class="bi bi-telephone-fill"></i><a href="tel:{$tel}">{$tel}</a></li>
            <li><i class="bi bi-envelope-fill"></i><a href="mailto:{$mail}">{$mail}</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-news">
        <div class="footer-news-text"><strong>Stay updated</strong><span>News, intakes and events from ANU Online, in your inbox.</span></div>
        <form class="footer-nl" onsubmit="handleNewsletter(event)">
          <input type="email" placeholder="Your email address" required>
          <button type="submit" aria-label="Subscribe"><i class="bi bi-send-fill"></i></button>
        </form>
        <div id="newsletter-msg"></div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <p>&copy; {$yr} Africa Nazarene University Online. All rights reserved.</p>
      <div class="footer-links">
        <a href="/anu/privacy.php">Privacy Policy</a><a href="/anu/data-protection.php">Data Protection (DPA 2019)</a><a href="/anu/terms.php">Terms of Use</a>
        <a href="/anu/admin/login.php">Admin Login</a>
      </div>
    </div>
  </div>
</footer>
<div id="anuDataNotice" role="dialog" aria-live="polite" aria-label="Data protection notice" hidden style="position:fixed;left:0;right:0;bottom:0;z-index:2000;background:#111;color:#fff;padding:14px 18px;box-shadow:0 -6px 24px rgba(0,0,0,.3);border-top:3px solid #FAC823">
  <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2" style="max-width:1140px;margin:0 auto">
    <p style="margin:0;font-size:.9rem;flex:1 1 420px">We use only the cookies needed to keep this site secure and working. Your personal data is handled under Kenya's Data Protection Act, 2019. See our <a href="/anu/privacy.php" style="color:#FAC823">Privacy Policy</a> and <a href="/anu/data-protection.php" style="color:#FAC823">Data Protection Notice</a>.</p>
    <button type="button" id="anuDataNoticeOk" style="background:#FAC823;color:#111;border:0;border-radius:6px;padding:.5rem 1.2rem;font-weight:700;cursor:pointer">Got it</button>
  </div>
</div>
<script>(function(){var k='anuOnlineDataNotice',b=document.getElementById('anuDataNotice');if(!b)return;var seen=false;try{seen=localStorage.getItem(k)==='1';}catch(e){}if(!seen)b.hidden=false;document.getElementById('anuDataNoticeOk').addEventListener('click',function(){b.hidden=true;try{localStorage.setItem(k,'1');}catch(e){}});})();</script>

HTML;
    if ($hsId !== '') {
        echo '<style>#anuDataNotice{display:none !important}</style>';
        anu_hubspot_render($hsId, ['privacy' => '/anu/privacy', 'dp' => '/anu/data-protection']);
    }
    // WhatsApp float button — built outside heredoc so PHP executes correctly
    $waNum = preg_replace('/[^0-9]/', '', ($s['whatsapp_number'] ?? '254714785249'));
    $waMsg = urlencode('Hello ANU Online, I would like to know more about your programs.');
    echo '<a id="waBtn" href="https://wa.me/' . $waNum . '?text=' . $waMsg . '" target="_blank" rel="noopener"'
       . ' aria-label="Chat on WhatsApp"'
       . ' style="position:fixed;bottom:90px;right:24px;z-index:1050;width:54px;height:54px;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.55rem;box-shadow:0 4px 18px rgba(37,211,102,.45);transition:transform .2s,box-shadow .2s;text-decoration:none"'
       . ' onmouseover="this.style.transform=&quot;scale(1.1)&quot;;this.style.boxShadow=&quot;0 6px 28px rgba(37,211,102,.6)&quot;"'
       . ' onmouseout="this.style.transform=&quot;scale(1)&quot;;this.style.boxShadow=&quot;0 4px 18px rgba(37,211,102,.45)&quot;">'
       . '<i class="bi bi-whatsapp"></i>'
       . '<span style="position:absolute;top:-4px;right:-4px;width:14px;height:14px;border-radius:50%;background:#ff0000;border:2px solid #fff;animation:waPulse 2s infinite"></span>'
       . '</a>'
       . '<style>@keyframes waPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.6}}</style>';
    echo '<button id="toTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>';
    echo <<<HTML
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="/anu/assets/js/main.js"></script>
<script src="/anu/assets/js/a11y-widget.js?v=2" defer></script>
<script>
AOS.init({once:true,duration:700,offset:60});
window.addEventListener('scroll',function(){
  document.getElementById('siteNav').classList.toggle('scrolled',window.scrollY>40);
  document.getElementById('toTop').classList.toggle('show',window.scrollY>500);
});
document.getElementById('toTop').addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
// ── Item 2: mobile slide-in nav — overlay, Escape, link-click, scroll lock, aria ──
(function(){
  var toggle  = document.getElementById('navToggle');
  var menu    = document.getElementById('navMenu');
  var overlay = document.getElementById('navOverlay');
  var closeBtn = document.getElementById('navClose');
  if(!toggle||!menu) return;

  function openMenu(){
    menu.classList.add('open');
    toggle.classList.add('is-open');
    toggle.querySelector('i').className='bi bi-x';
    toggle.setAttribute('aria-expanded','true');
    toggle.setAttribute('aria-label','Close menu');
    if(overlay) overlay.classList.add('show');
    document.body.classList.add('nav-locked');
  }
  function closeMenu(){
    menu.classList.remove('open');
    toggle.classList.remove('is-open');
    toggle.querySelector('i').className='bi bi-list';
    toggle.setAttribute('aria-expanded','false');
    toggle.setAttribute('aria-label','Open menu');
    if(overlay) overlay.classList.remove('show');
    document.body.classList.remove('nav-locked');
  }
  toggle.addEventListener('click',function(){
    menu.classList.contains('open') ? closeMenu() : openMenu();
  });
  if(overlay) overlay.addEventListener('click',closeMenu);
  if(closeBtn) closeBtn.addEventListener('click',closeMenu);
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape' && menu.classList.contains('open')) closeMenu();
  });
  // Close the slide-in panel when a real nav link (not a dropdown toggle) is tapped
  menu.querySelectorAll('a').forEach(function(a){
    a.addEventListener('click',function(){
      if(window.innerWidth<=768) closeMenu();
    });
  });
})();
document.querySelectorAll('.has-drop>.nav-link').forEach(function(el){
  el.addEventListener('click',function(e){
    if(window.innerWidth<992){e.preventDefault();this.closest('.has-drop').classList.toggle('open');}
  });
});
// ── Item 7: accreditation band pop-in on scroll into view ──
(function(){
  var band=document.querySelector('.accred-strip');
  if(!band) return;
  if(!('IntersectionObserver' in window)){ band.classList.add('in-view'); return; }
  var io=new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if(en.isIntersecting){ band.classList.add('in-view'); io.unobserve(band); }
    });
  },{threshold:.2});
  io.observe(band);
})();
// ── Item D2: stats count-up animation on scroll (site-wide — any page
// using .stat-num[data-count] gets this automatically, per item G) ──
(function(){
  var nums=document.querySelectorAll('.stat-num[data-count]');
  if(!nums.length) return;
  function animate(el){
    var target=parseFloat(el.getAttribute('data-count'))||0;
    var suffix=el.getAttribute('data-suffix')||'';
    var dur=1200, start=null;
    function step(ts){
      if(!start) start=ts;
      var p=Math.min((ts-start)/dur,1);
      var eased=1-Math.pow(1-p,3);
      var val=Math.round(target*eased);
      el.textContent=val.toLocaleString()+suffix;
      if(p<1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if(!('IntersectionObserver' in window)){ nums.forEach(animate); return; }
  var io2=new IntersectionObserver(function(entries){
    entries.forEach(function(en){
      if(en.isIntersecting){ animate(en.target); io2.unobserve(en.target); }
    });
  },{threshold:.4});
  nums.forEach(function(el){ io2.observe(el); });
})();
</script>
HTML;
}

// ─── Staff/team helpers (drives Director message, Faculty spotlight,
// Chaplaincy team and the Meet-the-Team page from the real `staff` table
// instead of hardcoded/invented names — content only ever appears once an
// admin has actually added it via Admin → Team). ───────────────────────
function getStaff(array $filters = [], int $limit = 0): array {
    try {
        $where = []; $params = [];
        if (!empty($filters['department'])) { $where[] = 'department = ?'; $params[] = $filters['department']; }
        if (!empty($filters['is_featured'])) { $where[] = 'is_featured = 1'; }
        $sql = 'SELECT * FROM staff';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int)$limit;
        $s = getDB()->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    } catch (Exception $e) { return []; }
}

// Director's Message (item 3, this phase). Reuses the same "Leadership"
// staff record as before, but now: (a) shows a short 60-80 word EXCERPT
// on the homepage instead of the full bio, with a "Read Full Message"
// button to a dedicated page; (b) per this phase's explicit spec, shows
// a clearly-marked placeholder (not an invented real message) instead of
// hiding entirely when no Leadership record has been added yet, so the
// section's presence/placement is always visible to review.
/** The ANU Online Director (Admin -> Team, department "Leadership"). Nothing invented: empty fields stay empty. */
function getDirector(): array {
    $rows = getStaff(['department' => 'Leadership'], 1);
    $d = $rows[0] ?? null;
    $photo = ($d && !empty($d['photo'])) ? imgUrl($d['photo']) : '/anu/assets/images/director.jpg';
    return [
        'real'  => (bool)$d,
        'name'  => $d ? (string)$d['name'] : '',
        'title' => $d ? (string)($d['title'] ?? '') : '',
        'bio'   => $d ? trim((string)($d['bio'] ?? '')) : '',
        'photo' => $photo,
    ];
}

function renderDirectorMessage(): void {
    // Same card design as the Dean / Head messages on the university and
    // school sites. Reads the real "Leadership" staff record (Admin -> Team).
    // Nothing is invented: with no record yet the card says the message
    // will be published soon.
    $dir = getDirector();
    $name  = $dir['name'];
    $title = $dir['title'];
    $photo = $dir['photo'];
    $bio   = trim(strip_tags($dir['bio']));
    $words = preg_split('/\s+/', $bio, -1, PREG_SPLIT_NO_EMPTY);
    $excerpt = count($words) > 55 ? implode(' ', array_slice($words, 0, 55)) . '…' : $bio;
    $initials = $name !== '' ? strtoupper(substr($name, 0, 1)) : '';

    echo '<section class="sm-section"><div class="container">';
    echo '<a class="sm-dean" href="/anu/directors-message.php">';
    echo '<div class="sm-dean-photo">';
    if ($photo) echo "<img src='".e($photo)."' alt='".e($name)."' loading='lazy'>";
    else echo "<div class='sm-ph'>".($initials !== '' ? e($initials) : "<i class='bi bi-person-fill'></i>")."</div>";
    echo '</div><div class="sm-dean-body">';
    echo '<span class="sm-eyebrow">ANU Online</span>';
    echo '<h2>Message from the Director</h2>';
    if ($excerpt !== '') echo '<p class="sm-quote">'.e($excerpt).'</p>';
    else echo '<p class="sm-quote">The Director&rsquo;s message will be published here soon.</p>';
    if ($name !== '') {
        echo '<p class="sm-sign"><strong>'.e($name).'</strong>'.($title !== '' ? '<span>'.e($title).'</span>' : '').'</p>';
    }
    echo '<span class="sm-more">Read the full message <i class="bi bi-arrow-right"></i></span>';
    echo '</div></a></div></section>';
}

// Reusable grid of staff cards (Faculty spotlight, Chaplaincy team, the
// full Team page). Shows a plain "not added yet" state instead of ever
// inventing placeholder people.
function renderStaffGrid(array $staff, string $emptyMsg = 'Team profiles will appear here once added via the Admin Panel.'): void {
    if (empty($staff)) {
        echo '<div class="staff-empty"><i class="bi bi-people"></i><p>'.e($emptyMsg).'</p></div>';
        return;
    }
    echo '<div class="staff-grid">';
    foreach ($staff as $m) {
        $photo = !empty($m['photo']) ? imgUrl($m['photo']) : '';
        $initials = strtoupper(substr($m['name'], 0, 2));
        echo '<div class="staff-card">';
        echo '<div class="staff-avatar">';
        echo $photo ? "<img src='".e($photo)."' alt='".e($m['name'])."'>" : e($initials);
        echo '</div>';
        echo '<div class="staff-name">'.e($m['name']).'</div>';
        if (!empty($m['title'])) echo '<div class="staff-title">'.e($m['title']).'</div>';
        if (!empty($m['department'])) echo '<div class="staff-dept">'.e($m['department']).'</div>';
        if (!empty($m['bio'])) echo '<p class="staff-bio">'.e($m['bio']).'</p>';
        if (!empty($m['email'])) echo '<div class="staff-email"><i class="bi bi-envelope"></i>'.e($m['email']).'</div>';
        echo '</div>';
    }
    echo '</div>';
}
?>
