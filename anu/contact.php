<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();
$settings = getAllSettings();
$msg = ''; $msgType = 'success';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$subject || !$message) {
        $msg = 'Please fill in all required fields.'; $msgType = 'error';
    } elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please enter a valid email address.'; $msgType = 'error';
    } else {
        try {
            $filePath = null;
            if (!empty($_FILES['file']['name'])) $filePath = handleUpload('file','contact');
            $db->prepare("INSERT INTO contact_messages (name,email,subject,message,file_path,ip_address,submitted_at) VALUES (?,?,?,?,?,?,NOW())")
               ->execute([$name,$email,$subject,$message,$filePath,$_SERVER['REMOTE_ADDR']??'']);
            $msg = 'Thank you '.$name.'! Your message has been received. We\'ll respond within 24 hours.';
            $name=$email=$subject=$message='';
        } catch(Exception $e){ $msg='Something went wrong. Please try again.'; $msgType='error'; }
    }
}

$appMsg = ''; $appMsgType = 'success';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['apply_submit'])) {
    $aName  = trim($_POST['full_name'] ?? '');
    $aEmail = trim($_POST['email']     ?? '');
    $aPhone = trim($_POST['phone']     ?? '');
    $aProg  = trim($_POST['program']   ?? '');
    $aLevel = trim($_POST['level']     ?? '');
    $aMsg   = trim($_POST['message']   ?? '');
    if (!$aName || !$aEmail) { $appMsg='Name and email are required.'; $appMsgType='error'; }
    else {
        try {
            $db->prepare("INSERT INTO contact_messages (name,email,subject,message,ip_address,submitted_at) VALUES (?,?,?,?,?,NOW())")
               ->execute([$aName,$aEmail,'Application Inquiry: '.$aProg,"Level: $aLevel\nPhone: $aPhone\nMessage: $aMsg",$_SERVER['REMOTE_ADDR']??'']);
            $appMsg = 'Thank you '.$aName.'! Our admissions team will contact you within 48 hours.';
        } catch(Exception $e){ $appMsg='Something went wrong.'; $appMsgType='error'; }
    }
}

try {
require_once __DIR__ . '/includes/programs_lib.php';
$programs = array_column(prog_unique($db->query("SELECT name FROM programs WHERE is_active=1 ORDER BY school,name")->fetchAll()), 'name');
} catch(Exception $e) {}

$heroSlides = getSlides('contact');
$heroData   = !empty($heroSlides) ? $heroSlides[0] : getHero('contact');
$heroBg     = !empty($heroData['bg_image']) ? imgUrl($heroData['bg_image']) : '';

trackPage('contact', 'Contact');
renderHead('Contact Us'); renderNavbar('contact');
?>

<!-- ══ HERO ══ -->
<div class="cnt-hero" <?= $heroBg ? "style=\"background-image:url('{$heroBg}');background-size:cover;background-position:center\"" : '' ?>>
  <div class="cnt-hero-noise"></div>
  <div class="container cnt-hero-inner">
    <div class="row align-items-center g-5">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="cnt-hero-eyebrow"><span class="cnt-hero-dot"></span>We'd love to hear from you</div>
        <h1 class="cnt-hero-title">Get in <span style="color:var(--gold)">Touch</span></h1>
        <p class="cnt-hero-sub">Our admissions and support teams are ready to answer every question and guide you through your ANU Online journey.</p>
        <div class="cnt-hero-chips">
          <a href="tel:<?= preg_replace('/\D/','',$settings['phone']??'+254714785249') ?>" class="cnt-hero-chip">
            <i class="bi bi-telephone-fill"></i>
            <div><small>Call Us</small><strong><?= e($settings['phone']??'+254 714 785 249') ?></strong></div>
          </a>
          <a href="mailto:<?= e($settings['email']??'distancelearning@anu.ac.ke') ?>" class="cnt-hero-chip">
            <i class="bi bi-envelope-fill"></i>
            <div><small>Email Us</small><strong><?= e($settings['email']??'distancelearning@anu.ac.ke') ?></strong></div>
          </a>
          <div class="cnt-hero-chip">
            <i class="bi bi-clock-fill"></i>
            <div><small>Office Hours</small><strong>Mon–Fri, 8am–5pm EAT</strong></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <!-- Floating quick-action cards -->
        <div class="cnt-hero-actions">
          <?php foreach([
            ['bi-laptop-fill',       'var(--red)',    'Student Portal',    'Access eNaz LMS →',        'https://enaz.anu.ac.ke',             '_blank'],
            ['bi-life-preserver',    '#1565C0',       'IT Helpdesk',       'Open a support ticket →',  'https://enaz.anu.ac.ke/local/helpdesk/','_blank'],
            ['bi-mortarboard-fill',  '#2e7d32',       'Admissions',        'Start your application →', '/anu/programs.php',                  ''],
            ['bi-question-circle-fill','#6d4c41',     'FAQs',              'Browse common questions →', '/anu/faq.php',                       ''],
          ] as [$ic,$col,$ti,$sub,$href,$target]): ?>
          <a href="<?= e($href) ?>" <?= $target?"target=\"$target\"":'' ?> class="cnt-action-card">
            <div class="cnt-action-ico" style="background:<?=$col?>18;color:<?=$col?>">
              <i class="bi <?= $ic ?>"></i>
            </div>
            <div>
              <div class="cnt-action-title"><?= $ti ?></div>
              <div class="cnt-action-sub"><?= $sub ?></div>
            </div>
            <i class="bi bi-arrow-right cnt-action-arrow"></i>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ QUICK CONTACT BAR ══ -->
<div class="container" style="position:relative;z-index:10;margin-top:-1px">
  <div class="cnt-quickbar">
    <a href="tel:<?= preg_replace('/\D/','',$settings['phone']??'+254714785249') ?>" class="cnt-qbar-item">
      <div class="cnt-qbar-ico"><i class="bi bi-telephone-fill"></i></div>
      <div><div class="cnt-qbar-label">Call Now</div><div class="cnt-qbar-value"><?= e($settings['phone']??'+254 714 785 249') ?></div></div>
    </a>
    <a href="mailto:<?= e($settings['email']??'distancelearning@anu.ac.ke') ?>" class="cnt-qbar-item">
      <div class="cnt-qbar-ico"><i class="bi bi-envelope-fill"></i></div>
      <div><div class="cnt-qbar-label">Email</div><div class="cnt-qbar-value"><?= e($settings['email']??'distancelearning@anu.ac.ke') ?></div></div>
    </a>
    <a href="https://enaz.anu.ac.ke" target="_blank" class="cnt-qbar-item">
      <div class="cnt-qbar-ico"><i class="bi bi-laptop-fill"></i></div>
      <div><div class="cnt-qbar-label">Student Portal</div><div class="cnt-qbar-value">eNaz LMS</div></div>
    </a>
    <a href="https://wa.me/<?= preg_replace('/\D/','',$settings['phone']??'254714785249') ?>" target="_blank" class="cnt-qbar-item">
      <div class="cnt-qbar-ico" style="background:rgba(37,211,102,.1);color:#25D366"><i class="bi bi-whatsapp"></i></div>
      <div><div class="cnt-qbar-label">WhatsApp</div><div class="cnt-qbar-value">Chat With Us</div></div>
    </a>
  </div>
</div>

<!-- ══ MAIN CONTACT SECTION ══ -->
<section class="sec" id="contact-form">
  <div class="container">
    <div class="cnt-form-grid">

      <!-- Left: Form -->
      <div data-aos="fade-right">
        <div class="cnt-form-box">
          <div class="cnt-form-hdr">
            <div class="cnt-form-hdr-icon"><i class="bi bi-chat-dots-fill"></i></div>
            <div>
              <div class="sec-tag" style="margin-bottom:4px">Send a Message</div>
              <h2 class="sec-title mb-0">We're <span class="hl">Listening</span></h2>
            </div>
          </div>
          <p style="color:var(--muted);font-size:.85rem;margin-bottom:28px">Fill in the form and we'll respond within 24 hours on working days.</p>

          <?php if($msg): ?>
          <div class="alert-anu-<?= $msgType ?> mb-4">
            <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'exclamation-circle-fill' ?> me-2"></i><?= e($msg) ?>
          </div>
          <?php endif; ?>

          <!-- Subject quick-pick buttons -->
          <div class="cnt-subject-pills" style="margin-bottom:20px">
            <span style="font-size:.72rem;color:var(--muted);margin-bottom:8px;display:block;font-weight:600;text-transform:uppercase;letter-spacing:.8px">Quick topic:</span>
            <?php foreach(['Admissions Inquiry','Program Information','Fee Structure','Technical Support','General Inquiry'] as $s): ?>
            <button type="button" onclick="document.querySelector('[name=subject]').value='<?= $s ?>'"
                    class="cnt-subj-pill"><?= $s ?></button>
            <?php endforeach; ?>
          </div>

          <form method="POST" enctype="multipart/form-data" class="anu-form">
            <input type="hidden" name="contact_submit" value="1">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label">Full Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($name??'') ?>" placeholder="Your full name" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label">Email Address <span style="color:var(--red)">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($email??'') ?>" placeholder="you@example.com" required>
              </div>
              <div class="col-12">
                <label class="form-label">Subject <span style="color:var(--red)">*</span></label>
                <input type="text" name="subject" class="form-control" value="<?= e($subject??'') ?>" placeholder="How can we help?" required>
              </div>
              <div class="col-12">
                <label class="form-label">Message <span style="color:var(--red)">*</span></label>
                <textarea name="message" class="form-control" rows="5" placeholder="Write your message here…" required><?= e($message??'') ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Attachment <span style="color:var(--muted);font-weight:400">(optional, max 5MB)</span></label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.zip,image/*">
                <small style="color:var(--muted);font-size:.73rem">PDF, DOC, ZIP, Images accepted</small>
              </div>
              <div class="col-12">
                <div style="font-size:.8rem;color:var(--muted);display:flex;gap:8px;align-items:flex-start;">
                  <input type="checkbox" name="consent" required style="margin-top:3px;flex-shrink:0;">
                  <label style="margin:0;font-weight:400;">I agree to ANU processing the information above to respond to my enquiry, per the <a href="/anu/privacy.php" target="_blank">Privacy Policy</a>.</label>
                </div>
              </div>
              <div class="col-12 mt-1">
                <button type="submit" class="btn-submit" style="width:100%">
                  <i class="bi bi-send-fill me-2"></i>Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Right: Info + Map -->
      <div data-aos="fade-left">
        <div class="cnt-info-stack">
          <?php foreach([
            ['bi-geo-alt-fill',   'Address',      $settings['address']??'Africa Nazarene University, Ongata Rongai, Kenya','Visit us in person'],
            ['bi-telephone-fill', 'Phone',         $settings['phone']??'+254 714 785 249','Mon–Fri, 8am–5pm EAT'],
            ['bi-envelope-fill',  'Email',         $settings['email']??'distancelearning@anu.ac.ke','We reply within 24 hours'],
            ['bi-clock-fill',     'Office Hours',  'Monday – Friday','8:00 AM – 5:00 PM EAT'],
          ] as [$ic,$lb,$vl,$sub]): ?>
          <div class="cnt-info-card">
            <div class="cnt-info-ico"><i class="bi <?= $ic ?>"></i></div>
            <div>
              <div class="cnt-info-label"><?= $lb ?></div>
              <div class="cnt-info-val"><?= e($vl) ?></div>
              <div class="cnt-info-sub"><?= $sub ?></div>
            </div>
          </div>
          <?php endforeach; ?>

          <!-- Social -->
          <div style="padding:22px;background:#fff;border-radius:16px;border:1.5px solid rgba(0,0,0,.07)">
            <div class="cnt-info-label mb-3">Follow ANU Online</div>
            <div class="cnt-social-row">
              <?php foreach([
                ['bi bi-facebook','Facebook',$settings['facebook_url']??'#'],
                ['bi bi-twitter-x','X / Twitter',$settings['twitter_url']??'#'],
                ['bi bi-linkedin-fill','LinkedIn',$settings['linkedin_url']??'#'],
                ['bi bi-instagram','Instagram',$settings['instagram_url']??'#'],
                ['bi bi-youtube','YouTube',$settings['youtube_url']??'#'],
              ] as [$ic,$lb,$url]): ?>
              <a href="<?= e($url) ?>" target="_blank" title="<?= $lb ?>" class="cnt-social-btn"><i class="<?= $ic ?>"></i></a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Map -->
          <div style="border-radius:16px;overflow:hidden;border:1.5px solid rgba(0,0,0,.07)">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15955.303370215508!2d36.78480157460338!3d-1.3935735999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1220d44931b1%3A0x7f949ecab9202a3e!2sAfrica%20Nazarene%20University!5e0!3m2!1sen!2ske!4v1715685000000"
                    width="100%" height="240" style="border:0;display:block" allowfullscreen loading="lazy"></iframe>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ DEPT CONTACT CARDS ══ -->
<section class="sec" style="background:var(--light)">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <div class="sec-tag">Reach the Right Team</div>
      <h2 class="sec-title">Contact by <span class="hl">Department</span></h2>
      <p class="sec-sub mx-auto">Skip the queue — reach the team that can best help you directly.</p>
    </div>
    <div class="cnt-dept-grid">
      <?php foreach([
        ['bi-mortarboard-fill','#c1121f','Admissions',        'distancelearning@anu.ac.ke', 'Programs, entry requirements, application status'],
        ['bi-laptop-fill',     '#1565C0','IT / eNaz Support', 'ict@anu.ac.ke',              'Login issues, LMS access, system errors'],
        ['bi-cash-stack',      '#2e7d32','Finance & Fees',    'finance@anu.ac.ke',          'Tuition, M-Pesa payment, fee balances'],
        ['bi-book-fill',       '#6d4c41','Library Services',  'library@anu.ac.ke',          'E-resources, journals, borrowing'],
        ['bi-journal-text',    '#6a1b9a','Registry / Exams',  'registry@anu.ac.ke',         'Transcripts, grades, registration'],
        ['bi-heart-pulse-fill','#c1121f','Chaplaincy',        'chaplain@anu.ac.ke',         'Spiritual support and counselling'],
      ] as [$ic,$col,$title,$email,$desc]): ?>
      <a href="mailto:<?= e($email) ?>" class="cnt-dept-card" data-aos="fade-up">
        <div class="cnt-dept-ico" style="background:<?=$col?>14;color:<?=$col?>"><i class="bi <?= $ic ?>"></i></div>
        <div class="cnt-dept-title"><?= $title ?></div>
        <div class="cnt-dept-email"><?= e($email) ?></div>
        <p class="cnt-dept-desc"><?= $desc ?></p>
        <div class="cnt-dept-arrow"><i class="bi bi-envelope-fill me-1"></i>Send Email</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ APPLICATION INQUIRY ══ -->
<section class="sec">
  <div class="container">
    <div class="cnt-apply-split">
      <div data-aos="fade-right">
        <div class="sec-tag">Quick Inquiry</div>
        <h2 class="sec-title">Interested in <span class="hl">Applying?</span></h2>
        <div class="rule"></div>
        <p style="color:var(--muted);line-height:1.85">Submit a quick inquiry and our admissions team will walk you through everything — from entry requirements to enrollment. No entrance exam required.</p>
        <div class="cnt-apply-feats">
          <?php foreach([
            ['bi-lightning-fill',    'Fast Response',   'Our admissions team responds within 48 hours.'],
            ['bi-mortarboard-fill',  'Expert Guidance', 'Dedicated counsellors to help you choose the right program.'],
            ['bi-shield-check-fill', 'No Commitment',   'Inquiring doesn\'t bind you to anything — explore freely.'],
            ['bi-phone-fill',        'Flexible Contact','Reach us by email, phone, or WhatsApp.'],
          ] as [$ic,$ti,$tx]): ?>
          <div class="cnt-apply-feat">
            <i class="bi <?= $ic ?>"></i>
            <div><strong><?= $ti ?></strong><span><?= $tx ?></span></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div data-aos="fade-left">
        <div class="cnt-form-box">
          <h5 style="font-family:var(--fh);font-size:1.3rem;margin-bottom:6px">Application Inquiry</h5>
          <p style="font-size:.8rem;color:var(--muted);margin-bottom:22px">Let our admissions team guide you.</p>
          <?php if($appMsg): ?>
          <div class="alert-anu-<?= $appMsgType ?> mb-4">
            <i class="bi bi-<?= $appMsgType==='success'?'check-circle-fill':'exclamation-circle-fill' ?> me-2"></i><?= e($appMsg) ?>
          </div>
          <?php endif; ?>
          <form method="POST" class="anu-form">
            <input type="hidden" name="apply_submit" value="1">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Full Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="full_name" class="form-control" required placeholder="Your full name">
              </div>
              <div class="col-sm-6">
                <label class="form-label">Email <span style="color:var(--red)">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="you@example.com">
              </div>
              <div class="col-sm-6">
                <label class="form-label">Phone / WhatsApp</label>
                <input type="tel" name="phone" class="form-control" placeholder="+254 7XX XXX XXX">
              </div>
              <div class="col-12">
                <label class="form-label">Program of Interest</label>
                <select name="program" class="form-select">
                  <option value="">Select a program…</option>
                  <?php foreach($programs as $p): ?>
                  <option value="<?= e($p) ?>"><?= e($p) ?></option>
                  <?php endforeach; ?>
                  <option value="Other">Other / Not sure yet</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Current Education Level</label>
                <select name="level" class="form-select">
                  <option value="">Select level…</option>
                  <option>KCSE / O-Level</option>
                  <option>A-Level / Diploma</option>
                  <option>Bachelor's Degree</option>
                  <option>Master's Degree</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Additional Message</label>
                <input type="text" name="message" class="form-control" placeholder="Any questions or special circumstances?">
              </div>
              <div class="col-12">
                <div style="font-size:.8rem;color:var(--muted);display:flex;gap:8px;align-items:flex-start;">
                  <input type="checkbox" name="consent" required style="margin-top:3px;flex-shrink:0;">
                  <label style="margin:0;font-weight:400;">I agree to ANU processing the information above to follow up on my application enquiry, per the <a href="/anu/privacy.php" target="_blank">Privacy Policy</a>.</label>
                </div>
              </div>
              <div class="col-12 mt-1">
                <button type="submit" class="btn-submit" style="width:100%">
                  <i class="bi bi-send-fill me-2"></i>Submit Inquiry
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-band">
  <div class="container text-center" style="position:relative;z-index:1">
    <h2 style="color:#fff;font-family:var(--fh);font-size:clamp(2rem,4vw,2.8rem)">Ready to Begin Your Journey?</h2>
    <p style="color:rgba(255,255,255,.68);max-width:440px;margin:14px auto 0;line-height:1.75">Join thousands of students transforming their lives through ANU Online education.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
      <a href="/anu/programs.php" class="btn-cta-w">Browse Programs <i class="bi bi-arrow-right"></i></a>
      <a href="/anu/faq.php"      class="btn-cta-o">Read FAQs</a>
    </div>
  </div>
</section>

<?php renderFooter(); ?>
