<?php
/**
 * Phase 7b -- Tuition Payment Options. Content sourced from
 * anu.ac.ke/guide-to-paying-fees/ and anu.ac.ke/application-fee/
 * (fetched 2026-09) -- real bank/M-Pesa/PayPal details, not invented.
 */
$pageTitle = 'Tuition Payment Options';
$pageDescription = 'How to pay tuition and fees at Africa Nazarene University: M-Pesa, bank transfer, international wire transfer and PayPal.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Finance</span>
    <h1>Tuition Payment Options</h1>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(page_content('admissions', 'payment_intro', 'Tuition and fees can be paid by M-Pesa, direct bank deposit, international wire transfer, or PayPal. Always include your student/admission number as the payment reference.'))) ?></p>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-phone"></i> M-Pesa (Lipa na M-Pesa)</h2>
      <div class="program-section-body">
        <ol class="ps-3 mb-0">
          <li>Go to the M-Pesa menu and select <strong>Lipa na M-Pesa &rarr; Paybill</strong>.</li>
          <li>Business number: <strong>7291071</strong></li>
          <li>Account number: your <strong>student/admission number</strong> (or your name for an application fee payment).</li>
        </ol>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-bank"></i> Direct Bank Deposit (Within Kenya)</h2>
      <div class="program-section-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Bank</th><th>Branch</th><th>Account Number</th></tr></thead>
            <tbody>
              <tr><td>Co-operative Bank</td><td>Maasai Mall</td><td>01129011316000</td></tr>
              <tr><td>KCB</td><td>Ongata Rongai</td><td>1104427184</td></tr>
              <tr><td>Absa Bank</td><td>Hurlingham</td><td>0451037048</td></tr>
              <tr><td>Equity Bank</td><td>Ongata Rongai</td><td>0610292522396</td></tr>
              <tr><td>Standard Chartered Bank</td><td>Galleria</td><td>0108096404600</td></tr>
            </tbody>
          </table>
        </div>
        <p class="small text-muted mb-0">Account name: Africa Nazarene University. Include your student/admission number as the payment reference.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-globe"></i> International Wire Transfer</h2>
      <div class="program-section-body">
        <p class="mb-2">For payments from outside Kenya, wire to:</p>
        <ul class="ps-3">
          <li>Bank: Co-operative Bank of Kenya</li>
          <li>SWIFT code: <strong>KCOOKENA</strong></li>
          <li>Address: 730-00511 Ongata Rongai, Kenya</li>
        </ul>
        <p class="small text-muted mb-0">Include your student/admission number in the transfer details. For an international application fee payment specifically, ANU's application-fee page also lists a Citibank correspondent account (SWIFT: CITIKENA) for USD/EUR/GBP remittances, confirm current details with the Finance office before sending a large payment.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-paypal"></i> PayPal</h2>
      <div class="program-section-body">
        <p class="mb-1">Send to: <strong>accounts@anu.ac.ke</strong></p>
        <p class="small text-muted mb-0">PayPal deducts its own processing cost (ANU's guidance states approximately 11.9%) before the funds reach the university, factor this in when sending a payment this way.</p>
      </div>
    </div>

    <div class="program-section">
      <h2 class="program-section-heading"><i class="bi bi-check2-circle"></i> Confirming Your Payment</h2>
      <div class="program-section-body">
        <p class="mb-0">After paying, forward your payment remittance advice/receipt to <a href="mailto:finance@anu.ac.ke">finance@anu.ac.ke</a> to receive email acknowledgment. A student with an outstanding fee balance cannot access examination results via the student portal.</p>
      </div>
    </div>

    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="<?= BASE_URL ?>/financial-aid.php" class="btn btn-outline-anu btn-lg">Financial Aid</a>
      <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-outline-anu btn-lg">&larr; Back to Admissions</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
