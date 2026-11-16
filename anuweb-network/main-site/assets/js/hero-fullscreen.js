/**
 * Measures the site's real header height (sum of every element marked
 * .site-header-bar -- a topbar strip plus the main nav on the school
 * sites, or just the nav on the main site) and publishes it as the
 * --header-h CSS custom property, so hero-fullscreen.css can size the
 * hero to exactly "100svh minus the header" on any screen, without ever
 * hardcoding a header height. Re-measures on load and resize/orientation
 * change so it stays correct if the header wraps to more lines on a
 * narrow phone.
 */
(function () {
  function updateHeaderHeightVar() {
    var bars = document.querySelectorAll('.site-header-bar');
    var total = 0;
    bars.forEach(function (el) {
      total += el.getBoundingClientRect().height;
    });
    document.documentElement.style.setProperty('--header-h', total + 'px');
  }

  updateHeaderHeightVar();
  window.addEventListener('load', updateHeaderHeightVar);
  window.addEventListener('resize', updateHeaderHeightVar);
  window.addEventListener('orientationchange', updateHeaderHeightVar);
})();
