<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);
if($action==='delete'&&$editId){$db->prepare("DELETE FROM events WHERE id=?")->execute([$editId]);$msg='Event deleted.';$action='list';}
if($action==='toggle'&&$editId){$db->prepare("UPDATE events SET is_active=NOT is_active WHERE id=?")->execute([$editId]);header('Location: ' . SITE_URL . '/admin/events.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $title=trim($_POST['title']??'');$desc=trim($_POST['description']??'');$date=trim($_POST['event_date']??'');
  $time=trim($_POST['event_time']??'')?:null;$loc=trim($_POST['location']??'');$type=trim($_POST['type']??'online');$link=trim($_POST['link']??'');
  $imgPath=null;
  if(!empty($_FILES['image']['name']))$imgPath=handleUpload('image','events');
  if(!$title||!$date){$msg='Title and date required.';$msgType='error';}
  else{
    if($editId){
      if(!$imgPath){$c=$db->prepare("SELECT image FROM events WHERE id=?");$c->execute([$editId]);$c=$c->fetch();$imgPath=$c?$c['image']:null;}
      $db->prepare("UPDATE events SET title=?,description=?,event_date=?,event_time=?,location=?,type=?,link=?,image=? WHERE id=?")->execute([$title,$desc,$date,$time,$loc,$type,$link,$imgPath,$editId]);$msg='Event updated!';
    }else{$db->prepare("INSERT INTO events (title,description,event_date,event_time,location,type,link,image) VALUES (?,?,?,?,?,?,?,?)")->execute([$title,$desc,$date,$time,$loc,$type,$link,$imgPath]);$msg='Event created!';}
    $action='list';
  }
}
$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM events WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
adminHead('Events');adminSidebar('events');
?>
<?php adminTopBar('Events & Deadlines'); ?>
<?php if($msg): ?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif; ?>
<?php if($action==='list'): ?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-calendar-event-fill me-2" style="color:var(--primary);"></i>All Events</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add Event</a>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>Title</th><th>Date</th><th>Location</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($items as $ev):$isPast=strtotime($ev['event_date'])<time(); ?>
        <tr style="<?=$isPast?'opacity:0.65':''?>">
          <td><strong><?=e($ev['title'])?></strong></td>
          <td><?=formatDate($ev['event_date'],'M d, Y')?><?=$isPast?' <span style="color:#bbb;font-size:0.75rem;">(past)</span>':''?></td>
          <td><?=e($ev['location'])?></td>
          <td><span class="event-type-badge"><?=e($ev['type'])?></span></td>
          <td><a href="?action=toggle&id=<?=$ev['id']?>"><span class="badge-status <?=$ev['is_active']?'badge-active':'badge-inactive'?>"><?=$ev['is_active']?'Active':'Hidden'?></span></a></td>
          <td><div class="d-flex gap-2">
            <a href="?action=edit&id=<?=$ev['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
            <button onclick="confirmDelete('/anu/admin/events.php?action=delete&id=<?=$ev['id']?>','<?=e(addslashes($ev['title']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
          </div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card">
  <h5><i class="bi bi-pencil-fill me-2"></i><?=$editId?'Edit Event':'New Event'?></h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label">Event Title *</label><input type="text" name="title" class="form-control" required value="<?=e($editing['title']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Date *</label><input type="date" name="event_date" class="form-control" required value="<?=e($editing['event_date']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Time (optional)</label><input type="time" name="event_time" class="form-control" value="<?=e($editing['event_time']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Type</label><select name="type" class="form-select"><?php foreach(['online','physical','hybrid'] as $t):?><option value="<?=$t?>" <?=($editing['type']??'online')===$t?'selected':''?>><?=ucfirst($t)?></option><?php endforeach;?></select></div>
      <div class="col-md-6"><label class="form-label">Location / Platform</label><input type="text" name="location" class="form-control" placeholder="e.g., Zoom, ANU Campus" value="<?=e($editing['location']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Registration Link</label><input type="text" name="link" class="form-control" placeholder="https://..." value="<?=e($editing['link']??'')?>"></div>
      <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"><?=e($editing['description']??'')?></textarea></div>
      <div class="col-md-5">
        <label class="form-label">Event Image</label>
        <div class="img-upload-wrap" onclick="document.getElementById('evtImg').click()">
          <?php if(!empty($editing['image'])):?><img src="/<?=e($editing['image'])?>" alt="Current event image" style="width:100%;height:180px;object-fit:cover;"><?php else:?><div class="img-placeholder"><i class="bi bi-image"></i><span>Upload Image</span></div><?php endif;?>
          <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:2rem;"></i><span>Change</span></div>
        </div>
        <input type="file" name="image" id="evtImg" class="img-upload-input" style="display:none;" accept="image/*">
      </div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Create Event'?></button></div>
    </div>
  </form>
</div>
<?php endif; ?>
</div>

<?php adminFoot(); ?>