<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();

// Date range filter
$range  = $_GET['range'] ?? '30';  // days
$ranges = ['7'=>'Last 7 days','30'=>'Last 30 days','90'=>'Last 90 days','365'=>'Last year','all'=>'All time'];
$label  = $ranges[$range] ?? 'Last 30 days';
$since  = $range === 'all' ? '2000-01-01' : date('Y-m-d', strtotime("-{$range} days"));

/* ── Helper: safe query ── */
function rq($db, $sql, $params=[]){
    try{$s=$db->prepare($sql);$s->execute($params);return $s;}
    catch(Exception $e){return null;}
}
function rqVal($db,$sql,$params=[],$default=0){
    $s=rq($db,$sql,$params);
    return $s?($s->fetchColumn()??$default):$default;
}

/* ── Summary KPIs ── */
$kpi = [
    'pageviews'    => rqVal($db,"SELECT COUNT(*) FROM page_views WHERE DATE(created_at)>=?",[$since]),
    'unique_ips'   => rqVal($db,"SELECT COUNT(DISTINCT ip) FROM page_views WHERE DATE(created_at)>=?",[$since]),
    'messages'     => rqVal($db,"SELECT COUNT(*) FROM contact_messages WHERE DATE(created_at)>=?",[$since]),
    'applications' => rqVal($db,"SELECT COUNT(*) FROM applications WHERE DATE(created_at)>=?",[$since]),
    'rsvps'        => rqVal($db,"SELECT COUNT(*) FROM event_rsvp WHERE DATE(created_at)>=?",[$since]),
    'prayers'      => rqVal($db,"SELECT COUNT(*) FROM prayer_requests WHERE DATE(created_at)>=?",[$since]),
];

/* ── Page Views by page ── */
$byPage = rq($db,
    "SELECT page_label, page, COUNT(*) as views
     FROM page_views WHERE DATE(created_at)>=?
     GROUP BY page ORDER BY views DESC LIMIT 12",[$since]);
$byPage = $byPage ? $byPage->fetchAll() : [];
$maxPageViews = !empty($byPage) ? max(array_column($byPage,'views')) : 1;

/* ── Views over time (daily) ── */
$daily = rq($db,
    "SELECT DATE(created_at) as day, COUNT(*) as views
     FROM page_views WHERE DATE(created_at)>=?
     GROUP BY DATE(created_at) ORDER BY day ASC",[$since]);
$daily = $daily ? $daily->fetchAll() : [];

/* ── Top referrers ── */
$topRefs = rq($db,
    "SELECT referrer, COUNT(*) as hits FROM page_views
     WHERE DATE(created_at)>=? AND referrer IS NOT NULL AND referrer!=''
     GROUP BY referrer ORDER BY hits DESC LIMIT 8",[$since]);
$topRefs = $topRefs ? $topRefs->fetchAll() : [];

/* ── Messages per day ── */
$msgDaily = rq($db,
    "SELECT DATE(created_at) as day, COUNT(*) as cnt
     FROM contact_messages WHERE DATE(created_at)>=?
     GROUP BY DATE(created_at) ORDER BY day ASC",[$since]);
$msgDaily = $msgDaily ? $msgDaily->fetchAll() : [];

/* ── Top programs viewed (via news/programs clicks if ref_id tracked) ── */
$topPrograms = rq($db,
    "SELECT p.name, p.school, COUNT(pv.id) as views
     FROM programs p
     LEFT JOIN page_views pv ON pv.page='programs' AND DATE(pv.created_at)>=?
     WHERE p.is_active=1
     GROUP BY p.id ORDER BY views DESC LIMIT 8",[$since]);
$topPrograms = $topPrograms ? $topPrograms->fetchAll() : [];

/* ── Recent RSVPs ── */
$recentRsvp = rq($db,
    "SELECT r.*, e.title as event_title
     FROM event_rsvp r LEFT JOIN events e ON e.id=r.event_id
     WHERE DATE(r.created_at)>=?
     ORDER BY r.created_at DESC LIMIT 20",[$since]);
$recentRsvp = $recentRsvp ? $recentRsvp->fetchAll() : [];

/* ── RSVPs by event ── */
$rsvpByEvent = rq($db,
    "SELECT e.title, e.event_date, COUNT(r.id) as total,
            SUM(r.status='confirmed') as confirmed,
            SUM(r.status='attended') as attended
     FROM events e LEFT JOIN event_rsvp r ON r.event_id=e.id
     WHERE e.is_active=1
     GROUP BY e.id ORDER BY e.event_date DESC LIMIT 10",[]);
$rsvpByEvent = $rsvpByEvent ? $rsvpByEvent->fetchAll() : [];

/* ── Recent messages ── */
$recentMsgs = rq($db,
    "SELECT name, email, subject, created_at FROM contact_messages
     WHERE DATE(created_at)>=?
     ORDER BY created_at DESC LIMIT 8",[$since]);
$recentMsgs = $recentMsgs ? $recentMsgs->fetchAll() : [];

// Chart data JSON
$chartDays    = json_encode(array_column($daily,'day'));
$chartViews   = json_encode(array_column($daily,'views'));
$chartMsgDays = json_encode(array_column($msgDaily,'day'));
$chartMsgCnt  = json_encode(array_column($msgDaily,'cnt'));

adminHead('Reports & Analytics'); adminSidebar('reports');
adminTopBar('Reports & Analytics', $label);
?>

<!-- Range filter pills -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;align-items:center">
  <span style="font-size:.78rem;font-weight:700;color:var(--text-mid);margin-right:4px">Date range:</span>
  <?php foreach($ranges as $v=>$l): ?>
  <a href="?range=<?=$v?>"
     style="display:inline-block;padding:6px 16px;border-radius:50px;font-size:.76rem;font-weight:700;text-decoration:none;
            <?=$range===$v?'background:var(--primary);color:#fff':'background:#fff;color:var(--text-mid);border:1.5px solid var(--border)'?>">
    <?=$l?>
  </a>
  <?php endforeach; ?>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['bi-eye-fill',          '#1565C0', 'Page Views',     $kpi['pageviews'],    'Total pages loaded'],
    ['bi-people-fill',       '#2e7d32', 'Unique Visitors', $kpi['unique_ips'],  'Distinct IP addresses'],
    ['bi-envelope-fill',     '#6a1b9a', 'Messages',        $kpi['messages'],    'Contact form submissions'],
    ['bi-person-plus-fill',  '#c1121f', 'Applications',    $kpi['applications'],'Admission inquiries'],
    ['bi-calendar-check-fill','#00796b','RSVPs',           $kpi['rsvps'],       'Event registrations'],
    ['bi-heart-fill',        '#e65100', 'Prayer Requests', $kpi['prayers'],     'Prayer submissions'],
  ];
  foreach($kpis as [$icon,$color,$title,$value,$hint]):
  ?>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="width:38px;height:38px;border-radius:10px;background:<?=$color?>14;display:flex;align-items:center;justify-content:center">
            <i class="bi <?=$icon?>" style="color:<?=$color?>;font-size:1.05rem"></i>
          </div>
        </div>
        <div style="font-size:1.75rem;font-weight:800;color:var(--text-dark);line-height:1"><?=number_format($value)?></div>
        <div style="font-size:.76rem;font-weight:700;color:var(--text-dark);margin-top:4px"><?=$title?></div>
        <div style="font-size:.68rem;color:var(--text-mid);margin-top:2px"><?=$hint?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="row g-4 mb-4">
  <!-- Page views chart -->
  <div class="col-lg-8">
    <div class="admin-card" style="padding:0">
      <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Page Views Over Time</div>
          <div style="font-size:.72rem;color:var(--text-mid)"><?=$label?></div>
        </div>
        <div style="font-size:1.3rem;color:#1565C0"><i class="bi bi-graph-up-arrow"></i></div>
      </div>
      <div style="padding:16px 24px 20px">
        <canvas id="viewsChart" height="90"></canvas>
      </div>
    </div>
  </div>

  <!-- Page breakdown donut -->
  <div class="col-lg-4">
    <div class="admin-card" style="padding:0">
      <div style="padding:20px 24px 0">
        <div style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Traffic by Page</div>
        <div style="font-size:.72rem;color:var(--text-mid)">Top pages visited</div>
      </div>
      <div style="padding:12px 24px 20px">
        <canvas id="donutChart" height="160"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Page views table + Messages chart -->
<div class="row g-4 mb-4">
  <!-- Top pages -->
  <div class="col-lg-7">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 24px;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Top Pages</div>
        <span style="font-size:.7rem;color:var(--text-mid)"><?=count($byPage)?> pages</span>
      </div>
      <?php if(empty($byPage)): ?>
      <div style="padding:40px;text-align:center;color:var(--text-mid);font-size:.84rem">
        <i class="bi bi-bar-chart" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3"></i>
        No data yet — run <a href="/migrate_analytics.php" style="color:var(--primary)">migrate_analytics.php</a> first.
      </div>
      <?php else: ?>
      <div style="padding:8px 0">
        <?php foreach($byPage as $row):
          $pct = $maxPageViews > 0 ? round(($row['views']/$maxPageViews)*100) : 0;
        ?>
        <div style="padding:10px 24px;display:flex;align-items:center;gap:14px">
          <div style="flex:1;min-width:0">
            <div style="font-size:.82rem;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=e($row['page_label'])?></div>
            <div style="margin-top:5px;height:5px;background:#f0f0f5;border-radius:3px;overflow:hidden">
              <div style="width:<?=$pct?>%;height:100%;background:linear-gradient(90deg,#1565C0,#42a5f5);border-radius:3px;transition:width .6s"></div>
            </div>
          </div>
          <div style="font-size:.88rem;font-weight:800;color:var(--text-dark);flex-shrink:0;min-width:36px;text-align:right"><?=number_format($row['views'])?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Messages chart -->
  <div class="col-lg-5">
    <div class="admin-card" style="padding:0">
      <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Contact Messages</div>
          <div style="font-size:.72rem;color:var(--text-mid)">Daily submissions</div>
        </div>
        <div style="font-size:1.3rem;color:#6a1b9a"><i class="bi bi-envelope-fill"></i></div>
      </div>
      <div style="padding:16px 24px 20px">
        <canvas id="msgChart" height="140"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- RSVPs by event + Top referrers -->
<div class="row g-4 mb-4">
  <!-- RSVPs by event -->
  <div class="col-lg-7">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 24px;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Event RSVPs</div>
        <a href="/anu/admin/rsvp.php" style="font-size:.72rem;font-weight:700;color:var(--primary)">View all →</a>
      </div>
      <?php if(empty($rsvpByEvent)): ?>
      <div style="padding:32px;text-align:center;color:var(--text-mid);font-size:.84rem">No RSVPs yet.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table mb-0" style="font-size:.8rem">
          <thead><tr>
            <th>Event</th>
            <th>Date</th>
            <th style="text-align:center">Total</th>
            <th style="text-align:center">Confirmed</th>
            <th style="text-align:center">Attended</th>
          </tr></thead>
          <tbody>
          <?php foreach($rsvpByEvent as $r): ?>
          <tr>
            <td style="font-weight:600;max-width:200px"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"><?=e($r['title'])?></div></td>
            <td style="white-space:nowrap;color:var(--text-mid)"><?=date('M j, Y',strtotime($r['event_date']))?></td>
            <td style="text-align:center;font-weight:800;color:var(--text-dark)"><?=$r['total']?0:0?><?=$r['total']??0?></td>
            <td style="text-align:center"><span style="background:rgba(46,125,50,.1);color:#2e7d32;padding:2px 10px;border-radius:50px;font-size:.7rem;font-weight:700"><?=$r['confirmed']??0?></span></td>
            <td style="text-align:center"><span style="background:rgba(21,101,192,.1);color:#1565C0;padding:2px 10px;border-radius:50px;font-size:.7rem;font-weight:700"><?=$r['attended']??0?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Top referrers -->
  <div class="col-lg-5">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 24px;border-bottom:1.5px solid var(--border)">
        <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Top Referrers</div>
        <div style="font-size:.7rem;color:var(--text-mid)">Where visitors come from</div>
      </div>
      <?php if(empty($topRefs)): ?>
      <div style="padding:32px;text-align:center;color:var(--text-mid);font-size:.84rem">No referrer data yet.</div>
      <?php else: ?>
      <div style="padding:8px 0">
        <?php foreach($topRefs as $ref):
          $host = parse_url($ref['referrer'], PHP_URL_HOST) ?: $ref['referrer'];
          $host = preg_replace('/^www\./','', $host);
        ?>
        <div style="padding:10px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div style="display:flex;align-items:center;gap:8px;min-width:0">
            <div style="width:28px;height:28px;border-radius:8px;background:#f0f2f7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="bi bi-link-45deg" style="color:#888;font-size:.8rem"></i>
            </div>
            <div style="font-size:.78rem;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=e($host)?></div>
          </div>
          <span style="font-size:.78rem;font-weight:800;color:var(--text-dark);flex-shrink:0"><?=$ref['hits']?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Recent RSVPs + Recent Messages -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 24px;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Recent RSVPs</div>
        <a href="/anu/admin/rsvp.php" style="font-size:.72rem;font-weight:700;color:var(--primary)">Manage →</a>
      </div>
      <?php if(empty($recentRsvp)): ?>
      <div style="padding:32px;text-align:center;color:var(--text-mid);font-size:.84rem">No RSVPs yet.</div>
      <?php else: ?>
      <div style="padding:8px 0">
        <?php foreach($recentRsvp as $r): ?>
        <div style="padding:10px 24px;display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--border)">
          <div style="width:34px;height:34px;border-radius:50%;background:rgba(0,121,107,.1);color:#00796b;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0">
            <?=strtoupper($r['name'][0])?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.82rem;font-weight:700;color:var(--text-dark)"><?=e($r['name'])?></div>
            <div style="font-size:.7rem;color:var(--text-mid);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=e($r['event_title'] ?? 'Unknown Event')?></div>
          </div>
          <div style="font-size:.68rem;color:#aaa;flex-shrink:0"><?=date('M j',strtotime($r['created_at']))?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="admin-card" style="padding:0">
      <div style="padding:18px 24px;border-bottom:1.5px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:.88rem;font-weight:700;color:var(--text-dark)">Recent Messages</div>
        <a href="/anu/admin/messages.php" style="font-size:.72rem;font-weight:700;color:var(--primary)">View all →</a>
      </div>
      <?php if(empty($recentMsgs)): ?>
      <div style="padding:32px;text-align:center;color:var(--text-mid);font-size:.84rem">No messages yet.</div>
      <?php else: ?>
      <div style="padding:8px 0">
        <?php foreach($recentMsgs as $m): ?>
        <div style="padding:10px 24px;display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--border)">
          <div style="width:34px;height:34px;border-radius:50%;background:rgba(106,27,154,.1);color:#6a1b9a;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0">
            <?=strtoupper($m['name'][0])?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.82rem;font-weight:700;color:var(--text-dark)"><?=e($m['name'])?></div>
            <div style="font-size:.7rem;color:var(--text-mid);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=e($m['subject'] ?? $m['email'])?></div>
          </div>
          <div style="font-size:.68rem;color:#aaa;flex-shrink:0"><?=date('M j',strtotime($m['created_at']))?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = '"Roboto", system-ui, sans-serif';
Chart.defaults.color = '#888';

// Page views line chart
var vCtx = document.getElementById('viewsChart').getContext('2d');
new Chart(vCtx, {
  type: 'line',
  data: {
    labels: <?= $chartDays ?>,
    datasets: [{
      label: 'Page Views',
      data: <?= $chartViews ?>,
      borderColor: '#1565C0',
      backgroundColor: 'rgba(21,101,192,.07)',
      borderWidth: 2.5,
      pointRadius: <?= count($daily) > 60 ? 0 : 3 ?>,
      pointBackgroundColor: '#1565C0',
      tension: 0.4,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
      y: { beginAtZero: true, grid: { color: '#f0f0f5' }, ticks: { font: { size: 10 }, stepSize: 1 } }
    }
  }
});

// Donut chart — top pages
<?php
$donutLabels = json_encode(array_map(fn($r)=>$r['page_label'], array_slice($byPage,0,7)));
$donutData   = json_encode(array_column(array_slice($byPage,0,7),'views'));
?>
var dCtx = document.getElementById('donutChart').getContext('2d');
new Chart(dCtx, {
  type: 'doughnut',
  data: {
    labels: <?= $donutLabels ?>,
    datasets: [{
      data: <?= $donutData ?>,
      backgroundColor: ['#1565C0','#cc0000','#fac823','#2e7d32','#6a1b9a','#00796b','#e65100'],
      borderWidth: 2,
      borderColor: '#fff',
    }]
  },
  options: {
    responsive: true,
    cutout: '62%',
    plugins: {
      legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } }
    }
  }
});

// Messages bar chart
var mCtx = document.getElementById('msgChart').getContext('2d');
new Chart(mCtx, {
  type: 'bar',
  data: {
    labels: <?= $chartMsgDays ?>,
    datasets: [{
      label: 'Messages',
      data: <?= $chartMsgCnt ?>,
      backgroundColor: 'rgba(106,27,154,.15)',
      borderColor: '#6a1b9a',
      borderWidth: 1.5,
      borderRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { maxTicksLimit: 6, font: { size: 10 } } },
      y: { beginAtZero: true, grid: { color: '#f0f0f5' }, ticks: { stepSize: 1, font: { size: 10 } } }
    }
  }
});
</script>

<?php adminFoot(); ?>
