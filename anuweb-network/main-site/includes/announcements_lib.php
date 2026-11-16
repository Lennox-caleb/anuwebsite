<?php
/**
 * Announcement pop-ups for every site that shares the ANU database.
 * announcements.target = 'main' (university main site only), 'all' (every site), or a unit slug (one site only).
 * Used by main-site (includes/popup.php) and by every unit site footer: anu_popup_render(UNIT_SLUG).
 */
if (!function_exists('anu_ann_ensure')) {
    function anu_ann_ensure(): void {
        static $done = false; if ($done) return; $done = true;
        try {
            $c = db()->query("SHOW COLUMNS FROM announcements LIKE 'target'")->fetch();
            if (!$c) db()->exec("ALTER TABLE announcements ADD COLUMN target VARCHAR(60) NOT NULL DEFAULT 'main'");
        } catch (Throwable $e) { error_log('announcements target: ' . $e->getMessage()); }
    }

    function anu_popup_render(string $unit = 'main'): void {
        try {
            anu_ann_ensure();
            $st = db()->prepare("SELECT * FROM announcements WHERE is_active = 1
                AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW())
                AND target IN ('all', ?)
                ORDER BY (target = 'all') ASC, id DESC LIMIT 1");
            $st->execute([$unit]);
            $announcement = $st->fetch();
        } catch (Throwable $e) { return; }
        if (!$announcement) return;
        $isMain = ($unit === 'main');
        $cta = (string)$announcement['cta_url'];
        if (!$isMain && $cta !== '' && $cta[0] === '/' && substr($cta, 0, 2) !== '//') {
            $cta = (in_array($announcement['target'], ['all', 'main'], true) ? MAIN_SITE_URL : BASE_URL) . $cta;
        }
        if (!$isMain): ?>
<style>
.anu-popup-open{overflow:hidden}
.anu-popup-overlay{position:fixed;inset:0;z-index:2000;background:rgba(10,10,10,.74);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;padding:1.25rem;opacity:0;transition:opacity .25s ease}
.anu-popup-overlay.show{display:flex;opacity:1}
.anu-popup-card{position:relative;background:#fff;color:#222;width:100%;max-width:580px;border-radius:14px;box-shadow:0 24px 60px rgba(0,0,0,.45);max-height:calc(100vh - 2.5rem);overflow-y:auto;font-family:inherit}
.anu-popup-card:focus{outline:none}
.anu-popup-card::before{content:"";display:block;height:6px;background:linear-gradient(90deg,#FF0000 0 70%,#FAC823 70% 100%)}
.anu-popup-card img{width:100%;height:auto;display:block}
.anu-popup-body{padding:1.6rem 1.75rem 1.9rem}
.anu-popup-body h2{font-size:clamp(1.35rem,3.2vw,1.8rem);font-weight:800;line-height:1.15;color:#000;margin:0 0 .85rem;padding-bottom:.7rem;position:relative}
.anu-popup-body h2::after{content:"";position:absolute;left:0;bottom:0;width:56px;height:4px;background:#FAC823;border-radius:2px}
.anu-popup-body p{color:#222;font-size:1.02rem;line-height:1.55;margin:0 0 1.35rem}
.anu-popup-actions{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem 1.25rem}
.anu-popup-actions .anu-popup-cta{display:inline-block;padding:.8rem 1.6rem;font-weight:700;border-radius:999px;background:#FF0000;color:#fff;border:2px solid #FF0000;text-decoration:none}
.anu-popup-actions .anu-popup-cta:hover{background:#000;border-color:#000;color:#fff}
.anu-popup-later{background:none;border:0;padding:.5rem .25rem;color:#000;font-weight:600;text-decoration:underline;text-decoration-color:#FF0000;text-decoration-thickness:2px;text-underline-offset:5px;cursor:pointer}
.anu-popup-close{position:absolute;top:.7rem;right:.7rem;width:44px;height:44px;font-size:1.7rem;line-height:1;background:#fff;color:#000;border:2px solid #000;border-radius:50%;box-shadow:0 4px 14px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:3;cursor:pointer}
.anu-popup-close:hover{background:#000;color:#fff}
.anu-popup-close:focus-visible,.anu-popup-later:focus-visible,.anu-popup-cta:focus-visible{outline:3px solid #FAC823;outline-offset:3px}
@media (max-width:575px){.anu-popup-body{padding:1.25rem 1.25rem 1.5rem}.anu-popup-actions .anu-popup-cta,.anu-popup-later{width:100%;text-align:center}}
</style>
<?php endif; ?>
<div class="anu-popup-overlay" id="anuPopup" data-announcement-id="<?= (int)$announcement['id'] ?>" aria-hidden="true">
  <div class="anu-popup-card" role="dialog" aria-modal="true" aria-labelledby="anuPopupTitle" aria-describedby="anuPopupMsg" tabindex="-1">
    <button type="button" class="anu-popup-close" id="anuPopupClose" aria-label="Close announcement">&times;</button>
    <?php if ($announcement['image']): ?>
      <img src="<?= h(UPLOADS_URL . '/' . $announcement['image']) ?>" alt="">
    <?php endif; ?>
    <div class="anu-popup-body">
      <h2 id="anuPopupTitle"><?= h($announcement['title']) ?></h2>
      <p id="anuPopupMsg"><?= nl2br(h($announcement['message'])) ?></p>
      <div class="anu-popup-actions">
        <?php if ($announcement['cta_text'] && $cta !== ''): ?>
          <a href="<?= h($cta) ?>" class="<?= $isMain ? 'btn btn-apply' : 'anu-popup-cta' ?>"><?= h($announcement['cta_text']) ?></a>
        <?php endif; ?>
        <button type="button" class="anu-popup-later" id="anuPopupLater">Maybe later</button>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var overlay = document.getElementById('anuPopup');
  if (!overlay) return;
  var card = overlay.querySelector('.anu-popup-card');
  var id = overlay.getAttribute('data-announcement-id');
  var key = 'anu_popup_dismissed_' + id;
  try { if (sessionStorage.getItem(key)) return; } catch (e) {}
  var opener = null;
  function focusables(){ return card.querySelectorAll('a[href], button:not([disabled])'); }
  function open(){
    opener = document.activeElement;
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('anu-popup-open');
    var close = document.getElementById('anuPopupClose');
    (close || card).focus();
  }
  function dismiss(){
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('anu-popup-open');
    try { sessionStorage.setItem(key, '1'); } catch (e) {}
    document.removeEventListener('keydown', onKey);
    if (opener && opener.focus) { try { opener.focus(); } catch (e) {} }
  }
  function onKey(e){
    if (e.key === 'Escape') { dismiss(); return; }
    if (e.key !== 'Tab') return;
    var f = focusables(); if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }
  document.getElementById('anuPopupClose').addEventListener('click', dismiss);
  document.getElementById('anuPopupLater').addEventListener('click', dismiss);
  overlay.addEventListener('click', function(e){ if (e.target === overlay) dismiss(); });
  setTimeout(function(){ open(); document.addEventListener('keydown', onKey); }, 900);
})();
</script>
<?php
    }
}
