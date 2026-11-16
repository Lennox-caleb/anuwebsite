<?php
$pageTitle = 'Financial Aid';
$pageDescription = "Financial aid, bursaries and partial scholarships available to needy and deserving students at Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Support</span>
    <h1>Financial Aid</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">Africa Nazarene University provides financial aid packages to
    needy and deserving students, alumni and their families.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-7 reveal">
        <div class="program-section">
          <h2 class="program-section-heading"><i class="bi bi-list-check"></i> How to Apply</h2>
          <div class="program-section-body">
            <ol class="ps-3 mb-0">
              <li>Financial aid applications are processed annually, note the deadline for the current cycle.</li>
              <li>Complete and submit your application promptly.</li>
              <li>Confirm you meet the requirements for the aid type you are applying for.</li>
              <li>Attach your KCSE results slip and admission letter (new students), or your transcripts and current fee statement (continuing students).</li>
              <li>Aid is credited to your account once all requirements are satisfied.</li>
              <li>Successful applicants are notified by phone or email.</li>
            </ol>
          </div>
        </div>

        <div class="program-section">
          <h2 class="program-section-heading"><i class="bi bi-check2-square"></i> Keeping Your Aid</h2>
          <div class="program-section-body">
            <p class="mb-2">To continue receiving financial aid, students must maintain satisfactory
            academic progress by:</p>
            <ul class="ps-3 mb-0">
              <li>Meeting the required class attendance</li>
              <li>Passing all units attempted</li>
              <li>Achieving the minimum average grade stated in their award letter</li>
            </ul>
            <p class="mt-2 mb-0 text-muted small">Aid is suspended if these conditions are not met.</p>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="program-side-card reveal">
          <h3 class="h5 mb-3">Apply for Aid</h3>
          <a href="https://bit.ly/ANU-Partial-Scholarship" target="_blank" rel="noopener" class="btn btn-apply w-100 mb-4">Partial Scholarship Application &rarr;</a>

          <div class="program-fact">
            <span class="program-fact-icon"><i class="bi bi-envelope"></i></span>
            <span><strong>Email</strong><span>admissions@anu.ac.ke</span></span>
          </div>
          <div class="program-fact">
            <span class="program-fact-icon"><i class="bi bi-telephone"></i></span>
            <span><strong>Phone</strong><span>+254 703 970 520 / +254 773 977 859</span></span>
          </div>
          <a href="<?= BASE_URL ?>/resources.php" class="btn btn-outline-anu w-100 mt-2">Financial Aid Forms &rarr;</a>
          <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-anu w-100 mt-2">Ask a Question</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
