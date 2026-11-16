<?php
$pageTitle = 'Data Protection Notice';
$pageDescription = "Your rights under Kenya's Data Protection Act, 2019, and how to use them at Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/legal_content.php';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <?php anu_legal_render('dp', [
        'address' => setting('address'), 'phone' => setting('phone_primary'), 'email_general' => setting('email_marketing'),
        'email_admissions' => setting('email_admissions'), 'email_dpo' => setting('email_dpo', 'dataprotection@anu.ac.ke'),
        'privacy_url' => BASE_URL . '/privacy.php', 'dp_url' => BASE_URL . '/data-protection.php', 'terms_url' => BASE_URL . '/terms.php']); ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
