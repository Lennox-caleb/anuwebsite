<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();
$msg = ''; $msgType = 'success';

// Handle prayer form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prayer_submit'])) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $request = trim($_POST['request'] ?? '');
    $priv    = isset($_POST['private']) ? 1 : 0;

    if (empty($request)) {
        $msg = 'Please share your prayer request.'; $msgType = 'error';
    } elseif (empty($_POST['consent'])) {
        $msg = 'Please confirm you agree to the Privacy Policy before submitting.'; $msgType = 'error';
    } elseif (!checkRateLimit('prayer_page', 15)) {
        $msg = 'Please wait a moment before submitting again.'; $msgType = 'error';
    } else {
        try {
            $db->prepare("INSERT INTO prayer_requests (name, email, request, is_private, created_at) VALUES (?,?,?,?,NOW())")
               ->execute([$name ?: 'Anonymous', $email, $request, $priv]);
            $msg = 'Your prayer request has been received. We are praying for you.';
        } catch (Exception $e) {
            $msg = 'Something went wrong. Please try again.'; $msgType = 'error';
        }
    }
}

$heroSlides = getSlides('chaplaincy');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('chaplaincy');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

// Real chaplaincy team only — added via Admin → Team with department
// "Chaplaincy". The featured one (if any) is shown as Lead Chaplain,
// the rest as supporting team. Nothing invented if none exist yet.
$chapTeam = getStaff(['department' => 'Chaplaincy']);
$chapLead = null; $chapRest = $chapTeam;
foreach ($chapTeam as $i => $m) { if (!empty($m['is_featured'])) { $chapLead = $m; unset($chapRest[$i]); break; } }
if (!$chapLead && $chapTeam) { $chapLead = array_shift($chapRest); }

trackPage('chaplaincy', 'Chaplaincy');
renderHead('Chaplaincy & Spiritual Life'); renderNavbar('student-life');
?>

<!-- ══ HERO BAND ══ -->
<div class="chap-hero-band" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="container chap-hero-inner">
    <div class="row align-items-center g-5">
      <div class="col-lg-7" data-aos="fade-right">
        <div class="sec-tag" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.8)">Spiritual Life</div>
        <h1 class="chap-hero-title">
          Chaplaincy &amp;<br><span style="color:var(--gold)">Spiritual Care</span>
        </h1>
        <p class="chap-hero-sub">Nurturing faith, hope, and community for every ANU Online student — wherever you are in the world.</p>
        <div class="chap-hero-stats">
          <?php foreach([['Weekly','Devotions'],['24/7','Prayer Support'],['All','Denominations Welcome']] as [$v,$l]): ?>
          <div class="chap-stat"><strong><?=$v?></strong><span><?=$l?></span></div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:36px">
          <a href="#prayer-form" class="btn-hero-solid"><i class="bi bi-heart-fill me-2"></i>Request Prayer</a>
          <a href="#programs"    class="btn-hero-outline">Our Programs</a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-center" data-aos="fade-left">
        <div style="width:220px;height:220px;border-radius:50%;border:2px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;position:relative">
          <div style="width:180px;height:180px;border-radius:50%;border:2px solid rgba(250,200,35,.25);display:flex;align-items:center;justify-content:center">
            <i class="bi bi-heart-fill" style="font-size:4rem;color:var(--gold);opacity:.6"></i>
          </div>
          <!-- Orbiting words -->
          <span style="position:absolute;top:10px;left:50%;transform:translateX(-50%);font-size:.6rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);white-space:nowrap">FAITH</span>
          <span style="position:absolute;bottom:10px;left:50%;transform:translateX(-50%);font-size:.6rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);white-space:nowrap">COMMUNITY</span>
          <span style="position:absolute;left:0;top:50%;transform:translateY(-50%);font-size:.6rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);white-space:nowrap">PRAYER</span>
          <span style="position:absolute;right:-10px;top:50%;transform:translateY(-50%);font-size:.6rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);white-space:nowrap">HOPE</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ CHAPLAINCY TEAM — real staff only (Admin → Team, department "Chaplaincy") ══ -->
<section class="sec" id="team">
  <div class="container">

    <!-- Section header -->
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Meet the Team</div>
      <h2 class="sec-title">Your <span class="hl">Chaplaincy Team</span></h2>
      <p class="sec-sub mx-auto">Dedicated servants walking alongside every ANU Online student in faith, prayer, and community.</p>
    </div>

    <?php if ($chapLead): ?>
    <!-- ── LEAD CHAPLAIN ── -->
    <div class="chap-lead-grid">
      <div class="chap-photo-card" data-aos="fade-right">
        <div class="chap-photo-frame">
          <?php $leadPhoto = !empty($chapLead['photo']) ? imgUrl($chapLead['photo']) : ''; ?>
          <?php if ($leadPhoto): ?>
          <img src="<?= e($leadPhoto) ?>" alt="<?= e($chapLead['name']) ?>"
               onload="this.nextElementSibling.style.display='none'" onerror="this.style.display='none'">
          <?php endif; ?>
          <div class="chap-initials-fb" style="<?= $leadPhoto ? '' : 'display:flex' ?>">
            <div class="chap-initials-circle"><?= e(strtoupper(substr($chapLead['name'],0,2))) ?></div>
          </div>
          <div class="chap-photo-overlay">
            <div class="chap-photo-name-tag"><?= e($chapLead['name']) ?></div>
            <div class="chap-photo-role-tag"><i class="bi bi-patch-check-fill"></i><?= e($chapLead['title'] ?: 'University Chaplain') ?></div>
          </div>
        </div>
        <?php if (!empty($chapLead['email'])): ?>
        <div class="chap-contact-row">
          <div class="chap-contact-item"><i class="bi bi-envelope-fill"></i><span><?= e($chapLead['email']) ?></span></div>
        </div>
        <?php endif; ?>
      </div>

      <div data-aos="fade-left">
        <div class="sec-tag">Lead Chaplain</div>
        <h2 class="chap-name"><?= e($chapLead['name']) ?></h2>
        <div class="chap-role"><?= e($chapLead['title'] ?: 'University Chaplain') ?> · ANU Online</div>
        <div class="rule"></div>
        <?php if (!empty($chapLead['bio'])): ?>
        <p class="chap-bio"><?= e($chapLead['bio']) ?></p>
        <?php endif; ?>

        <div class="chap-pillars" style="margin-top:32px">
          <?php foreach([
            ['bi-book-fill',       'Scripture & Devotion',  'Weekly devotionals and Scripture reflections delivered to all students.'],
            ['bi-chat-heart-fill', 'Prayer Ministry',       'Private, confidential prayer support available to every student.'],
            ['bi-people-fill',     'Community & Fellowship','Connecting students across borders through virtual groups and gatherings.'],
          ] as [$ic,$ti,$tx]): ?>
          <div class="chap-pillar-card">
            <div class="chap-pillar-ico"><i class="bi <?=$ic?>"></i></div>
            <div class="chap-pillar-title"><?=$ti?></div>
            <p class="chap-pillar-text"><?=$tx?></p>
          </div>
          <?php endforeach; ?>
        </div>

        <a href="#prayer-form" class="btn-hero-solid" style="display:inline-flex;margin-top:32px;font-size:.87rem;padding:13px 28px">
          <i class="bi bi-heart-fill me-2"></i>Request Prayer
        </a>
      </div>
    </div>

    <?php if ($chapRest): ?>
    <div class="text-center mt-5 mb-4" data-aos="fade-up">
      <div class="sec-tag">Supporting Ministry</div>
      <h3 style="font-family:var(--fh);font-size:clamp(1.6rem,2.5vw,2.2rem);color:var(--dark)">Associate Chaplaincy Team</h3>
    </div>
    <div style="max-width:960px;margin:0 auto">
      <?php renderStaffGrid($chapRest); ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No chaplaincy staff added yet — nothing invented, just a prayer CTA -->
    <div class="staff-empty">
      <i class="bi bi-heart"></i>
      <p>Our chaplaincy team's profiles will appear here once added via the Admin Panel.<br>
      In the meantime, you're always welcome to reach out below.</p>
      <a href="#prayer-form" class="btn-hero-solid" style="display:inline-flex;margin-top:18px;font-size:.87rem;padding:13px 28px;background:var(--red);color:#fff">
        <i class="bi bi-heart-fill me-2"></i>Request Prayer
      </a>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ══ SCRIPTURE BAND ══ -->
<div class="chap-scripture-band">
  <div class="container">
    <div class="chap-scripture-quote" data-aos="fade-up">
      <span class="chap-quote-mark">&ldquo;</span>
      <p class="chap-quote-text">I can do all this through him who gives me strength.</p>
      <div class="chap-quote-ref">Philippians 4:13 — NIV</div>
    </div>
  </div>
</div>

<!-- ══ WEEKLY PROGRAMS ══ -->
<section class="sec bg-light" id="programs">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Spiritual Programs</div>
      <h2 class="sec-title">What We <span class="hl">Offer</span></h2>
      <p class="sec-sub mx-auto">Regular programs designed to nurture your faith and wellbeing throughout the academic year.</p>
    </div>
    <div class="chap-programs-grid">
      <?php
      $programs = [
        ['Every Monday',    'bi-sunrise-fill',        'Morning Devotions',         'Start each week with a short Scripture reading, reflection, and prayer sent to your email.', '7:00 AM EAT'],
        ['Every Wednesday', 'bi-camera-video-fill',   'Virtual Chapel',            'A live online worship service with praise, message, and community prayer via Zoom.', '12:00 PM EAT'],
        ['Every Thursday',  'bi-chat-heart-fill',     'Prayer & Share Hour',        'Open virtual room for students to pray together and share testimonies in a safe space.', '8:00 PM EAT'],
        ['Monthly',         'bi-book-half',           'Bible Study Series',         'Deep-dive thematic Bible studies exploring faith, ethics, and Christian purpose.', 'Saturdays 10 AM'],
        ['As Needed',       'bi-person-hearts',       'One-on-One Counselling',    'Confidential pastoral counselling sessions with the University Chaplain by appointment.', 'By Appointment'],
        ['Bi-annually',     'bi-star-fill',           'Spiritual Life Conference', 'An annual gathering for worship, fellowship, and spiritual renewal open to all students.', 'See Calendar'],
      ];
      foreach($programs as $i=>[$day,$ic,$title,$desc,$time]): ?>
      <div class="chap-prog-card" data-aos="fade-up" data-aos-delay="<?=($i%3)*60?>">
        <div class="chap-prog-top"></div>
        <div class="chap-prog-body">
          <div class="chap-prog-day"><i class="bi <?=$ic?> me-2"></i><?=$day?></div>
          <div class="chap-prog-title"><?=$title?></div>
          <p class="chap-prog-desc"><?=$desc?></p>
          <div class="chap-prog-time"><i class="bi bi-clock me-1" style="color:var(--red)"></i><?=$time?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ PRAYER REQUEST ══ -->
<section class="sec" id="prayer-form">
  <div class="container">
    <div class="chap-prayer-wrap">

      <!-- Left context -->
      <div class="chap-prayer-context" data-aos="fade-right">
        <div class="sec-tag">Submit a Request</div>
        <h2 class="sec-title">Share Your <span class="hl">Prayer Need</span></h2>
        <div class="rule"></div>
        <p style="color:var(--muted);line-height:1.85;margin-bottom:8px">
          Whatever you are carrying — academic pressure, personal challenges, family concerns — you do not have to face it alone. Our chaplaincy team receives every prayer request with care and confidentiality.
        </p>
        <ul class="chap-promise-list">
          <li><i class="bi bi-shield-check-fill"></i>Every request is treated with complete confidentiality</li>
          <li><i class="bi bi-heart-fill"></i>We pray personally over every need submitted</li>
          <li><i class="bi bi-envelope-fill"></i>You may receive a personal follow-up from our chaplain</li>
          <li><i class="bi bi-incognito"></i>Anonymous requests are fully welcomed and respected</li>
        </ul>
        <!-- Scripture encouragement -->
        <div style="margin-top:32px;padding:24px 28px;background:rgba(255,0,0,.04);border-left:4px solid var(--red);border-radius:0 12px 12px 0">
          <p style="font-family:var(--fh);font-size:1.1rem;color:var(--dark);font-style:italic;margin:0 0 8px">
            &ldquo;Cast all your anxiety on him because he cares for you.&rdquo;
          </p>
          <span style="font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--red);font-weight:700">1 Peter 5:7</span>
        </div>
      </div>

      <!-- Right form -->
      <div data-aos="fade-left">
        <div class="chap-form-box">
          <h5 style="font-family:var(--fh);font-size:1.3rem;margin-bottom:6px;color:var(--dark)">Prayer Request Form</h5>
          <p style="font-size:.8rem;color:var(--muted);margin-bottom:24px">Your request is confidential and received with love.</p>

          <?php if ($msg): ?>
          <div class="alert-anu-<?=$msgType?> mb-4">
            <i class="bi bi-<?=$msgType==='success'?'check-circle-fill':'exclamation-circle-fill'?> me-2"></i><?=e($msg)?>
          </div>
          <?php endif; ?>

          <form method="POST" class="anu-form">
            <input type="hidden" name="prayer_submit" value="1">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label">Your Name <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                <input type="text" name="name" class="form-control" placeholder="Anonymous is welcome">
              </div>
              <div class="col-sm-6">
                <label class="form-label">Email <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                <input type="email" name="email" class="form-control" placeholder="For a follow-up from chaplain">
              </div>
              <div class="col-12">
                <label class="form-label">Your Prayer Request <span style="color:var(--red)">*</span></label>
                <textarea name="request" class="form-control" rows="5"
                          placeholder="Share what is on your heart. There is no request too big or too small..." required></textarea>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="private" id="privChk" checked>
                  <label class="form-check-label" for="privChk" style="font-size:.82rem;color:var(--muted)">
                    Keep my request private (shared only with the chaplain)
                  </label>
                </div>
              </div>
              <div class="col-12">
                <div style="font-size:.8rem;color:var(--muted);display:flex;gap:8px;align-items:flex-start;">
                  <input type="checkbox" name="consent" required style="margin-top:3px;flex-shrink:0;">
                  <label style="margin:0;font-weight:400;">I agree to ANU's chaplaincy team receiving and storing this request, per the <a href="/anu/privacy.php" target="_blank">Privacy Policy</a>.</label>
                </div>
              </div>
              <div class="col-12 mt-1">
                <button type="submit" class="btn-submit" style="width:100%">
                  <i class="bi bi-heart-fill me-2"></i>Submit Prayer Request
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ CONTACT CTA ══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <i class="bi bi-heart-fill" style="font-size:2.5rem;color:rgba(255,255,255,.2);display:block;margin-bottom:16px"></i>
    <h2 style="color:#fff;font-family:var(--fh);font-size:clamp(1.8rem,3.5vw,2.6rem)">
      You Are Not Alone
    </h2>
    <p style="color:rgba(255,255,255,.68);max-width:440px;margin:14px auto 0;line-height:1.75">
      Our chaplaincy is here for every student, at every stage of life. Reach out anytime.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="#prayer-form" class="btn-cta-w"><i class="bi bi-heart-fill me-2"></i>Submit Prayer Request</a>
      <a href="/anu/contact.php" class="btn-cta-o">Contact Us</a>
    </div>
  </div>
</section>

<?php renderFooter(); ?>
