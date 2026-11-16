/* Orientation interactivity: tickable "What to bring" checklist (remembered in this browser), FAQ search, section highlight. */
(function () {
  var KEY = 'anuOrientChecklist';
  var boxes = [].slice.call(document.querySelectorAll('[data-orient-check]'));
  var bar = document.getElementById('orientProgress');
  var saved = {};
  try { saved = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) {}
  function paint() {
    var done = boxes.filter(function (b) { return b.checked; }).length;
    if (!bar) return;
    bar.hidden = false;
    document.getElementById('orientProgressFill').style.width = (boxes.length ? done / boxes.length * 100 : 0) + '%';
    document.getElementById('orientProgressText').textContent = done + ' of ' + boxes.length + ' ready';
    boxes.forEach(function (b) { var c = b.closest('.orient-check'); if (c) c.classList.toggle('is-done', b.checked); });
  }
  boxes.forEach(function (b) {
    b.checked = !!saved[b.dataset.orientCheck];
    b.addEventListener('change', function () {
      saved[b.dataset.orientCheck] = b.checked;
      try { localStorage.setItem(KEY, JSON.stringify(saved)); } catch (e) {}
      paint();
    });
  });
  var reset = document.getElementById('orientReset');
  if (reset) reset.addEventListener('click', function () {
    boxes.forEach(function (b) { b.checked = false; }); saved = {};
    try { localStorage.removeItem(KEY); } catch (e) {}
    paint();
  });
  paint();

  var search = document.getElementById('orientFaqSearch');
  if (search) search.addEventListener('input', function () {
    var q = search.value.trim().toLowerCase();
    document.querySelectorAll('#orientationFaq .accordion-item').forEach(function (it) {
      it.style.display = !q || it.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
    });
  });

  var links = [].slice.call(document.querySelectorAll('.orient-subnav a'));
  if (links.length && 'IntersectionObserver' in window) {
    var map = {};
    links.forEach(function (a) { var s = document.querySelector(a.getAttribute('href')); if (s) map[s.id] = a; });
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting && map[e.target.id]) { links.forEach(function (l) { l.classList.remove('active'); }); map[e.target.id].classList.add('active'); } });
    }, { rootMargin: '-40% 0px -55% 0px' });
    Object.keys(map).forEach(function (id) { io.observe(document.getElementById(id)); });
  }
})();
