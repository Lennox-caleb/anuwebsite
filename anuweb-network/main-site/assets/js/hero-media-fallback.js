/**
 * If a hero's background video/YouTube embed can't be confirmed as
 * actually playing within a short timeout, or errors outright, this
 * swaps it out for the poster image underneath (rendered by
 * render_hero_media() in hero_render.php whenever the hero row also has
 * an image set) by adding .hero-video-failed to the hero section --
 * hero-fullscreen.css hides the video/iframe for that class, revealing
 * the poster. Never leaves a blank/broken box: a hero with no poster set
 * just falls back to its own background-color, same as a "none" hero.
 *
 * For a plain <video>, "playing" is the real DOM 'playing' event, or an
 * 'error' event fails it immediately -- no guessing needed.
 *
 * For a YouTube iframe, the ONLY reliable signal is the player's own
 * postMessage API (enablejsapi=1 + origin, set by hero_render.php),
 * which posts {event:"onStateChange", info:1} when actual playback
 * starts. An iframe's own 'load' event fires once the *player page*
 * loads, not once the video is actually playing, so it's not used here
 * as a success signal by itself -- only the timeout and the API message
 * decide the outcome.
 */
(function () {
  var TIMEOUT_MS = 15000;

  function fail(hero) {
    // Never hide a video that has already been confirmed playing.
    if (hero.getAttribute('data-hero-playing') === '1') return;
    hero.classList.add('hero-video-failed');
  }

  function watchVideo(video, hero) {
    var settled = false;
    var timer = setTimeout(function () { if (!settled) { settled = true; fail(hero); } }, TIMEOUT_MS);
    video.addEventListener('playing', function () { settled = true; clearTimeout(timer); }, { once: true });
    video.addEventListener('error', function () { if (!settled) { settled = true; clearTimeout(timer); fail(hero); } }, { once: true });
  }

  function watchYouTube(iframe, hero) {
    var settled = false;
    var timer = setTimeout(function () { if (!settled) { settled = true; fail(hero); } }, TIMEOUT_MS);

    function markPlaying() {
      hero.setAttribute('data-hero-playing', '1');
      hero.classList.remove('hero-video-failed');
      settled = true;
      clearTimeout(timer);
      clearInterval(hs);
    }

    function onMessage(e) {
      if (e.source !== iframe.contentWindow) return;
      if (typeof e.data !== 'string') return;
      var data;
      try { data = JSON.parse(e.data); } catch (err) { return; }
      if (!data) return;
      // info: 1 === YT.PlayerState.PLAYING
      if (data.event === 'onStateChange' && data.info === 1) markPlaying();
      // infoDelivery messages carry the live player state / current time
      if (data.event === 'infoDelivery' && data.info && (data.info.playerState === 1 || data.info.currentTime > 0.5)) markPlaying();
      if (data.event === 'onError') {
        if (!settled) { settled = true; clearTimeout(timer); clearInterval(hs); fail(hero); }
      }
    }
    window.addEventListener('message', onMessage);

    // YouTube only starts sending events after the page says "listening".
    // Send that handshake on load and every second until playback is confirmed.
    function handshake() {
      try { iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening', id: 1, channel: 'widget' }), '*'); } catch (err) {}
    }
    iframe.addEventListener('load', handshake);
    var hs = setInterval(handshake, 1000);
    setTimeout(function () { clearInterval(hs); }, TIMEOUT_MS);

    iframe.addEventListener('error', function () {
      if (!settled) { settled = true; clearTimeout(timer); clearInterval(hs); fail(hero); window.removeEventListener('message', onMessage); }
    }, { once: true });
  }

  document.querySelectorAll('.hero-fullscreen').forEach(function (hero) {
    var video = hero.querySelector('.hero-bg-video');
    var iframe = hero.querySelector('.hero-bg-iframe');
    if (video) watchVideo(video, hero);
    else if (iframe) watchYouTube(iframe, hero);
  });
})();
