document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.navbar-anu');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('shadow-sm', window.scrollY > 8);
    });
  }

  // Explicitly initialize dropdowns as a safety net, in case anything
  // prevented Bootstrap's own data-attribute auto-init from running.
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
      new bootstrap.Dropdown(el);
    }
  });

  // Open dropdowns on hover (desktop only) instead of requiring a click,
  // matching the reference site's mega-menu behavior. Click still works
  // as a fallback (and is the only option on touch devices, since hover
  // events don't fire there).
  if (window.matchMedia('(min-width: 992px)').matches) {
    document.querySelectorAll('.dropdown').forEach(function (dropdownEl) {
      var toggle = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
      if (!toggle) return;
      var hideTimer;
      dropdownEl.addEventListener('mouseenter', function () {
        clearTimeout(hideTimer);
        bootstrap.Dropdown.getOrCreateInstance(toggle).show();
      });
      dropdownEl.addEventListener('mouseleave', function () {
        hideTimer = setTimeout(function () {
          bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
        }, 150);
      });
    });
  }

  // Scroll-reveal: fade/slide elements up as they enter the viewport.
  // Content is fully visible without JS (progressive enhancement only).
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
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el) { io.observe(el); });
  })();

  // Hero video/YouTube slides: pause the ones off-screen (no point
  // decoding video nobody sees), skip autoplay entirely for
  // prefers-reduced-motion (the poster frame set in PHP still shows, so
  // nothing looks broken), and fall back to that same poster/background
  // photo if a video URL 404s. .hero-video is shared by both <video> and
  // the YouTube <iframe> (Phase 2), so every action here checks the tag
  // name first -- an iframe has no .pause()/.play()/.currentTime.
  (function () {
    var videos = document.querySelectorAll('.hero-video');
    if (!videos.length) return;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function isVideoEl(v) { return v.tagName === 'VIDEO'; }
    videos.forEach(function (v) {
      if (isVideoEl(v)) v.addEventListener('error', function () { v.style.display = 'none'; });
      if (reducedMotion) {
        v.style.display = 'none'; // hides the iframe outright; poster/photo behind it still shows
        if (isVideoEl(v)) { v.removeAttribute('autoplay'); v.pause(); }
      }
    });
    var carousel = document.getElementById('heroCarousel');
    if (carousel && !reducedMotion) {
      carousel.addEventListener('slide.bs.carousel', function (e) {
        videos.forEach(function (v) { if (isVideoEl(v)) v.pause(); });
        var next = e.relatedTarget && e.relatedTarget.querySelector('.hero-video');
        if (next && isVideoEl(next)) { next.currentTime = 0; next.play().catch(function () {}); }
      });
    }
  })();

  // Count-up animation for the "Fun Facts" strip (e.g. "4,000+" counts up to 4,000 then adds "+").
  (function () {
    var facts = document.querySelectorAll('.fact .value[data-count]');
    if (!facts.length) return;
    function animate(el) {
      var target = parseInt(el.getAttribute('data-count'), 10);
      var suffix = el.getAttribute('data-suffix') || '';
      // Skip the count-up for tiny numbers (e.g. "1st") -- animating 0 to 1 over
      // a fixed 1.2s just leaves the stat looking stuck/broken on "0" the whole time.
      if (!target || target <= 9 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        el.textContent = (target ? target.toLocaleString() : el.textContent) + suffix;
        return;
      }
      var start = null;
      var duration = 1200;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var value = Math.floor(progress * target);
        el.textContent = value.toLocaleString() + suffix;
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }
    if ('IntersectionObserver' in window) {
      var io2 = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) { animate(entry.target); io2.unobserve(entry.target); }
        });
      }, { threshold: 0.5 });
      facts.forEach(function (el) { io2.observe(el); });
    } else {
      facts.forEach(animate);
    }
  })();

  // School cards: click/tap toggle for the programme preview (hover already
  // handles this on real pointers via CSS; this covers touch + keyboard).
  document.querySelectorAll('.school-card-toggle').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var card = btn.closest('.school-card');
      if (!card) return;
      var open = card.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  // Simple client-side tabs (used on the Events page: Upcoming / Past).
  document.querySelectorAll('.anu-tabs').forEach(function (tabs) {
    var buttons = tabs.querySelectorAll('button[data-tab-target]');
    function activate(targetSel) {
      buttons.forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-tab-target') === targetSel); });
      document.querySelectorAll('[data-tab-pane]').forEach(function (pane) {
        pane.hidden = pane.getAttribute('data-tab-pane') !== targetSel;
      });
    }
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () { activate(btn.getAttribute('data-tab-target')); });
    });
    // If the page loaded with a #slug hash pointing at an event in the
    // "past" pane, switch to that tab so the link actually shows something.
    if (window.location.hash) {
      var target = document.querySelector(window.location.hash);
      if (target) {
        var pane = target.closest('[data-tab-pane]');
        if (pane) activate(pane.getAttribute('data-tab-pane'));
      }
    }
  });

  // Back-to-top button.
  (function () {
    var btn = document.getElementById('anuBackToTop');
    if (!btn) return;
    window.addEventListener('scroll', function () {
      btn.classList.toggle('show', window.scrollY > 500);
    });
    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();

  // Instant search overlay (header search icon).
  (function () {
    var trigger = document.getElementById('anuSearchTrigger');
    var overlay = document.getElementById('anuSearchOverlay');
    if (!trigger || !overlay) return;
    var closeBtn = document.getElementById('anuSearchClose');
    var input = document.getElementById('anuSearchInput');
    var resultsBox = document.getElementById('anuSearchResults');
    var searchUrl = overlay.getAttribute('data-search-url');
    var debounceTimer = null;
    var activeRequest = null;

    function openOverlay() {
      overlay.hidden = false;
      document.body.classList.add('search-overlay-open');
      trigger.setAttribute('aria-expanded', 'true');
      setTimeout(function () { overlay.classList.add('is-open'); input.focus(); }, 10);
    }
    function closeOverlay() {
      overlay.classList.remove('is-open');
      document.body.classList.remove('search-overlay-open');
      trigger.setAttribute('aria-expanded', 'false');
      setTimeout(function () { overlay.hidden = true; }, 200);
    }

    trigger.addEventListener('click', openOverlay);
    closeBtn.addEventListener('click', closeOverlay);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeOverlay(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !overlay.hidden) closeOverlay();
    });

    document.querySelectorAll('.search-overlay-hints button[data-q]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        input.value = btn.getAttribute('data-q');
        runSearch(input.value);
        input.focus();
      });
    });

    function renderResults(data) {
      if (!data.items.length) {
        resultsBox.innerHTML = data.q
          ? '<p class="search-overlay-empty">No quick matches for &ldquo;' + escapeHtml(data.q) + '&rdquo;. Press Enter to see the full results page.</p>'
          : '';
        return;
      }
      var html = data.items.map(function (item) {
        return '<a class="search-overlay-result" href="' + item.url + '">' +
          '<span class="result-icon"><i class="bi ' + item.icon + '"></i></span>' +
          '<span class="result-text"><span class="result-type">' + escapeHtml(item.type) + '</span>' +
          '<span class="result-title">' + escapeHtml(item.title) + '</span>' +
          (item.desc ? '<span class="result-desc">' + escapeHtml(item.desc) + '</span>' : '') +
          '</span></a>';
      }).join('');
      html += '<a class="search-overlay-seeall" href="' + data.seeAllUrl + '">See all results for &ldquo;' + escapeHtml(data.q) + '&rdquo; <i class="bi bi-arrow-right"></i></a>';
      resultsBox.innerHTML = html;
    }

    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    function runSearch(q) {
      q = q.trim();
      if (q.length < 2) { resultsBox.innerHTML = ''; return; }
      if (activeRequest) activeRequest.abort();
      var controller = ('AbortController' in window) ? new AbortController() : null;
      activeRequest = controller;
      resultsBox.innerHTML = '<p class="search-overlay-loading">Searching&hellip;</p>';
      fetch(searchUrl + '?q=' + encodeURIComponent(q), controller ? { signal: controller.signal } : {})
        .then(function (r) { return r.json(); })
        .then(function (data) { renderResults(data); })
        .catch(function (err) { if (err.name !== 'AbortError') resultsBox.innerHTML = ''; });
    }

    input.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      var val = input.value;
      debounceTimer = setTimeout(function () { runSearch(val); }, 250);
    });
  })();

  // Cookie / privacy notice banner (Data Protection Act, 2019).
  // Consent choice is stored only in the visitor's own browser; nothing
  // is sent to the server and no tracking cookie is set by this script.
  (function () {
    var banner = document.getElementById('anuCookieBanner');
    if (!banner) return;
    var STORAGE_KEY = 'anu_cookie_consent';
    try {
      if (localStorage.getItem(STORAGE_KEY)) return;
    } catch (e) { /* storage unavailable (private mode etc.) -- show the banner anyway */ }

    setTimeout(function () { banner.classList.add('show'); }, 600);

    var acceptBtn = document.getElementById('anuCookieAccept');
    if (acceptBtn) {
      acceptBtn.addEventListener('click', function () {
        try { localStorage.setItem(STORAGE_KEY, 'accepted'); } catch (e) {}
        banner.classList.remove('show');
      });
    }
  })();
});
