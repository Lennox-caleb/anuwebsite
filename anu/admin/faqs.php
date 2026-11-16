<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);
if($action==='delete'&&$editId){$db->prepare("DELETE FROM faqs WHERE id=?")->execute([$editId]);$msg='FAQ deleted.';$action='list';}
if($action==='toggle'&&$editId){$db->prepare("UPDATE faqs SET is_active=NOT is_active WHERE id=?")->execute([$editId]);header('Location: ' . SITE_URL . '/admin/faqs.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $q=trim($_POST['question']??'');$a=trim($_POST['answer']??'');$cat=trim($_POST['category']??'General');
  $page=trim($_POST['page']??'general');$sort=(int)($_POST['sort_order']??0);
  if(!$q||!$a){$msg='Question and answer required.';$msgType='error';}
  else{
    if($editId){$db->prepare("UPDATE faqs SET question=?,answer=?,category=?,page=?,sort_order=? WHERE id=?")->execute([$q,$a,$cat,$page,$sort,$editId]);$msg='FAQ updated!';}
    else{$db->prepare("INSERT INTO faqs (question,answer,category,page,sort_order) VALUES (?,?,?,?,?)")->execute([$q,$a,$cat,$page,$sort]);$msg='FAQ added!';}
    $action='list';
  }
}
$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM faqs WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM faqs ORDER BY page,sort_order,id")->fetchAll();
adminHead('FAQs');adminSidebar('faqs');
?>
<?php adminTopBar('FAQs'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>
<?php if($action==='list'):?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-question-circle-fill me-2" style="color:var(--primary);"></i>FAQs (<?=count($items)?>)</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add FAQ</a>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>#</th><th>Question</th><th>Category</th><th>Page</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($items as $i=>$f):?>
        <tr>
          <td style="color:#aaa;font-size:0.82rem;"><?=$f['sort_order']?></td>
          <td><?=e(substr($f['question'],0,70))?><?=strlen($f['question'])>70?'...':''?></td>
          <td><span class="badge-status badge-new"><?=e($f['category'])?></span></td>
          <td><?=e($f['page'])?></td>
          <td><a href="?action=toggle&id=<?=$f['id']?>"><span class="badge-status <?=$f['is_active']?'badge-active':'badge-inactive'?>"><?=$f['is_active']?'Visible':'Hidden'?></span></a></td>
          <td><div class="d-flex gap-2">
            <a href="?action=edit&id=<?=$f['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
            <button onclick="confirmDelete('/anu/admin/faqs.php?action=delete&id=<?=$f['id']?>','this FAQ')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
          </div></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php else:?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card">
  <h5><?=$editId?'Edit FAQ':'Add New FAQ'?></h5>
  <form method="POST" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-12"><label class="form-label">Question *</label><input type="text" name="question" class="form-control" required value="<?=e($editing['question']??'')?>"></div>
      <div class="col-12"><label class="form-label">Answer *</label><textarea name="answer" class="form-control" rows="5" required><?=e($editing['answer']??'')?></textarea></div>
      <div class="col-md-4"><label class="form-label">Category</label>
        <select name="category" class="form-select"><?php foreach(['General','Admissions','Programs','Fees','Technical','Academic','Faith'] as $c):?><option <?=($editing['category']??'')===$c?'selected':''?>><?=$c?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Show on Page</label>
        <select name="page" class="form-select"><?php foreach(['general','admissions','programs','faq'] as $pg):?><option value="<?=$pg?>" <?=($editing['page']??'general')===$pg?'selected':''?>><?=ucfirst($pg)?></option><?php endforeach;?></select></div>
      <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?=e($editing['sort_order']??0)?>"></div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add FAQ'?></button></div>
    </div>
  </form>
</div>
<?php endif;?>
</div>

<?php adminFoot(); ?>