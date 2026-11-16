/* Testimonial slider arrows (scroll-snap does the rest; keyboard: focus the track and use arrow keys). */
(function () {
  var root = document.getElementById('anuTestimonials');
  if (!root) return;
  var track = root.querySelector('.t-track');
  var step = function () { var c = track.querySelector('.t-card'); return c ? c.getBoundingClientRect().width + 16 : 300; };
  root.querySelectorAll('.t-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      track.scrollBy({ left: step() * parseInt(b.dataset.dir, 10), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    });
  });
  track.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') { track.scrollBy({ left: step() }); e.preventDefault(); }
    if (e.key === 'ArrowLeft') { track.scrollBy({ left: -step() }); e.preventDefault(); }
  });
})();
