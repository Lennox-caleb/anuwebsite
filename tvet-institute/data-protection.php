<?php
$pageTitle = 'Data Protection Notice';
require_once __DIR__ . '/includes/functions.php';
$pageDescription = "Your rights under Kenya's Data Protection Act, 2019, and how to use them.";
require_once __DIR__ . '/includes/legal_content.php';
require __DIR__ . '/includes/header.php';
?>
<section class="tvet-hero compact">
  <div class="container">
    <div class="eyebrow mb-2">Legal</div>
    <h1>Data Protection Notice</h1>
  </div>
</section>
<section class="tvet-section">
  <div class="container">
    <?php anu_legal_render('dp', [
        'unit' => 'Africa Nazarene University - Non-Degree Programmes', 'address' => setting('address'), 'phone' => setting('phone_primary'),
        'email_general' => setting('email_marketing'), 'email_admissions' => setting('email_admissions'), 'email_dpo' => setting('email_dpo', 'dataprotection@anu.ac.ke'),
        'privacy_url' => BASE_URL . '/privacy-policy.php', 'dp_url' => BASE_URL . '/data-protection.php', 'terms_url' => BASE_URL . '/terms-conditions.php']); ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
