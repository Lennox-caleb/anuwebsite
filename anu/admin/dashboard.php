<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();
$stats = [
  'programs'     => $db->query("SELECT COUNT(*) FROM programs WHERE is_active=1")->fetchColumn(),
  'news'         => $db->query("SELECT COUNT(*) FROM news WHERE is_published=1")->fetchColumn(),
  'messages'     => $db->query("SELECT COUNT(*) FROM contact_messages WHERE status='new'")->fetchColumn(),
  'events'       => $db->query("SELECT COUNT(*) FROM events WHERE is_active=1")->fetchColumn(),
  'applications' => $db->query("SELECT COUNT(*) FROM applications WHERE status='new'")->fetchColumn(),
  'prayers'      => $db->query("SELECT COUNT(*) FROM prayer_requests WHERE status='new'")->fetchColumn(),
];
$recentMessages = $db->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
$recentApps     = $db->query("SELECT * FROM applications ORDER BY submitted_at DESC LIMIT 5")->fetchAll();

adminHead('Dashboard');
adminSidebar('dashboard');
adminTopBar('Dashboard', 'Overview');
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
<?php
$widgets = [
  ['bi-mortarboard-fill','Programs',    $stats['programs'],    '#e8f5e9','#2e7d32', '/anu/admin/programs.php'],
  ['bi-newspaper',        'News',        $stats['news'],        '#e3f2fd','#1565c0', '/anu/admin/news.php'],
  ['bi-envelope-fill',    'Messages',    $stats['messages'],    '#fff8e1','#e65100', '/anu/admin/messages.php'],
  ['bi-calendar-event-fill','Events',   $stats['events'],      '#f3e5f5','#6a1b9a', '/anu/admin/events.php'],
  ['bi-person-plus-fill', 'Applications',$stats['applications'],'#fce4ec','#c62828','/anu/admin/applications.php'],
  ['bi-heart-fill',       'Prayers',    $stats['prayers'],     '#fff3e0','#e65100', '/anu/admin/prayers.php'],
];
foreach($widgets as [$ic,$lb,$val,$bg,$clr,$href]):?>
<div class="col-6 col-md-4 col-xl-2">
  <a href="<?=$href?>" style="text-decoration:none">
    <div class="stat-card">
      <div class="stat-card-icon" style="background:<?=$bg?>;color:<?=$clr?>"><i class="bi <?=$ic?>"></i></div>
      <div><div class="stat-card-num"><?=$val?></div><div class="stat-card-label"><?=$lb?></div></div>
    </div>
  </a>
</div>
<?php endforeach;?>
</div>

<!-- Quick Actions -->
<div class="admin-card mb-4">
  <div class="admin-card-header"><h5><i class="bi bi-lightning-fill me-2" style="color:#fac823"></i>Quick Actions</h5></div>
  <div class="admin-card-body">
    <div class="d-flex flex-wrap gap-2">
      <?php foreach([
        ['/anu/admin/slides.php',  'bi-collection-fill',     'Manage Slides'],
        ['/anu/admin/news.php?action=new','bi-plus-circle','Add News'],
        ['/anu/admin/events.php?action=new','bi-calendar-plus','Add Event'],
        ['/anu/admin/programs.php?action=new','bi-mortarboard','Add Program'],
        ['/anu/admin/settings.php','bi-gear','Site Settings'],
        ['/anu/admin/gallery.php', 'bi-images','Gallery'],
      ] as [$h,$i,$l]):?>
      <a href="<?=$h?>" class="btn btn-sm btn-light border" style="border-radius:50px;font-size:.82rem">
        <i class="bi <?=$i?> me-1"></i><?=$l?>
      </a>
      <?php endforeach;?>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Messages -->
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="bi bi-envelope-fill me-2" style="color:#cc0000"></i>Recent Messages</h5>
        <a href="/anu/admin/messages.php" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.75rem">View All</a>
      </div>
      <div class="admin-card-body p-0">
        <?php if(empty($recentMessages)):?>
        <p class="text-muted text-center py-4">No messages yet.</p>
        <?php else:?>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach($recentMessages as $m):?>
              <tr style="cursor:pointer" onclick="location='/anu/admin/messages.php?id=<?=$m['id']?>'">
                <td><strong style="font-size:.85rem"><?=e($m['name'])?></strong><br><small class="text-muted"><?=e($m['email'])?></small></td>
                <td style="font-size:.83rem"><?=e(substr($m['subject'],0,35))?><?=strlen($m['subject'])>35?'…':''?></td>
                <td><span class="anu-badge <?=$m['status']==='new'?'badge-warning':'badge-success'?>"><?=e($m['status'])?></span></td>
                <td style="font-size:.77rem;white-space:nowrap;color:#888"><?=date('M d, g:ia',strtotime($m['submitted_at']))?></td>
              </tr>
              <?php endforeach;?>
            </tbody>
          </table>
        </div>
        <?php endif;?>
      </div>
    </div>
  </div>

  <!-- Applications -->
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="bi bi-person-plus-fill me-2" style="color:#cc0000"></i>Recent Applications</h5>
        <a href="/anu/admin/applications.php" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.75rem">View All</a>
      </div>
      <div class="admin-card-body">
        <?php if(empty($recentApps)):?>
        <p class="text-muted text-center py-2">No applications yet.</p>
        <?php else:?>
        <?php foreach($recentApps as $app):?>
        <div style="display:flex;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid #f5f5f5">
          <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#cc0000,#fac823);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0;font-size:.85rem">
            <?=strtoupper($app['full_name'][0])?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.85rem"><?=e($app['full_name'])?></div>
            <div style="font-size:.77rem;color:#aaa;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=e($app['program_interest']?:'Not specified')?></div>
          </div>
          <span class="anu-badge <?=$app['status']==='new'?'badge-warning':'badge-success'?>"><?=e($app['status'])?></span>
        </div>
        <?php endforeach;?>
        <?php endif;?>
      </div>
    </div>
  </div>
</div>

<?php adminFoot(); ?>
