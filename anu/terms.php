<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$s = getAllSettings();
$contactEmail = $s['contact_email'] ?? 'distancelearning@anu.ac.ke';

trackPage('terms', 'Terms of Use');
renderHead('Terms of Use');
renderNavbar('');
?>

<section style="padding:70px 0 30px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        <div class="mb-4">
          <div class="sec-tag">Legal</div>
          <h1 style="font-family:'Roboto',Arial,sans-serif;font-weight:700;font-size:clamp(1.8rem,3.5vw,2.6rem);">Terms of Use</h1>
          <p style="color:var(--muted);font-size:.9rem;">Last updated: <?= date('F j, Y') ?> &middot; Applies to anu.ac.ke and all ANU Online / ODEL pages</p>
        </div>

        <div style="background:#fff8f0;border:1px solid rgba(250,200,35,.35);border-radius:12px;padding:18px 22px;margin-bottom:32px;font-size:.88rem;color:#6b4e00;">
          <strong>Template notice:</strong> This is a general-purpose terms page suitable for a university's marketing/informational website. It does not cover academic regulations, examination rules, or the student code of conduct — those should remain in ANU's official student handbook and admission agreements, referenced here rather than duplicated.
        </div>

        <div style="line-height:1.85;color:#333;">

          <h4 class="mt-4" style="font-weight:700;">1. Acceptance of these terms</h4>
          <p>By accessing or using this website, you agree to these Terms of Use. If you do not agree, please do not use the site.</p>

          <h4 class="mt-4" style="font-weight:700;">2. Purpose of this site</h4>
          <p>This website provides information about Africa Nazarene University's Open, Distance &amp; e-Learning (ODEL) programs, campus life, chaplaincy, news, and support resources, and allows prospective and current students to make enquiries, request information, and register for events. It is informational and does not itself constitute enrolment — formal admission is handled through ANU's official application and student portal (records.anu.ac.ke).</p>

          <h4 class="mt-4" style="font-weight:700;">3. Accuracy of information</h4>
          <p>We make reasonable efforts to keep program details, fees, and dates up to date, but this site is not a substitute for the official program handbook, fee structure, or academic calendar. Where this website and an official ANU document disagree, the official document governs.</p>

          <h4 class="mt-4" style="font-weight:700;">4. Acceptable use</h4>
          <p>You agree not to:</p>
          <ul>
            <li>Submit false, misleading, or fraudulent information through any form on this site;</li>
            <li>Attempt to gain unauthorized access to the admin panel, database, or any restricted area;</li>
            <li>Use automated tools to scrape, spam, or overload the site's forms or infrastructure;</li>
            <li>Upload malicious files through the contact/attachment form;</li>
            <li>Use this site to harass, defame, or transmit unlawful content.</li>
          </ul>

          <h4 class="mt-4" style="font-weight:700;">5. Intellectual property</h4>
          <p>The ANU name, crest, and logo are the property of Africa Nazarene University. Text, images, and design on this site may not be reproduced for commercial purposes without written permission. You may share links to this site freely.</p>

          <h4 class="mt-4" style="font-weight:700;">6. Third-party links and portals</h4>
          <p>This site links to external systems, including the Student Portal (records.anu.ac.ke) and the eNaz e-learning platform (enaz.anu.ac.ke), which have their own terms and login requirements. ANU is not responsible for the availability of third-party services (e.g. WhatsApp) linked from this site.</p>

          <h4 class="mt-4" style="font-weight:700;">7. No warranty</h4>
          <p>This site is provided "as is." While we aim for accuracy and uptime, we do not guarantee the site will be error-free, uninterrupted, or free of technical issues.</p>

          <h4 class="mt-4" style="font-weight:700;">8. Limitation of liability</h4>
          <p>To the extent permitted by Kenyan law, ANU is not liable for indirect or consequential loss arising from your use of this website. Nothing in these terms limits liability that cannot lawfully be excluded.</p>

          <h4 class="mt-4" style="font-weight:700;">9. Governing law</h4>
          <p>These terms are governed by the laws of the Republic of Kenya.</p>

          <h4 class="mt-4" style="font-weight:700;">10. Privacy</h4>
          <p>Your use of this site is also governed by our <a href="/anu/privacy.php">Privacy Policy</a>, which explains how we handle personal data you submit.</p>

          <h4 class="mt-4" style="font-weight:700;">11. Contact</h4>
          <p>Questions about these terms can be sent to <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>.</p>

        </div>
      </div>
    </div>
  </div>
</section>

<?php renderFooter(); ?>
