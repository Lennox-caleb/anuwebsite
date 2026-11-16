<?php
// Item 9b (phase upgrade): admin management for the "Recognised &
// Accredited By" band. Table created by migrate_phase_upgrade.php,
// seeded with the same 4 real accreditation bodies already on the
// homepage so nothing is lost switching from hardcoded to admin-managed.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);

if($action==='delete'&&$editId){$db->prepare("DELETE FROM accreditations WHERE id=?")->execute([$editId]);$msg='Accreditation removed.';$action='list';}
if($action==='toggle'&&$editId){$db->prepare("UPDATE accreditations SET is_active=1-is_active WHERE id=?")->execute([$editId]);$action='list';}

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $name=trim($_POST['name']??'');$url=trim($_POST['url']??'');$icon=trim($_POST['icon']??'bi-patch-check-fill');$sort=(int)($_POST['sort_order']??0);
  $imgPath=null;if(!empty($_FILES['image']['name']))$imgPath=handleUpload('image','accreditations');
  if(!$name){$msg='Name required.';$msgType='error';}
  else{
    if($editId){
      if(!$imgPath){$c=$db->prepare("SELECT image FROM accreditations WHERE id=?");$c->execute([$editId]);$c=$c->fetch();$imgPath=$c?$c['image']:null;}
      $db->prepare("UPDATE accreditations SET name=?,icon=?,url=?,image=?,sort_order=? WHERE id=?")->execute([$name,$icon,$url,$imgPath,$sort,$editId]);$msg='Accreditation updated!';
    }else{$db->prepare("INSERT INTO accreditations (name,icon,url,image,sort_order) VALUES (?,?,?,?,?)")->execute([$name,$icon,$url,$imgPath,$sort]);$msg='Accreditation added!';}
    $action='list';
  }
}
$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM accreditations WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM accreditations ORDER BY sort_order,name")->fetchAll();
adminHead('Accreditations');adminSidebar('accreditations');
?>
<?php adminTopBar('Recognised & Accredited By', 'Homepage accreditation band'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>
<?php if($action==='list'):?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-patch-check-fill me-2" style="color:var(--primary);"></i>All Accreditations</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add Accreditation</a>
  </div>
  <div class="row g-3">
    <?php foreach($items as $c):?>
    <div class="col-md-6">
      <div style="background:#f8f8f8;border-radius:14px;padding:18px;display:flex;gap:14px;align-items:center;border:1px solid rgba(0,0,0,0.07);">
        <div style="width:48px;height:48px;background:#fff;border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden">
          <?php if(!empty($c['image'])):?><img src="/<?=e($c['image'])?>" alt="" style="width:100%;height:100%;object-fit:contain"><?php else:?><i class="bi <?=e($c['icon']?:'bi-patch-check-fill')?>" style="color:var(--primary);font-size:1.2rem"></i><?php endif;?>
        </div>
        <div style="flex:1;">
          <div style="font-weight:700;"><?=e($c['name'])?></div>
          <span class="badge-status <?=$c['is_active']?'badge-active':'badge-inactive'?>"><?=$c['is_active']?'Active':'Hidden'?></span>
        </div>
        <div class="d-flex flex-column gap-2">
          <a href="?action=edit&id=<?=$c['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
          <a href="?action=toggle&id=<?=$c['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;"><?=$c['is_active']?'Hide':'Show'?></a>
          <button onclick="confirmDelete('/anu/admin/accreditations.php?action=delete&id=<?=$c['id']?>','<?=e(addslashes($c['name']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(!$items):?><div class="admin-empty"><i class="bi bi-patch-check"></i><p>No accreditations yet — add the first one.</p></div><?php endif;?>
  </div>
</div>
<?php else:?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card">
  <h5><?=$editId?'Edit Accreditation':'Add New Accreditation'?></h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-md-7"><label class="form-label">Body Name *</label><input type="text" name="name" class="form-control" required value="<?=e($editing['name']??'')?>" placeholder="e.g. Commission for University Education (CUE)"></div>
      <div class="col-md-3"><label class="form-label">Fallback Icon</label><input type="text" name="icon" class="form-control" placeholder="bi-patch-check-fill" value="<?=e($editing['icon']??'bi-patch-check-fill')?>"></div>
      <div class="col-md-2"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?=e($editing['sort_order']??0)?>"></div>
      <div class="col-md-6"><label class="form-label">Link (optional)</label><input type="url" name="url" class="form-control" value="<?=e($editing['url']??'')?>" placeholder="https://..."></div>
      <div class="col-md-4">
        <label class="form-label">Logo Image (optional — falls back to the icon above if not set)</label>
        <div class="img-upload-wrap" onclick="document.getElementById('accImg').click()">
          <?php if(!empty($editing['image'])):?><img src="/<?=e($editing['image'])?>" alt="Current logo" style="width:100%;height:120px;object-fit:contain;background:#fff"><?php else:?><div class="img-placeholder"><i class="bi bi-image"></i><span>Upload</span></div><?php endif;?>
          <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:1.5rem;"></i></div>
        </div>
        <input type="file" name="image" id="accImg" class="img-upload-input" style="display:none;" accept="image/*">
      </div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add Accreditation'?></button></div>
    </div>
  </form>
</div>
<?php endif;?>

<?php adminFoot(); ?>
