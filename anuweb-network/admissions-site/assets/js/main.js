document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('.adm-nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('shadow-sm', window.scrollY > 8);
    });
  }

  var collapseEl = document.getElementById('admNav');
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
