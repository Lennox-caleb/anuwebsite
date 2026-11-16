// ANU Online — main.js
'use strict';

// FAQ accordion
document.addEventListener('click', function(e) {
  const q = e.target.closest('.faq-q');
  if (!q) return;
  const item = q.closest('.faq-item');
  const ans  = item.querySelector('.faq-a');
  const isOpen = item.classList.contains('open');
  // Close all
  document.querySelectorAll('.faq-item.open').forEach(el => {
    el.classList.remove('open');
    el.querySelector('.faq-a').classList.remove('open');
  });
  if (!isOpen) { item.classList.add('open'); ans.classList.add('open'); }
});

// Newsletter
function handleNewsletter(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button');
  const msg = document.getElementById('newsletter-msg');
  btn.disabled = true;
  setTimeout(() => {
    if (msg) { msg.textContent = '✓ Subscribed! Thank you.'; msg.style.color='var(--gold)'; }
    btn.disabled = false;
    e.target.reset();
  }, 800);
}

// Filter pills
document.querySelectorAll('.pill').forEach(pill => {
  pill.addEventListener('click', function() {
    const group = this.dataset.group;
    document.querySelectorAll(`.pill[data-group="${group}"]`).forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    const filter = this.dataset.filter;
    document.querySelectorAll('[data-filter-group="'+group+'"]').forEach(el => {
      el.style.display = (filter === 'all' || el.dataset.cat === filter) ? '' : 'none';
    });
  });
});

// Live FAQ search
const faqSearch = document.getElementById('faqSearch');
if (faqSearch) {
  faqSearch.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
      item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}
