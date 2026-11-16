<?php
/**
 * TV-style "Latest news" ticker (homepage). Uses ONLY real, published news
 * rows -- if there are none, nothing is rendered. Headlines link to the
 * article. Pauses on hover/focus and via a pause button (accessibility);
 * with "reduce motion" it becomes a plain scrollable row.
 */
$__tickerScope = function_exists('unit_scope_sql') ? unit_scope_sql('news') : '';
$__tickerNews = db()->query("SELECT title, slug, published_at FROM news WHERE is_published=1{$__tickerScope} ORDER BY published_at DESC LIMIT 8")->fetchAll();
if ($__tickerNews):
    $__items = function (bool $hidden) use ($__tickerNews) {
        $out = '';
        foreach ($__tickerNews as $n) {
            $d = !empty($n['published_at']) ? date('j M', strtotime($n['published_at'])) : '';
            $out .= '<li><a href="' . h(BASE_URL . '/news-single.php?slug=' . $n['slug']) . '"' . ($hidden ? ' tabindex="-1"' : '') . '>'
                  . ($d ? '<time>' . h($d) . '</time>' : '') . h($n['title']) . '</a></li>';
        }
        return $out;
    };
?>
<section class="anu-ticker" aria-label="Latest news" id="anuTicker">
  <div class="anu-ticker-label"><span class="anu-ticker-dot" aria-hidden="true"></span><span class="d-none d-sm-inline">Latest </span>News</div>
  <div class="anu-ticker-viewport">
    <div class="anu-ticker-track">
      <ul class="anu-ticker-set"><?= $__items(false) ?></ul>
      <ul class="anu-ticker-set" aria-hidden="true"><?= $__items(true) ?></ul>
    </div>
  </div>
  <button type="button" class="anu-ticker-toggle" id="anuTickerToggle" aria-label="Pause news ticker" aria-pressed="false"><i class="bi bi-pause-fill"></i></button>
  <a class="anu-ticker-all d-none d-md-inline-flex" href="<?= BASE_URL ?>/news-events.php">All news &rarr;</a>
</section>
<script>
(function(){
  var box = document.getElementById('anuTicker'); if (!box) return;
  var track = box.querySelector('.anu-ticker-track');
  var set = box.querySelector('.anu-ticker-set');
  var btn = document.getElementById('anuTickerToggle');
  function speed(){ var w = set.getBoundingClientRect().width; if (w) track.style.setProperty('--ticker-dur', Math.max(20, Math.round(w / 70)) + 's'); }
  speed(); window.addEventListener('load', speed); window.addEventListener('resize', speed);
  btn.addEventListener('click', function(){
    var paused = box.classList.toggle('is-paused');
    btn.setAttribute('aria-pressed', paused ? 'true' : 'false');
    btn.setAttribute('aria-label', paused ? 'Play news ticker' : 'Pause news ticker');
    btn.firstElementChild.className = paused ? 'bi bi-play-fill' : 'bi bi-pause-fill';
  });
})();
</script>
<?php endif; ?>
