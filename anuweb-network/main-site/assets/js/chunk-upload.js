/**
 * Resumable chunked uploader for big hero videos (admin).
 * Markup: <div data-chunk-upload data-endpoint="..." data-token="..." data-target="#hiddenInputId"> with a file input inside.
 * 1 MB pieces, 3 retries per piece with back-off, and resume: the server reports which pieces it already has,
 * so re-selecting the same file after a dropped connection continues where it stopped.
 */
(function () {
  var CHUNK = 1024 * 1024, MAX = 2 * 1024 * 1024 * 1024;
  document.querySelectorAll('[data-chunk-upload]').forEach(function (box) {
    var input = box.querySelector('input[type=file]');
    var bar = box.querySelector('.cu-bar'), msg = box.querySelector('.cu-msg');
    var target = document.querySelector(box.dataset.target);
    var endpoint = box.dataset.endpoint, token = box.dataset.token;
    var form = box.closest('form'), busy = false;

    function say(t, bad) { msg.textContent = t; msg.className = 'cu-msg small ' + (bad ? 'text-danger' : 'text-muted'); }
    function post(fd) { return fetch(endpoint, { method: 'POST', body: fd, headers: { 'X-CSRF-Token': token }, credentials: 'same-origin' }).then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Unexpected server reply (HTTP ' + r.status + ').' }; }); }); }
    function wait(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }
    // stable id per file so a retry of the same file resumes
    function idFor(f) { var s = f.name + '|' + f.size + '|' + f.lastModified; return crypto.subtle.digest('SHA-256', new TextEncoder().encode(s)).then(function (b) { return Array.prototype.map.call(new Uint8Array(b).slice(0, 16), function (x) { return ('0' + x.toString(16)).slice(-2); }).join(''); }); }

    input.addEventListener('change', function () {
      var f = input.files[0]; if (!f) return;
      if (f.size > MAX) { say('That file is over the 2 GB limit.', true); input.value = ''; return; }
      if (!/\.(mp4|webm)$/i.test(f.name)) { say('Choose an MP4 or WEBM file.', true); input.value = ''; return; }
      busy = true; target.value = ''; run(f).then(function () { busy = false; }, function (e) { busy = false; say(String(e && e.message || e), true); });
    });
    if (form) form.addEventListener('submit', function (e) { if (busy) { e.preventDefault(); say('Please wait for the upload to finish before saving.', true); } });

    function run(f) {
      var total = Math.ceil(f.size / CHUNK), done = 0;
      return idFor(f).then(function (id) {
        return fetch(endpoint + '?action=status&upload_id=' + id, { headers: { 'X-CSRF-Token': token }, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (st) {
          if (!st.ok) throw new Error(st.error || 'Could not start the upload.');
          var have = {}; (st.have || []).forEach(function (i) { have[i] = 1; }); done = Object.keys(have).length;
          if (done) say('Resuming: ' + done + ' of ' + total + ' parts already uploaded.');
          var chain = Promise.resolve();
          for (var i = 0; i < total; i++) (function (i) {
            if (have[i]) return;
            chain = chain.then(function () { return sendPiece(f, id, i, total).then(function () { done++; paint(done, total, f); }); });
          })(i);
          return chain.then(function () {
            say('Joining the parts on the server…');
            var fd = new FormData(); fd.append('action', 'finish'); fd.append('upload_id', id); fd.append('total', total);
            return post(fd);
          });
        }).then(function (res) {
          if (!res.ok) throw new Error(res.error || 'Could not finish the upload.');
          target.value = res.file; bar.style.width = '100%';
          say('Uploaded   (' + (res.bytes / 1048576).toFixed(1) + ' MB). Now press Save to attach it.');
        });
      });
    }
    function sendPiece(f, id, i, total) {
      var attempt = 0;
      function go() {
        var fd = new FormData(); fd.append('action', 'chunk'); fd.append('upload_id', id); fd.append('index', i); fd.append('total', total);
        fd.append('chunk', f.slice(i * CHUNK, Math.min(f.size, (i + 1) * CHUNK)), 'part');
        return post(fd).catch(function () { return { ok: false, error: 'Network problem.' }; }).then(function (r) {
          if (r.ok) return;
          if (++attempt >= 3) throw new Error((r.error || 'Upload failed') + ' You can select the same file again to resume.');
          return wait(800 * attempt).then(go);
        });
      }
      return go();
    }
    function paint(done, total, f) { bar.style.width = (done / total * 100).toFixed(1) + '%'; say('Uploading ' + (done / total * 100).toFixed(0) + '%  (' + (Math.min(done * CHUNK, f.size) / 1048576).toFixed(0) + ' of ' + (f.size / 1048576).toFixed(0) + ' MB)'); }
  });
})();
