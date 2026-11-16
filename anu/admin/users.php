<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);
if($action==='delete'&&$editId&&$editId!==$_SESSION['admin_id']){$db->prepare("DELETE FROM admin_users WHERE id=?")->execute([$editId]);$msg='User deleted.';$action='list';}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){
  $un=trim($_POST['username']??'');$em=trim($_POST['email']??'');$fn=trim($_POST['full_name']??'');
  $role=trim($_POST['role']??'editor');$pw=trim($_POST['password']??'');
  if(!$un||!$em){$msg='Username and email required.';$msgType='error';}
  else{
    if($editId){
      $sql="UPDATE admin_users SET username=?,email=?,full_name=?,role=? WHERE id=?";$params=[$un,$em,$fn,$role,$editId];
      if($pw){$sql="UPDATE admin_users SET username=?,email=?,full_name=?,role=?,password=? WHERE id=?";$params=[$un,$em,$fn,$role,password_hash($pw,PASSWORD_DEFAULT),$editId];}
      $db->prepare($sql)->execute($params);$msg='User updated!';
    }else{
      if(!$pw){$msg='Password required for new user.';$msgType='error';}
      else{$db->prepare("INSERT INTO admin_users (username,email,full_name,role,password) VALUES (?,?,?,?,?)")->execute([$un,$em,$fn,$role,password_hash($pw,PASSWORD_DEFAULT)]);$msg='User created!';}
    }
    if($msgType==='success')$action='list';
  }
}
$editing=[];if($action==='edit'&&$editId){$s=$db->prepare("SELECT * FROM admin_users WHERE id=?");$s->execute([$editId]);$editing=$s->fetch()?:[];}
$items=$db->query("SELECT * FROM admin_users ORDER BY id")->fetchAll();
adminHead('Users');adminSidebar('users');
?>
<?php adminTopBar('Admin Users'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>
<?php if($action==='list'):?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-person-circle me-2" style="color:var(--primary);"></i>Admin Users</h5>
    <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add User</a>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Last Login</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($items as $u):?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px;"><div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.9rem;"><?=strtoupper($u['full_name'][0]??'A')?></div><div><strong><?=e($u['username'])?></strong><br><small class="text-muted"><?=e($u['full_name'])?></small></div></div></td>
          <td><?=e($u['email'])?></td>
          <td><span class="badge-status <?=$u['role']==='super_admin'?'badge-new':'badge-active'?>"><?=ucfirst(str_replace('_',' ',$u['role']))?></span></td>
          <td style="font-size:0.82rem;"><?=$u['last_login']?formatDate($u['last_login'],'M d, Y'):'Never'?></td>
          <td><div class="d-flex gap-2">
            <a href="?action=edit&id=<?=$u['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
            <?php if($u['id']!==$_SESSION['admin_id']):?><button onclick="confirmDelete('/anu/admin/users.php?action=delete&id=<?=$u['id']?>','<?=e(addslashes($u['username']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button><?php endif;?>
          </div></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php else:?>
<div style="margin-bottom:20px;"><a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="admin-card" style="max-width:600px;">
  <h5><?=$editId?'Edit User':'Add Admin User'?></h5>
  <form method="POST" class="anu-form">
    <input type="hidden" name="save" value="1"><input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?=e($editing['full_name']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required value="<?=e($editing['username']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?=e($editing['email']??'')?>"></div>
      <div class="col-md-6"><label class="form-label">Role</label>
        <select name="role" class="form-select"><?php foreach(['super_admin','editor','viewer'] as $r):?><option value="<?=$r?>" <?=($editing['role']??'editor')===$r?'selected':''?>><?=ucfirst(str_replace('_',' ',$r))?></option><?php endforeach;?></select></div>
      <div class="col-12"><label class="form-label">Password <?=$editId?'(leave blank to keep current)':'*'?></label><input type="password" name="password" class="form-control" <?=$editId?'':'required'?> placeholder="Min. 8 characters"></div>
      <div class="col-12"><button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 40px;"><i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Create User'?></button></div>
    </div>
  </form>
</div>
<?php endif;?>
</div>

<?php adminFoot(); ?>