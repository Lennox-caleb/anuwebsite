<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tracker.php';
require_once __DIR__ . '/includes/layout.php';

$db = getDB();

function ec(array $r, string $k, $d = '') { return $r[$k] ?? $d; }

function ytId(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/', $url, $m)) return $m[1];
    return '';
}
function ytThumb(string $url): string {
    $id = ytId($url);
    return $id ? "https://img.youtube.com/vi/{$id}/hqdefault.jpg" : '';
}
function ytEmbed(string $url, bool $auto = false): string {
    $id = ytId($url);
    if ($id) return "https://www.youtube.com/embed/{$id}?autoplay=".($auto?1:0)."&rel=0&modestbranding=1";
    return $url;
}
function countdown(string $date, string $time = '00:00'): array {
    $diff = strtotime($date.' '.$time) - time();
    if ($diff <= 0) return ['label'=>'Happening now','secs'=>0];
    $d = floor($diff/86400); $h = floor(($diff%86400)/3600);
    $m = floor(($diff%3600)/60); $s = $diff%60;
    return ['d'=>$d,'h'=>$h,'m'=>$m,'s'=>$s,'ts'=>strtotime($date.' '.$time),'secs'=>$diff];
}

// Fetch
$allEvents = [];
try {
    $allEvents = $db->query(
        "SELECT * FROM events WHERE is_active=1
         ORDER BY is_live DESC, is_featured DESC, event_date ASC, event_time ASC"
    )->fetchAll();
} catch(Exception $e) { $allEvents = []; }

$liveNow  = array_values(array_filter($allEvents, fn($e)=>ec($e,'is_live',0)==1));
$upcoming = array_values(array_filter($allEvents, fn($e)=>ec($e,'is_live',0)==0 && strtotime($e['event_date'].' '.(ec($e,'event_time','00:00')))>=strtotime('today')));
$past     = array_values(array_filter($allEvents, fn($e)=>ec($e,'is_live',0)==0 && strtotime($e['event_date'].' '.(ec($e,'event_time','00:00')))< strtotime('today')));

$catLabels = ['academic'=>'Academic','chapel'=>'Chapel','university'=>'University','orientation'=>'Orientation','other'=>'Other'];
$catColors = ['academic'=>'#1565C0','chapel'=>'#6a1b9a','university'=>'#c1121f','orientation'=>'#2e7d32','other'=>'#5d4037'];
$catBgs    = ['academic'=>'rgba(21,101,192,.12)','chapel'=>'rgba(106,27,154,.12)','university'=>'rgba(193,18,31,.12)','orientation'=>'rgba(46,125,50,.12)','other'=>'rgba(93,64,55,.12)'];
$platIcon  = ['youtube'=>'bi-youtube','zoom'=>'bi-camera-video-fill','teams'=>'bi-windows','facebook'=>'bi-facebook','other'=>'bi-broadcast-pin'];
$platColor = ['youtube'=>'#FF0000','zoom'=>'#2D8CFF','teams'=>'#5C2D91','facebook'=>'#1877F2','other'=>'#888'];

// JSON-encode events for JS
$eventsJson = json_encode(array_map(function($e) use (&$upcoming, &$past) {
    $su = ec($e,'stream_url',''); $ru = ec($e,'recording_url','');
    $activeUrl = $su ?: $ru ?: ec($e,'link','');
    $id = ytId($activeUrl);
    return [
        'id'        => $e['id'],
        'title'     => ec($e,'title','Untitled'),
        'desc'      => ec($e,'description',''),
        'date'      => $e['event_date'],
        'time'      => ec($e,'event_time',''),
        'category'  => ec($e,'category','academic'),
        'platform'  => ec($e,'stream_platform','other'),
        'stream_url'=> $su,
        'rec_url'   => $ru,
        'link'      => ec($e,'link',''),
        'embed_url' => $id ? "https://www.youtube.com/embed/{$id}?autoplay=1&rel=0&modestbranding=1" : ($activeUrl?:null),
        'thumb'     => ec($e,'thumbnail','') ? imgUrl(ec($e,'thumbnail','')) : ($id?"https://img.youtube.com/vi/{$id}/hqdefault.jpg":null),
        'speaker'   => ec($e,'speaker_name',''),
        'speaker_t' => ec($e,'speaker_title',''),
        'is_live'   => (bool)ec($e,'is_live',0),
        'is_free'   => (bool)ec($e,'is_free',1),
        'is_featured'=>(bool)ec($e,'is_featured',0),
        'reg_url'   => ec($e,'registration_url',''),
        'location'  => ec($e,'location',''),
    ];
}, $allEvents));

trackPage('livestream', 'Live Events');
renderHead('Live Events Portal'); renderNavbar('livestream');
?>

<!-- ══ PORTAL HEADER ══ -->
<div class="prt-header">
  <div class="prt-header-bg"></div>
  <div class="container prt-header-inner">
    <div class="prt-brand">
      <img src="/anu/assets/images/anu-logo-white.png" alt="ANU Online" class="prt-logo">
      <div class="prt-brand-text">
        <span class="prt-brand-name">ANU Online</span>
        <span class="prt-brand-sub">Live Events & Webinars</span>
      </div>
    </div>
    <div class="prt-header-right">
      <?php if(!empty($liveNow)): ?>
      <div class="prt-live-indicator">
        <span class="prt-live-dot"></span>
        <?= count($liveNow) ?> Live Now
      </div>
      <?php endif; ?>
      <a href="/anu/contact.php" class="prt-help-btn">
        <i class="bi bi-question-circle me-1"></i>Help
      </a>
    </div>
  </div>
</div>

<!-- ══ PORTAL TABS ══ -->
<div class="prt-tabs-bar" id="prtTabBar">
  <div class="container">
    <div class="prt-tabs">
      <button class="prt-tab <?=!empty($liveNow)?'prt-tab-live':''?> <?=empty($liveNow)?'active':''?>" onclick="prtTab('upcoming',this)" id="tabUpcoming">
        <i class="bi bi-calendar-event me-2"></i>Upcoming
        <?php if(!empty($upcoming)): ?>
        <span class="prt-tab-count"><?=count($upcoming)?></span>
        <?php endif; ?>
      </button>
      <?php if(!empty($liveNow)): ?>
      <button class="prt-tab active prt-tab-live" onclick="prtTab('live',this)" id="tabLive">
        <span class="prt-tab-dot"></span>Live Now
        <span class="prt-tab-count prt-count-red"><?=count($liveNow)?></span>
      </button>
      <?php endif; ?>
      <button class="prt-tab" onclick="prtTab('past',this)" id="tabPast">
        <i class="bi bi-collection-play me-2"></i>On Demand
        <?php if(!empty($past)): ?>
        <span class="prt-tab-count"><?=count($past)?></span>
        <?php endif; ?>
      </button>
    </div>
    <!-- Category filter pills -->
    <div class="prt-cat-filters" id="prtCatFilters">
      <button class="prt-cat-btn active" onclick="prtFilterCat('all',this)">All</button>
      <?php foreach($catLabels as $k=>$v): ?>
      <button class="prt-cat-btn" onclick="prtFilterCat('<?=$k?>',this)"><?=$v?></button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ PORTAL BODY ══ -->
<div class="prt-body" id="prtBody">
  <div class="container">

    <!-- Player panel (hidden until event selected) -->
    <div class="prt-player-wrap" id="prtPlayerWrap" style="display:none">
      <div class="prt-player-layout">
        <!-- Left: video + info -->
        <div class="prt-player-main">
          <div class="prt-player-frame" id="prtPlayerFrame">
            <iframe id="prtIframe" src="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
          </div>
          <!-- Event info bar -->
          <div class="prt-info-bar" id="prtInfoBar">
            <div class="prt-info-badges" id="prtInfoBadges"></div>
            <div class="prt-info-mid">
              <h2 class="prt-info-title" id="prtInfoTitle"></h2>
              <div class="prt-info-meta" id="prtInfoMeta"></div>
            </div>
            <div class="prt-info-actions" id="prtInfoActions"></div>
          </div>
          <!-- View mode toggle -->
          <div class="prt-view-modes">
            <button class="prt-view-btn active" onclick="prtViewMode('split',this)" title="Split View"><i class="bi bi-layout-split"></i></button>
            <button class="prt-view-btn" onclick="prtViewMode('full',this)" title="Full View"><i class="bi bi-fullscreen"></i></button>
            <button class="prt-view-btn" onclick="prtClose()" title="Close player"><i class="bi bi-x-lg"></i></button>
          </div>
        </div>
        <!-- Right: sidebar list -->
        <div class="prt-player-sidebar" id="prtSidebar">
          <div class="prt-sidebar-hdr">
            <span id="prtSidebarLabel">Events</span>
          </div>
          <div class="prt-sidebar-list" id="prtSidebarList"></div>
        </div>
      </div>
    </div>

    <!-- ── Tab: Live Now ── -->
    <div class="prt-tab-panel" id="panelLive" style="display:none">
      <?php if(empty($liveNow)): ?>
      <div class="prt-empty">
        <i class="bi bi-broadcast-pin"></i>
        <h4>Nothing streaming right now</h4>
        <p>Check the Upcoming tab for what's coming next.</p>
        <button onclick="prtTab('upcoming',document.getElementById('tabUpcoming'))" class="prt-empty-btn">View Schedule</button>
      </div>
      <?php else: ?>
      <div class="prt-grid" id="gridLive">
        <?php foreach($liveNow as $i=>$ev):
          $su = ec($ev,'stream_url',''); $ru = ec($ev,'recording_url','');
          $activeUrl = $su ?: $ru ?: ec($ev,'link','');
          $ytid = ytId($activeUrl);
          $thumb = ec($ev,'thumbnail','') ? imgUrl($ev['thumbnail']) : ($ytid?"https://img.youtube.com/vi/{$ytid}/hqdefault.jpg":'');
          $cat = ec($ev,'category','academic');
        ?>
        <div class="prt-card prt-card-live" data-id="<?=$ev['id']?>" data-cat="<?=$cat?>"
             onclick="prtPlay(<?=$ev['id']?>)">
          <div class="prt-card-thumb">
            <?php if($thumb): ?><img src="<?=e($thumb)?>" alt="<?=e($ev['title'])?>" loading="lazy" onerror="this.closest('.prt-card-thumb').classList.add('prt-no-thumb')">
            <?php else: ?><div class="prt-no-thumb-inner"><i class="bi bi-broadcast-pin"></i></div><?php endif; ?>
            <div class="prt-card-live-badge"><span class="prt-live-dot"></span>LIVE</div>
            <?php if($ytid||$activeUrl): ?><div class="prt-card-play"><i class="bi bi-play-circle-fill"></i></div><?php endif; ?>
          </div>
          <div class="prt-card-body">
            <div class="prt-card-tags">
              <span class="prt-cat-tag" style="background:<?=$catBgs[$cat]?>;color:<?=$catColors[$cat]?>"><?=$catLabels[$cat]?></span>
              <?php if(ec($ev,'is_free',1)): ?><span class="prt-free-tag">FREE</span><?php endif; ?>
            </div>
            <h4 class="prt-card-title"><?=e($ev['title'])?></h4>
            <?php if(ec($ev,'speaker_name','')): ?>
            <div class="prt-card-speaker"><i class="bi bi-person-fill"></i><?=e($ev['speaker_name'])?></div>
            <?php endif; ?>
            <div class="prt-card-meta">
              <span><i class="bi bi-clock"></i><?=date('g:i A',strtotime(ec($ev,'event_time','00:00')))?> EAT</span>
              <span style="color:<?=$platColor[ec($ev,'stream_platform','other')]?>">
                <i class="bi <?=$platIcon[ec($ev,'stream_platform','other')]?>"></i>
              </span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Tab: Upcoming ── -->
    <div class="prt-tab-panel" id="panelUpcoming" style="display:<?=empty($liveNow)?'block':'none'?>">
      <?php if(empty($upcoming)): ?>
      <div class="prt-empty">
        <i class="bi bi-calendar-x"></i>
        <h4>No upcoming events scheduled</h4>
        <p>Check back soon for the next live session.</p>
      </div>
      <?php else: ?>
      <div class="prt-grid" id="gridUpcoming">
        <?php foreach($upcoming as $i=>$ev):
          $su = ec($ev,'stream_url',''); $ytid = ytId($su);
          $thumb = ec($ev,'thumbnail','') ? imgUrl($ev['thumbnail']) : ($ytid?"https://img.youtube.com/vi/{$ytid}/hqdefault.jpg":'');
          $cat  = ec($ev,'category','academic');
          $cd   = countdown($ev['event_date'], ec($ev,'event_time','00:00'));
          $hasStream = !empty($su)||!empty(ec($ev,'link',''));
        ?>
        <div class="prt-card" data-id="<?=$ev['id']?>" data-cat="<?=$cat?>"
             onclick="<?=$hasStream?"prtPlay({$ev['id']})":'void(0)'?>"
             style="<?=$hasStream?'cursor:pointer':'cursor:default'?>">
          <div class="prt-card-thumb">
            <?php if($thumb): ?><img src="<?=e($thumb)?>" alt="<?=e($ev['title'])?>" loading="lazy" onerror="this.closest('.prt-card-thumb').classList.add('prt-no-thumb')">
            <?php else: ?><div class="prt-no-thumb-inner"><i class="bi bi-<?=$platIcon[ec($ev,'stream_platform','other')]?>"></i></div><?php endif; ?>
            <!-- Countdown overlay -->
            <div class="prt-card-countdown" id="cd_<?=$ev['id']?>" data-ts="<?=$cd['ts']??0?>">
              <?php if(($cd['secs']??0)>0): ?>
              <span class="prt-cd-label">In </span>
              <span class="prt-cd-val" data-cd="<?=$ev['id']?>"><?=($cd['d']??0)>0?$cd['d'].'d ':''?><?=str_pad($cd['h']??0,2,'0',STR_PAD_LEFT)?>:<?=str_pad($cd['m']??0,2,'0',STR_PAD_LEFT)?></span>
              <?php else: ?><span style="font-weight:700">Now</span><?php endif; ?>
            </div>
            <?php if(ec($ev,'is_featured',0)): ?><div class="prt-card-featured-badge"><i class="bi bi-star-fill"></i></div><?php endif; ?>
            <?php if($hasStream): ?><div class="prt-card-play"><i class="bi bi-play-circle-fill"></i></div><?php endif; ?>
          </div>
          <div class="prt-card-body">
            <div class="prt-card-date-row">
              <span class="prt-card-date"><i class="bi bi-calendar3"></i><?=date('M j',strtotime($ev['event_date']))?></span>
              <?php if(ec($ev,'event_time','')): ?><span class="prt-card-time"><?=date('g:i A',strtotime($ev['event_time']))?></span><?php endif; ?>
            </div>
            <div class="prt-card-tags">
              <span class="prt-cat-tag" style="background:<?=$catBgs[$cat]?>;color:<?=$catColors[$cat]?>"><?=$catLabels[$cat]?></span>
              <?php if(ec($ev,'is_free',1)): ?><span class="prt-free-tag">FREE</span><?php endif; ?>
            </div>
            <h4 class="prt-card-title"><?=e($ev['title'])?></h4>
            <?php if(ec($ev,'speaker_name','')): ?>
            <div class="prt-card-speaker"><i class="bi bi-person-fill"></i><?=e($ev['speaker_name'])?></div>
            <?php endif; ?>
            <?php if(!$hasStream): ?>
            <div style="font-size:.7rem;color:#bbb;margin-top:6px"><i class="bi bi-clock me-1"></i>Stream link coming soon</div>
            <?php endif; ?>
            <!-- RSVP button -->
            <button class="prt-rsvp-btn" onclick="event.stopPropagation();openRsvp(<?=$ev['id']?>,<?=e(json_encode($ev['title']))?>,<?=e(json_encode($ev['event_date']))?>,<?=e(json_encode(ec($ev,'event_time','')))?> )" title="Register / RSVP">
              <i class="bi bi-calendar-check-fill"></i> RSVP
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Tab: On Demand ── -->
    <div class="prt-tab-panel" id="panelPast" style="display:none">
      <?php if(empty($past)): ?>
      <div class="prt-empty">
        <i class="bi bi-collection-play"></i>
        <h4>No recordings yet</h4>
        <p>Past event recordings will appear here after they air.</p>
      </div>
      <?php else: ?>
      <div class="prt-grid" id="gridPast">
        <?php foreach($past as $i=>$ev):
          $su = ec($ev,'stream_url',''); $ru = ec($ev,'recording_url','');
          $activeUrl = $ru ?: $su;
          $ytid = ytId($activeUrl);
          $thumb = ec($ev,'thumbnail','') ? imgUrl($ev['thumbnail']) : ($ytid?"https://img.youtube.com/vi/{$ytid}/hqdefault.jpg":'');
          $cat = ec($ev,'category','academic');
          $hasVideo = !empty($activeUrl);
        ?>
        <div class="prt-card prt-card-past" data-id="<?=$ev['id']?>" data-cat="<?=$cat?>"
             onclick="<?=$hasVideo?"prtPlay({$ev['id']})":'void(0)'?>"
             style="<?=$hasVideo?'cursor:pointer':'cursor:default'?>">
          <div class="prt-card-thumb">
            <?php if($thumb): ?><img src="<?=e($thumb)?>" alt="<?=e($ev['title'])?>" loading="lazy" onerror="this.closest('.prt-card-thumb').classList.add('prt-no-thumb')">
            <?php else: ?><div class="prt-no-thumb-inner"><i class="bi bi-play-circle"></i></div><?php endif; ?>
            <div class="prt-card-od-badge">ON DEMAND</div>
            <?php if($hasVideo): ?><div class="prt-card-play"><i class="bi bi-play-circle-fill"></i></div>
            <?php else: ?><div class="prt-card-soon-badge">Coming Soon</div><?php endif; ?>
          </div>
          <div class="prt-card-body">
            <div class="prt-card-date-row">
              <span class="prt-card-date"><i class="bi bi-calendar3"></i><?=date('M j, Y',strtotime($ev['event_date']))?></span>
            </div>
            <div class="prt-card-tags">
              <span class="prt-cat-tag" style="background:<?=$catBgs[$cat]?>;color:<?=$catColors[$cat]?>"><?=$catLabels[$cat]?></span>
            </div>
            <h4 class="prt-card-title"><?=e($ev['title'])?></h4>
            <?php if(ec($ev,'speaker_name','')): ?>
            <div class="prt-card-speaker"><i class="bi bi-person-fill"></i><?=e($ev['speaker_name'])?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Empty state when filter yields nothing -->
    <div class="prt-empty" id="prtFilterEmpty" style="display:none">
      <i class="bi bi-funnel"></i>
      <h4>No events in this category</h4>
      <button onclick="prtFilterCat('all',document.querySelector('.prt-cat-btn'))" class="prt-empty-btn">Show all</button>
    </div>

  </div><!-- /container -->
</div><!-- /prt-body -->

<!-- ══ JS: portal logic ══ -->
<script>
const PRT_EVENTS = <?= $eventsJson ?>;

// Tab management
function prtTab(name, btn) {
  document.querySelectorAll('.prt-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.prt-tab-panel').forEach(p => p.style.display='none');
  document.getElementById('panel'+name.charAt(0).toUpperCase()+name.slice(1)).style.display='block';
  prtClose();
  applyFilter(document.querySelector('.prt-cat-btn.active')?.dataset.cat||'all');
}

// Category filter
function prtFilterCat(cat, btn) {
  document.querySelectorAll('.prt-cat-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  btn.dataset.cat = cat;
  applyFilter(cat);
}
function applyFilter(cat) {
  let anyVisible = false;
  document.querySelectorAll('.prt-card').forEach(c => {
    const show = cat==='all' || c.dataset.cat===cat;
    c.style.display = show ? '' : 'none';
    if(show) anyVisible = true;
  });
  document.getElementById('prtFilterEmpty').style.display = anyVisible ? 'none' : 'block';
}

// Play an event
function prtPlay(id) {
  const ev = PRT_EVENTS.find(e=>e.id==id);
  if (!ev) return;

  const wrap = document.getElementById('prtPlayerWrap');
  wrap.style.display = 'block';
  wrap.scrollIntoView({behavior:'smooth', block:'start'});

  // Set iframe
  const iframe = document.getElementById('prtIframe');
  if (ev.embed_url) {
    iframe.src = ev.embed_url;
  } else if (ev.link) {
    iframe.src = '';
    document.getElementById('prtPlayerFrame').innerHTML =
      '<div class="prt-no-embed"><i class="bi bi-box-arrow-up-right"></i><p>This event opens in an external player.</p><a href="'+ev.link+'" target="_blank" class="prt-join-btn">Join '+ev.platform.toUpperCase()+'</a></div>';
  }

  // Info bar
  const cat = ev.category;
  const catColors = <?= json_encode($catColors) ?>;
  const catBgs    = <?= json_encode($catBgs) ?>;
  const catLabels = <?= json_encode($catLabels) ?>;
  const platIcon  = <?= json_encode($platIcon) ?>;
  const platColor = <?= json_encode($platColor) ?>;

  document.getElementById('prtInfoBadges').innerHTML =
    (ev.is_live ? '<span class="prt-info-live-badge"><span class="prt-live-dot"></span>LIVE</span>' : '') +
    '<span class="prt-cat-tag" style="background:'+catBgs[cat]+';color:'+catColors[cat]+'">'+catLabels[cat]+'</span>' +
    (ev.is_free ? '<span class="prt-free-tag">FREE</span>' : '');

  document.getElementById('prtInfoTitle').textContent = ev.title;

  let meta = '';
  if (ev.date) meta += '<span><i class="bi bi-calendar3"></i>'+formatDate(ev.date,ev.time)+'</span>';
  if (ev.speaker) meta += '<span><i class="bi bi-person-fill"></i>'+ev.speaker+(ev.speaker_t?' — '+ev.speaker_t:'')+'</span>';
  if (ev.location) meta += '<span><i class="bi bi-geo-alt-fill"></i>'+ev.location+'</span>';
  document.getElementById('prtInfoMeta').innerHTML = meta;

  let actions = '';
  if (ev.reg_url) actions += '<a href="'+ev.reg_url+'" target="_blank" class="prt-action-btn prt-action-reg"><i class="bi bi-person-plus-fill me-1"></i>Register</a>';
  const shareUrl = encodeURIComponent(ev.stream_url||ev.link||window.location.href);
  actions += '<a href="https://wa.me/?text=Watch+'+encodeURIComponent(ev.title)+'+'+shareUrl+'" target="_blank" class="prt-action-btn prt-action-share" title="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>';
  actions += '<a href="https://twitter.com/intent/tweet?text='+encodeURIComponent(ev.title)+'&url='+shareUrl+'" target="_blank" class="prt-action-btn prt-action-share" title="Share on X"><i class="bi bi-twitter-x"></i></a>';
  document.getElementById('prtInfoActions').innerHTML = actions;

  // Sidebar: show current tab events minus this one
  buildSidebar(id);

  // Highlight active card
  document.querySelectorAll('.prt-card').forEach(c=>c.classList.remove('prt-card-active'));
  document.querySelector('.prt-card[data-id="'+id+'"]')?.classList.add('prt-card-active');
}

function buildSidebar(activeId) {
  const activeTab = document.querySelector('.prt-tab.active')?.id || '';
  let pool = PRT_EVENTS.filter(e => e.id != activeId);
  if (activeTab==='tabLive') pool = pool.filter(e=>e.is_live);
  else if (activeTab==='tabPast') pool = pool.filter(e=>!e.is_live);
  else pool = pool.filter(e=>!e.is_live);

  const label = activeTab==='tabPast' ? 'On Demand' : activeTab==='tabLive' ? 'Also Live' : 'Upcoming Events';
  document.getElementById('prtSidebarLabel').textContent = label;

  const list = document.getElementById('prtSidebarList');
  if (!pool.length) { list.innerHTML='<div style="padding:24px;text-align:center;color:#aaa;font-size:.82rem">No other events</div>'; return; }

  list.innerHTML = pool.map(ev => {
    const catColors = <?= json_encode($catColors) ?>;
    const catBgs    = <?= json_encode($catBgs) ?>;
    const catLabels = <?= json_encode($catLabels) ?>;
    const thumb = ev.thumb||'';
    const cat   = ev.category;
    return `<div class="prt-sb-item" onclick="prtPlay(${ev.id})">
      <div class="prt-sb-thumb">
        ${thumb?`<img src="${thumb}" alt="${(ev.title||'Event thumbnail').replace(/"/g,'&quot;')}" onerror="this.style.display='none'">`:'<div class="prt-sb-no-thumb"><i class="bi bi-play-circle"></i></div>'}
        ${ev.is_live?'<div class="prt-sb-live"><span class="prt-live-dot"></span></div>':''}
      </div>
      <div class="prt-sb-info">
        <div class="prt-sb-title">${ev.title}</div>
        <div class="prt-sb-meta">
          <span style="color:${catColors[cat]}">${catLabels[cat]}</span>
          ${ev.date?'<span>'+formatDate(ev.date,ev.time)+'</span>':''}
        </div>
      </div>
    </div>`;
  }).join('');
}

function formatDate(date, time) {
  if (!date) return '';
  const d = new Date(date+'T'+(time||'00:00'));
  const opts = {month:'short',day:'numeric'};
  let str = d.toLocaleDateString('en-KE',opts);
  if (time) str += ' · '+d.toLocaleTimeString('en-KE',{hour:'numeric',minute:'2-digit'});
  return str;
}

// View modes
function prtViewMode(mode, btn) {
  document.querySelectorAll('.prt-view-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  const layout = document.querySelector('.prt-player-layout');
  if (mode==='full') {
    layout.classList.add('prt-full-view');
    document.getElementById('prtSidebar').style.display='none';
  } else {
    layout.classList.remove('prt-full-view');
    document.getElementById('prtSidebar').style.display='';
  }
}

// Close player
function prtClose() {
  const wrap = document.getElementById('prtPlayerWrap');
  wrap.style.display='none';
  document.getElementById('prtIframe').src='';
  document.getElementById('prtPlayerFrame').innerHTML='<iframe id="prtIframe" src="" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
  document.querySelectorAll('.prt-card').forEach(c=>c.classList.remove('prt-card-active'));
}

// Auto-open live event if there is one
<?php if(!empty($liveNow)): ?>
window.addEventListener('DOMContentLoaded', function() {
  prtTab('live', document.getElementById('tabLive'));
  prtPlay(<?=$liveNow[0]['id']?>);
});
<?php else: ?>
window.addEventListener('DOMContentLoaded', function() {
  prtTab('upcoming', document.getElementById('tabUpcoming'));
});
<?php endif; ?>

// Live countdown ticks
(function tickAll(){
  document.querySelectorAll('[data-cd]').forEach(function(el){
    const id = el.dataset.cd;
    const wrap = document.getElementById('cd_'+id);
    if (!wrap) return;
    const ts = parseInt(wrap.dataset.ts)*1000;
    const diff = ts - Date.now();
    if (diff<=0){ wrap.innerHTML='<span style="font-weight:700;color:var(--gold)">Now</span>'; return; }
    const d=Math.floor(diff/86400000), h=Math.floor((diff%86400000)/3600000), m=Math.floor((diff%3600000)/60000), s=Math.floor((diff%60000)/1000);
    el.textContent=(d?d+'d ':'')+String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
  });
  setTimeout(tickAll,1000);
})();
</script></script>

<!-- ── RSVP Modal ── -->
<div id="rsvpModal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.65);align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:20px;max-width:460px;width:100%;padding:32px;position:relative;box-shadow:0 24px 80px rgba(0,0,0,.35)">
    <button onclick="closeRsvp()" style="position:absolute;top:16px;right:18px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#888;line-height:1">&times;</button>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
      <div style="width:44px;height:44px;border-radius:12px;background:rgba(204,0,0,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="bi bi-calendar-check-fill" style="color:#cc0000;font-size:1.2rem"></i>
      </div>
      <div>
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#aaa">Register for Event</div>
        <div id="rsvpEventTitle" style="font-size:.95rem;font-weight:700;color:#1e1e2e;margin-top:2px"></div>
      </div>
    </div>
    <div id="rsvpEventMeta" style="font-size:.78rem;color:#888;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap"></div>
    <div id="rsvpMsg" style="display:none;padding:12px 16px;border-radius:10px;font-size:.84rem;font-weight:600;margin-bottom:16px"></div>
    <form id="rsvpForm" onsubmit="submitRsvp(event)">
      <input type="hidden" id="rsvpEventId" name="event_id">
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:5px;color:#444">Full Name *</label>
        <input type="text" name="name" required placeholder="Your full name"
               style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e8;border-radius:10px;font-size:.88rem;outline:none;font-family:inherit"
               onfocus="this.style.borderColor='#cc0000'" onblur="this.style.borderColor='#e0e0e8'">
      </div>
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:5px;color:#444">Email Address *</label>
        <input type="email" name="email" required placeholder="you@example.com"
               style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e8;border-radius:10px;font-size:.88rem;outline:none;font-family:inherit"
               onfocus="this.style.borderColor='#cc0000'" onblur="this.style.borderColor='#e0e0e8'">
      </div>
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:5px;color:#444">Phone <span style="color:#aaa;font-weight:400">(optional)</span></label>
        <input type="tel" name="phone" placeholder="+254 7XX XXX XXX"
               style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e8;border-radius:10px;font-size:.88rem;outline:none;font-family:inherit"
               onfocus="this.style.borderColor='#cc0000'" onblur="this.style.borderColor='#e0e0e8'">
      </div>
      <div style="margin-bottom:14px;font-size:.78rem;color:#888;display:flex;gap:8px;align-items:flex-start;">
        <input type="checkbox" name="consent" required style="margin-top:3px;flex-shrink:0;">
        <label style="margin:0;font-weight:400;">I agree to ANU storing this information to process my registration, per the <a href="/anu/privacy.php" target="_blank" style="color:#cc0000">Privacy Policy</a>.</label>
      </div>
      <button type="submit" id="rsvpSubmitBtn"
              style="width:100%;padding:13px;background:#cc0000;color:#fff;border:none;border-radius:50px;font-size:.9rem;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px">
        <i class="bi bi-calendar-check-fill"></i>Confirm Registration
      </button>
    </form>
    <p style="text-align:center;font-size:.7rem;color:#bbb;margin-top:14px;margin-bottom:0">We'll send you a reminder before the event starts.</p>
  </div>
</div>

<style>
.prt-rsvp-btn{
  display:flex;align-items:center;gap:6px;margin-top:10px;padding:8px 16px;
  background:rgba(204,0,0,.08);color:#cc0000;border:1.5px solid rgba(204,0,0,.18);
  border-radius:50px;font-size:.74rem;font-weight:700;cursor:pointer;
  font-family:inherit;transition:.18s ease;width:100%;justify-content:center;
}
.prt-rsvp-btn:hover{background:#cc0000;color:#fff;border-color:#cc0000}
</style>

<script>
function openRsvp(id, title, date, time) {
  document.getElementById('rsvpEventId').value = id;
  document.getElementById('rsvpEventTitle').textContent = title;
  var meta = '';
  if (date) {
    var d = new Date(date); 
    meta += '<span><i class="bi bi-calendar3 me-1"></i>'+d.toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})+'</span>';
  }
  if (time) meta += '<span><i class="bi bi-clock me-1"></i>'+time+' EAT</span>';
  document.getElementById('rsvpEventMeta').innerHTML = meta;
  var msg = document.getElementById('rsvpMsg');
  msg.style.display='none'; msg.textContent='';
  document.getElementById('rsvpForm').reset();
  document.getElementById('rsvpEventId').value = id;
  var modal = document.getElementById('rsvpModal');
  modal.style.display='flex';
  document.body.style.overflow='hidden';
}
function closeRsvp() {
  document.getElementById('rsvpModal').style.display='none';
  document.body.style.overflow='';
}
document.getElementById('rsvpModal').addEventListener('click', function(e){
  if(e.target===this) closeRsvp();
});
function submitRsvp(e) {
  e.preventDefault();
  var btn = document.getElementById('rsvpSubmitBtn');
  var msg = document.getElementById('rsvpMsg');
  btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Registering...';
  var fd = new FormData(document.getElementById('rsvpForm'));
  fetch('/anu/api/rsvp', {method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(data){
      msg.style.display='block';
      if(data.ok){
        msg.style.cssText='display:block;padding:12px 16px;border-radius:10px;font-size:.84rem;font-weight:600;margin-bottom:16px;background:rgba(46,125,50,.1);color:#2e7d32;border:1.5px solid rgba(46,125,50,.2)';
        msg.innerHTML='<i class="bi bi-check-circle-fill me-2"></i>'+data.msg;
        document.getElementById('rsvpForm').style.display='none';
        btn.style.display='none';
      } else {
        msg.style.cssText='display:block;padding:12px 16px;border-radius:10px;font-size:.84rem;font-weight:600;margin-bottom:16px;background:rgba(204,0,0,.08);color:#cc0000;border:1.5px solid rgba(204,0,0,.15)';
        msg.innerHTML='<i class="bi bi-exclamation-triangle-fill me-2"></i>'+data.msg;
        btn.disabled=false; btn.innerHTML='<i class="bi bi-calendar-check-fill"></i> Confirm Registration';
      }
    })
    .catch(function(){
      msg.style.cssText='display:block;padding:12px 16px;border-radius:10px;font-size:.84rem;font-weight:600;margin-bottom:16px;background:rgba(204,0,0,.08);color:#cc0000;border:1.5px solid rgba(204,0,0,.15)';
      msg.textContent='Network error. Please try again.';
      btn.disabled=false; btn.innerHTML='<i class="bi bi-calendar-check-fill"></i> Confirm Registration';
    });
}
</script>


<?php renderFooter(); ?>
