<?php
$pageTitle = 'Campus Tour';
$pageDescription = 'Take a video tour of the Africa Nazarene University campus and jump straight to the place you want to see.';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/campus_tour_lib.php';

$videoId = ct_video_id(ct_video_url());
$places  = $videoId ? ct_places(true) : [];
$js = [];
foreach ($places as $p) {
    $js[] = ['t' => (int)$p['start_seconds'], 'n' => $p['title'], 'd' => $p['description']];
}

require __DIR__ . '/includes/header.php';
?>
<style>
.ct-wrap{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:24px;align-items:start}
@media(max-width:991px){.ct-wrap{grid-template-columns:minmax(0,1fr)}}
.ct-wrap.ct-solo{grid-template-columns:minmax(0,1fr)}
.ct-player{background:#000;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,.25)}
.ct-frame{position:relative;aspect-ratio:16/9;background:#000}
.ct-frame iframe,.ct-frame #ctPlayer{position:absolute;inset:0;width:100%;height:100%;border:0}
.ct-now{display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;padding:12px 16px;color:#fff;background:#000;border-top:3px solid var(--anu-red)}
.ct-now .lbl{font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--anu-gold)}
.ct-now .ttl{font-weight:700;font-size:1.1rem;line-height:1.2}
.ct-now .nav{display:flex;gap:8px}
.ct-btn{background:var(--anu-gold);color:#000;border:0;border-radius:6px;padding:8px 14px;font-weight:700;min-height:40px}
.ct-btn:hover{background:#fff}
.ct-btn:disabled{opacity:.4;cursor:default}
.ct-segs{display:flex;gap:3px;padding:0 16px 14px;background:#000}
.ct-seg{flex:1;height:8px;border:0;border-radius:4px;background:rgba(255,255,255,.28);padding:0;cursor:pointer}
.ct-seg:hover{background:rgba(255,255,255,.55)}
.ct-seg.done{background:var(--anu-gold)}
.ct-seg.on{background:var(--anu-red)}
.ct-panel{border:1px solid var(--anu-line);border-radius:12px;background:#fff;padding:8px}
.ct-panel h2{font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;color:rgba(0,0,0,.6);margin:10px 10px 6px}
.ct-list{list-style:none;margin:0;padding:0;max-height:520px;overflow:auto}
.ct-list button{width:100%;display:grid;grid-template-columns:34px minmax(0,1fr) auto;gap:10px;align-items:center;text-align:left;background:none;border:0;border-radius:8px;padding:10px;color:#000}
.ct-list button:hover{background:var(--anu-panel)}
.ct-list .no{width:34px;height:34px;border-radius:50%;background:#000;color:var(--anu-gold);display:grid;place-items:center;font-weight:700;font-size:.85rem}
.ct-list .nm{font-weight:500;display:block}
.ct-list .ds{display:block;font-size:.8rem;color:rgba(0,0,0,.6)}
.ct-list .tm{font-variant-numeric:tabular-nums;font-size:.82rem;color:rgba(0,0,0,.6)}
.ct-list li.on button{background:var(--anu-panel);box-shadow:inset 4px 0 0 var(--anu-red)}
.ct-list li.on .nm{font-weight:700}
.ct-list li.on .no{background:var(--anu-red);color:#fff}
.ct-cta{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.ct-cta .out{border:1px solid var(--anu-line);border-radius:8px;padding:10px 16px;font-weight:700;text-decoration:none;color:#000;background:#fff}
.ct-cta .out:hover{background:var(--anu-gold)}
</style>

<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">Visit us from wherever you are</span>
    <h1>Walk the ANU Campus</h1>
    <p class="lead text-muted mb-0">Watch the full tour, or jump straight to the place you want to see.</p>
  </div>
</section>

<section class="py-5">
  <div class="container container-narrow">
  <?php if (!$videoId): ?>
    <p class="text-muted text-center">The campus tour video is coming soon.</p>
  <?php else: ?>
    <div class="ct-wrap<?= $js ? '' : ' ct-solo' ?>">
      <div>
        <div class="ct-player">
          <div class="ct-frame"><div id="ctPlayer"></div></div>
          <?php if ($js): ?>
          <div class="ct-now">
            <div><div class="lbl" id="ctLbl">Place 1 of <?= count($js) ?></div><div class="ttl" id="ctTtl"><?= h($js[0]['n']) ?></div></div>
            <div class="nav">
              <button type="button" class="ct-btn" id="ctPrev" aria-label="Previous place">&larr; Previous</button>
              <button type="button" class="ct-btn" id="ctNext" aria-label="Next place">Next &rarr;</button>
            </div>
          </div>
          <div class="ct-segs" id="ctSegs" aria-hidden="true"></div>
          <?php endif; ?>
        </div>
        <noscript><p class="mt-3"><a href="https://www.youtube.com/watch?v=<?= h($videoId) ?>" target="_blank" rel="noopener">Watch the campus tour on YouTube</a></p></noscript>
        <div class="ct-cta">
          <a class="btn btn-apply" href="<?= h(setting('apply_url', BASE_URL . '/admissions-how-to-apply.php')) ?>" target="_blank" rel="noopener">Apply now</a>
          <a class="out" href="<?= BASE_URL ?>/contact.php">Plan a visit</a>
          <a class="out" href="https://www.youtube.com/watch?v=<?= h($videoId) ?>" target="_blank" rel="noopener">Watch on YouTube</a>
        </div>
      </div>
      <?php if ($js): ?>
      <aside class="ct-panel" aria-label="Places on the tour">
        <h2>Places on this tour</h2>
        <ul class="ct-list" id="ctList">
          <?php foreach ($js as $i => $p): ?>
          <li><button type="button" data-i="<?= $i ?>"><span class="no"><?= $i + 1 ?></span><span><span class="nm"><?= h($p['n']) ?></span><?php if ($p['d'] !== ''): ?><span class="ds"><?= h($p['d']) ?></span><?php endif; ?></span><span class="tm"><?= h(ct_fmt_time($p['t'])) ?></span></button></li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <?php endif; ?>
    </div>

    <script>
    (function(){
      var VID=<?= json_encode($videoId) ?>, P=<?= json_encode($js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      var yt=null, cur=0, dur=0, tick=null;
      function $(i){return document.getElementById(i)}
      function idxAt(t){var k=0;for(var i=0;i<P.length;i++)if(t>=P[i].t)k=i;return k}
      function paint(i){
        if(!P.length)return; cur=i;
        $('ctLbl').textContent='Place '+(i+1)+' of '+P.length; $('ctTtl').textContent=P[i].n;
        var li=$('ctList').children; for(var j=0;j<li.length;j++)li[j].classList.toggle('on',j===i);
        var sg=$('ctSegs').children; for(var s=0;s<sg.length;s++){sg[s].className='ct-seg'+(s<i?' done':(s===i?' on':''))}
        $('ctPrev').disabled=(i===0); $('ctNext').disabled=(i===P.length-1);
      }
      function go(i){ if(!yt||i<0||i>=P.length)return; yt.seekTo(P[i].t,true); yt.playVideo(); paint(i) }
      function buildSegs(){
        var box=$('ctSegs'); if(!box||!P.length)return; box.innerHTML='';
        for(var i=0;i<P.length;i++){
          var end=(i<P.length-1)?P[i+1].t:(dur||P[i].t+60), w=Math.max(end-P[i].t,1);
          var b=document.createElement('button'); b.type='button'; b.className='ct-seg'; b.style.flex=String(w);
          b.title=P[i].n; b.setAttribute('aria-label',P[i].n); (function(n){b.onclick=function(){go(n)}})(i); box.appendChild(b);
        }
        box.removeAttribute('aria-hidden'); paint(cur);
      }
      if(P.length){
        $('ctPrev').onclick=function(){ var t=yt?yt.getCurrentTime():0; go(t-P[cur].t>4?cur:Math.max(0,cur-1)) };
        $('ctNext').onclick=function(){ go(Math.min(P.length-1,cur+1)) };
        var bs=$('ctList').querySelectorAll('button'); for(var b=0;b<bs.length;b++)(function(btn){btn.onclick=function(){go(+btn.getAttribute('data-i'));$('ctPlayer').scrollIntoView&&window.innerWidth<992&&window.scrollTo({top:$('ctPlayer').getBoundingClientRect().top+window.pageYOffset-90,behavior:'smooth'})}})(bs[b]);
        paint(0); buildSegs();
      }
      window.onYouTubeIframeAPIReady=function(){
        yt=new YT.Player('ctPlayer',{host:'https://www.youtube-nocookie.com',videoId:VID,
          playerVars:{rel:0,modestbranding:1,playsinline:1},
          events:{onReady:function(){dur=yt.getDuration()||0;buildSegs()},
            onStateChange:function(e){
              clearInterval(tick);
              if(e.data===1){ if(!dur){dur=yt.getDuration()||0;buildSegs()}
                tick=setInterval(function(){var i=idxAt(yt.getCurrentTime());if(i!==cur)paint(i)},500)}
            }}});
      };
      var s=document.createElement('script'); s.src='https://www.youtube.com/iframe_api'; s.async=true; document.head.appendChild(s);
    })();
    </script>
  <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
