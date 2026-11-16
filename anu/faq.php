<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();
try {
$faqs = $db->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
} catch(Exception $e) {}

// Group by category
$grouped = [];
foreach ($faqs as $f) {
  $grouped[$f['category']][] = $f;
}

trackPage('faq', 'FAQs');
renderHead('FAQs');
renderNavbar('faq');
renderHero('faq');
?>

<section style="padding:60px 0 20px;" id="faq-section">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-7 text-center">
        <div class="search-bar">
          <input type="text" id="faqSearch" placeholder="Search FAQs — programs, admissions, fees...">
          <button type="button"><i class="bi bi-search"></i></button>
        </div>
      </div>
    </div>
    
    <!-- Category pills -->
    <div class="text-center mb-5">
      <button class="faq-cat-btn active" onclick="filterFAQ('')">All</button>
      <?php foreach ($grouped as $cat => $items): ?>
      <button class="faq-cat-btn" onclick="filterFAQ('<?= e(str_replace("'","\'",$cat)) ?>')"><?= e($cat) ?></button>
      <?php endforeach; ?>
    </div>
    
    <style>
    .faq-cat-btn {
      background: white;
      border: 1.5px solid rgba(0,0,0,0.1);
      color: var(--mid);
      padding: 8px 22px;
      border-radius: 50px;
      font-size: 0.88rem;
      font-weight: 600;
      margin: 4px;
      cursor: pointer;
      transition: 0.3s;
    }
    .faq-cat-btn.active,
    .faq-cat-btn:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    </style>
  </div>
</section>

<section style="padding: 0 0 80px;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <?php foreach ($grouped as $cat => $items): ?>
        <div class="faq-category mb-4" data-cat="<?= e($cat) ?>">
          <h5 style="color:var(--primary);font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <span style="display:inline-block;width:8px;height:8px;background:var(--accent);border-radius:50%;"></span>
            <?= e($cat) ?>
          </h5>
          
          <div class="anu-accordion accordion" id="faqAcc<?= md5($cat) ?>">
            <?php foreach ($items as $i => $faq): ?>
            <div class="accordion-item faq-item" data-cat="<?= e($cat) ?>">
              <h2 class="accordion-header">
                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                  data-bs-toggle="collapse" data-bs-target="#faq<?= $faq['id'] ?>">
                  <?= e($faq['question']) ?>
                </button>
              </h2>
              <div id="faq<?= $faq['id'] ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>">
                <div class="accordion-body">
                  <?= nl2br(e($faq['answer'])) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
        
        <div id="noFaqResults" style="display:none;text-align:center;padding:60px 0;">
          <i class="bi bi-question-circle" style="font-size:3rem;color:#ddd;"></i>
          <p class="mt-3 text-muted">No FAQs found. Try different search terms.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Still have questions? -->
<section class="cta-band">
  <div class="container text-center">
    <h2>Still Have Questions?</h2>
    <p>Our friendly team is ready to help you. Get in touch today.</p>
    <div class="d-flex gap-3 justify-content-center">
      <a href="/anu/contact.php" class="btn-cta btn">Contact Us</a>
      <a href="/anu/support.php" class="btn btn-outline-anu" style="border-color:rgba(255,255,255,0.4);color:white;">Support Center</a>
    </div>
  </div>
</section>

<script>
function filterFAQ(cat) {
  document.querySelectorAll('.faq-cat-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  
  document.querySelectorAll('.faq-category').forEach(section => {
    section.style.display = (!cat || section.dataset.cat === cat) ? 'block' : 'none';
  });
}

document.getElementById('faqSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase().trim();
  let visible = 0;
  
  document.querySelectorAll('.faq-item').forEach(item => {
    const text = item.textContent.toLowerCase();
    const show = !q || text.includes(q);
    item.style.display = show ? 'block' : 'none';
    if (show) visible++;
  });
  
  document.querySelectorAll('.faq-category').forEach(sec => {
    const hasVisible = [...sec.querySelectorAll('.faq-item')].some(i => i.style.display !== 'none');
    sec.style.display = hasVisible ? 'block' : 'none';
  });
  
  document.getElementById('noFaqResults').style.display = visible ? 'none' : 'block';
});
</script>

<?php renderFooter(); ?>
