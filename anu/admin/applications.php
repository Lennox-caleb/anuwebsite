<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$action=$_GET['action']??'list';$viewId=(int)($_GET['id']??0);
if($action==='delete'&&$viewId){$db->prepare("DELETE FROM applications WHERE id=?")->execute([$viewId]);header('Location: ' . SITE_URL . '/admin/applications.php');exit;}
if(isset($_POST['status'])&&$viewId){$db->prepare("UPDATE applications SET status=? WHERE id=?")->execute([$_POST['status'],$viewId]);}
$item=null;if($viewId){$s=$db->prepare("SELECT * FROM applications WHERE id=?");$s->execute([$viewId]);$item=$s->fetch();}
$items=$db->query("SELECT * FROM applications ORDER BY submitted_at DESC")->fetchAll();
adminHead('Applications');adminSidebar('applications');
?>
<?php adminTopBar('Application Inquiries'); ?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-person-plus-fill me-2" style="color:var(--primary);"></i>All Applications (<?=count($items)?>)</h5>
    <div style="display:flex;gap:8px;">
      <?php foreach(['new','contacted','enrolled','declined'] as $st):$count=$db->prepare("SELECT COUNT(*) FROM applications WHERE status=?");$count->execute([$st]);$count=$count->fetchColumn();?>
      <span style="background:<?=$st==='new'?'rgba(0,120,220,0.1)':($st==='enrolled'?'rgba(25,200,100,0.1)':($st==='declined'?'rgba(255,0,0,0.1)':'rgba(0,0,0,0.07)'));?>;color:<?=$st==='new'?'#0078dc':($st==='enrolled'?'#18a060':($st==='declined'?'var(--primary)':'var(--mid)'));?>;padding:4px 12px;border-radius:50px;font-size:0.75rem;font-weight:700;"><?=ucfirst($st)?>(<?=$count?>)</span>
      <?php endforeach;?>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>Name</th><th>Email/Phone</th><th>Program Interest</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($items as $app):?>
        <tr>
          <td><strong><?=e($app['full_name'])?></strong></td>
          <td><?=e($app['email'])?><br><small class="text-muted"><?=e($app['phone'])?></small></td>
          <td><?=e($app['program_interest']?:'Not specified')?></td>
          <td>
            <form method="POST" action="?id=<?=$app['id']?>" style="display:inline;">
              <select name="status" onchange="this.form.submit()" style="border:1.5px solid rgba(0,0,0,0.1);border-radius:50px;padding:3px 10px;font-size:0.78rem;background:white;">
                <?php foreach(['new','contacted','enrolled','declined'] as $st):?><option value="<?=$st?>" <?=$app['status']===$st?'selected':''?>><?=ucfirst($st)?></option><?php endforeach;?>
              </select>
            </form>
          </td>
          <td style="font-size:0.82rem;"><?=formatDate($app['submitted_at'],'M d, Y')?></td>
          <td><div class="d-flex gap-2">
            <?php if($app['email']):?><a href="mailto:<?=e($app['email'])?>" class="btn btn-sm btn-outline-primary" style="border-radius:50px;font-size:0.78rem;">Reply</a><?php endif;?>
            <button onclick="confirmDelete('/anu/admin/applications.php?action=delete&id=<?=$app['id']?>','application from <?=e(addslashes($app['full_name']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
          </div></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
</div>

<?php adminFoot(); ?>