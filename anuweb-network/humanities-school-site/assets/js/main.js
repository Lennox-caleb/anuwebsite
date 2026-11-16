document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.hum-nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('shadow-sm', window.scrollY > 8);
    });
  }
  var collapseEl = document.getElementById('humNav');
  if (collapseEl) {
    collapseEl.querySelectorAll('a.nav-link, a.btn').forEach(function (link) {
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
  // Scroll-reveal
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

// Phase 2 (2026-09): hero video -- respect prefers-reduced-motion (poster/
// fallback photo still shows via CSS, video is just not played), and hide
// a video element if it fails to load so the section's own background
// color/gradient shows instead of a broken box.
document.addEventListener('DOMContentLoaded', function () {
  var heroVideos = document.querySelectorAll('.hero-bg-video');
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  heroVideos.forEach(function (v) {
    v.addEventListener('error', function () { v.style.display = 'none'; });
    if (reducedMotion) { v.removeAttribute('autoplay'); v.pause(); v.style.display = 'none'; }
  });
  if (reducedMotion) {
    document.querySelectorAll('.hero-bg-iframe').forEach(function (f) { f.style.display = 'none'; });
  }
});
