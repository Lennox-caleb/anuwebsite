/*!
 * ANU Accessibility Menu (self-hosted, no third party, no tracking).
 * Opens with the round button (bottom left) or Ctrl+U. Choices are kept in this browser only (localStorage "anu_a11y").
 */
(function () {
  'use strict';
  if (window.__anuA11y) return; window.__anuA11y = true;
  var KEY = 'anu_a11y';
  var FEATURES = [
    { id: 'contrast', label: 'Contrast +', icon: '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor"/>' },
    { id: 'links', label: 'Highlight Links', icon: '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3A4 4 0 0 0 11 18.7l1-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
    { id: 'text', label: 'Bigger Text', levels: 3, icon: '<path d="M3 18 8 6l5 12M5 14h6M15 18l3.5-8 3.5 8M16.5 15h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' },
    { id: 'spacing', label: 'Text Spacing', icon: '<path d="M3 5v14M21 5v14M7 12h10M7 12l3-3M7 12l3 3M17 12l-3-3M17 12l-3 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' },
    { id: 'pause', label: 'Pause Animations', icon: '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 8v8M14 8v8" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>' },
    { id: 'images', label: 'Hide Images', icon: '<rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="10" r="1.6" fill="currentColor"/><path d="m4 18 5-5 4 4 3-3 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>' },
    { id: 'lh', label: 'Line Height', levels: 3, icon: '<path d="M4 6h10M4 12h10M4 18h10M19 5v14M19 5l-2 2.5M19 5l2 2.5M19 19l-2-2.5M19 19l2-2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' },
    { id: 'align', label: 'Align Left', icon: '<path d="M4 6h16M4 10h10M4 14h16M4 18h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
    { id: 'guide', label: 'Reading Guide', icon: '<rect x="3" y="9" width="18" height="6" rx="1.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 5h18M3 19h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity=".45"/>' },
    { id: 'gray', label: 'Grayscale', icon: '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 3v18M12 7h5M12 12h7M12 17h5" stroke="currentColor" stroke-width="2"/>' },
    { id: 'speak', label: 'Read Aloud', speech: true, icon: '<path d="M4 9v6h4l5 4V5L8 9z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M16.5 9a4 4 0 0 1 0 6M18.8 6.5a7.5 7.5 0 0 1 0 11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
    { id: 'font', label: 'Readable Font', icon: '<path d="M5 19 11 5l6 14M7.5 14h7M19 9v10M19 9c-2 0-3 1-3 2.5s1 2.5 3 2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' },
    { id: 'cursor', label: 'Big Cursor', icon: '<path d="M6 3v15l4-4 3 6 3-1.4-3-6H18z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>' },
    { id: 'focus', label: 'Highlight Focus', icon: '<rect x="4" y="6" width="16" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="3 2"/><path d="M9 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' },
    { id: 'headings', label: 'Highlight Headings', icon: '<path d="M5 5v14M13 5v14M5 12h8M17 8l2-1v12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' }
  ];
  var HAS_SPEECH = ('speechSynthesis' in window) && ('SpeechSynthesisUtterance' in window);
  FEATURES = FEATURES.filter(function (f) { return !f.speech || HAS_SPEECH; });
  var state = {};
  try { state = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { state = {}; }
  function save() { try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {} }

  var CSS = '' +
  '#a11yRoot,#a11yRoot *{box-sizing:border-box;font-family:Roboto,Arial,Helvetica,sans-serif;letter-spacing:normal!important;word-spacing:normal!important;line-height:1.3!important}' +
  '#a11yBtn{position:fixed;left:18px;bottom:18px;z-index:2147482000;width:54px;height:54px;border-radius:50%;border:3px solid #FAC823;background:#111;color:#FAC823;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,.35);padding:0}' +
  '#a11yBtn:hover{background:#FAC823;color:#111}#a11yBtn:focus-visible,#a11yRoot button:focus-visible{outline:3px solid #0a7cff;outline-offset:2px}' +
  '#a11yBtn svg{width:30px;height:30px}' +
  'body:has(#anuCookieBar:not([hidden])) #a11yBtn,body:has(#anuDataNotice:not([hidden])) #a11yBtn{bottom:104px}' +
  'body:has(#anuCookieBar:not([hidden])) #a11yPanel,body:has(#anuDataNotice:not([hidden])) #a11yPanel{bottom:170px;max-height:calc(100vh - 190px)}' +
  '#a11yPanel{position:fixed;left:18px;bottom:84px;z-index:2147482001;width:430px;max-width:calc(100vw - 24px);max-height:calc(100vh - 110px);display:flex;flex-direction:column;background:#f1f2f6;border-radius:14px;box-shadow:0 14px 44px rgba(0,0,0,.4);overflow:hidden;color:#111}' +
  '#a11yPanel[hidden]{display:none}' +
  '#a11yHead{background:#FF0000;color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;font-weight:700;font-size:15px}' +
  '#a11yHead button{background:#fff;color:#111;border:0;width:34px;height:34px;border-radius:50%;font-size:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}' +
  '#a11yGrid{padding:14px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;overflow:auto}@media (max-width:520px){#a11yGrid{grid-template-columns:1fr 1fr}}' +
  '#a11yGrid button{background:#fff;border:2px solid transparent;border-radius:12px;padding:12px 6px 10px;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:7px;font-size:12.5px;font-weight:700;color:#111;min-height:88px;text-align:center;position:relative}' +
  '#a11yGrid button svg{width:30px;height:30px}' +
  '#a11yGrid button[aria-pressed="true"]{border-color:#FF0000;background:#fff3f3}' +
  '#a11yGrid button[aria-pressed="true"]::after{content:"On";position:absolute;top:6px;right:8px;font-size:10px;background:#FF0000;color:#fff;border-radius:8px;padding:1px 6px}' +
  '#a11yGrid button .lv{font-size:11px;font-weight:600;color:#555}' +
  '#a11yFoot{padding:10px 14px 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;border-top:1px solid #d9dbe3;background:#f1f2f6}' +
  '#a11yFoot button{background:#111;color:#fff;border:0;border-radius:8px;padding:9px 14px;font-weight:700;font-size:13px;cursor:pointer}' +
  '#a11yFoot span{font-size:11px;color:#555}' +
  /* effects: everything except the widget itself */
  'html.a11y-contrast body *:not(#a11yRoot):not(#a11yRoot *){background-color:#000!important;background-image:none!important;color:#fff!important;border-color:#fff!important;text-shadow:none!important;box-shadow:none!important}' +
  'html.a11y-contrast body a:not(#a11yRoot a),html.a11y-contrast body a *:not(#a11yRoot *){color:#ffe600!important}' +
  'html.a11y-contrast body button:not(#a11yRoot button){border:2px solid #fff!important}' +
  'html.a11y-links body a:not(#a11yRoot a){background:#ffe600!important;color:#000!important;text-decoration:underline!important;outline:2px solid #000!important;outline-offset:1px}' +
  'html.a11y-text1 body>*:not(#a11yRoot){zoom:1.1}html.a11y-text2 body>*:not(#a11yRoot){zoom:1.2}html.a11y-text3 body>*:not(#a11yRoot){zoom:1.35}' +
  'html.a11y-spacing body *:not(#a11yRoot):not(#a11yRoot *){letter-spacing:.12em!important;word-spacing:.18em!important;line-height:1.8!important}' +
  'html.a11y-pause *:not(#a11yRoot):not(#a11yRoot *),html.a11y-pause *::before,html.a11y-pause *::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' +
  'html.a11y-lh1 body *:not(#a11yRoot):not(#a11yRoot *){line-height:1.6!important}html.a11y-lh2 body *:not(#a11yRoot):not(#a11yRoot *){line-height:1.9!important}html.a11y-lh3 body *:not(#a11yRoot):not(#a11yRoot *){line-height:2.3!important}' +
  'html.a11y-align body *:not(#a11yRoot):not(#a11yRoot *){text-align:left!important}' +
  'html.a11y-gray{filter:grayscale(1)}' +
  'html.a11y-speak body *:not(#a11yRoot):not(#a11yRoot *){cursor:help}' +
  '#a11yGuideTop,#a11yGuideBot{position:fixed;left:0;right:0;background:rgba(0,0,0,.55);z-index:2147481000;pointer-events:none;display:none}' +
  '#a11yGuideTop{top:0;border-bottom:3px solid #FAC823}#a11yGuideBot{bottom:0;border-top:3px solid #FAC823}' +
  'html.a11y-guide #a11yGuideTop,html.a11y-guide #a11yGuideBot{display:block}' +
  '.a11y-skip{position:fixed;left:8px;top:-60px;z-index:2147483001;background:#111;color:#FAC823;padding:10px 16px;border-radius:0 0 8px 8px;font:700 14px Roboto,Arial,sans-serif;text-decoration:none}.a11y-skip:focus{top:0;outline:3px solid #0a7cff}' +
  'html.a11y-images body img,html.a11y-images body picture,html.a11y-images body video,html.a11y-images body iframe{visibility:hidden!important}' +
  'html.a11y-images body *:not(#a11yRoot):not(#a11yRoot *){background-image:none!important}' +
  'html.a11y-font body *:not(#a11yRoot):not(#a11yRoot *):not(i):not([class*="bi-"]){font-family:"Comic Sans MS","Trebuchet MS",Verdana,Arial,sans-serif!important}' +
  'html.a11y-cursor,html.a11y-cursor body *{cursor:url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 24 24\'><path d=\'M5 2v18l5-5 3.5 7 3-1.5L13 13.5h7z\' fill=\'black\' stroke=\'white\' stroke-width=\'1.5\'/></svg>") 4 2,auto!important}' +
  'html.a11y-focus body *:focus,html.a11y-focus body *:focus-visible{outline:4px solid #0a7cff!important;outline-offset:3px!important}' +
  'html.a11y-headings body :is(h1,h2,h3,h4,h5,h6):not(#a11yRoot *){outline:3px solid #FAC823!important;outline-offset:3px;background:#fff8d6!important;color:#000!important}';

  var st = document.createElement('style'); st.id = 'a11yStyle'; st.textContent = CSS; (document.head || document.documentElement).appendChild(st);

  var root = document.createElement('div'); root.id = 'a11yRoot';
  var svg = function (inner) { return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + inner + '</svg>'; };
  root.innerHTML =
    '<button id="a11yBtn" type="button" aria-label="Open accessibility menu (Ctrl+U)" aria-expanded="false" aria-controls="a11yPanel" title="Accessibility Menu (Ctrl+U)">' +
      svg('<circle cx="12" cy="4.5" r="2.2" fill="currentColor"/><path d="M4 8.5l8 1.6 8-1.6M12 10.2V14M12 14l-3.5 6.5M12 14l3.5 6.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>') + '</button>' +
    '<div id="a11yPanel" role="dialog" aria-label="Accessibility menu" hidden>' +
      '<div id="a11yHead"><span>Accessibility Menu (CTRL+U)</span><button type="button" id="a11yClose" aria-label="Close accessibility menu">&times;</button></div>' +
      '<div id="a11yGrid"></div>' +
      '<div id="a11yFoot"><button type="button" id="a11yReset">Reset all</button><span>Saved on this device only</span></div>' +
    '</div>';

  var grid;
  function level(f) { var v = state[f.id]; return f.levels ? (+v || 0) : (v ? 1 : 0); }
  function apply() {
    var h = document.documentElement;
    FEATURES.forEach(function (f) {
      if (f.levels) { for (var i = 1; i <= f.levels; i++) h.classList.remove('a11y-' + f.id + i); var l = level(f); if (l) h.classList.add('a11y-' + f.id + l); }
      else h.classList.toggle('a11y-' + f.id, !!level(f));
    });
    if (!state.speak && HAS_SPEECH) { try { speechSynthesis.cancel(); } catch (e) {} }
    // stop moving media / carousels
    if (state.pause) {
      try { document.querySelectorAll('video').forEach(function (v) { v.pause(); }); } catch (e) {}
      try { if (window.bootstrap && bootstrap.Carousel) document.querySelectorAll('.carousel').forEach(function (c) { bootstrap.Carousel.getOrCreateInstance(c).pause(); }); } catch (e) {}
    }
    if (grid) FEATURES.forEach(function (f) {
      var b = grid.querySelector('[data-f="' + f.id + '"]'); if (!b) return; var l = level(f);
      b.setAttribute('aria-pressed', l ? 'true' : 'false');
      var lv = b.querySelector('.lv'); if (lv) lv.textContent = f.levels ? (l ? 'Level ' + l + ' of ' + f.levels : 'Off') : '';
    });
  }
  function build() {
    document.body.appendChild(root);
    grid = document.getElementById('a11yGrid');
    FEATURES.forEach(function (f) {
      var b = document.createElement('button'); b.type = 'button'; b.setAttribute('data-f', f.id); b.setAttribute('aria-pressed', 'false');
      b.innerHTML = svg(f.icon) + '<span>' + f.label + '</span>' + (f.levels ? '<span class="lv"></span>' : '');
      b.addEventListener('click', function () {
        if (f.levels) state[f.id] = ((+state[f.id] || 0) + 1) % (f.levels + 1); else state[f.id] = !state[f.id];
        save(); apply();
      });
      grid.appendChild(b);
    });
    var btn = document.getElementById('a11yBtn'), panel = document.getElementById('a11yPanel');
    function toggle(open) {
      var show = (typeof open === 'boolean') ? open : panel.hidden;
      panel.hidden = !show; btn.setAttribute('aria-expanded', show ? 'true' : 'false');
      if (show) { var first = grid.querySelector('button'); if (first) first.focus(); } else btn.focus();
    }
    btn.addEventListener('click', function () { toggle(); });
    document.getElementById('a11yClose').addEventListener('click', function () { toggle(false); });
    document.getElementById('a11yReset').addEventListener('click', function () { state = {}; save(); apply(); });
    document.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && (e.key === 'u' || e.key === 'U')) { e.preventDefault(); toggle(); }
      else if (e.key === 'Escape' && !panel.hidden) toggle(false);
    });
    // reading guide (follows the pointer / finger)
    var gt = document.createElement('div'), gb = document.createElement('div'); gt.id = 'a11yGuideTop'; gb.id = 'a11yGuideBot'; root.appendChild(gt); root.appendChild(gb);
    function guideAt(y) { gt.style.height = Math.max(0, y - 28) + 'px'; gb.style.height = Math.max(0, window.innerHeight - y - 28) + 'px'; }
    guideAt(window.innerHeight / 2);
    document.addEventListener('mousemove', function (e) { if (state.guide) guideAt(e.clientY); }, { passive: true });
    document.addEventListener('touchmove', function (e) { if (state.guide && e.touches[0]) guideAt(e.touches[0].clientY); }, { passive: true });
    // read aloud: select text, or click a paragraph / heading / link / button
    function say(t) {
      if (!HAS_SPEECH) return; t = (t || '').replace(/\s+/g, ' ').trim(); if (!t) return;
      try { speechSynthesis.cancel(); var u = new SpeechSynthesisUtterance(t.slice(0, 900)); u.lang = document.documentElement.lang || 'en'; speechSynthesis.speak(u); } catch (e) {}
    }
    document.addEventListener('mouseup', function () { if (!state.speak) return; var sel = String(window.getSelection ? window.getSelection() : ''); if (sel.trim()) say(sel); });
    document.addEventListener('click', function (e) {
      if (!state.speak || root.contains(e.target)) return;
      var sel = String(window.getSelection ? window.getSelection() : ''); if (sel.trim()) return;
      var el = e.target.closest ? e.target.closest('p,li,h1,h2,h3,h4,h5,h6,a,button,td,th,label,figcaption,blockquote') : null;
      if (el) say(el.getAttribute('aria-label') || el.innerText);
    }, true);
    // skip link for keyboard and screen-reader users (only if the page has none)
    if (!document.querySelector('a[href="#main"],a[href="#content"],a[href="#main-content"],.skip-link,.skip-to-content')) {
      var target = document.querySelector('main,#main,#content,[role="main"]') || document.querySelector('h1');
      if (target) {
        if (!target.id) target.id = 'a11yMain'; if (target.tabIndex < 0 && !target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
        var sk = document.createElement('a'); sk.className = 'a11y-skip'; sk.href = '#' + target.id; sk.textContent = 'Skip to main content'; document.body.insertBefore(sk, document.body.firstChild);
      }
    }
    apply();
  }
  // apply saved choices as early as possible (before the page finishes loading), build the menu once the body exists
  apply();
  if (document.body) build(); else document.addEventListener('DOMContentLoaded', build);
})();
