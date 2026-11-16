<?php
$pageTitle = 'Data Protection Notice';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/../main-site/includes/legal_content.php';
require __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <?php anu_legal_render('dp', [
        'unit' => 'Africa Nazarene University - SEPC', 'address' => main_setting('address'), 'phone' => main_setting('phone_primary'), 'email_general' => main_setting('email_marketing'),
        'email_admissions' => main_setting('email_admissions'), 'email_dpo' => main_setting('email_dpo', 'dataprotection@anu.ac.ke'),
        'privacy_url' => BASE_URL . '/privacy.php', 'dp_url' => BASE_URL . '/data-protection.php', 'terms_url' => MAIN_SITE_URL . '/terms.php']); ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
