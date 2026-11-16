/**
 * Shared "priority+" navbar (Audit Part 3). One file for the main site and
 * every school site.
 *
 * On desktop widths (whenever Bootstrap's hamburger is hidden) the top-level
 * menu items are measured against the space actually available. Items that
 * do not fit are moved into a trailing "More" dropdown, so the bar is always
 * ONE line -- at any viewport width, zoom level, font size, or label length
 * (French/Swahili labels included). On mobile the existing Bootstrap
 * off-canvas panel is used unchanged and nothing is moved.
 *
 * An item marked data-pn-pin is never moved: less important items go to "More" first.
 *
 * Moved items are cloned into the More menu (dropdown groups are flattened
 * to a header + their links); originals are only hidden, so resizing back
 * up restores the normal bar. Without JS the bar simply stays nowrap/clipped.
 */
(function () {
  var navs = document.querySelectorAll('nav.navbar');
  if (!navs.length) return;

  function strip(el) {
    el.removeAttribute('id');
    el.removeAttribute('data-bs-toggle');
    el.removeAttribute('aria-expanded');
    el.removeAttribute('aria-controls');
    Array.prototype.forEach.call(el.querySelectorAll('[id]'), function (n) { n.removeAttribute('id'); });
  }

  function addLink(menu, a) {
    var li = document.createElement('li');
    var c = a.cloneNode(true);
    strip(c);
    c.classList.remove('nav-link', 'dropdown-toggle', 'active');
    c.classList.add('dropdown-item');
    if (a.classList.contains('active') || a.getAttribute('aria-current')) c.classList.add('active');
    li.appendChild(c);
    menu.appendChild(li);
  }

  function setup(nav) {
    var toggler = nav.querySelector('.navbar-toggler');
    var body = nav.querySelector('.offcanvas-body') || nav.querySelector('.navbar-collapse');
    var ul = body && body.querySelector(':scope > ul');
    if (!toggler || !ul) return;

    var items = Array.prototype.filter.call(ul.children, function (li) { return li.tagName === 'LI'; });
    var movable = items.filter(function (li, i) {
      return i > 0 && !li.querySelector('.btn, .search-trigger') && !li.classList.contains('pn-more') && !li.hasAttribute('data-pn-pin');   // data-pn-pin: item always stays on the bar
    });

    var more = document.createElement('li');
    more.className = 'nav-item dropdown pn-more';
    more.hidden = true;
    more.innerHTML = '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>' +
                     '<ul class="dropdown-menu dropdown-menu-end pn-more-menu"></ul>';
    var moreMenu = more.querySelector('.pn-more-menu');
    // keep "More" before any pinned trailing items (search / Apply Now)
    var firstPinned = items.filter(function (li) { return li.querySelector('.btn, .search-trigger'); })[0] || null;
    ul.insertBefore(more, firstPinned);

    function isDesktop() { return window.getComputedStyle(toggler).display === 'none'; }

    function reset() {
      items.forEach(function (li) { li.classList.remove('pn-hidden'); });
      more.hidden = true;
      moreMenu.innerHTML = '';
    }

    function used() {
      var w = 0;
      Array.prototype.forEach.call(ul.children, function (li) {
        if (li.classList.contains('pn-hidden') || li.hidden) return;
        var cs = window.getComputedStyle(li);
        if (cs.display === 'none') return;
        w += li.getBoundingClientRect().width + (parseFloat(cs.marginLeft) || 0) + (parseFloat(cs.marginRight) || 0);
      });
      return w;
    }

    function layout() {
      reset();
      if (!isDesktop()) return;
      var avail = body.clientWidth - 2; // 2px tolerance for sub-pixel rounding
      if (used() <= avail) return;
      more.hidden = false;
      var hiddenList = [];
      for (var i = movable.length - 1; i >= 0 && used() > avail; i--) {
        movable[i].classList.add('pn-hidden');
        hiddenList.unshift(movable[i]);
      }
      hiddenList.forEach(function (li) {
        var toggle = li.querySelector(':scope > a, :scope > button');
        var menu = li.querySelector('.dropdown-menu');
        if (menu) {
          var head = document.createElement('li');
          head.innerHTML = '<h6 class="dropdown-header"></h6>';
          head.firstChild.textContent = (toggle.textContent || '').trim();
          moreMenu.appendChild(head);
          Array.prototype.forEach.call(menu.querySelectorAll('a[href]'), function (a) { addLink(moreMenu, a); });
          var sep = document.createElement('li'); sep.innerHTML = '<hr class="dropdown-divider">'; moreMenu.appendChild(sep);
        } else if (toggle && toggle.tagName === 'A') {
          addLink(moreMenu, toggle);
        }
      });
      // trailing divider tidy-up
      var last = moreMenu.lastElementChild;
      if (last && last.querySelector('hr')) moreMenu.removeChild(last);
      if (moreMenu.querySelector('.active')) more.querySelector('.nav-link').classList.add('active');
      else more.querySelector('.nav-link').classList.remove('active');
    }

    var raf = 0;
    function schedule() { cancelAnimationFrame(raf); raf = requestAnimationFrame(layout); }
    layout();
    window.addEventListener('resize', schedule);
    window.addEventListener('orientationchange', schedule);
    if (window.ResizeObserver) new ResizeObserver(schedule).observe(body);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(schedule);
    window.addEventListener('load', schedule);
    nav.__priorityNav = layout; // test hook
  }

  function init() { Array.prototype.forEach.call(navs, setup); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
