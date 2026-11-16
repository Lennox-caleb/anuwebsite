<?php
/**
 * Privacy Policy and Data Protection Notice (Kenya Data Protection Act, 2019) for every ANU website.
 * Stand-alone on purpose (no dependencies) so the same file is used by the main site, every unit
 * site, the Non-Degree (TVET) site and ANU Online.
 *
 *   anu_legal_render('privacy', $ctx);   // Privacy Policy
 *   anu_legal_render('dp', $ctx);        // Data Protection Notice (rights, requests, complaints)
 *
 * $ctx keys (all optional): unit, address, phone, email_general, email_admissions, email_dpo,
 *                           privacy_url, dp_url, terms_url
 */
if (!function_exists('anu_legal_render')) {

    define('ANU_LEGAL_UPDATED', '20 October 2026');

    function anu_legal_render(string $doc, array $ctx = []): void
    {
        $e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $unit    = trim($ctx['unit'] ?? '') ?: 'Africa Nazarene University';
        $addr    = trim($ctx['address'] ?? '') ?: 'Ongata Rongai, Kajiado North, Kenya';
        $phone   = ''; // phone numbers are intentionally not shown on the legal pages
        $mail    = trim($ctx['email_general'] ?? '');
        $admMail = trim($ctx['email_admissions'] ?? '');
        $dpo     = 'odp@anu.ac.ke'; // Office of the Data Protection Officer (fixed)
        $pUrl    = $ctx['privacy_url'] ?? 'privacy.php';
        $dUrl    = $ctx['dp_url'] ?? 'data-protection.php';
        $tUrl    = $ctx['terms_url'] ?? '';
        $site    = ($unit === 'Africa Nazarene University') ? 'our websites' : 'this website (' . $unit . ') and our other university websites';
        ?>
<style>
.anu-legal{max-width:860px;margin:0 auto;line-height:1.75;color:#222}
.anu-legal h1{font-weight:800;margin-bottom:.4rem}
.anu-legal h2{font-size:1.3rem;font-weight:700;margin-top:2.4rem}
.anu-legal .anu-legal-meta{color:#666;margin-bottom:1.5rem}
.anu-legal ul{padding-left:1.2rem}.anu-legal li{margin-bottom:.35rem}
.anu-legal .anu-legal-box{background:#fff8df;border-left:5px solid #FAC823;border-radius:8px;padding:1rem 1.2rem;margin:1.5rem 0}
.anu-legal .anu-legal-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;margin:1.2rem 0}
.anu-legal .anu-legal-grid div{border:1px solid #e5e5e5;border-top:3px solid #FAC823;border-radius:8px;padding:1rem;background:#fff}
.anu-legal .anu-legal-grid b{display:block;margin-bottom:.25rem}
.anu-legal a{word-break:break-word}
</style>
<div class="anu-legal">
<?php if ($doc === 'dp'): ?>
  <h1>Data Protection Notice</h1>
  <p class="anu-legal-meta">Kenya Data Protection Act, 2019 &middot; Last updated: <?= $e(ANU_LEGAL_UPDATED) ?></p>

  <p>Africa Nazarene University ("ANU", "the University", "we") handles the personal data of applicants, students, alumni, staff,
  partners and visitors in line with the <strong>Data Protection Act, 2019</strong> of Kenya, the regulations made under it, and the
  guidance of the <strong>Office of the Data Protection Commissioner (ODPC)</strong>. This notice explains how we do that in practice and how
  you can use your rights. It applies to <?= $e($site) ?>. Our <a href="<?= $e($pUrl) ?>">Privacy Policy</a> describes the data we collect and why.</p>

  <h2>1. The principles we follow</h2>
  <p>Everything we do with personal data is guided by the data protection principles in the Act. Personal data must be:</p>
  <ul>
    <li>processed lawfully, fairly and in a transparent way;</li>
    <li>collected for a specified, explicit and legitimate purpose, and not used in a way that is incompatible with it;</li>
    <li>adequate, relevant and limited to what is necessary;</li>
    <li>accurate, and kept up to date;</li>
    <li>kept for no longer than necessary for the purpose it was collected for;</li>
    <li>kept secure, with appropriate technical and organisational measures; and</li>
    <li>transferred outside Kenya only where the Act's safeguards are met.</li>
  </ul>

  <h2>2. Your rights as a data subject</h2>
  <p>Under Section 26 of the Act, and related provisions, you have the right to:</p>
  <div class="anu-legal-grid">
    <div><b>Be informed</b>how your personal data is being used.</div>
    <div><b>Access</b>the personal data we hold about you.</div>
    <div><b>Object</b>to the processing of your personal data.</div>
    <div><b>Correct</b>inaccurate or misleading personal data.</div>
    <div><b>Delete</b>false or misleading data, or data we no longer need to keep.</div>
    <div><b>Withdraw consent</b>at any time, where we rely on your consent.</div>
    <div><b>Data portability</b>receive your data in a commonly used format, where the Act applies.</div>
    <div><b>Human review</b>of decisions made solely by automated means that significantly affect you.</div>
  </div>

  <h2>3. How to make a request</h2>
  <ol>
    <li>Write to our Data Protection contact at <a href="mailto:<?= $e($dpo) ?>?subject=Data%20subject%20request"><?= $e($dpo) ?></a>
        (subject line "Data subject request"), or post it to the address below.</li>
    <li>Tell us what you are asking for (for example access, correction or deletion), which university service or website your data is connected to,
        and any dates or reference numbers that help us find it.</li>
    <li>Include your name and the contact details you used with us. We may ask you to confirm your identity first, so that your data is never released to
        the wrong person.</li>
    <li>We will acknowledge your request and reply within the time limits set by the Act and its regulations. If we cannot do what you ask, for example because
        the law requires us to keep a record, we will explain why.</li>
  </ol>
  <div class="anu-legal-box">
    <strong>Data Protection contact</strong><br>
    Africa Nazarene University, <?= $e($addr) ?><br>
    Email: <a href="mailto:<?= $e($dpo) ?>"><?= $e($dpo) ?></a><?php if ($phone !== ''): ?><br>Phone: <a href="tel:<?= $e(preg_replace('/\s+/', '', $phone)) ?>"><?= $e($phone) ?></a><?php endif; ?>
  </div>

  <h2>4. Who sees your data</h2>
  <p>Access to personal data is limited to staff and service providers who need it to do their work, and who are bound by confidentiality. We do not sell personal
  data. Where we use a service provider (for example for email delivery or hosting), we require it to protect your data and to use it only on our instructions.</p>

  <h2>5. Sensitive personal data</h2>
  <p>Some information needs extra care, for example health information, religious beliefs, or information about a child. We collect it only when it is genuinely needed
  (for example to support a student's wellbeing or to provide pastoral care at your request) and handle it with stricter access controls.</p>

  <h2>6. Children</h2>
  <p>Our websites are not directed at children. Where an applicant is under 18, their personal data is processed with the involvement and consent of a parent or guardian,
  as the Act requires.</p>

  <h2>7. Security and data breaches</h2>
  <p>We protect personal data with access controls, secured accounts and encrypted password storage. If a breach occurs that is likely to put your rights at risk, we will
  report it to the Office of the Data Protection Commissioner and tell the people affected, within the time the Act requires, together with what they can do to protect themselves.</p>

  <h2>8. Transfers outside Kenya</h2>
  <p>We keep personal data in Kenya wherever possible. If data must be processed outside Kenya (for example by an email or cloud provider), we do so only where the Act's
  safeguards are met, such as adequate protection in the destination country or your consent where it is required.</p>

  <h2>9. Complaints</h2>
  <p>If you are not satisfied with how we have handled your data or your request, please tell us first so we can put it right. You also have the right to complain to the
  <strong>Office of the Data Protection Commissioner</strong>, Kenya: <a href="https://www.odpc.go.ke/" target="_blank" rel="noopener">www.odpc.go.ke</a>.</p>

  <p class="mt-5"><a href="<?= $e($pUrl) ?>">Read our Privacy Policy &rarr;</a><?php if ($tUrl !== ''): ?> &nbsp;&middot;&nbsp; <a href="<?= $e($tUrl) ?>">Terms &amp; Conditions &rarr;</a><?php endif; ?></p>

<?php else: ?>
  <h1>Privacy Policy</h1>
  <p class="anu-legal-meta">Last updated: <?= $e(ANU_LEGAL_UPDATED) ?></p>

  <p>Africa Nazarene University ("ANU", "the University", "we", "us") respects your privacy and is committed to protecting personal data in accordance with the
  <strong>Data Protection Act, 2019</strong> of Kenya and the regulations issued under it. This Privacy Policy explains what personal data we collect through
  <?= $e($site) ?>, why we collect it, how we use and protect it, and the rights you have over it. For how to use those rights, see our
  <a href="<?= $e($dUrl) ?>">Data Protection Notice</a>.</p>

  <h2>1. Who we are (data controller)</h2>
  <p>Africa Nazarene University is the data controller responsible for personal data collected through this website<?= $unit !== 'Africa Nazarene University' ? ' (' . $e($unit) . ')' : '' ?>.</p>
  <ul>
    <li>Address: <?= $e($addr) ?></li>
    <?php if ($phone !== ''): ?><li>Phone: <a href="tel:<?= $e(preg_replace('/\s+/', '', $phone)) ?>"><?= $e($phone) ?></a></li><?php endif; ?>
    <?php if ($mail !== ''): ?><li>General enquiries: <a href="mailto:<?= $e($mail) ?>"><?= $e($mail) ?></a></li><?php endif; ?>
    <?php if ($admMail !== ''): ?><li>Admissions enquiries: <a href="mailto:<?= $e($admMail) ?>"><?= $e($admMail) ?></a></li><?php endif; ?>
    <li>Data Protection contact: <a href="mailto:<?= $e($dpo) ?>"><?= $e($dpo) ?></a></li>
  </ul>

  <h2>2. Personal data we collect</h2>
  <p>Depending on how you use our websites, we may collect:</p>
  <ul>
    <li><strong>Contact and enquiry details</strong>: your name, email address, phone number and the content of your message, when you use a contact, enquiry or prayer-request form.</li>
    <li><strong>Application and admissions details</strong>: collected through our application portal, which has its own data notice.</li>
    <li><strong>Subscription details</strong>: your name and email address, when you subscribe to updates.</li>
    <li><strong>Event and programme details</strong>: for example names and contact details when you register for an event or request information about a programme.</li>
    <li><strong>Technical data</strong>: standard server logs (IP address, browser type, pages visited, date and time), collected automatically to keep the site secure and to understand how it is used.</li>
    <li><strong>Cookies and similar technologies</strong>: see Section 10.</li>
  </ul>
  <p>We ask only for what we need. Please do not send sensitive information (such as health details or identity numbers) through general contact forms unless we have asked for it.</p>

  <h2>3. Lawful basis and purpose</h2>
  <p>We process personal data only where the Act allows it, including:</p>
  <ul>
    <li><strong>Consent</strong>, for example when you submit an enquiry or subscribe to updates. You may withdraw consent at any time.</li>
    <li><strong>Performance of a contract</strong>, for example processing an application or enrolment.</li>
    <li><strong>Legal obligation</strong>, for example reporting to regulators such as the Commission for University Education (CUE).</li>
    <li><strong>Legitimate interests</strong>, for example responding to enquiries and keeping our websites secure, where these are not overridden by your rights.</li>
  </ul>

  <h2>4. How we use your data</h2>
  <p>We use personal data to respond to enquiries and requests, process admissions and applications, send updates you asked for, provide pastoral and student support where you
  request it, keep our websites secure, improve our services using aggregated information, and meet our legal obligations. We do not sell personal data, and we do not
  make decisions about you based solely on automated processing.</p>

  <h2>5. Sharing your data</h2>
  <p>We share personal data only with: staff and departments who need it to deal with your request; service providers that process data for us (such as email delivery and
  hosting) under confidentiality obligations; and regulators or public authorities where the law requires it.</p>
  <p>Our pages may also load content from third-party services, such as embedded videos (YouTube), web fonts, icon libraries and the WhatsApp chat link. When your browser loads
  them, those providers may receive technical data such as your IP address, under their own policies.</p>

  <h2>6. Transfers outside Kenya</h2>
  <p>Where personal data is processed outside Kenya, we do so only with the safeguards the Data Protection Act, 2019 requires.</p>

  <h2>7. How long we keep data</h2>
  <p>We keep personal data only as long as needed for the purpose it was collected for, or as required by law or University record-keeping policy. After that it is securely deleted or
  anonymised. Enquiries are kept only for as long as needed to respond and follow up.</p>

  <h2>8. Your rights</h2>
  <p>Under Section 26 of the Data Protection Act, 2019, you have the right to be informed how your data is used, to access it, to object to its processing, to have inaccurate
  data corrected, to have false or misleading data deleted, and to withdraw consent where processing is based on it. Our <a href="<?= $e($dUrl) ?>">Data Protection Notice</a>
  explains how to make a request.</p>

  <h2>9. Security</h2>
  <p>We use reasonable technical and organisational measures to protect personal data against unauthorised access, loss, misuse or alteration, including access controls, encrypted
  password storage and secure form submission. If a personal data breach occurs, we will respond as the Act requires, including notifying the Office of the Data Protection Commissioner
  and affected individuals where required.</p>

  <h2>10. Cookies</h2>
  <p>Our websites use a small number of cookies and browser storage: essential cookies for security and sign-in (for example session and form-protection cookies), and a preference
  remembering that you have seen our cookie notice. We do not use them to build advertising profiles. You can control or delete cookies in your browser settings at any time; the site
  will still work, although some features such as signing in may not.</p>
  <p><strong>Accessibility menu.</strong> If you use the Accessibility Menu (for example larger text or high contrast), your choices are saved in your browser only so that they apply on your next visit. They are not sent to us.</p>
  <p><strong>Chat and enquiry tools (HubSpot).</strong> Where we use HubSpot for live chat and enquiry forms, it is switched on only if you choose Accept in the cookie notice. HubSpot
  then sets cookies on your device and records your chat messages, the details you give us and the pages you visit, so that our staff can reply to you. If you choose Decline, HubSpot is
  not loaded. You can change your choice at any time with the Cookie settings link at the bottom of each page, and you can ask us to access or delete the chat data we hold about you
  (see the Data Protection Notice).</p>

  <h2>11. Children</h2>
  <p>Our websites are not directed at children. Where a prospective student is under 18, personal data is processed with the involvement and consent of a parent or guardian.</p>

  <h2>12. Complaints</h2>
  <p>If you believe your personal data has been mishandled, please contact our Data Protection contact (Section 1). You may also lodge a complaint with the
  <strong>Office of the Data Protection Commissioner (ODPC)</strong>, Kenya: <a href="https://www.odpc.go.ke/" target="_blank" rel="noopener">www.odpc.go.ke</a>.</p>

  <h2>13. Changes to this policy</h2>
  <p>We may update this policy to reflect changes in the law or our practices. The "Last updated" date at the top shows when it was last revised.</p>

  <p class="mt-5"><a href="<?= $e($dUrl) ?>">Data Protection Notice &rarr;</a><?php if ($tUrl !== ''): ?> &nbsp;&middot;&nbsp; <a href="<?= $e($tUrl) ?>">Terms &amp; Conditions &rarr;</a><?php endif; ?></p>
<?php endif; ?>
</div>
<?php
    }
}
