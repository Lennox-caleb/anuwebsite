<?php
$pageTitle = 'Terms & Conditions';
$pageDescription = 'Terms and conditions governing the use of the Africa Nazarene University website.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container container-narrow">
    <h1>Terms &amp; Conditions</h1>
    <p class="text-muted">Last updated: <?= date('j F Y') ?></p>

    <p>These Terms &amp; Conditions govern your use of <?= h(setting('site_name')) ?>'s website
    (the "Site"). By accessing or using the Site, you agree to be bound by these terms. If you do not
    agree, please do not use the Site.</p>

    <h2 class="h4 mt-5">1. Use of the Site</h2>
    <p>You may browse the Site and use its forms (enquiry, application referral, newsletter subscription)
    for lawful purposes only. You agree not to misuse the Site, attempt unauthorised access to any
    university system, or submit false or misleading information through any form.</p>

    <h2 class="h4 mt-5">2. Information Accuracy</h2>
    <p>We take reasonable care to keep programme information, fees, dates and contact details up to
    date, but the University does not guarantee that all content is complete, current or error-free.
    Where this Site conflicts with an official University communication, admission letter or gazette
    notice, the official communication takes precedence. Programme details, fee structures and intake
    dates are confirmed at the point of application and enrolment.</p>

    <h2 class="h4 mt-5">3. Admissions and Applications</h2>
    <p>Applications submitted through our application portal are subject to the University's admissions
    policies and requirements, which may be updated from time to time. Submission of an application does
    not guarantee admission.</p>

    <h2 class="h4 mt-5">4. Intellectual Property</h2>
    <p>All content on this Site, including text, graphics, logos and the University crest, 
    is the property of Africa Nazarene University or its licensors and is protected by Kenyan and
    international intellectual property law. You may view and print content for personal, non-commercial
    reference, but may not reproduce, distribute or modify it without prior written permission.</p>

    <h2 class="h4 mt-5">5. Third-Party Links</h2>
    <p>This Site links to external services operated by the University's own schools and units, as well
    as to third-party platforms (e.g. social media, the student portal, the application system). We are
    not responsible for the content or privacy practices of external sites.</p>

    <h2 class="h4 mt-5">6. Privacy and Data Protection</h2>
    <p>Our collection and use of personal data through this Site is described in our
    <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>, issued in line with the Data Protection
    Act, 2019.</p>

    <h2 class="h4 mt-5">7. Limitation of Liability</h2>
    <p>To the extent permitted by law, Africa Nazarene University shall not be liable for any indirect
    or consequential loss arising from your use of, or inability to use, this Site.</p>

    <h2 class="h4 mt-5">8. Governing Law</h2>
    <p>These Terms &amp; Conditions are governed by the laws of the Republic of Kenya. Any dispute
    arising from use of this Site shall be subject to the exclusive jurisdiction of the Kenyan courts.</p>

    <h2 class="h4 mt-5">9. Changes to These Terms</h2>
    <p>We may revise these Terms &amp; Conditions from time to time. Continued use of the Site after a
    change is posted constitutes acceptance of the revised terms.</p>

    <h2 class="h4 mt-5">10. Contact Us</h2>
    <p>Questions about these terms can be sent to
    <a href="mailto:<?= h(setting('email_marketing')) ?>"><?= h(setting('email_marketing')) ?></a>.</p>

    <p class="mt-5"><a href="<?= BASE_URL ?>/privacy.php">See also our Privacy Policy &rarr;</a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
