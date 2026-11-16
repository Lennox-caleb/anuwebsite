document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.tvet-nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('shadow-sm', window.scrollY > 8);
    });
  }

  var collapseEl = document.getElementById('tvetNav');
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

  // Live client-side filter for the programmes grid (level + department + text)
  var searchInput = document.getElementById('programmeSearch');
  var levelSelect = document.getElementById('programmeLevel');
  var deptSelect = document.getElementById('programmeDept');
  var cards = document.querySelectorAll('[data-programme-card]');

  function applyFilter() {
    if (!cards.length) return;
    var q = (searchInput && searchInput.value || '').toLowerCase().trim();
    var lvl = (levelSelect && levelSelect.value) || '';
    var dept = (deptSelect && deptSelect.value) || '';
    var visibleCount = 0;

    cards.forEach(function (card) {
      var title = (card.getAttribute('data-title') || '').toLowerCase();
      var cardLevel = card.getAttribute('data-level') || '';
      var cardDept = card.getAttribute('data-dept') || '';
      var matches = (!q || title.indexOf(q) !== -1) &&
                    (!lvl || cardLevel === lvl) &&
                    (!dept || cardDept === dept);
      card.closest('[data-programme-col]').style.display = matches ? '' : 'none';
      if (matches) visibleCount++;
    });

    var emptyState = document.getElementById('programmeEmptyState');
    if (emptyState) emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
  }

  [searchInput, levelSelect, deptSelect].forEach(function (el) {
    if (el) el.addEventListener('input', applyFilter);
  });

  // Simple client-side form validation feedback (Bootstrap pattern)
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
});
