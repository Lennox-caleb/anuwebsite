<?php
require_once __DIR__ . '/functions.php';
$current = basename($_SERVER['PHP_SELF']);

// Canonical URL: rebuild the production URL regardless of the local
// BASE_URL subfolder, the same way sitemap.php hardcodes its own $base.
$__path = strtok($_SERVER['REQUEST_URI'], '?');
if (BASE_URL !== '' && strpos($__path, BASE_URL) === 0) {
    $__path = substr($__path, strlen(BASE_URL));
}
$canonicalUrl = SITE_URL . $__path;
$ogImage = SITE_URL . '/assets/images/anu-logo.png';
$metaDescription = $pageDescription ?? setting('mission');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= h(setting('site_name')) ?></title>
<meta name="description" content="<?= h($metaDescription) ?>">
<link rel="canonical" href="<?= h($canonicalUrl) ?>">
<meta name="theme-color" content="#FF0000">

<!-- Favicon (reuses the existing university logo; add a dedicated .ico/.png set when available) -->
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/anu-logo.png">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/anu-logo.png">

<!-- Open Graph / social sharing -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= h(setting('site_name')) ?>">
<meta property="og:title" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= h(setting('site_name')) ?>">
<meta property="og:description" content="<?= h($metaDescription) ?>">
<meta property="og:url" content="<?= h($canonicalUrl) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?><?= h(setting('site_name')) ?>">
<meta name="twitter:description" content="<?= h($metaDescription) ?>">
<meta name="twitter:image" content="<?= h($ogImage) ?>">

<link href="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/hero-fullscreen.css') ?>">
<?php if ($current !== 'index.php'): /* homepage keeps its original look */ ?>
<?php endif; ?>
<script src="<?= asset_v(BASE_URL, 'assets/js/hero-fullscreen.js') ?>" defer></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/hero-media-fallback.js') ?>" defer></script>
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/priority-nav.css') ?>">
<script src="<?= asset_v(BASE_URL, 'assets/js/priority-nav.js') ?>" defer></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/hero-controls.js') ?>" defer></script>

<!-- Structured data: helps Google show ANU as a recognised institution in search results -->
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollegeOrUniversity',
    'name' => setting('site_name'),
    'alternateName' => 'ANU',
    'url' => SITE_URL,
    'logo' => $ogImage,
    'description' => setting('mission'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => setting('address'),
        'addressCountry' => 'KE',
    ],
    'telephone' => setting('phone_primary'),
    'email' => setting('email_marketing'),
    'sameAs' => array_values(array_filter([
        setting('facebook_url'), setting('twitter_url'), setting('instagram_url'),
        setting('linkedin_url'), setting('youtube_url'), setting('tiktok_url'),
    ])),
], JSON_UNESCAPED_SLASHES) ?>
</script>
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/site-extras.css') ?>">
<?= $extraHead ?? '' ?>
<link rel="stylesheet" href="<?= asset_v(BASE_URL, 'assets/css/a11y.css') ?>">
</head>
<body>

<a href="#main-content" class="skip-link">Skip to main content</a>

<div class="utility-bar">
  <div class="container d-flex flex-wrap justify-content-between align-items-center">
    <div>
      <i class="bi bi-telephone"></i> <a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a>
      <span class="divider">|</span>
      <a href="tel:<?= h(setting('phone_secondary_2')) ?>"><?= h(setting('phone_secondary_2')) ?></a>
      <span class="divider d-none d-md-inline">|</span>
      <a href="mailto:<?= h(setting('email_marketing')) ?>" class="d-none d-md-inline"><i class="bi bi-envelope"></i> <?= h(setting('email_marketing')) ?></a>
    </div>
    <div class="d-flex align-items-center flex-wrap">
      <a href="<?= h(setting('enaz_url')) ?>" target="_blank" rel="noopener">eNaz</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('student_portal_url')) ?>" target="_blank" rel="noopener">Student Portal</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('url_anu_online')) ?>" target="_blank" rel="noopener">ANU Online</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('repository_url')) ?>" target="_blank" rel="noopener" title="University Repository">Repository</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('myloft_url')) ?>" target="_blank" rel="noopener">MyLoft</a>
      <span class="divider">|</span>
      <a href="<?= BASE_URL ?>/orientation.php" title="Orientation for New Students">Orientation</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('alumni_url')) ?>" target="_blank" rel="noopener">Alumni</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('foundation_url')) ?>" target="_blank" rel="noopener">ANU Foundation</a>
      <span class="divider">|</span>
      <a href="<?= BASE_URL ?>/contact.php">Contact Us</a>
      <span class="divider">|</span>
      <a href="<?= h(setting('donate_url')) ?>" target="_blank" rel="noopener" class="donate-link"><i class="bi bi-heart-fill me-1"></i>Donate</a>
    </div>
  </div>
</div>

<nav class="navbar navbar-expand-xl navbar-anu sticky-top site-header-bar">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University logo">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end offcanvas-xl mobile-nav-panel" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
      <div class="offcanvas-header d-xl-none">
        <a class="mobile-nav-brand" href="<?= BASE_URL ?>/index.php" id="mobileNavLabel">
          <img src="<?= BASE_URL ?>/assets/images/anu-logo.png" alt="Africa Nazarene University logo">
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
      </div>
      <div class="offcanvas-body p-0 p-xl-0">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link <?= $current==='index.php'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
        <li class="nav-item dropdown mega-parent">
          <a class="nav-link dropdown-toggle <?= $current==='about.php'?'active':'' ?>" href="<?= BASE_URL ?>/about.php" role="button" data-bs-toggle="dropdown">About ANU</a>
          <div class="dropdown-menu mega-menu">
            <div class="row g-4">
              <div class="col-md-3">
                <p class="mega-heading">Introduction</p>
                <a class="mega-link" href="<?= BASE_URL ?>/leadership.php">Welcome Message from Vice Chancellor</a>
                <a class="mega-link" href="<?= BASE_URL ?>/leadership.php">Deputy Vice Chancellors</a>
              </div>
              <div class="col-md-3">
                <p class="mega-heading">Governance &amp; Administration</p>
                <a class="mega-link" href="<?= BASE_URL ?>/board-of-trustees.php">Board of Trustees</a>
                <a class="mega-link" href="<?= BASE_URL ?>/chancellor.php">Chancellor</a>
                <a class="mega-link" href="<?= BASE_URL ?>/university-council.php">University Council</a>
                <a class="mega-link" href="<?= BASE_URL ?>/management.php">Management Board</a>
              </div>
              <div class="col-md-3">
                <p class="mega-heading">About ANU</p>
                <a class="mega-link" href="<?= BASE_URL ?>/who-we-are.php">Who We Are</a>
                <a class="mega-link" href="<?= BASE_URL ?>/our-history.php">History</a>
                <a class="mega-link" href="<?= BASE_URL ?>/affiliation.php">Affiliations</a>
                <a class="mega-link" href="<?= BASE_URL ?>/about.php#campus">Main Campus</a>
              </div>
              <div class="col-md-3">
                <p class="mega-heading">More</p>
                <a class="mega-link" href="<?= BASE_URL ?>/mission-vision.php">Mission &amp; Vision</a>
                <a class="mega-link" href="<?= BASE_URL ?>/core-values.php">Core Values</a>
                <a class="mega-link" href="<?= BASE_URL ?>/accreditation.php">Accreditation</a>
                <a class="mega-link" href="<?= BASE_URL ?>/csr.php">Corporate Social Responsibility</a>
              </div>
            </div>
          </div>
        </li>

        <li class="nav-item dropdown mega-parent">
          <a class="nav-link dropdown-toggle" href="<?= BASE_URL ?>/programs.php" role="button" data-bs-toggle="dropdown">Academics</a>
          <div class="dropdown-menu mega-menu">
            <div class="row g-4">
              <div class="col-md-4">
                <p class="mega-heading">Programmes</p>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php">Explore Programmes</a>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php?level=Undergraduate">Undergraduate</a>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php?level=Masters">Postgraduate, Masters</a>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php?level=PhD">Postgraduate, Doctoral</a>
              </div>
              <div class="col-md-4">
                <p class="mega-heading">&nbsp;</p>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php?level=Diploma">Diploma</a>
                <a class="mega-link" href="<?= BASE_URL ?>/programs.php?level=Certificate">Certificate &amp; Other Courses</a>
                <a class="mega-link" href="<?= BASE_URL ?>/financial-aid.php">Financial Aid</a>
              </div>
              <div class="col-md-4">
                <p class="mega-heading">Academic Resources</p>
                <a target="_blank" rel="noopener" class="mega-link" href="<?= h(setting('url_library')) ?>">Library</a>
                <a target="_blank" rel="noopener" class="mega-link" href="<?= h(setting('enaz_url')) ?>">eNaz</a>
                <a target="_blank" rel="noopener" class="mega-link" href="<?= h(setting('student_portal_url')) ?>">Student Portal</a>
              </div>
            </div>
          </div>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $current==='admissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/admissions.php" role="button" data-bs-toggle="dropdown" id="admissionsDropdown" aria-expanded="false">Admissions</a>
          <ul class="dropdown-menu" aria-labelledby="admissionsDropdown">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions.php">Overview</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-how-to-apply.php">How to Apply</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-requirements.php">Requirements &amp; Application Fees</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-international.php">International Students (ANUISA)</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial-aid.php">Financial Aid</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-tuition-payment.php">Tuition Payment Options</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-faqs.php">FAQs</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admissions-enquiries.php">Admissions Enquiries</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item fw-bold text-red" target="_blank" rel="noopener" href="<?= h(setting('apply_url')) ?>">Click to Apply &rarr;</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown mega-parent">
          <a class="nav-link dropdown-toggle" href="<?= BASE_URL ?>/schools.php" role="button" data-bs-toggle="dropdown">Schools</a>
          <div class="dropdown-menu mega-menu">
            <p class="mega-heading">Our Five Schools</p>
            <div class="row g-2">
              <div class="col-md-6">
                <a target="_blank" rel="noopener" class="mega-link-rich" href="<?= h(setting('url_school_law')) ?>">
                  <i class="bi bi-bank"></i>
                  <span class="mega-link-title">School of Law</span>
                </a>
                <a target="_blank" rel="noopener" class="mega-link-rich" href="<?= h(setting('url_school_business')) ?>">
                  <i class="bi bi-briefcase"></i>
                  <span class="mega-link-title">School of Business</span>
                </a>
                <a target="_blank" rel="noopener" class="mega-link-rich" href="<?= h(setting('url_school_scitech')) ?>">
                  <i class="bi bi-cpu"></i>
                  <span class="mega-link-title">School of Science &amp; Technology</span>
                </a>
              </div>
              <div class="col-md-6">
                <a target="_blank" rel="noopener" class="mega-link-rich" href="<?= h(setting('url_school_humanities')) ?>">
                  <i class="bi bi-people"></i>
                  <span class="mega-link-title">School of Humanities &amp; Social Sciences</span>
                </a>
                <a target="_blank" rel="noopener" class="mega-link-rich" href="<?= h(setting('url_school_religion')) ?>">
                  <i class="bi bi-book"></i>
                  <span class="mega-link-title">School of Religion &amp; Christian Ministry</span>
                </a>
                <a class="mega-link-rich" href="<?= BASE_URL ?>/schools.php">
                  <i class="bi bi-grid"></i>
                  <span class="mega-link-title">Browse All Schools</span>
                </a>
              </div>
            </div>
            <div class="border-top mt-3 pt-3">
              <a class="mega-link" href="<?= BASE_URL ?>/programs.php">View All Programmes &rarr;</a>
            </div>
          </div>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="nonDegreeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Non-Degree</a>
          <ul class="dropdown-menu" aria-labelledby="nonDegreeDropdown">
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('url_tvet')) ?>">Non-Degree Academic Programs Unit</a></li>
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('url_sepc')) ?>">Short Executive and Professional Courses (SEPC) Unit</a></li>
          </ul>
        </li>
        <li class="nav-item"><a target="_blank" rel="noopener" class="nav-link" href="<?= h(setting('url_research')) ?>">Research</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="lifeAtAnuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Life at ANU</a>
          <ul class="dropdown-menu" aria-labelledby="lifeAtAnuDropdown">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/life-at-anu.php">Life at ANU Overview</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campus-tour.php">Campus Tour</a></li>
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('url_student_life')) ?>">Student Life</a></li>
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('url_chaplaincy')) ?>">Chaplaincy</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/orientation.php">Orientation</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial-aid.php">Financial Aid</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="libraryNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Library</a>
          <ul class="dropdown-menu" aria-labelledby="libraryNavDropdown">
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('url_library')) ?>">Library Home</a></li>
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('repository_url')) ?>">University Repository</a></li>
            <li><a target="_blank" rel="noopener" class="dropdown-item" href="<?= h(setting('myloft_url')) ?>">MyLOFT (E-Resources)</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown mega-parent" data-pn-pin>
          <a class="nav-link dropdown-toggle <?= $current==='resources.php' || $current==='tenders.php' ?'active':'' ?>" href="<?= BASE_URL ?>/resources.php" role="button" data-bs-toggle="dropdown">Resources</a>
          <div class="dropdown-menu mega-menu">
            <div class="row g-4">
              <div class="col-6 col-md-6">
                <p class="mega-heading">Procurement</p>
                <a class="mega-link" href="<?= BASE_URL ?>/tenders.php">Tenders</a>
                <p class="mega-heading mt-3">Careers</p>
                <a class="mega-link" href="<?= h(setting('vacancies_url')) ?>">Vacancies</a>
              </div>
              <div class="col-6 col-md-6">
                <p class="mega-heading">Publications</p>
                <a class="mega-link" href="<?= BASE_URL ?>/aspire-magazine.php">ASPIRE Magazine</a>
                <a class="mega-link" href="<?= BASE_URL ?>/anu-voice.php">ANU Voice</a>
                <a class="mega-link" href="<?= BASE_URL ?>/graduation-list.php">Graduation List</a>
              </div>
            </div>
          </div>
        </li>
        <li class="nav-item dropdown" data-pn-pin>
          <a class="nav-link dropdown-toggle <?= in_array($current, ['news-events.php','news.php','news-single.php','events.php','photo-gallery.php'], true) ?'active':'' ?>" href="<?= BASE_URL ?>/news-events.php" role="button" data-bs-toggle="dropdown" aria-expanded="false">News &amp; Events</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= $current==='news.php' || $current==='news-single.php' ?'active':'' ?>" href="<?= BASE_URL ?>/news.php">News</a></li>
            <li><a class="dropdown-item <?= $current==='events.php'?'active':'' ?>" href="<?= BASE_URL ?>/events.php">Events</a></li>
            <li><a class="dropdown-item <?= $current==='photo-gallery.php'?'active':'' ?>" href="<?= BASE_URL ?>/photo-gallery.php">Photo Gallery</a></li>
            <li><a class="dropdown-item" target="_blank" rel="noopener" href="<?= h(setting('youtube_url')) ?>">Videos</a></li>
          </ul>
        </li>
        <li class="nav-item"><button type="button" class="nav-link search-trigger" id="anuSearchTrigger" aria-label="Search" aria-haspopup="dialog" aria-expanded="false" aria-controls="anuSearchOverlay"><i class="bi bi-search"></i></button></li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0 d-none d-xl-block">
          <a target="_blank" rel="noopener" class="btn btn-apply" href="<?= h(setting('apply_url')) ?>">Apply Now</a>
        </li>
      </ul>

      <!-- Mobile-only panel footer: sticky Apply Now + quick links, then
           contact + socials, so a visitor browsing on a phone doesn't
           have to close the menu and hunt the page footer for a way to
           reach someone or apply. Hidden at xl+, where Apply Now already
           appears inline above and this same contact info already lives
           in the utility bar and site footer. -->
      <div class="mobile-nav-footer d-xl-none">
        <a target="_blank" rel="noopener" class="btn btn-apply mobile-nav-apply" href="<?= h(setting('apply_url')) ?>">Apply Now</a>
        <div class="mobile-nav-quicklinks">
          <a href="<?= h(setting('enaz_url')) ?>" target="_blank" rel="noopener">eNaz</a>
          <a href="<?= h(setting('student_portal_url')) ?>" target="_blank" rel="noopener">Student Portal</a>
          <a href="<?= h(setting('url_library')) ?>" target="_blank" rel="noopener">Library</a>
          <a href="mailto:<?= h(setting('email_marketing')) ?>">Email</a>
        </div>
        <a href="tel:<?= h(setting('phone_primary')) ?>" class="mobile-nav-contact"><i class="bi bi-telephone"></i> <?= h(setting('phone_primary')) ?></a>
        <a href="mailto:<?= h(setting('email_marketing')) ?>" class="mobile-nav-contact"><i class="bi bi-envelope"></i> <?= h(setting('email_marketing')) ?></a>
        <div class="mobile-nav-socials">
          <?php foreach ([
            ['facebook_url', 'bi-facebook'], ['twitter_url', 'bi-twitter-x'], ['instagram_url', 'bi-instagram'],
            ['linkedin_url', 'bi-linkedin'], ['youtube_url', 'bi-youtube'],
          ] as [$key, $icon]): $url = setting($key); if (!$url) continue; ?>
            <a href="<?= h($url) ?>" target="_blank" rel="noopener" aria-label="<?= h($key) ?>"><i class="bi <?= $icon ?>"></i></a>
          <?php endforeach; ?>
        </div>
      </div>
      </div>
    </div>
  </div>
</nav>

<!-- Instant search overlay: opened by the search icon above. Full-screen
     panel that drops down from the top of the page, with a large input,
     quick suggested searches, and live results grouped by type as you
     type -- inspired by how university sites like Daystar present search
     as an immersive overlay rather than only a separate results page.
     the search page (a real, linkable page) remains the "See all results"
     destination and the no-JS fallback. -->
<div class="search-overlay" id="anuSearchOverlay" data-search-url="<?= BASE_URL ?>/search-ajax" role="dialog" aria-modal="true" aria-label="Search this site" hidden>
  <div class="search-overlay-inner">
    <button type="button" class="search-overlay-close" id="anuSearchClose" aria-label="Close search">
      <i class="bi bi-x-lg"></i>
    </button>
    <form action="<?= BASE_URL ?>/search.php" method="get" class="search-overlay-form" role="search">
      <i class="bi bi-search search-overlay-icon"></i>
      <input type="text" name="q" id="anuSearchInput" class="search-overlay-input" placeholder="Search programmes, schools, news, events, people&hellip;" autocomplete="off">
      <button type="submit" class="search-overlay-go" aria-label="Search">Search</button>
    </form>
    <div class="search-overlay-hints">
      <span>Try:</span>
      <button type="button" data-q="admissions">Admissions</button>
      <button type="button" data-q="business">Business</button>
      <button type="button" data-q="law">Law</button>
      <button type="button" data-q="orientation">Orientation</button>
      <button type="button" data-q="research">Research</button>
      <button type="button" data-q="contact">Contact</button>
    </div>
    <div class="search-overlay-results" id="anuSearchResults" aria-live="polite"></div>
  </div>
</div>

<main id="main-content">
