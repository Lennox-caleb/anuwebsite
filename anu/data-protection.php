<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/legal_content.php';

$s = getAllSettings();
$contactEmail = $s['contact_email'] ?? 'distancelearning@anu.ac.ke';

trackPage('data-protection', 'Data Protection Notice');
renderHead('Data Protection Notice');
renderNavbar('');
?>

<section class="pp-hero" style="padding:70px 0 40px;background:#111;color:#fff">
  <div class="container"><div class="sec-tag" style="color:#FAC823">Legal</div><h1 style="color:#fff;font-weight:800">Data Protection Notice</h1></div>
</section>
<section style="padding:50px 0 70px;">
  <div class="container">
    <?php anu_legal_render('dp', [
        'unit' => 'Africa Nazarene University - ANU Online', 'address' => $s['contact_address'] ?? 'Ongata Rongai, Kajiado North, Kenya', 'phone' => $s['contact_phone'] ?? '',
        'email_general' => $contactEmail, 'email_dpo' => $s['dpo_email'] ?? 'dataprotection@anu.ac.ke',
        'privacy_url' => '/anu/privacy.php', 'dp_url' => '/anu/data-protection.php', 'terms_url' => '/anu/terms.php']); ?>
  </div>
</section>

<?php renderFooter(); ?>
