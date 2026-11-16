<?php
$pageTitle = 'Privacy Policy';
require_once __DIR__ . '/includes/functions.php';
$pageDescription = 'How Africa Nazarene University Non-Degree Programmes collects, uses and protects personal data, in line with the Data Protection Act, 2019 (Kenya).';
require_once __DIR__ . '/includes/legal_content.php';
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact">
  <div class="container">
    <div class="eyebrow mb-2">Legal</div>
    <h1>Privacy Policy</h1>
  </div>
</section>
<section class="tvet-section">
  <div class="container">
    <?php anu_legal_render('privacy', [
        'unit' => 'Africa Nazarene University - Non-Degree Programmes', 'address' => setting('address'), 'phone' => setting('phone_primary'),
        'email_general' => setting('email_marketing'), 'email_admissions' => setting('email_admissions'), 'email_dpo' => setting('email_dpo', 'dataprotection@anu.ac.ke'),
        'privacy_url' => BASE_URL . '/privacy-policy.php', 'dp_url' => BASE_URL . '/data-protection.php', 'terms_url' => BASE_URL . '/terms-conditions.php']); ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
