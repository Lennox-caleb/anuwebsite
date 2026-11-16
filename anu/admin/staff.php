<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);

if($action==='delete'&&$editId){$db->prepare("DELETE FROM staff WHERE id=?")->execute([$editId]);$msg='Team member removed.';$action='list';}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $name=trim($_POST['name']??'');$title=trim($_POST['title']??'');$dept=trim($_POST['department']??'');
  $bio=trim($_POST['bio']??'');$email=trim($_POST['email']??'');$sort=(int)($_POST['sort_order']??0);
  $featured=isset($_POST['is_featured'])?1:0;
  $photoPath=null;if(!empty($_FILES['photo']['name']))$photoPath=handleUpload('photo','staff');
  if(!$name){$msg='Name is required.';$msgType='error';}
  else{
    if($editId){
      if(!$photoPath){$c=$db->prepare("SELECT photo FROM staff WHERE id=?");$c->execute([$editId]);$c=$c->fetch();$photoPath=$c?$c['photo']:null;}
      $db->prepare("UPDATE staff SET name=?,title=?,department=?,bio=?,photo=?,email=?,is_featured=?,sort_order=? WHERE id=?")
         ->execute([$name,$title,$dept,$bio,$photoPath,$email,$featured,$sort,$editId]);
      $msg='Team member updated!';
    }else{
      $db->prepare("INSERT INTO staff (name,title,department,bio,photo,email,is_featured,sort_order) VALUES (?,?,?,?,?,?,?,?)")
         ->execute([$name,$title,$dept,$bio,$photoPath,$email,$featured,$sort]);
      $msg='Team member added!';
    }
    $action='list';
  }
}

$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM staff WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM staff ORDER BY sort_order,name")->fetchAll();
adminHead('Team'); adminSidebar('staff');
?>
<?php adminTopBar('Team / Staff'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>

<?php if($action==='list'):?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <h5 style="margin:0;"><i class="bi bi-people-fill me-2" style="color:var(--primary);"></i>All Team Members</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add Team Member</a>
  </div>
  <p style="font-size:.82rem;color:#888;margin-bottom:20px;">
    <i class="bi bi-info-circle me-1"></i>
    Set <strong>Department</strong> to <code>Leadership</code> to drive the Director/Welcome message on the Home, Programs, About and Student Life pages (mark that one person <strong>Featured</strong>).
    Set it to <code>Chaplaincy</code> to populate the Chaplaincy page team (mark the lead chaplain <strong>Featured</strong>).
    Any department shows up grouped on the public <a href="/anu/team.php" target="_blank">Meet the Team</a> page.
  </p>
  <div class="row g-3">
    <?php foreach($items as $m):?>
    <div class="col-md-6">
      <div style="background:#f8f8f8;border-radius:14px;padding:20px;display:flex;gap:16px;align-items:flex-start;border:1px solid rgba(0,0,0,0.07);">
        <?php if(!empty($m['photo'])):?>
          <img src="/<?=e($m['photo'])?>" alt="<?=e($m['name'])?>" style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <?php else:?>
          <div style="width:52px;height:52px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;flex-shrink:0;"><?=strtoupper(substr($m['name'],0,2))?></div>
        <?php endif;?>
        <div style="flex:1;">
          <div style="font-weight:700;"><?=e($m['name'])?> <?php if($m['is_featured']):?><span class="badge" style="background:var(--accent);color:#5a3a00;font-size:.65rem;">Featured</span><?php endif;?></div>
          <div style="font-size:0.82rem;color:var(--mid);"><?=e($m['title'])?><?php if($m['department']):?> · <?=e($m['department'])?><?php endif;?></div>
          <?php if($m['email']):?><div style="font-size:0.76rem;color:#888;margin-top:2px;"><?=e($m['email'])?></div><?php endif;?>
        </div>
        <div class="d-flex flex-column gap-2">
          <a href="?action=edit&id=<?=$m['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;white-space:nowrap;">Edit</a>
          <button onclick="confirmDelete('/anu/admin/staff.php?action=delete&id=<?=$m['id']?>','<?=e(addslashes($m['name']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(!$items):?>
    <div class="col-12 text-center py-5" style="color:#888;">
      <i class="bi bi-people" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
      No team members yet. Add your first one above.
    </div>
    <?php endif;?>
  </div>
</div>
<?php else:?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card">
  <h5><?=$editId?'Edit Team Member':'Add Team Member'?></h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?=e($editing['name']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Title / Role</label><input type="text" name="title" class="form-control" placeholder="e.g. Director, ANU Online" value="<?=e($editing['title']??'')?>"></div>
      <div class="col-md-6">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" list="deptList" placeholder="e.g. Leadership, Chaplaincy, Faculty" value="<?=e($editing['department']??'')?>">
        <datalist id="deptList">
          <option value="Leadership"><option value="Chaplaincy"><option value="Faculty"><option value="Support">
        </datalist>
      </div>
      <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?=e($editing['email']??'')?>"></div>
      <div class="col-md-8"><label class="form-label">Bio / Message</label><textarea name="bio" class="form-control" rows="4" placeholder="For Leadership, this is shown as the welcome message quote."><?=e($editing['bio']??'')?></textarea></div>
      <div class="col-md-4">
        <label class="form-label">Photo</label>
        <div class="img-upload-wrap" onclick="document.getElementById('staffPhoto').click()">
          <?php if(!empty($editing['photo'])):?><img src="/<?=e($editing['photo'])?>" alt="Current photo" style="width:100%;height:140px;object-fit:cover;"><?php else:?><div class="img-placeholder"><i class="bi bi-image"></i><span>Upload</span></div><?php endif;?>
          <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:1.5rem;"></i></div>
        </div>
        <input type="file" name="photo" id="staffPhoto" class="img-upload-input" style="display:none;" accept="image/*">
      </div>
      <div class="col-md-6">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="is_featured" id="featChk" <?=!empty($editing['is_featured'])?'checked':''?>>
          <label class="form-check-label" for="featChk">Featured (the one shown for Leadership/Chaplaincy spotlight)</label>
        </div>
      </div>
      <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?=e($editing['sort_order']??0)?>"></div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add Team Member'?></button></div>
    </div>
  </form>
</div>
<?php endif;?>

<?php adminFoot(); ?>
