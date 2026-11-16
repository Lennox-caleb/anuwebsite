/**
 * Hero video controls (Audit Part 2): mute/unmute + play/pause.
 *
 * Progressive enhancement: injects two buttons into every .hero-fullscreen
 * that has a background <video> or YouTube iframe, so all school sites get
 * them from this one file. Without JS the hero behaves exactly as before
 * (muted autoplay loop).
 *
 * Behaviour
 *  - Always starts muted (browsers block autoplay with sound). The sound
 *    choice is NOT remembered across page loads because browsers would
 *    block unmuted autoplay there anyway; the pause choice is remembered
 *    for the browser session.
 *  - prefers-reduced-motion: CSS already hides the moving media; this
 *    script also pauses it and adds no controls.
 *  - Data-saver (Save-Data / 2g connections): video is paused on load and
 *    shows the poster; the visitor can press Play.
 *  - Pauses when the hero is mostly off-screen or the tab is hidden, and
 *    resumes only if the visitor had not paused it themselves.
 *  - YouTube: controlled through the IFrame postMessage API (enablejsapi=1
 *    is already set by hero_render.php).
 */
(function () {
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var conn = navigator.connection || {};
  var saveData = conn.saveData === true || /(^|-)2g$/.test(conn.effectiveType || '');
  var KEY = 'anuHeroPaused';
  var storedPause = false;
  try { storedPause = sessionStorage.getItem(KEY) === '1'; } catch (e) {}

  function icon(name) {
    var paths = {
      muted: '<path d="M11 5 6 9H2v6h4l5 4V5z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/>',
      sound: '<path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/>',
      pause: '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>',
      play:  '<polygon points="6 3 20 12 6 21 6 3"/>'
    };
    return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + paths[name] + '</svg>';
  }

  function makeButton(cls) {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'hero-ctl ' + cls;
    return b;
  }

  function setup(hero) {
    var video = hero.querySelector('.hero-bg-video');
    var iframe = hero.querySelector('.hero-bg-iframe');
    if (!video && !iframe) return;

    var state = { muted: true, userPaused: storedPause || saveData, playing: false, ytReady: false };
    var ytQueue = [];

    function yt(func, args) {
      var msg = JSON.stringify({ event: 'command', func: func, args: args || [] });
      if (state.ytReady) iframe.contentWindow.postMessage(msg, '*'); else ytQueue.push(msg);
    }
    function doPlay() {
      state.playing = true;
      if (video) { var p = video.play(); if (p && p.catch) p.catch(function () {}); } else yt('playVideo');
    }
    function doPause() {
      state.playing = false;
      if (video) video.pause(); else yt('pauseVideo');
    }
    function doMute(m) {
      state.muted = m;
      if (video) video.muted = m; else yt(m ? 'mute' : 'unMute');
    }

    if (iframe) {
      // YouTube only starts sending/accepting events after a "listening" handshake.
      var hs = function () {
        iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening', id: 1, channel: 'widget' }), '*');
        state.ytReady = true;
        ytQueue.splice(0).forEach(function (m) { iframe.contentWindow.postMessage(m, '*'); });
        if (state.userPaused) doPause();
      };
      iframe.addEventListener('load', hs);
      // Keep the background video going: if YouTube pauses or ends it on its
      // own (buffering hiccup, loop edge) while it should be playing, restart it.
      window.addEventListener('message', function (e) {
        if (e.source !== iframe.contentWindow || typeof e.data !== 'string') return;
        var d; try { d = JSON.parse(e.data); } catch (err) { return; }
        if (!d) return;
        var st = (d.event === 'onStateChange') ? d.info : (d.event === 'infoDelivery' && d.info ? d.info.playerState : null);
        if (st === 0 && !state.userPaused && state.playing) { yt('seekTo', [0, true]); yt('playVideo'); }
        else if (st === 2 && !state.userPaused && state.playing && !document.hidden) { setTimeout(function () { if (!state.userPaused && state.playing) yt('playVideo'); }, 400); }
      });
      setTimeout(function () { if (!state.ytReady) hs(); }, 1500); // load may already have fired
    }

    if (reduce) { if (video) video.pause(); return; } // CSS hides the media; no controls needed
    if (state.userPaused) { state.playing = false; if (video) { video.removeAttribute('autoplay'); video.pause(); } }
    else state.playing = true;

    var wrap = document.createElement('div');
    wrap.className = 'hero-controls';
    wrap.setAttribute('role', 'group');
    wrap.setAttribute('aria-label', 'Background video controls');
    var play = makeButton('hero-ctl-play');
    var mute = makeButton('hero-ctl-mute');
    wrap.appendChild(play); wrap.appendChild(mute);
    hero.appendChild(wrap);

    function paint() {
      play.innerHTML = icon(state.playing ? 'pause' : 'play');
      play.setAttribute('aria-label', state.playing ? 'Pause background video' : 'Play background video');
      play.setAttribute('aria-pressed', state.playing ? 'false' : 'true');
      play.title = play.getAttribute('aria-label');
      mute.innerHTML = icon(state.muted ? 'muted' : 'sound');
      mute.setAttribute('aria-label', state.muted ? 'Turn sound on' : 'Turn sound off');
      mute.setAttribute('aria-pressed', state.muted ? 'false' : 'true');
      mute.title = mute.getAttribute('aria-label');
    }

    play.addEventListener('click', function (e) {
      e.stopPropagation();
      if (state.playing) { state.userPaused = true; doPause(); } else { state.userPaused = false; doPlay(); }
      try { sessionStorage.setItem(KEY, state.userPaused ? '1' : '0'); } catch (e) {}
      paint();
    });
    mute.addEventListener('click', function (e) {
      e.stopPropagation();
      if (iframe && !state.ytReady) { try { iframe.contentWindow.postMessage(JSON.stringify({ event: 'listening', id: 1, channel: 'widget' }), '*'); } catch (err) {} state.ytReady = true; }
      doMute(!state.muted);
      if (iframe && !state.muted) yt('setVolume', [100]);
      paint();
    });

    // Pause off-screen / hidden tab; resume only if the visitor didn't pause.
    var visible = true;
    function sync() {
      var shouldPlay = visible && !document.hidden && !state.userPaused;
      if (shouldPlay && !state.playing) { doPlay(); paint(); }
      else if (!shouldPlay && state.playing) { doPause(); paint(); }
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].intersectionRatio >= 0.25 || entries[0].isIntersecting && entries[0].intersectionRatio > 0.1;
        sync();
      }, { threshold: [0, 0.1, 0.25, 0.5] }).observe(hero);
    }
    document.addEventListener('visibilitychange', sync);
    paint();
  }

  function init() { document.querySelectorAll('.hero-fullscreen').forEach(setup); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
