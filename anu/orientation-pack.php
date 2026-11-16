<?php
// Orientation for ANU Online: same content and structure as the university site's Orientation page.
// Differences for ANU Online: the welcome is from the Director (not the Vice Chancellor) and the
// meeting link / details are set by the admin (Admin -> Orientation).
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$s = getAllSettings();
$packUrl  = getSetting('orientation_pack_url', '#');
$packReady = $packUrl && $packUrl !== '#';
$meetingLink    = trim(getSetting('orientation_meeting_url', ''));
$meetingDetails = trim(getSetting('orientation_meeting_details', ''));
$applyUrl = $s['apply_url'] ?? 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp';
$phone = $s['contact_phone'] ?? '';
$mail  = 'distancelearning@anu.ac.ke';
$dir = getDirector();

$journey = [
  ['Pick your programme', 'Browse programmes across the five schools, Non-Degree, ANU Online and Short Courses.', 'bi-search'],
  ['Create your application', 'Apply through the online records portal and pay the application fee.', 'bi-pencil-square'],
  ['Upload your documents', 'Submit transcripts, ID and a passport photo.', 'bi-cloud-arrow-up'],
  ['Await your admission letter', 'Feedback is usually given within 5 working days.', 'bi-envelope-check'],
  ['Confirm your place', 'Follow the fee payment guide in your admission letter to secure your place.', 'bi-patch-check'],
];
$steps = [
  ['Online Registration', 'Log in to the student portal, complete your registration, and activate your student account and email.'],
  ['Meet the Learning Platform', 'A guided walkthrough of eNaz, your online classroom, so you know where to find units, assignments and results.'],
  ['Meet Your School', 'Introductions with your Dean, lecturers, and academic advisor, plus an overview of your programme.'],
  ['Digital Library & Support', 'Learn how to use the online library, the repository, and how to reach the ICT and student support teams.'],
  ['Student Life & Community', 'Discover online clubs, societies and student activities, and how to take part from wherever you are.'],
  ['Online Chapel & Fellowship', 'A welcome devotion introducing ANU\'s Nazarene faith community and its online ministry life.'],
];
$checklist = [
  ['bi-card-text', 'Admission & Fees Documents', 'Your admission letter, ID or passport copy, and proof of fees payment, ready to upload.'],
  ['bi-laptop', 'A Reliable Device', 'A laptop, tablet or smartphone that can run a web browser and join video meetings.'],
  ['bi-wifi', 'Internet Access', 'A stable internet connection or data bundle for classes, meetings and downloads.'],
  ['bi-envelope-at', 'A Working Email Address', 'An email you check often, so you do not miss notices from your school.'],
  ['bi-journal-bookmark', 'Notebook & Pen', 'Something to take notes with during live sessions and orientation meetings.'],
  ['bi-camera', 'Passport Photo', 'A recent passport-size photo (digital copy) for your student ID and registration.'],
];
$faqs = [
  ['When is Orientation?', 'Your exact date and time are on your admission letter and in the orientation meeting details on this page, once confirmed.'],
  ['How do I join the orientation meeting?', 'Use the Join Orientation Meeting button in the meeting details section of this page at the scheduled time. You can join from any device with internet.'],
  ['Do I need to come to campus?', 'No. ANU Online is a virtual campus, so orientation, classes and support all happen online.'],
  ['Who do I contact if I have questions?', 'Reach out using the contacts below, or the relevant office (admissions, ICT support, or your school).'],
];

trackPage('orientation', 'Orientation');
renderHead('Orientation'); renderNavbar('orientation');
?>
<section class="or-hero" id="sec-a">
  <div class="container text-center">
    <span class="or-eyebrow">New Students</span>
    <h1>Orientation</h1>
    <p>Everything you need to prepare for orientation at ANU Online, what you need, what to expect, and who to ask when you need help.</p>
    <div class="or-cta">
      <a href="#sec-b" class="btn-hero-solid">See how the week flows</a>
      <a href="#sec-c" class="or-ghost">What you need</a>
      <?php if ($meetingLink): ?><a href="<?= e($meetingLink) ?>" target="_blank" rel="noopener" class="or-ghost"><i class="bi bi-camera-video"></i> Join the meeting</a><?php endif; ?>
    </div>
  </div>
</section>

<nav class="or-subnav" aria-label="Orientation sections">
  <div class="container">
    <a href="#sec-a"><span>A</span> Welcome</a><a href="#sec-b"><span>B</span> Schedule</a><a href="#sec-c"><span>C</span> What You Need</a>
    <a href="#sec-d"><span>D</span> Meeting Details</a><a href="#sec-e"><span>E</span> Documents</a><a href="#sec-f"><span>F</span> FAQ</a><a href="#sec-g"><span>G</span> Need Help</a>
  </div>
</nav>

<div class="container or-narrow">
  <div class="or-quick">
    <span><i class="bi bi-laptop"></i> 100% online, join from anywhere</span>
    <span><i class="bi bi-calendar-check"></i> Dates confirmed on your admission letter</span>
    <?php if ($phone): ?><span><i class="bi bi-telephone"></i> <a href="tel:<?= e($phone) ?>"><?= e($phone) ?></a></span><?php endif; ?>
  </div>
</div>

<section class="sec">
  <div class="container or-narrow">
    <div class="or-message">
      <div class="or-message-photo"><img src="<?= e($dir['photo']) ?>" alt="<?= e($dir['name']) ?>" onerror="this.closest('.or-message').classList.add('no-photo');this.parentNode.remove()"></div>
      <div class="or-message-body">
        <i class="bi bi-quote or-quote"></i>
        <span class="or-eyebrow dark">A Word from the Director</span>
        <?php
          $full = $dir['bio'] !== '' ? trim($dir['bio']) : 'Welcome to the ANU family! Orientation is designed to help you settle in, find your way around the online campus, and meet the staff and fellow students who will walk this journey with you.';
          $short = $full; $cut = false;
          if (preg_match_all('/\S+/u', $full, $m, PREG_OFFSET_CAPTURE) && count($m[0]) > 55) { $short = rtrim(substr($full, 0, $m[0][55][1]), " ,.;:-") . '…'; $cut = true; }
        ?>
        <p class="or-msg"><?= nl2br(e($short)) ?></p>
        <?php if ($cut): ?><a href="/anu/directors-message.php" class="or-more">Read more <i class="bi bi-arrow-right"></i></a><?php endif; ?>
        <p class="mb-0 mt-3"><strong><?= $dir['name'] !== '' ? e($dir['name']) : 'Office of the Director' ?></strong><br>
        <span class="text-muted small"><?= $dir['title'] !== '' ? e($dir['title']) : 'Director, ANU Online, Africa Nazarene University' ?></span></p>
      </div>
    </div>
  </div>
</section>

<section class="sec bg-light" id="sec-journey">
  <div class="container">
    <div class="or-head"><h2>Your Journey to ANU</h2><a href="/anu/contact.php">Need help applying? &rarr;</a></div>
    <div class="or-journey">
      <?php foreach ($journey as $i => [$t,$x,$ic]): ?>
        <a class="or-jcard" href="<?= e($applyUrl) ?>" target="_blank" rel="noopener">
          <span class="or-jnum"><?= $i+1 ?></span><i class="bi <?= $ic ?>"></i><h3><?= e($t) ?></h3><p><?= e($x) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4"><a href="<?= e($applyUrl) ?>" target="_blank" rel="noopener" class="btn-hero-solid">Start Your Application <i class="bi bi-arrow-right"></i></a></div>
  </div>
</section>

<section class="sec" id="sec-b">
  <div class="container or-narrow">
    <div class="or-head"><h2>How Orientation Flows</h2></div>
    <p class="text-muted mb-4">A typical orientation looks like this. Your admission letter and the Dean of Students Office will confirm the dates and times for your intake.</p>
    <div class="or-steps">
      <?php foreach ($steps as $i => [$t,$x]): ?>
        <div class="or-step"><span class="or-step-num"><?= $i+1 ?></span><div><h3><?= e($t) ?></h3><p><?= e($x) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec bg-light" id="sec-c">
  <div class="container or-narrow">
    <div class="or-head"><h2>What You Need</h2></div>
    <div class="or-progress" id="orProgress" hidden>
      <div class="or-bar"><span id="orFill"></span></div><span id="orText" aria-live="polite"></span>
      <button type="button" class="or-mini" id="orReset">Reset</button>
      <button type="button" class="or-mini" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
    <div class="or-grid">
      <?php foreach ($checklist as $i => [$ic,$t,$x]): $n=$i+1; ?>
        <label class="or-check" for="chk<?= $n ?>">
          <input type="checkbox" id="chk<?= $n ?>" data-or-check="<?= $n ?>" aria-label="I have this ready">
          <span class="or-icon"><i class="bi <?= e($ic) ?>"></i></span>
          <h3><?= e($t) ?></h3><p><?= e($x) ?></p>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec" id="sec-d">
  <div class="container or-narrow">
    <div class="or-head"><h2>Orientation Meeting Details</h2></div>
    <?php if ($meetingLink || $meetingDetails): ?>
      <div class="or-meeting">
        <span class="or-meeting-icon"><i class="bi bi-camera-video"></i></span>
        <div class="or-meeting-body"><?php if ($meetingDetails): ?><p><?= nl2br(e($meetingDetails)) ?></p><?php endif; ?></div>
        <?php if ($meetingLink): ?><a href="<?= e($meetingLink) ?>" target="_blank" rel="noopener" class="btn-hero-solid"><i class="bi bi-camera-video"></i> Join Orientation Meeting</a><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-4"><i class="bi bi-camera-video" style="font-size:2.2rem;color:#ccc"></i>
        <p class="text-muted mt-3 mb-0">The orientation meeting link and schedule will be published here once confirmed by the Dean of Students Office.</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="sec bg-light" id="sec-e">
  <div class="container or-narrow">
    <div class="or-head"><h2>Orientation Documents</h2></div>
    <?php if ($packReady): ?>
      <div class="or-meeting">
        <span class="or-meeting-icon"><i class="bi bi-file-earmark-pdf"></i></span>
        <div class="or-meeting-body"><p><strong>Orientation guide</strong><br>Registration steps, the academic calendar, portal access guide and more.</p></div>
        <a href="<?= e($packUrl) ?>" target="_blank" rel="noopener" class="btn-hero-solid"><i class="bi bi-download"></i> Download Orientation</a>
      </div>
    <?php else: ?>
      <div class="text-center py-4"><i class="bi bi-file-earmark-text" style="font-size:2.2rem;color:#ccc"></i>
        <p class="text-muted mt-3 mb-0">Orientation documents will appear here once uploaded. <a href="/anu/contact.php">Contact us</a> and we will send them to you directly.</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="sec" id="sec-f">
  <div class="container or-narrow">
    <div class="or-head"><h2>Frequently Asked Questions</h2></div>
    <input type="search" id="orFaqSearch" class="form-control mb-3" placeholder="Search the questions&hellip;" aria-label="Search the FAQ">
    <div class="accordion or-faq" id="orFaq">
      <?php foreach ($faqs as $i => [$q,$a]): ?>
        <div class="accordion-item">
          <h3 class="accordion-header"><button class="accordion-button <?= $i>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>"><?= e($q) ?></button></h3>
          <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>" data-bs-parent="#orFaq"><div class="accordion-body"><?= e($a) ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec bg-light" id="sec-g">
  <div class="container or-narrow text-center">
    <div class="or-head center"><h2>Need Help?</h2></div>
    <p class="text-muted">Our team is ready to help you get settled.</p>
    <p class="mb-4">
      <?php if ($phone): ?><i class="bi bi-telephone"></i> <a href="tel:<?= e($phone) ?>"><?= e($phone) ?></a> <span class="mx-2">|</span><?php endif; ?>
      <i class="bi bi-envelope"></i> <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>
    </p>
    <a href="/anu/student-life.php" class="btn-ghost-red me-2">Student Life &rarr;</a>
    <a href="/anu/contact.php" class="btn-ghost-red">Contact Us &rarr;</a>
  </div>
</section>
<script>
(function(){
  var KEY='anuOnlineOrientation', boxes=[].slice.call(document.querySelectorAll('[data-or-check]'));
  var wrap=document.getElementById('orProgress'), fill=document.getElementById('orFill'), txt=document.getElementById('orText');
  function load(){ try{ return JSON.parse(localStorage.getItem(KEY)||'{}'); }catch(e){ return {}; } }
  function save(o){ try{ localStorage.setItem(KEY,JSON.stringify(o)); }catch(e){} }
  function paint(){ var n=boxes.filter(function(b){return b.checked}).length; wrap.hidden=false; fill.style.width=(n/boxes.length*100)+'%';
    txt.textContent=n+' of '+boxes.length+' ready'; boxes.forEach(function(b){ b.closest('.or-check').classList.toggle('done',b.checked); }); }
  var st=load(); boxes.forEach(function(b){ b.checked=!!st[b.dataset.orCheck]; b.addEventListener('change',function(){ var o=load(); o[b.dataset.orCheck]=b.checked; save(o); paint(); }); });
  document.getElementById('orReset').addEventListener('click',function(){ boxes.forEach(function(b){b.checked=false}); save({}); paint(); });
  paint();
  var q=document.getElementById('orFaqSearch');
  q.addEventListener('input',function(){ var v=q.value.toLowerCase(); document.querySelectorAll('#orFaq .accordion-item').forEach(function(it){ it.style.display=it.textContent.toLowerCase().indexOf(v)>-1?'':'none'; }); });
})();
</script>
<?php renderFooter(); ?>
