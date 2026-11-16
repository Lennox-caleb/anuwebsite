<?php
/**
 * Phase 7b -- FAQs. Curated subset of the real, published FAQs at
 * anu.ac.ke/frequently-asked-questions/ (fetched 2026-09), limited to
 * the Admissions/Finance categories most relevant to this hub. The full
 * FAQ list (which also covers ANU Online, student life, health clinic,
 * transport, etc.) is linked at the bottom rather than duplicated here.
 */
$pageTitle = 'Admissions FAQs';
$pageDescription = 'Answers to common questions about applying to and paying fees at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';

$faqs = [
    ['How much is the application fee?', 'KES 1,500 for Kenyan/East African applicants; USD 35 for international applicants.'],
    ['How do I pay my application fee?', 'By M-Pesa Paybill 7291071 (account: your name), or by bank transfer to the university accounts listed on the Tuition Payment Options page. An official receipt is issued by the Finance office once paid.'],
    ["I'm not from Kenya, how do I pay my application fee?", 'International applicants can pay by wire transfer or PayPal, see International Students and Tuition Payment Options for details.'],
    ['Does ANU offer application fee waivers to international students?', 'No, no waivers are offered on application or tuition fees, for local or international applicants.'],
    ['Is there an interview?', "No interview is required, unless your chosen programme has special entry requirements."],
    ['Are my chances of admission improved by applying early?', 'Yes, ANU encourages early applications. Admission is on a first-come, first-served basis.'],
    ['I submitted my application but received no confirmation, how do I know ANU received it?', 'Applications are verified and processed with feedback usually given within 5 working days. Contact Admissions for a status update.'],
    ['Can I defer my admission once admitted?', 'Yes. Your admission letter is valid for one year, and deferring within that period has no financial penalty.'],
    ['Do you accept credit transfers from another university?', 'Yes, credits from other accredited universities are accepted for undergraduate programmes.'],
    ['What is the deadline for fee payment?', 'Fees are due at the beginning of each trimester/session during registration. An installment plan may be allowed if you cannot pay in full at once.'],
    ['How can I confirm ANU has received my fee payment?', 'Forward your payment remittance advice to finance@anu.ac.ke and you will receive an email acknowledgment.'],
    ['What types of scholarships does ANU offer?', 'Merit-based scholarships for top KCSE performers (a B+ mean qualifies for 30%, an A- for 50%), plus government loans, bursaries and work-study opportunities. See Financial Aid for how to apply.'],
    ['Can I access my exam results if I have an outstanding fee balance?', 'No, only students without a fee balance can access results via the student portal.'],
    ['If I paid more than I was billed, can I get a refund?', 'Yes, fees paid in excess are refundable to the sponsor/parent. Submit a written request to Finance, in person or by email to finance@anu.ac.ke.'],
];
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Quick Answers</span>
    <h1>Admissions &amp; Fees FAQs</h1>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
    <p class="text-muted"><?= nl2br(h(page_content('admissions', 'faqs_intro', 'Answers to the questions applicants ask most often. For anything not covered here, contact the Admissions office directly.'))) ?></p>

    <div class="accordion" id="admFaqAccordion">
      <?php foreach ($faqs as $i => [$q, $a]): ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
              <?= h($q) ?>
            </button>
          </h2>
          <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#admFaqAccordion">
            <div class="accordion-body text-muted"><?= h($a) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <p class="mt-4">
      This page covers Admissions &amp; Finance questions only. For ANU Online, student life, health clinic, transport and other topics, see the
      <a href="<?= h(page_content('admissions', 'faqs_full_list_url', 'https://www.anu.ac.ke/frequently-asked-questions/')) ?>" target="_blank" rel="noopener">full FAQ list on anu.ac.ke</a>.
    </p>

    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="<?= BASE_URL ?>/admissions-enquiries.php" class="btn btn-apply btn-lg">Ask Admissions a Question</a>
      <a href="<?= BASE_URL ?>/admissions.php" class="btn btn-outline-anu btn-lg">&larr; Back to Admissions</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
