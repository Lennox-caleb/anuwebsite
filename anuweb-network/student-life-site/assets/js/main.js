document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.sl-nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('shadow-sm', window.scrollY > 8);
    });
  }

  var collapseEl = document.getElementById('slNav');
  if (collapseEl) {
    collapseEl.querySelectorAll('a.nav-link:not(.dropdown-toggle), a.btn, a.dropdown-item').forEach(function (link) {
      link.addEventListener('click', function () {
        var bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
        if (bsCollapse && collapseEl.classList.contains('show')) {
          bsCollapse.hide();
        }
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  // Scroll-reveal for standalone decorative elements only (not primary content)
  (function () {
    var els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el) { io.observe(el); });
  })();

  // Back to top
  (function () {
    var btn = document.getElementById('unitBackToTop');
    if (!btn) return;
    window.addEventListener('scroll', function () {
      btn.classList.toggle('show', window.scrollY > 500);
    });
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();
});
