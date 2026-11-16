<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();

/* Auto-migrate streaming columns */
$newCols = [
    'stream_url'       => "VARCHAR(1000) DEFAULT NULL",
    'stream_platform'  => "VARCHAR(50) DEFAULT 'youtube'",
    'recording_url'    => "VARCHAR(1000) DEFAULT NULL",
    'thumbnail'        => "VARCHAR(500) DEFAULT NULL",
    'category'         => "VARCHAR(50) DEFAULT 'academic'",
    'is_live'          => "TINYINT(1) DEFAULT 0",
    'is_featured'      => "TINYINT(1) DEFAULT 0",
    'is_free'          => "TINYINT(1) DEFAULT 1",
    'registration_url' => "VARCHAR(500) DEFAULT NULL",
    'speaker_name'     => "VARCHAR(255) DEFAULT NULL",
    'speaker_title'    => "VARCHAR(255) DEFAULT NULL",
    'speaker_photo'    => "VARCHAR(500) DEFAULT NULL",
    'end_time'         => "TIME DEFAULT NULL",
];
try {
    $have = $db->query("SHOW COLUMNS FROM events")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($newCols as $col => $def) {
        if (!in_array($col, $have)) {
            try { $db->exec("ALTER TABLE events ADD COLUMN `$col` $def"); } catch(Exception $ex) {}
        }
    }
} catch(Exception $ex) {}

/* Helpers */
function lsVal($row, $k, $d = '') { return isset($row[$k]) ? $row[$k] : $d; }
function lsYt($url) {
    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/', $url, $m);
    return isset($m[1]) ? 'https://img.youtube.com/vi/'.$m[1].'/default.jpg' : '';
}

$catLabels  = ['academic'=>'Academic','chapel'=>'Chapel','university'=>'University','orientation'=>'Orientation','other'=>'Other'];
$catColors  = ['academic'=>'#1565C0','chapel'=>'#6a1b9a','university'=>'#c1121f','orientation'=>'#2e7d32','other'=>'#5d4037'];
$platLabels = ['youtube'=>'YouTube Live','zoom'=>'Zoom','teams'=>'Microsoft Teams','facebook'=>'Facebook Live','other'=>'Other'];

/* Actions */
$msg     = '';
$msgType = 'success';
$action  = isset($_GET['action']) ? $_GET['action'] : 'list';
$editId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'golive' && $editId) {
    try { $db->prepare("UPDATE events SET is_live = NOT is_live WHERE id=?")->execute([$editId]); } catch(Exception $ex) {}
    header('Location: ' . SITE_URL . '/admin/livestream.php'); exit;
}
if ($action === 'feature' && $editId) {
    try { $db->prepare("UPDATE events SET is_featured = NOT is_featured WHERE id=?")->execute([$editId]); } catch(Exception $ex) {}
    header('Location: ' . SITE_URL . '/admin/livestream.php'); exit;
}
if ($action === 'toggle' && $editId) {
    try { $db->prepare("UPDATE events SET is_active = NOT is_active WHERE id=?")->execute([$editId]); } catch(Exception $ex) {}
    header('Location: ' . SITE_URL . '/admin/livestream.php'); exit;
}
if ($action === 'delete' && $editId) {
    try { $db->prepare("DELETE FROM events WHERE id=?")->execute([$editId]); } catch(Exception $ex) {}
    $msg = 'Event deleted.'; $action = 'list';
}

/* Save */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $id         = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    $title      = trim(isset($_POST['title'])           ? $_POST['title']           : '');
    $desc       = trim(isset($_POST['description'])      ? $_POST['description']      : '');
    $date       = trim(isset($_POST['event_date'])       ? $_POST['event_date']       : '');
    $time       = trim(isset($_POST['event_time'])       ? $_POST['event_time']       : '') ?: null;
    $endTime    = trim(isset($_POST['end_time'])         ? $_POST['end_time']         : '') ?: null;
    $location   = trim(isset($_POST['location'])         ? $_POST['location']         : '');
    $type       = trim(isset($_POST['type'])             ? $_POST['type']             : 'online');
    $category   = trim(isset($_POST['category'])         ? $_POST['category']         : 'academic');
    $streamUrl  = trim(isset($_POST['stream_url'])       ? $_POST['stream_url']       : '');
    $streamPlat = trim(isset($_POST['stream_platform'])  ? $_POST['stream_platform']  : 'youtube');
    $recUrl     = trim(isset($_POST['recording_url'])    ? $_POST['recording_url']    : '');
    $regUrl     = trim(isset($_POST['registration_url']) ? $_POST['registration_url'] : '');
    $link       = trim(isset($_POST['link'])             ? $_POST['link']             : '');
    $speaker    = trim(isset($_POST['speaker_name'])     ? $_POST['speaker_name']     : '');
    $speakerT   = trim(isset($_POST['speaker_title'])    ? $_POST['speaker_title']    : '');
    $isFree     = isset($_POST['is_free'])     ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isLive     = isset($_POST['is_live'])     ? 1 : 0;
    $isActive   = isset($_POST['is_active'])   ? 1 : 0;

    $thumbPath = null;
    if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] === 0)
        $thumbPath = handleUpload('thumbnail', 'events');

    $speakerPhoto = null;
    if (!empty($_FILES['speaker_photo']['name']) && $_FILES['speaker_photo']['error'] === 0)
        $speakerPhoto = handleUpload('speaker_photo', 'speakers');

    if (!$title || !$date) {
        $msg = 'Title and date are required.'; $msgType = 'error';
    } else {
        $cols = ['title','description','event_date','event_time','end_time','location','type',
                 'category','stream_url','stream_platform','recording_url','registration_url',
                 'link','speaker_name','speaker_title','is_free','is_featured','is_live','is_active'];
        $vals = [$title,$desc,$date,$time,$endTime,$location,$type,
                 $category,$streamUrl,$streamPlat,$recUrl,$regUrl,
                 $link,$speaker,$speakerT,$isFree,$isFeatured,$isLive,$isActive];
        if ($thumbPath)    { $cols[] = 'thumbnail';     $vals[] = $thumbPath; }
        if ($speakerPhoto) { $cols[] = 'speaker_photo'; $vals[] = $speakerPhoto; }

        try {
            if ($id) {
                if (!$thumbPath) {
                    try {
                        $s = $db->prepare("SELECT thumbnail FROM events WHERE id=?");
                        $s->execute([$id]);
                        $old = $s->fetchColumn();
                        if ($old) { $cols[] = 'thumbnail'; $vals[] = $old; }
                    } catch(Exception $ex) {}
                }
                $set = implode(',', array_map(function($c){ return "`$c`=?"; }, $cols));
                $db->prepare("UPDATE events SET $set WHERE id=?")->execute(array_merge($vals, [$id]));
                $msg = 'Event updated!';
            } else {
                $ph = implode(',', array_fill(0, count($cols), '?'));
                $db->prepare("INSERT INTO events (`".implode('`,`',$cols)."`) VALUES ($ph)")->execute($vals);
                $msg = 'Event created!';
            }
        } catch(Exception $ex) {
            $msg = 'Error: '.$ex->getMessage(); $msgType = 'error';
        }
        $action = 'list';
    }
}

/* Fetch */
$events = [];
try {
    $events = $db->query("SELECT * FROM events ORDER BY COALESCE(is_live,0) DESC, event_date ASC")->fetchAll();
} catch(Exception $ex) {
    try { $events = $db->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll(); }
    catch(Exception $ex2) { $events = []; }
}

$editing = [];
if ($action === 'edit' && $editId) {
    try {
        $s = $db->prepare("SELECT * FROM events WHERE id=?");
        $s->execute([$editId]);
        $editing = $s->fetch(PDO::FETCH_ASSOC);
        if (!$editing) { $editing = []; $action = 'list'; $msg = 'Event not found.'; $msgType = 'error'; }
    } catch(Exception $ex) { $editing = []; $action = 'list'; }
}

adminHead('Live Events'); adminSidebar('livestream');
adminTopBar('Live Events & Webinars', 'Manage events, streams and recordings');

if ($msg) {
    $icon = ($msgType === 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill';
    echo '<div class="alert-anu-'.$msgType.' mb-4"><i class="bi bi-'.$icon.' me-2"></i>'.e($msg).'</div>';
}

/* ─────────────────────────── FORM ─────────────────────────── */
if ($action === 'edit' || $action === 'new') {

echo '<div class="mb-3">
  <a href="?action=list" style="color:var(--primary);font-weight:600;font-size:.88rem">
    <i class="bi bi-arrow-left me-1"></i>Back to Events
  </a>
</div>
<div class="admin-card">
<div style="padding:28px">';

/* Card header */
echo '<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;padding-bottom:20px;border-bottom:1.5px solid var(--border)">
  <div style="width:44px;height:44px;border-radius:12px;background:rgba(204,0,0,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <i class="bi bi-broadcast-pin" style="color:var(--primary);font-size:1.2rem"></i>
  </div>
  <div>
    <h5 style="margin:0;font-size:1rem;font-weight:700">'.($editId?'Edit Event':'Create New Event').'</h5>
    <div style="font-size:.74rem;color:var(--text-mid);margin-top:2px">Stream URL, speaker, and thumbnail are optional</div>
  </div>
</div>';

echo '<form method="POST" enctype="multipart/form-data" class="anu-form">
<input type="hidden" name="save_event" value="1">
<input type="hidden" name="id" value="'.$editId.'">
<div class="row g-4">';

/* Left column */
echo '<div class="col-lg-8"><div class="row g-3">';

/* Title */
echo '<div class="col-12">
  <label class="form-label">Event Title <span style="color:var(--red)">*</span></label>
  <input type="text" name="title" class="form-control" required
         value="'.e(lsVal($editing,'title')).'" placeholder="e.g. ANU Online Open Day 2026">
</div>';

/* Description */
echo '<div class="col-12">
  <label class="form-label">Description</label>
  <textarea name="description" class="form-control" rows="4" placeholder="What will attendees learn?">'.e(lsVal($editing,'description')).'</textarea>
</div>';

/* Date/time */
echo '<div class="col-md-4">
  <label class="form-label">Date <span style="color:var(--red)">*</span></label>
  <input type="date" name="event_date" class="form-control" required value="'.e(lsVal($editing,'event_date')).'">
</div>
<div class="col-md-4">
  <label class="form-label">Start Time (EAT)</label>
  <input type="time" name="event_time" class="form-control" value="'.e(lsVal($editing,'event_time')).'">
</div>
<div class="col-md-4">
  <label class="form-label">End Time (EAT)</label>
  <input type="time" name="end_time" class="form-control" value="'.e(lsVal($editing,'end_time')).'">
</div>';

/* Category / Format */
$catOpts = '';
foreach ($catLabels as $v => $l) {
    $sel = lsVal($editing,'category','academic') === $v ? ' selected' : '';
    $catOpts .= '<option value="'.e($v).'"'.$sel.'>'.e($l).'</option>';
}
echo '<div class="col-md-6">
  <label class="form-label">Category</label>
  <select name="category" class="form-select">'.$catOpts.'</select>
</div>';

$typeMap = ['online'=>'Online Only','physical'=>'On Campus','hybrid'=>'Hybrid'];
$typeOpts = '';
foreach ($typeMap as $v => $l) {
    $sel = lsVal($editing,'type','online') === $v ? ' selected' : '';
    $typeOpts .= '<option value="'.e($v).'"'.$sel.'>'.e($l).'</option>';
}
echo '<div class="col-md-6">
  <label class="form-label">Format</label>
  <select name="type" class="form-select">'.$typeOpts.'</select>
</div>';

/* Location */
echo '<div class="col-12">
  <label class="form-label">Location / Venue</label>
  <input type="text" name="location" class="form-control" value="'.e(lsVal($editing,'location')).'" placeholder="e.g. Online (Zoom) or ANU Nairobi Campus">
</div>';

/* Stream box */
$platOpts = '';
foreach ($platLabels as $v => $l) {
    $sel = lsVal($editing,'stream_platform','youtube') === $v ? ' selected' : '';
    $platOpts .= '<option value="'.e($v).'"'.$sel.'>'.e($l).'</option>';
}
echo '<div class="col-12">
<div style="border:1.5px solid var(--border);border-radius:12px;padding:20px;background:#fafbff">
  <div style="font-size:.74rem;font-weight:800;letter-spacing:.9px;text-transform:uppercase;color:var(--text-dark);margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <i class="bi bi-broadcast-pin" style="color:var(--primary)"></i>Stream Settings
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Platform</label>
      <select name="stream_platform" class="form-select">'.$platOpts.'</select>
    </div>
    <div class="col-md-8">
      <label class="form-label">Live Stream URL</label>
      <input type="url" name="stream_url" id="streamUrlInput" class="form-control"
             value="'.e(lsVal($editing,'stream_url')).'" placeholder="https://youtube.com/watch?v=..."
             oninput="lsPreviewYt(this.value)">
    </div>
    <div class="col-12">
      <label class="form-label">Recording URL <small style="color:#aaa;font-weight:400">(paste after event ends)</small></label>
      <input type="url" name="recording_url" class="form-control"
             value="'.e(lsVal($editing,'recording_url')).'" placeholder="https://youtube.com/watch?v=...">
    </div>
    <div class="col-md-6">
      <label class="form-label">Registration Link <small style="color:#aaa;font-weight:400">(optional)</small></label>
      <input type="url" name="registration_url" class="form-control"
             value="'.e(lsVal($editing,'registration_url')).'" placeholder="Zoom or Google Form">
    </div>
    <div class="col-md-6">
      <label class="form-label">Fallback / Info Link</label>
      <input type="url" name="link" class="form-control"
             value="'.e(lsVal($editing,'link')).'" placeholder="Any other event URL">
    </div>
  </div>
</div>
</div>';

/* Speaker box */
$spPhoto = lsVal($editing,'speaker_photo','');
$spPhotoHtml = $spPhoto
    ? '<img src="'.imgUrl($spPhoto).'" alt="Speaker photo" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border)" onerror="this.style.display=\'none\'">'
    : '';
echo '<div class="col-12">
<div style="border:1.5px solid var(--border);border-radius:12px;padding:20px;background:#fafbff">
  <div style="font-size:.74rem;font-weight:800;letter-spacing:.9px;text-transform:uppercase;color:var(--text-dark);margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <i class="bi bi-person-video2" style="color:var(--primary)"></i>Speaker / Presenter
    <span style="font-weight:400;font-size:.68rem;text-transform:none;letter-spacing:0;color:#aaa">optional</span>
  </div>
  <div class="row g-3">
    <div class="col-md-5">
      <label class="form-label">Name</label>
      <input type="text" name="speaker_name" class="form-control" value="'.e(lsVal($editing,'speaker_name')).'" placeholder="Dr. Jane Wanjiku">
    </div>
    <div class="col-md-7">
      <label class="form-label">Title / Role</label>
      <input type="text" name="speaker_title" class="form-control" value="'.e(lsVal($editing,'speaker_title')).'" placeholder="Dean, School of Business">
    </div>
    <div class="col-12">
      <label class="form-label">Speaker Photo</label>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        '.$spPhotoHtml.'
        <input type="file" name="speaker_photo" class="form-control" accept="image/*" style="flex:1;min-width:180px">
      </div>
    </div>
  </div>
</div>
</div>';

echo '</div></div>'; /* /row g-3 /col-lg-8 */

/* Right column */
echo '<div class="col-lg-4">';

/* Publish settings */
echo '<div class="admin-card mb-3" style="border:1.5px solid var(--border)">
<div style="padding:18px 20px">
  <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-dark);margin-bottom:16px">Publish Settings</div>
  <div style="display:flex;flex-direction:column;gap:12px">';

$toggles = [
    ['is_active',   lsVal($editing,'is_active',1),   'Active',         'Visible on the public portal',     false],
    ['is_featured', lsVal($editing,'is_featured',0),  'Featured',       'Highlighted with a ★ star badge',  false],
    ['is_free',     lsVal($editing,'is_free',1),      'Free to Watch',  'Shows a FREE badge on the card',   false],
    ['is_live',     lsVal($editing,'is_live',0),      '🔴 Go Live Now', 'Marks as currently streaming',     true],
];
foreach ($toggles as $tog) {
    list($name,$checked,$label,$hint,$red) = $tog;
    $chk   = $checked ? ' checked' : '';
    $style = $red ? 'background:rgba(204,0,0,.05);border:1.5px solid rgba(204,0,0,.18);border-radius:9px;padding:10px 12px;' : '';
    $lclr  = $red ? 'color:var(--primary);' : 'color:var(--text-dark);';
    echo '<label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;'.$style.'margin:0">
      <input type="checkbox" name="'.$name.'" class="form-check-input" style="margin-top:3px;flex-shrink:0"'.$chk.'>
      <div>
        <div style="font-size:.84rem;font-weight:700;'.$lclr.'">'.$label.'</div>
        <div style="font-size:.72rem;color:var(--text-mid);margin-top:2px">'.$hint.'</div>
      </div>
    </label>';
}

echo '</div></div></div>'; /* /publish settings card */

/* Thumbnail */
$existingThumb = '';
if (lsVal($editing,'thumbnail',''))   $existingThumb = imgUrl($editing['thumbnail']);
elseif (lsVal($editing,'stream_url','')) $existingThumb = lsYt($editing['stream_url']);

$thumbImgHtml = $existingThumb
    ? '<img id="thumbPreviewImg" src="'.e($existingThumb).'" alt="Livestream thumbnail preview" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display=\'none\'">'
    : '<i class="bi bi-image" id="thumbPlaceholderIcon" style="font-size:2.2rem;color:rgba(255,255,255,.2)"></i>';

echo '<div class="admin-card" style="border:1.5px solid var(--border)">
<div style="padding:18px 20px">
  <div style="font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--text-dark);margin-bottom:14px">Thumbnail</div>
  <div id="thumbPreviewWrap" style="width:100%;aspect-ratio:16/9;border-radius:10px;overflow:hidden;margin-bottom:12px;background:linear-gradient(135deg,#100000,#280010);display:flex;align-items:center;justify-content:center">
    '.$thumbImgHtml.'
  </div>
  <input type="file" name="thumbnail" class="form-control" accept="image/*" onchange="lsPreviewFile(this)" style="margin-bottom:8px">
  <small style="color:var(--text-mid);font-size:.7rem">16:9 recommended. YouTube URLs auto-preview above.</small>
</div>
</div>';

echo '</div>'; /* /col-lg-4 */

/* Submit */
echo '<div class="col-12 d-flex gap-3 pt-2">
  <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px">
    <i class="bi bi-check-circle-fill me-2"></i>'.($editId?'Save Changes':'Create Event').'
  </button>
  <a href="?action=list" class="btn btn-outline-secondary" style="border-radius:50px;padding:12px 22px">Cancel</a>
</div>';

echo '</div></form>'; /* /row g-4 /form */
echo '</div></div>'; /* /padding /admin-card */

?>
<script>
function lsPreviewYt(url) {
    var m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/);
    if (m) lsShowThumb('https://img.youtube.com/vi/'+m[1]+'/hqdefault.jpg');
}
function lsPreviewFile(inp) {
    if (!inp.files[0]) return;
    var r = new FileReader();
    r.onload = function(e) { lsShowThumb(e.target.result); };
    r.readAsDataURL(inp.files[0]);
}
function lsShowThumb(src) {
    var wrap = document.getElementById('thumbPreviewWrap');
    if (!wrap) return;
    var icon = document.getElementById('thumbPlaceholderIcon');
    if (icon) icon.style.display = 'none';
    var img = document.getElementById('thumbPreviewImg');
    if (!img) {
        img = document.createElement('img');
        img.id = 'thumbPreviewImg';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover';
        wrap.appendChild(img);
    }
    img.src = src;
    img.style.display = 'block';
}
// Auto-preview existing stream URL
window.addEventListener('DOMContentLoaded', function(){
    var u = document.getElementById('streamUrlInput');
    if (u && u.value) lsPreviewYt(u.value);
});
</script>
<?php

} else {
/* ─────────────────────────── LIST ─────────────────────────── */

$liveCount = 0;
foreach ($events as $ev) { if (lsVal($ev,'is_live',0) == 1) $liveCount++; }

/* Header bar */
echo '<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">';
echo '<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">';
if ($liveCount) {
    echo '<div style="display:inline-flex;align-items:center;gap:7px;background:rgba(204,0,0,.08);color:var(--primary);border:1.5px solid rgba(204,0,0,.25);border-radius:50px;padding:5px 14px;font-size:.76rem;font-weight:800">
      <span style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0"></span>
      '.$liveCount.' Live Now
    </div>';
}
echo '<span style="font-size:.83rem;color:var(--text-mid)">'.count($events).' total event'.(count($events)!==1?'s':'').'</span>';
echo '</div>';
echo '<div style="display:flex;gap:10px;flex-wrap:wrap">
  <a href="/anu/livestream.php" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;padding:7px 16px;font-size:.8rem">
    <i class="bi bi-eye me-1"></i>View Portal
  </a>
  <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px">
    <i class="bi bi-plus-lg me-1"></i>Add Event
  </a>
</div></div>';

if (empty($events)) {
    echo '<div class="admin-card text-center" style="padding:70px 24px">
      <div style="width:72px;height:72px;border-radius:20px;background:rgba(204,0,0,.07);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
        <i class="bi bi-broadcast-pin" style="font-size:2rem;color:var(--primary)"></i>
      </div>
      <h5 style="font-size:1.05rem;margin-bottom:8px">No events yet</h5>
      <p style="color:var(--text-mid);font-size:.85rem;margin-bottom:24px">Create your first live event or webinar.</p>
      <a href="?action=new" class="btn btn-primary-anu" style="border-radius:50px;padding:11px 32px">
        <i class="bi bi-plus-lg me-2"></i>Create First Event
      </a>
    </div>';
} else {
    $now = strtotime('today');
    $evLive = array_filter($events, function($e){ return lsVal($e,'is_live',0)==1; });
    $evUp   = array_filter($events, function($e) use ($now){ return lsVal($e,'is_live',0)==0 && strtotime($e['event_date'].' '.lsVal($e,'event_time','00:00'))>=$now; });
    $evPast = array_filter($events, function($e) use ($now){ return lsVal($e,'is_live',0)==0 && strtotime($e['event_date'].' '.lsVal($e,'event_time','00:00'))<$now; });

    $groups = [];
    if (!empty($evLive)) $groups[] = ['label'=>'🔴 Live Now',    'rows'=>$evLive, 'red'=>true];
    if (!empty($evUp))   $groups[] = ['label'=>'📅 Upcoming',    'rows'=>$evUp,   'red'=>false];
    if (!empty($evPast)) $groups[] = ['label'=>'📼 Past Events', 'rows'=>$evPast, 'red'=>false];

    foreach ($groups as $group) {
        $redBg = $group['red'] ? 'background:rgba(204,0,0,.07);' : '';
        echo '<div class="admin-card mb-4" style="overflow:hidden;padding:0">
          <div class="prog-school-hdr" style="'.$redBg.'">
            <span style="font-size:.88rem">'.$group['label'].'</span>
            <em>'.count($group['rows']).' event'.(count($group['rows'])!==1?'s':'').'</em>
          </div>
          <div class="table-responsive">
          <table class="table mb-0">
          <thead><tr>
            <th style="width:62px">Thumb</th>
            <th>Event</th>
            <th>Date &amp; Time</th>
            <th>Category</th>
            <th>Stream</th>
            <th style="text-align:center">Go Live</th>
            <th style="text-align:center">Status</th>
            <th style="text-align:right">Actions</th>
          </tr></thead>
          <tbody>';

        foreach ($group['rows'] as $row) {
            $cat    = isset($catLabels[$row['category']??'']) ? $catLabels[$row['category']] : 'Academic';
            $col    = isset($catColors[$row['category']??''])  ? $catColors[$row['category']]  : '#555';
            $sUrl   = lsVal($row,'stream_url','');
            $rUrl   = lsVal($row,'recording_url','');
            $isLive = lsVal($row,'is_live',0) == 1;
            $isFeat = lsVal($row,'is_featured',0) == 1;
            $isAct  = lsVal($row,'is_active',1) == 1;

            $thumb = '';
            if (!empty($row['thumbnail']))  $thumb = imgUrl($row['thumbnail']);
            elseif ($sUrl)                  $thumb = lsYt($sUrl);
            elseif ($rUrl)                  $thumb = lsYt($rUrl);

            $rowBg = $isLive ? 'background:rgba(255,0,0,.015)' : '';

            /* Thumbnail cell */
            $thumbCell = $thumb
                ? '<img src="'.e($thumb).'" alt="Event thumbnail" style="width:100%;height:100%;object-fit:cover" onerror="this.style.display=\'none\'">'
                : '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center"><i class="bi bi-broadcast-pin" style="color:rgba(255,255,255,.2);font-size:.85rem"></i></div>';

            /* Stream cell */
            if ($sUrl) {
                $streamCell = '<a href="'.e($sUrl).'" target="_blank" style="display:inline-flex;align-items:center;gap:5px;font-size:.74rem;font-weight:700;color:var(--primary)"><i class="bi bi-play-circle-fill"></i>Stream</a>';
            } elseif ($rUrl) {
                $streamCell = '<a href="'.e($rUrl).'" target="_blank" style="display:inline-flex;align-items:center;gap:5px;font-size:.74rem;font-weight:700;color:#2e7d32"><i class="bi bi-collection-play-fill"></i>Replay</a>';
            } else {
                $streamCell = '<span style="color:#ccc;font-size:.8rem">—</span>';
            }

            /* Live toggle */
            $confirmMsg = $isLive ? 'Take offline?' : 'Mark as LIVE NOW?';
            if ($isLive) {
                $liveCell = '<span style="display:inline-flex;align-items:center;gap:5px;font-size:.67rem;font-weight:800;background:rgba(204,0,0,.1);color:var(--primary);padding:5px 11px;border-radius:50px;border:1.5px solid rgba(204,0,0,.2)"><span style="width:7px;height:7px;border-radius:50%;background:var(--primary);flex-shrink:0"></span>LIVE</span>';
            } else {
                $liveCell = '<span style="font-size:.67rem;font-weight:700;background:#f2f2f2;color:#bbb;padding:5px 11px;border-radius:50px;border:1.5px solid #e8e8e8">Offline</span>';
            }

            /* Status badge */
            $statusCell = '<span class="badge-status '.($isAct?'badge-active':'badge-inactive').'">'.($isAct?'Active':'Hidden').'</span>';

            /* Star */
            $starHtml = $isFeat
                ? '<i class="bi bi-star-fill" style="font-size:1rem;color:#fac823" title="Remove featured"></i>'
                : '<i class="bi bi-star" style="font-size:1rem;color:#ccc" title="Mark featured"></i>';

            /* Speaker sub-line */
            $speakerLine = !empty($row['speaker_name'])
                ? '<div style="font-size:.72rem;color:var(--text-mid);margin-top:3px"><i class="bi bi-person me-1"></i>'.e($row['speaker_name']).'</div>'
                : '';
            $locationLine = !empty($row['location'])
                ? '<div style="font-size:.68rem;color:#aaa;margin-top:2px"><i class="bi bi-geo-alt me-1"></i>'.e($row['location']).'</div>'
                : '';

            $timeStr = !empty($row['event_time'])
                ? '<div style="font-size:.72rem;color:var(--text-mid)">'.date('g:i A', strtotime($row['event_time'])).' EAT</div>'
                : '';

            $featStar = $isFeat ? '<i class="bi bi-star-fill" style="color:#fac823;font-size:.72rem;margin-left:4px"></i>' : '';

            echo '<tr style="'.$rowBg.'">
              <td>
                <div style="width:58px;height:38px;border-radius:7px;overflow:hidden;background:linear-gradient(135deg,#0d0000,#280010);position:relative;flex-shrink:0">
                  '.$thumbCell.'
                </div>
              </td>
              <td style="max-width:250px">
                <div style="font-weight:700;font-size:.85rem;color:var(--text-dark);line-height:1.3">'.e($row['title']).$featStar.'</div>
                '.$speakerLine.$locationLine.'
              </td>
              <td style="white-space:nowrap;font-size:.82rem">
                <strong>'.date('M j, Y',strtotime($row['event_date'])).'</strong>
                '.$timeStr.'
              </td>
              <td>
                <span style="display:inline-block;font-size:.67rem;font-weight:800;padding:3px 10px;border-radius:50px;background:'.$col.'18;color:'.$col.';letter-spacing:.3px">'.e($cat).'</span>
              </td>
              <td>'.$streamCell.'</td>
              <td style="text-align:center">
                <a href="?action=golive&id='.$row['id'].'" onclick="return confirm(\''.addslashes($confirmMsg).'\')">'.$liveCell.'</a>
              </td>
              <td style="text-align:center">
                <a href="?action=toggle&id='.$row['id'].'">'.$statusCell.'</a>
              </td>
              <td>
                <div class="d-flex gap-2 justify-content-end align-items-center">
                  <a href="?action=feature&id='.$row['id'].'">'.$starHtml.'</a>
                  <a href="?action=edit&id='.$row['id'].'" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.72rem;padding:4px 14px">Edit</a>
                  <button onclick="confirmDelete(\'?action=delete&id='.$row['id'].'\',\''.addslashes(e($row['title'])).'\')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.72rem;padding:4px 12px">Delete</button>
                </div>
              </td>
            </tr>';
        }

        echo '</tbody></table></div></div>';
    }
}

} /* end list */

adminFoot();
