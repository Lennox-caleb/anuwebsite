<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db  = getDB();
$msg = ''; $msgType = 'success';

// Update status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $allowed = ['confirmed','attended','cancelled'];
    $st = $_GET['status'];
    $id = (int)$_GET['id'];
    if (in_array($st, $allowed) && $id) {
        try { $db->prepare("UPDATE event_rsvp SET status=? WHERE id=?")->execute([$st,$id]); }
        catch(Exception $e) {}
    }
    $eid = $_GET['event_id'] ?? '';
    header("Location: " . SITE_URL . "/admin/rsvp.php".($eid?"?event_id=$eid":'')); exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try { $db->prepare("DELETE FROM event_rsvp WHERE id=?")->execute([$id]); } catch(Exception $e) {}
    $eid = $_GET['event_id'] ?? '';
    header("Location: " . SITE_URL . "/admin/rsvp.php".($eid?"?event_id=$eid":'')); exit;
}

// Filter by event
$filterEvent = (int)($_GET['event_id'] ?? 0);

// Fetch events for filter dropdown
$events = [];
try {
    $events = $db->query("SELECT id, title, event_date FROM events WHERE is_active=1 ORDER BY event_date DESC")->fetchAll();
} catch(Exception $e) {}

// Fetch RSVPs
$rsvps = [];
try {
    if ($filterEvent) {
        $s = $db->prepare(
            "SELECT r.*, e.title as event_title, e.event_date
             FROM event_rsvp r LEFT JOIN events e ON e.id=r.event_id
             WHERE r.event_id=? ORDER BY r.created_at DESC"
        );
        $s->execute([$filterEvent]);
    } else {
        $s = $db->query(
            "SELECT r.*, e.title as event_title, e.event_date
             FROM event_rsvp r LEFT JOIN events e ON e.id=r.event_id
             ORDER BY r.created_at DESC LIMIT 200"
        );
    }
    $rsvps = $s->fetchAll();
} catch(Exception $e) {}

$total     = count($rsvps);
$confirmed = count(array_filter($rsvps, fn($r)=>$r['status']==='confirmed'));
$attended  = count(array_filter($rsvps, fn($r)=>$r['status']==='attended'));
$cancelled = count(array_filter($rsvps, fn($r)=>$r['status']==='cancelled'));

adminHead('Event RSVPs'); adminSidebar('rsvp');
adminTopBar('Event RSVPs', 'Manage event registrations');
?>

<!-- Filter + stats bar -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
    <form method="GET" style="display:flex;gap:8px;align-items:center">
      <select name="event_id" class="form-select form-select-sm" style="min-width:220px;border-radius:50px;font-size:.8rem" onchange="this.form.submit()">
        <option value="">All Events</option>
        <?php foreach($events as $ev): ?>
        <option value="<?=$ev['id']?>" <?=$filterEvent===$ev['id']?'selected':''?>>
          <?=e($ev['title'])?> (<?=date('M j Y',strtotime($ev['event_date']))?>)
        </option>
        <?php endforeach; ?>
      </select>
    </form>
    <!-- Stats pills -->
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <span style="background:#f4f5f7;border:1.5px solid var(--border);border-radius:50px;padding:4px 14px;font-size:.74rem;font-weight:700;color:var(--text-dark)"><?=$total?> Total</span>
      <span style="background:rgba(46,125,50,.08);border:1.5px solid rgba(46,125,50,.2);border-radius:50px;padding:4px 14px;font-size:.74rem;font-weight:700;color:#2e7d32"><?=$confirmed?> Confirmed</span>
      <span style="background:rgba(21,101,192,.08);border:1.5px solid rgba(21,101,192,.2);border-radius:50px;padding:4px 14px;font-size:.74rem;font-weight:700;color:#1565C0"><?=$attended?> Attended</span>
      <span style="background:rgba(204,0,0,.07);border:1.5px solid rgba(204,0,0,.15);border-radius:50px;padding:4px 14px;font-size:.74rem;font-weight:700;color:#cc0000"><?=$cancelled?> Cancelled</span>
    </div>
  </div>
  <?php if(!empty($rsvps)): ?>
  <a href="?<?=http_build_query(array_merge($_GET,['export'=>'csv']))?>"
     class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.78rem;padding:7px 16px">
    <i class="bi bi-download me-1"></i>Export CSV
  </a>
  <?php endif; ?>
</div>

<?php
// CSV export
if (isset($_GET['export']) && $_GET['export']==='csv' && !empty($rsvps)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rsvps-'.date('Y-m-d').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Name','Email','Phone','Event','Event Date','Status','Registered At','Message']);
    foreach($rsvps as $r) {
        fputcsv($out,[$r['name'],$r['email'],$r['phone']??'',$r['event_title']??'',$r['event_date']??'',$r['status'],date('Y-m-d H:i',strtotime($r['created_at'])),$r['message']??'']);
    }
    fclose($out); exit;
}
?>

<?php if(empty($rsvps)): ?>
<div class="admin-card text-center" style="padding:70px 24px">
  <div style="width:72px;height:72px;border-radius:20px;background:rgba(0,121,107,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
    <i class="bi bi-calendar-check-fill" style="font-size:2rem;color:#00796b"></i>
  </div>
  <h5 style="font-size:1rem;margin-bottom:8px">No RSVPs yet</h5>
  <p style="color:var(--text-mid);font-size:.84rem">RSVPs will appear here once students register for events on the <a href="/anu/livestream.php" target="_blank" style="color:var(--primary)">Live Events portal</a>.</p>
</div>
<?php else: ?>

<div class="admin-card" style="padding:0;overflow:hidden">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Name & Contact</th>
          <th>Event</th>
          <th>Date</th>
          <th style="text-align:center">Status</th>
          <th>Registered</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($rsvps as $i=>$r):
        $stColors = ['confirmed'=>['#2e7d32','rgba(46,125,50,.1)'],'attended'=>['#1565C0','rgba(21,101,192,.1)'],'cancelled'=>['#cc0000','rgba(204,0,0,.08)']];
        [$stClr,$stBg] = $stColors[$r['status']] ?? ['#888','#f4f4f4'];
      ?>
      <tr>
        <td style="color:var(--text-mid);font-size:.78rem"><?=$i+1?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:50%;background:rgba(0,121,107,.1);color:#00796b;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0">
              <?=strtoupper($r['name'][0])?>
            </div>
            <div>
              <div style="font-size:.84rem;font-weight:700;color:var(--text-dark)"><?=e($r['name'])?></div>
              <div style="font-size:.72rem;color:var(--text-mid)"><?=e($r['email'])?></div>
              <?php if(!empty($r['phone'])): ?>
              <div style="font-size:.68rem;color:#aaa"><?=e($r['phone'])?></div>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td style="max-width:200px">
          <div style="font-size:.82rem;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">
            <?=e($r['event_title'] ?? 'Unknown Event')?>
          </div>
          <?php if(!empty($r['message'])): ?>
          <div style="font-size:.68rem;color:#aaa;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px" title="<?=e($r['message'])?>">
            <i class="bi bi-chat-text me-1"></i><?=e($r['message'])?>
          </div>
          <?php endif; ?>
        </td>
        <td style="white-space:nowrap;font-size:.8rem;color:var(--text-mid)">
          <?=!empty($r['event_date'])?date('M j, Y',strtotime($r['event_date'])):'-'?>
        </td>
        <td style="text-align:center">
          <span style="display:inline-block;padding:4px 12px;border-radius:50px;font-size:.68rem;font-weight:800;background:<?=$stBg?>;color:<?=$stClr?>;text-transform:capitalize">
            <?=$r['status']?>
          </span>
        </td>
        <td style="font-size:.76rem;color:var(--text-mid);white-space:nowrap">
          <?=date('M j, g:i A',strtotime($r['created_at']))?>
        </td>
        <td>
          <div class="d-flex gap-1 justify-content-end flex-wrap">
            <?php if($r['status']!=='attended'): ?>
            <a href="?status=attended&id=<?=$r['id']?>&event_id=<?=$filterEvent?>"
               class="btn btn-sm" style="border-radius:50px;font-size:.68rem;padding:3px 10px;background:rgba(21,101,192,.1);color:#1565C0;border:1px solid rgba(21,101,192,.2)"
               title="Mark attended">✓ Attended</a>
            <?php endif; ?>
            <?php if($r['status']!=='confirmed'): ?>
            <a href="?status=confirmed&id=<?=$r['id']?>&event_id=<?=$filterEvent?>"
               class="btn btn-sm" style="border-radius:50px;font-size:.68rem;padding:3px 10px;background:rgba(46,125,50,.1);color:#2e7d32;border:1px solid rgba(46,125,50,.2)"
               title="Mark confirmed">Confirm</a>
            <?php endif; ?>
            <?php if($r['status']!=='cancelled'): ?>
            <a href="?status=cancelled&id=<?=$r['id']?>&event_id=<?=$filterEvent?>"
               onclick="return confirm('Cancel this registration?')"
               class="btn btn-sm" style="border-radius:50px;font-size:.68rem;padding:3px 10px;background:rgba(204,0,0,.07);color:#cc0000;border:1px solid rgba(204,0,0,.15)"
               title="Cancel">Cancel</a>
            <?php endif; ?>
            <button onclick="confirmDelete('/anu/admin/rsvp.php?delete=<?=$r['id']?>&event_id=<?=$filterEvent?>','<?=e(addslashes($r['name']))?>')"
                    class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.68rem;padding:3px 10px">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php adminFoot(); ?>
