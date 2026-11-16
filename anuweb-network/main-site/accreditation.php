<?php
$pageTitle = 'Accreditation';
$pageDescription = "Africa Nazarene University's charter status and regulatory recognition in Kenya.";
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Accreditation</h1>
  </div>
</section>
<section class="py-5">
  <div class="container container-narrow reveal">
    <p>Africa Nazarene University is chartered by the <strong>Commission for University Education (CUE)</strong>,
    Kenya's statutory body responsible for regulating university education, and is the first private
    university in Kenya to be accredited under the University Act without external affiliation to
    another institution.</p>
    <p>Degree programmes offered by the University's five Schools are approved by CUE. Non-degree
    Certificate, Diploma and short-course programmes are offered through
    <a href="<?= h(setting('url_tvet')) ?>" target="_blank" rel="noopener">ANU Non-Degree Programmes</a>.</p>
    <p class="text-muted">The University's current CUE charter number and any additional professional-body
    accreditations (e.g. for specific professional programmes) should be confirmed with the Registrar's
    office and published here as an official reference.</p>
    <p><a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a> &middot; <a href="<?= BASE_URL ?>/terms.php">Terms &amp; Conditions</a></p>
    <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu">&larr; Back to About ANU</a>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
