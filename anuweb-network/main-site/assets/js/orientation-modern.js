/* Orientation page motion: reading progress, timeline fill, "all ready" celebration, gentle hero parallax. */
(function () {
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var bar = document.querySelector('.orient-scrollbar span');
  var steps = document.querySelector('.orient-steps');
  var ticking = false;
  function update() {
    ticking = false;
    var h = document.documentElement;
    var max = h.scrollHeight - window.innerHeight;
    if (bar && max > 0) bar.style.transform = 'scaleX(' + Math.min(1, Math.max(0, window.scrollY / max)) + ')';
    if (steps) {
      var r = steps.getBoundingClientRect();
      var p = (window.innerHeight * 0.6 - r.top) / Math.max(1, r.height);
      steps.style.setProperty('--orient-p', Math.min(1, Math.max(0, p)).toFixed(3));
    }
  }
  function onScroll() { if (!ticking) { ticking = true; window.requestAnimationFrame(update); } }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  update();

  // everything on the checklist ticked: show a short thank-you with a small burst
  var all = document.getElementById('orientAllSet');
  var boxes = [].slice.call(document.querySelectorAll('[data-orient-check]'));
  var wasAll = false;
  function checkAll(initial) {
    if (!all || !boxes.length) return;
    var done = boxes.every(function (b) { return b.checked; });
    all.hidden = !done;
    if (done && !wasAll && !initial && !reduce) {
      all.classList.remove('burst'); void all.offsetWidth; all.classList.add('burst');
    }
    wasAll = done;
  }
  if (all) {
    [].slice.call(all.querySelectorAll('.dot')).forEach(function (d, i, list) {
      var a = (i / list.length) * Math.PI * 2, dist = 46 + (i % 3) * 22;
      d.style.setProperty('--dx', Math.round(Math.cos(a) * dist) + 'px');
      d.style.setProperty('--dy', Math.round(Math.sin(a) * dist) + 'px');
    });
    boxes.forEach(function (b) { b.addEventListener('change', function () { checkAll(false); }); });
    window.setTimeout(function () { checkAll(true); }, 60);   // after orientation.js restores saved ticks
  }

  // soft parallax on the hero glows (mouse devices only)
  var hero = document.querySelector('.orient-hero');
  if (hero && !reduce && window.matchMedia && window.matchMedia('(pointer:fine)').matches) {
    var glows = [].slice.call(hero.querySelectorAll('.orient-glow'));
    hero.addEventListener('mousemove', function (e) {
      var r = hero.getBoundingClientRect();
      var x = (e.clientX - r.left) / r.width - 0.5, y = (e.clientY - r.top) / r.height - 0.5;
      glows.forEach(function (g, i) { g.style.translate = (x * (i ? -40 : 40)) + 'px ' + (y * (i ? -30 : 30)) + 'px'; });
    });
  }
})();
