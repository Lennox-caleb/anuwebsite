<?php
$pageTitle = 'Terms & Conditions';
require_once __DIR__ . '/includes/functions.php';
$pageDescription = 'Terms and conditions for using the Africa Nazarene University Non-Degree Programmes website and applying for admission.';
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact">
  <div class="container">
    <div class="eyebrow mb-2">Legal</div>
    <h1>Terms &amp; Conditions</h1>
    <p class="lead">Last updated: <?= date('d F Y') ?></p>
  </div>
</section>

<section class="tvet-section">
  <div class="container container-narrow">

    <p>These Terms &amp; Conditions govern your use of this website and any application for admission to Non-Degree Programmes at Africa Nazarene University ("ANU", "we", "us", "our"). By using this website or submitting an application, you agree to be bound by these terms. If you do not agree, please do not use this website.</p>

    <h2 class="h4 mt-5">1. Use of This Website</h2>
    <p>This website is provided for the purpose of informing prospective and current students about Non-Degree Programmes, and for submitting enquiries and applications. You agree to use this website only for lawful purposes and not to attempt to gain unauthorised access to any part of the site, its administrative systems, or the data of other users.</p>

    <h2 class="h4 mt-5">2. Accuracy of Information Provided</h2>
    <p>When submitting an application or enquiry, you confirm that all information provided is true, accurate and complete to the best of your knowledge. Submission of false, misleading or fraudulent information may result in disqualification from the admissions process or, if already admitted, disciplinary action including discontinuation of studies.</p>

    <h2 class="h4 mt-5">3. Admissions</h2>
    <p>Submission of an online application does not guarantee admission. All applications are subject to review against the published entry requirements and available capacity for each programme. ANU reserves the right to request additional documentation, conduct interviews or assessments, and to accept or decline any application at its discretion.</p>

    <h2 class="h4 mt-5">4. Programme Information &amp; Fees</h2>
    <p>Programme details, intakes, durations and indicative fees published on this website are provided in good faith and are subject to change without prior notice. Applicants and students should confirm current programme details and fee structures directly with the Admissions or Finance Office before making any payment or commitment.</p>

    <h2 class="h4 mt-5">5. Intellectual Property</h2>
    <p>All content on this website, including text, graphics, logos and the Africa Nazarene University name and crest, is the property of Africa Nazarene University or its licensors and is protected by applicable intellectual property laws. You may not reproduce, distribute or otherwise use this content for commercial purposes without our prior written consent.</p>

    <h2 class="h4 mt-5">6. Third-Party Links</h2>
    <p>This website may contain links to third-party websites (such as the ANU main site, eNaz Portal, or Student Portal) for your convenience. We are not responsible for the content, accuracy or privacy practices of any third-party website.</p>

    <h2 class="h4 mt-5">7. Limitation of Liability</h2>
    <p>While we take reasonable care to ensure the information on this website is accurate and up to date, ANU makes no warranties, express or implied, as to the completeness or accuracy of any content, and shall not be liable for any loss or damage arising from your use of, or reliance on, this website, to the fullest extent permitted by law.</p>

    <h2 class="h4 mt-5">8. Data Protection</h2>
    <p>Our collection and use of personal data submitted through this website is governed by our <a href="<?= BASE_URL ?>/privacy-policy.php">Privacy Policy</a>, prepared in accordance with the Data Protection Act, 2019 of Kenya.</p>

    <h2 class="h4 mt-5">9. Changes to These Terms</h2>
    <p>We may revise these Terms &amp; Conditions from time to time. The "last updated" date at the top of this page indicates when they were last revised. Continued use of this website after any changes constitutes acceptance of the revised terms.</p>

    <h2 class="h4 mt-5">10. Governing Law</h2>
    <p>These Terms &amp; Conditions are governed by and construed in accordance with the laws of the Republic of Kenya. Any disputes arising from the use of this website shall be subject to the exclusive jurisdiction of the courts of Kenya.</p>

    <h2 class="h4 mt-5">11. Contact Us</h2>
    <p>For any questions about these Terms &amp; Conditions, contact:</p>
    <p class="mb-1"><i class="bi bi-envelope text-red"></i> <a href="mailto:<?= h(setting('email_general')) ?>"><?= h(setting('email_general')) ?></a></p>
    <p class="mb-1"><i class="bi bi-telephone text-red"></i> <?= h(setting('phone_primary')) ?></p>
    <p><i class="bi bi-geo-alt text-red"></i> <?= h(setting('address')) ?></p>

    <p class="text-muted small mt-5">See also our <a href="<?= BASE_URL ?>/privacy-policy.php">Privacy Policy</a>.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
