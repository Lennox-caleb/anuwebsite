<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);
if($action==='delete'&&$editId){$db->prepare("DELETE FROM clubs WHERE id=?")->execute([$editId]);$msg='Club deleted.';$action='list';}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $name=trim($_POST['name']??'');$icon=trim($_POST['icon']??'bi-people');$desc=trim($_POST['description']??'');
  $leader=trim($_POST['leader_name']??'');$email=trim($_POST['leader_email']??'');$sort=(int)($_POST['sort_order']??0);
  $imgPath=null;if(!empty($_FILES['image']['name']))$imgPath=handleUpload('image','clubs');
  if(!$name){$msg='Name required.';$msgType='error';}
  else{
    if($editId){
      if(!$imgPath){$c=$db->prepare("SELECT image FROM clubs WHERE id=?");$c->execute([$editId]);$c=$c->fetch();$imgPath=$c?$c['image']:null;}
      $db->prepare("UPDATE clubs SET name=?,icon=?,description=?,leader_name=?,leader_email=?,image=?,sort_order=? WHERE id=?")->execute([$name,$icon,$desc,$leader,$email,$imgPath,$sort,$editId]);$msg='Club updated!';
    }else{$db->prepare("INSERT INTO clubs (name,icon,description,leader_name,leader_email,image,sort_order) VALUES (?,?,?,?,?,?,?)")->execute([$name,$icon,$desc,$leader,$email,$imgPath,$sort]);$msg='Club added!';}
    $action='list';
  }
}
$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM clubs WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM clubs ORDER BY sort_order,name")->fetchAll();
adminHead('Clubs');adminSidebar('clubs');
?>
<?php adminTopBar('Clubs & Societies'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>
<?php if($action==='list'):?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-people-fill me-2" style="color:var(--primary);"></i>All Clubs</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add Club</a>
  </div>
  <div class="row g-3">
    <?php foreach($items as $c):?>
    <div class="col-md-6">
      <div style="background:#f8f8f8;border-radius:14px;padding:20px;display:flex;gap:16px;align-items:flex-start;border:1px solid rgba(0,0,0,0.07);">
        <div style="width:52px;height:52px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:14px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.3rem;flex-shrink:0;"><i class="bi <?=e($c['icon'])?>"></i></div>
        <div style="flex:1;">
          <div style="font-weight:700;"><?=e($c['name'])?></div>
          <div style="font-size:0.82rem;color:#888;margin:4px 0;"><?=e(substr($c['description'],0,80))?><?=strlen($c['description'])>80?'...':''?></div>
          <?php if($c['leader_name']):?><div style="font-size:0.78rem;color:var(--mid);">Leader: <?=e($c['leader_name'])?></div><?php endif;?>
        </div>
        <div class="d-flex flex-column gap-2">
          <a href="?action=edit&id=<?=$c['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;white-space:nowrap;">Edit</a>
          <button onclick="confirmDelete('/anu/admin/clubs.php?action=delete&id=<?=$c['id']?>','<?=e(addslashes($c['name']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
        </div>
      </div>
    </div>
    <?php endforeach;?>
  </div>
</div>
<?php else:?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card">
  <h5><?=$editId?'Edit Club':'Add New Club'?></h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-md-7"><label class="form-label">Club Name *</label><input type="text" name="name" class="form-control" required value="<?=e($editing['name']??'')?>"></div>
      <div class="col-md-3"><label class="form-label">Bootstrap Icon</label><input type="text" name="icon" class="form-control" placeholder="bi-people" value="<?=e($editing['icon']??'bi-people')?>"></div>
      <div class="col-md-2"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?=e($editing['sort_order']??0)?>"></div>
      <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?=e($editing['description']??'')?></textarea></div>
      <div class="col-md-6"><label class="form-label">Leader Name</label><input type="text" name="leader_name" class="form-control" value="<?=e($editing['leader_name']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Leader Email</label><input type="email" name="leader_email" class="form-control" value="<?=e($editing['leader_email']??'')?>"></div>
      <div class="col-md-4">
        <label class="form-label">Club Image</label>
        <div class="img-upload-wrap" onclick="document.getElementById('clubImg').click()">
          <?php if(!empty($editing['image'])):?><img src="/<?=e($editing['image'])?>" alt="Current club image" style="width:100%;height:160px;object-fit:cover;"><?php else:?><div class="img-placeholder"><i class="bi bi-image"></i><span>Upload</span></div><?php endif;?>
          <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:1.5rem;"></i></div>
        </div>
        <input type="file" name="image" id="clubImg" class="img-upload-input" style="display:none;" accept="image/*">
      </div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add Club'?></button></div>
    </div>
  </form>
</div>
<?php endif;?>
</div>

<?php adminFoot(); ?>