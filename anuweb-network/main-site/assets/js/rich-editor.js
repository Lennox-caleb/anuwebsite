/**
 * Shared rich-text editor setup (Quill) for the admin: newsletters and news.
 *   AnuRichEditor.init({ editor:'#id', textarea:'#id', message:'#id', form:'form', endpoint:'url', csrf:'token', placeholder:'...' })
 * The editor's HTML is copied into the hidden textarea when the form is submitted.
 * Pictures are uploaded to `endpoint` (never embedded as base64).
 */
(function (w) {
  w.AnuRichEditor = {
    init: function (o) {
      if (!w.Quill) return null;
      var quill = new Quill(o.editor, {
        theme: 'snow',
        placeholder: o.placeholder || 'Write here…',
        modules: { toolbar: { container: [
          [{ header: [2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ color: ['#000000', '#FF0000', '#B00000', '#555555', '#FAC823'] }],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ align: [] }],
          ['blockquote', 'link', 'image'],
          ['clean']
        ], handlers: { image: pickImage } } }
      });
      var msg = document.querySelector(o.message);
      function say(t, bad) { if (!msg) return; msg.textContent = t; msg.className = 'small mt-1 ' + (bad ? 'text-danger' : 'text-muted'); }
      function upload(file) {
        if (!file || !/^image\//.test(file.type)) { say('Please choose a picture file.', true); return; }
        var range = quill.getSelection(true) || { index: quill.getLength() };
        say('Uploading picture…');
        var fd = new FormData(); fd.append('csrf_token', o.csrf); fd.append('image', file);
        fetch(o.endpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) { return r.json().catch(function () { return { error: 'The server sent an unexpected reply.' }; }); })
          .then(function (j) {
            if (j.url) { quill.insertEmbed(range.index, 'image', j.url, 'user'); quill.setSelection(range.index + 1); say('Picture added.'); }
            else say(j.error || 'The picture could not be added.', true);
          })
          .catch(function () { say('The picture could not be uploaded. Check your connection and try again.', true); });
      }
      function pickImage() {
        var i = document.createElement('input'); i.type = 'file'; i.accept = 'image/jpeg,image/png,image/gif,image/webp';
        i.onchange = function () { if (i.files && i.files[0]) upload(i.files[0]); };
        i.click();
      }
      function firstImage(dt) { if (!dt || !dt.files) return null; for (var k = 0; k < dt.files.length; k++) if (/^image\//.test(dt.files[k].type)) return dt.files[k]; return null; }
      quill.root.addEventListener('paste', function (e) { var f = firstImage(e.clipboardData); if (f) { e.preventDefault(); e.stopPropagation(); upload(f); } }, true);
      quill.root.addEventListener('drop', function (e) { var f = firstImage(e.dataTransfer); if (f) { e.preventDefault(); e.stopPropagation(); upload(f); } }, true);
      var names = { 'ql-bold': 'Bold', 'ql-italic': 'Italic', 'ql-underline': 'Underline', 'ql-strike': 'Strikethrough', 'ql-blockquote': 'Quote', 'ql-link': 'Link', 'ql-image': 'Insert picture', 'ql-clean': 'Clear formatting' };
      var bar = quill.container.parentNode.querySelector('.ql-toolbar') || document;
      bar.querySelectorAll('button').forEach(function (b) {
        for (var c in names) if (b.classList.contains(c)) { b.setAttribute('aria-label', names[c]); b.setAttribute('title', names[c]); }
        if (b.classList.contains('ql-list')) { var ord = b.value === 'ordered'; b.setAttribute('aria-label', ord ? 'Numbered list' : 'Bullet list'); b.setAttribute('title', ord ? 'Numbered list' : 'Bullet list'); }
      });
      document.querySelector(o.form).addEventListener('submit', function () {
        quill.root.querySelectorAll('img[src^="data:"]').forEach(function (im) { im.remove(); });   // pasted inline pictures are not allowed; use the picture button
        document.querySelector(o.textarea).value = quill.root.innerHTML;
      });
      return quill;
    }
  };
})(window);
