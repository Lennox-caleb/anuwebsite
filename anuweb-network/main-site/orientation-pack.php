<?php
$pageTitle = 'Orientation';
require_once __DIR__ . '/includes/functions.php';

// Documents are pulled the same way resources.php does it, from the
// existing Resources Library, just filtered to whatever the admin tags
// as "Orientation" in the Resources admin screen. Nothing here is
// invented: until real files are uploaded under that category, the
// section below shows an honest "coming soon" placeholder instead of
// fake links.
$orientationDocs = db()->query(
    "SELECT * FROM resources WHERE is_published = 1 AND category IN ('Orientation','Orientation Pack') ORDER BY title"
)->fetchAll();

$icons = [
    'pdf' => 'bi-file-earmark-pdf', 'doc' => 'bi-file-earmark-word', 'docx' => 'bi-file-earmark-word',
    'xls' => 'bi-file-earmark-excel', 'xlsx' => 'bi-file-earmark-excel',
];

// Phase 2: this hero now goes through the SAME shared image/video/YouTube
// component as the homepage and the school sites, sourced from the same
// admin Hero Images screen (a row with unit_slug = 'orientation-pack').
// No row has been added yet, so $heroMediaHtml is '' today and the
// section below falls back to its existing static photo exactly as
// before -- nothing changes visually until an admin (or Phase 3) sets one.
$orientHero = db()->prepare("SELECT * FROM sliders WHERE unit_slug = 'orientation-pack' AND is_published = 1 ORDER BY sort_order DESC, id DESC LIMIT 1");
$orientHero->execute();
$orientHero = $orientHero->fetch() ?: null;
$heroMediaHtml = render_hero_media($orientHero, UPLOADS_URL, 'hero-media');

// Phase 3: the Vice Chancellor's welcome message reuses the SAME featured-
// leader mechanism as every Dean's Message elsewhere on this site (Admin ->
// Sites -> Orientation -> Vice Chancellor's Welcome Message), rather
// than a one-off content field. No name is invented if none has been set --
// the byline falls back to the office itself, exactly like the Dean's
// Message pattern falls back to "Office of the Dean".
$vcStmt = db()->prepare("SELECT * FROM staff WHERE unit_slug = 'vc-welcome' AND is_published = 1 LIMIT 1");
$vcStmt->execute();
$vc = $vcStmt->fetch() ?: null;

// Meeting/Zoom details: real, admin-entered only (Admin -> Sites ->
// Orientation -> Orientation Meeting / Zoom Details). Both are blank
// by default -- no invented link or schedule -- so Section D below shows
// an honest "not yet scheduled" note until an admin fills them in.
$testimonials = [];
try { $testimonials = db()->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order, id DESC LIMIT 12')->fetchAll(); } catch (Throwable $e) { error_log('orientation testimonials: ' . $e->getMessage()); }
$od = include __DIR__ . '/includes/orientation_defaults.php';
// Testimonials: only real, published rows -- the section is hidden when there are none.
$testimonials = [];
try { $testimonials = db()->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order, id DESC LIMIT 12')->fetchAll(); } catch (Throwable $e) { error_log('orientation testimonials: ' . $e->getMessage()); }
$extraHead = '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/design-system.css') . '">'
           . '<link rel="stylesheet" href="' . asset_v(BASE_URL, 'assets/css/orientation-modern.css') . '">';
$meetingLink = page_content('orientation-pack', 'meeting_zoom_link');
$meetingDetails = page_content('orientation-pack', 'meeting_zoom_details');

require __DIR__ . '/includes/header.php';
?>
<div class="orient-scrollbar" aria-hidden="true"><span></span></div>
<section class="orient-hero hero-fullscreen<?= $heroMediaHtml ? ' has-media' : '' ?>" id="sec-a">
  <?= $heroMediaHtml ?>
  <span class="orient-glow g1" aria-hidden="true"></span><span class="orient-glow g2" aria-hidden="true"></span>
  <div class="container container-narrow text-center hero-fullscreen-content">
    <span class="hero-eyebrow">New Students</span>
    <h1>Orientation</h1>
    <p class="mb-0">Everything you need to prepare for Orientation Week at Africa Nazarene University, what to bring, what to expect, and who to ask when you need help.</p>
    <div class="orient-cta">
      <a href="#sec-b" class="btn btn-apply btn-lg">See how the week flows</a>
      <a href="#sec-c" class="btn-ghost btn-lg">What to bring</a>
      <?php if ($meetingLink): ?><a href="<?= h($meetingLink) ?>" target="_blank" rel="noopener" class="btn-ghost btn-lg"><i class="bi bi-camera-video me-1"></i> Join the meeting</a><?php endif; ?>
    </div>
  </div>
  <a href="#sec-journey" class="orient-cue" aria-label="Scroll down"></a>
</section>

<nav class="orient-subnav" aria-label="Orientation sections">
  <div class="container container-narrow">
    <a href="#sec-a"><span class="letter">A</span> Welcome</a>
    <a href="#sec-b"><span class="letter">B</span> Schedule</a>
    <a href="#sec-c"><span class="letter">C</span> What to Bring</a>
    <a href="#sec-d"><span class="letter">D</span> Meeting Details</a>
    <a href="#sec-e"><span class="letter">E</span> Documents</a>
    <a href="#sec-f"><span class="letter">F</span> FAQ</a>
    <a href="#sec-g"><span class="letter">G</span> Need Help</a>
  </div>
</nav>

<div class="container container-narrow">
  <div class="orient-quickinfo reveal">
    <span class="item"><i class="bi bi-geo-alt"></i> Ongata Rongai Main Campus</span>
    <span class="item"><i class="bi bi-calendar-check"></i> Dates confirmed on your admission letter</span>
    <span class="item"><i class="bi bi-telephone"></i> <a href="tel:<?= h(setting('phone_primary')) ?>" style="color:inherit;"><?= h(setting('phone_primary')) ?></a></span>
  </div>
</div>

<section class="py-5">
  <div class="container container-narrow">
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
        <span class="message-card-eyebrow">A Word from the Vice Chancellor</span>
        <p class="message"><?= $vc && $vc['bio'] ? nl2br(h($vc['bio'])) : 'Welcome to the ANU family! Orientation Week is designed to help you settle in, find your way around campus, and meet the staff and fellow students who will walk this journey with you. This page brings together everything you need in one place, check back often, as details are updated as Orientation Week approaches.' ?></p>
        <p class="mb-0 mt-3"><strong><?= $vc && $vc['full_name'] ? h($vc['full_name']) : 'Office of the Vice Chancellor' ?></strong><br>
        <span class="text-muted small"><?= $vc && $vc['title'] ? h($vc['title']) : 'Vice Chancellor, Africa Nazarene University' ?></span></p>
      </div>
    </div>
  </div>
</section>

<!-- YOUR JOURNEY (same steps as Admissions > How to Apply) -->
<section class="py-5 journey-section" id="sec-journey">
  <div class="container">
    <div class="section-head reveal">
      <h2>Your Journey to ANU</h2>
      <a href="<?= BASE_URL ?>/admissions-how-to-apply.php" class="see-all">Full application guide &rarr;</a>
    </div>
    <div class="row g-3 journey-row">
      <?php foreach ([
        ['Pick your programme', 'Browse programmes across the five schools, Non-Degree, ANU Online and Short Courses.', 'bi-search'],
        ['Create your application', 'Apply through the online records portal and pay the application fee.', 'bi-pencil-square'],
        ['Upload your documents', 'Submit transcripts, ID and a passport photo.', 'bi-cloud-arrow-up'],
        ['Await your admission letter', 'Feedback is usually given within ' . page_content('admissions', 'feedback_time', '5 working days') . '.', 'bi-envelope-check'],
        ['Confirm your place', 'Follow the fee payment guide in your admission letter to secure your place.', 'bi-patch-check'],
      ] as $__i => [$__t, $__d, $__ic]): ?>
        <div class="col-6 col-md-4 col-xl journey-col">
          <a class="journey-card reveal" href="<?= BASE_URL ?>/admissions-how-to-apply.php">
            <span class="journey-num"><?= $__i + 1 ?></span>
            <i class="bi <?= $__ic ?> journey-icon" aria-hidden="true"></i>
            <h3><?= h($__t) ?></h3>
            <p><?= h($__d) ?></p>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4"><a href="<?= h(setting('apply_url')) ?>" target="_blank" rel="noopener" class="btn btn-apply btn-lg">Start Your Application</a></div>
  </div>
</section>

<section class="py-5 bg-cream" id="sec-b">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <h2>How Orientation Week Flows</h2>
    </div>
    <p class="text-muted" style="margin-top:-1rem; margin-bottom:2rem;">A typical week looks like this, your admission letter and the Dean of Students Office will confirm the exact dates and times for your intake.</p>
    <div class="orient-steps">
      <?php foreach ($od['steps'] as $i => [$__t, $__x]): $n = $i + 1; ?>
      <div class="orient-step reveal <?= $i % 4 ? 'reveal-' . (($i % 4) + 1) : '' ?>">
        <span class="orient-step-num"><?= $n ?></span>
        <div>
          <h3><?= h(page_content('orientation-pack', "step{$n}_title", $__t)) ?></h3>
          <p><?= h(page_content('orientation-pack', "step{$n}_text", $__x)) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5" id="sec-c">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <h2>What to Bring</h2>
    </div>
    <div class="orient-progress" id="orientProgress" hidden>
      <div class="orient-progress-bar"><span id="orientProgressFill"></span></div>
      <span id="orientProgressText" aria-live="polite"></span>
      <button type="button" class="btn btn-sm btn-outline-anu" id="orientReset">Reset</button>
      <button type="button" class="btn btn-sm btn-outline-anu" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
    <div class="orient-allset" id="orientAllSet" role="status" hidden>
      <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
      <span>Everything on the list is ready. See you at Orientation Week.</span>
      <?php for ($d = 0; $d < 12; $d++): ?><span class="dot" aria-hidden="true"></span><?php endfor; ?>
    </div>
    <div class="row g-4">
      <?php foreach ($od['checklist'] as $i => [$icon, $__t, $__x]): $n = $i + 1; ?>
        <div class="col-md-6 col-lg-4">
          <label class="card-anu feature-card h-100 reveal orient-check <?= $i > 0 ? 'reveal-' . min($i + 1, 4) : '' ?>" for="chk<?= $n ?>">
            <div class="body">
              <span class="orient-check-box"><input type="checkbox" id="chk<?= $n ?>" data-orient-check="<?= $n ?>" aria-label="I have this ready"></span>
              <span class="icon-badge"><i class="bi <?= h($icon) ?>"></i></span>
              <h3 class="h6"><?= h(page_content('orientation-pack', "chk{$n}_title", $__t)) ?></h3>
              <p class="card-meta" style="color:var(--anu-ink-soft); font-size:.9rem;"><?= h(page_content('orientation-pack', "chk{$n}_text", $__x)) ?></p>
            </div>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-cream" id="sec-d">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <h2>Orientation Meeting Details</h2>
    </div>
    <?php if ($meetingLink || $meetingDetails): ?>
      <div class="orient-meeting reveal">
        <span class="orient-meeting-icon" aria-hidden="true"><i class="bi bi-camera-video"></i></span>
        <div class="orient-meeting-body">
          <?php if ($meetingDetails): ?><p><?= nl2br(h($meetingDetails)) ?></p><?php endif; ?>
        </div>
        <?php if ($meetingLink): ?>
          <a href="<?= h($meetingLink) ?>" target="_blank" rel="noopener" class="btn btn-apply btn-lg"><i class="bi bi-camera-video me-1"></i> Join Orientation Meeting</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-4">
        <i class="bi bi-camera-video" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3 mb-0">The orientation meeting/Zoom link and schedule will be published here once confirmed by the Dean of Students Office.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <div class="orient-download-cta reveal">
      <div>
        <h3>Get the Orientation Documents</h3>
        <p><?= $orientationDocs ? 'Download the complete schedule, hostel guide and checklist below, or scroll down for every document.' : 'The full schedule, hostel guide and checklist will be published here as Orientation Week approaches -- check the Documents section below.' ?></p>
      </div>
      <a href="#sec-e" class="btn btn-apply"><i class="bi bi-download me-1"></i> View Documents</a>
    </div>
  </div>
</section>

<section class="py-5 bg-cream" id="sec-e">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <h2>Orientation Documents</h2>
    </div>
    <?php if ($orientationDocs): ?>
      <div class="shelf-row">
        <?php foreach ($orientationDocs as $i => $item):
          $ext = strtolower(pathinfo($item['file_name'], PATHINFO_EXTENSION));
          $icon = $icons[$ext] ?? 'bi-file-earmark';
          $spine = $i % 2 === 0 ? 'spine-red' : 'spine-black';
        ?>
          <a href="<?= h(UPLOADS_URL . '/' . $item['file_name']) ?>" class="book-card <?= $spine ?>" download title="<?= h($item['title']) ?>">
            <span class="book-icon"><i class="bi <?= h($icon) ?>"></i></span>
            <span class="book-title"><?= h($item['title']) ?></span>
            <span class="book-meta"><?= strtoupper($ext) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-4">
        <i class="bi bi-file-earmark-text" style="font-size:2.2rem; color: var(--anu-line);"></i>
        <p class="text-muted mt-3 mb-0">Orientation documents (schedule, handbook, hostel guide) will appear here once
        uploaded via the Resources Library under the "Orientation" category.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="py-5" id="sec-f">
  <div class="container container-narrow">
    <div class="section-head reveal">
      <h2>Frequently Asked Questions</h2>
    </div>
    <input type="search" id="orientFaqSearch" class="form-control mb-3" placeholder="Search the questions&hellip;" aria-label="Search the FAQ">
    <div class="accordion" id="orientationFaq">
      <?php foreach ($od['faqs'] as $i => [$__q, $__a]): $n = $i + 1; $id = 'faq' . $i;
        $q = page_content('orientation-pack', "faq{$n}_q", $__q); $a = page_content('orientation-pack', "faq{$n}_a", $__a); ?>
        <div class="accordion-item">
          <h3 class="accordion-header">
            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>">
              <?= h($q) ?>
            </button>
          </h3>
          <div id="<?= $id ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#orientationFaq">
            <div class="accordion-body"><?= h($a) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ACCREDITATION -->
<section class="py-5 accred-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-6">
        <a class="accred-card reveal" href="<?= BASE_URL ?>/accreditation.php">
          <i class="bi bi-patch-check-fill accred-icon" aria-hidden="true"></i>
          <div>
            <span class="tag">Accreditation</span>
            <h2 class="h4">Chartered by the Commission for University Education</h2>
            <p class="mb-0">The first private university in Kenya accredited under the University Act without external affiliation to another institution. <span class="see-all">See accreditation details &rarr;</span></p>
          </div>
        </a>
      </div>
      <div class="col-lg-6">
        <a class="accred-card reveal" href="<?= BASE_URL ?>/affiliation.php">
          <i class="bi bi-bank accred-icon" aria-hidden="true"></i>
          <div>
            <span class="tag">Sponsored by the Church of the Nazarene</span>
            <h2 class="h4">Faith, learning and service</h2>
            <p class="mb-0">An institution forming graduates with academic excellence and Christian character. <span class="see-all">Learn about our affiliation &rarr;</span></p>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<?php if ($testimonials): ?>
<!-- TESTIMONIALS (compact slider; only real published entries) -->
<section class="py-4 testimonial-section" aria-roledescription="carousel" aria-label="Testimonials">
  <div class="container">
    <div class="section-head reveal"><h2>In Their Words</h2></div>
    <div class="t-slider" id="anuTestimonials">
      <div class="t-track" tabindex="0">
        <?php foreach ($testimonials as $__t): ?>
          <figure class="t-card">
            <blockquote><?= h(excerpt($__t['quote'], 220)) ?></blockquote>
            <figcaption>
              <?php if ($__t['photo']): ?><img src="<?= h(UPLOADS_URL . '/' . $__t['photo']) ?>" alt="" loading="lazy"><?php endif; ?>
              <span><strong><?= h($__t['person_name']) ?></strong><?php if ($__t['person_role']): ?><br><small><?= h($__t['person_role']) ?></small><?php endif; ?></span>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
      <?php if (count($testimonials) > 1): ?>
      <div class="t-controls">
        <button type="button" class="t-btn" data-dir="-1" aria-label="Previous testimonial"><i class="bi bi-chevron-left"></i></button>
        <button type="button" class="t-btn" data-dir="1" aria-label="Next testimonial"><i class="bi bi-chevron-right"></i></button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5 bg-cream" id="sec-g">
  <div class="container container-narrow text-center">
    <div class="section-head reveal" style="justify-content:center; border-bottom:none;">
      <h2>Need Help?</h2>
    </div>
    <p class="text-muted">Our team is ready to help you get settled.</p>
    <p class="mb-4">
      <i class="bi bi-telephone"></i> <a href="tel:<?= h(setting('phone_primary')) ?>"><?= h(setting('phone_primary')) ?></a>
      <span class="divider mx-2">|</span>
      <i class="bi bi-envelope"></i> <a href="mailto:<?= h(setting('email_marketing')) ?>"><?= h(setting('email_marketing')) ?></a>
    </p>
    <a href="<?= BASE_URL ?>/student-life.php" class="btn btn-outline-anu me-2">Student Life &rarr;</a>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-anu">Contact Us &rarr;</a>
  </div>
</section>
<script src="<?= asset_v(BASE_URL, 'assets/js/orientation.js') ?>" defer></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/testimonials.js') ?>" defer></script>
<script src="<?= asset_v(BASE_URL, 'assets/js/orientation-modern.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
