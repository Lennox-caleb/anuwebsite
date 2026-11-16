<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$action=$_GET['action']??'list';$viewId=(int)($_GET['id']??0);
if($action==='delete'&&$viewId){$db->prepare("DELETE FROM prayer_requests WHERE id=?")->execute([$viewId]);header('Location: ' . SITE_URL . '/admin/prayers.php');exit;}
if($action==='prayed'&&$viewId){$db->prepare("UPDATE prayer_requests SET status='prayed' WHERE id=?")->execute([$viewId]);header('Location: ' . SITE_URL . '/admin/prayers.php');exit;}
$items=$db->query("SELECT * FROM prayer_requests ORDER BY submitted_at DESC")->fetchAll();
adminHead('Prayer Requests');adminSidebar('prayers');
?>
<?php adminTopBar('Prayer Requests'); ?>
<div class="admin-card">
  <h5 style="margin-bottom:20px;"><i class="bi bi-heart-fill me-2" style="color:var(--primary);"></i>Prayer Requests (<?=count($items)?>)</h5>
  <div class="row g-3">
    <?php foreach($items as $p):?>
    <div class="col-md-6">
      <div style="background:#f8f8f8;border-radius:14px;padding:20px;border:1px solid rgba(0,0,0,0.07);<?=$p['status']==='new'?'border-left:4px solid var(--primary);':''?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
          <div>
            <div style="font-weight:700;font-size:0.9rem;"><?=$p['name']?e($p['name']):'<em style="color:#aaa;">Anonymous</em>'?></div>
            <?php if($p['email']):?><div style="font-size:0.78rem;color:#888;"><?=e($p['email'])?></div><?php endif;?>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
            <span class="badge-status <?=$p['status']==='new'?'badge-new':'badge-active'?>"><?=e($p['status'])?></span>
            <span style="font-size:0.72rem;color:#aaa;"><?=date('M d, Y',strtotime($p['submitted_at']))?></span>
          </div>
        </div>
        <p style="font-size:0.9rem;color:#444;line-height:1.7;font-style:italic;">"<?=e($p['request'])?>"</p>
        <div class="d-flex gap-2 mt-3">
          <?php if($p['status']==='new'):?>
          <a href="?action=prayed&id=<?=$p['id']?>" class="btn btn-sm btn-primary-anu" style="border-radius:50px;font-size:0.78rem;"><i class="bi bi-check-circle me-1"></i>Mark Prayed</a>
          <?php endif;?>
          <?php if($p['email']):?><a href="mailto:<?=e($p['email'])?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;"><i class="bi bi-envelope me-1"></i>Reply</a><?php endif;?>
          <button onclick="confirmDelete('/anu/admin/prayers.php?action=delete&id=<?=$p['id']?>','this prayer request')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(empty($items)):?><div class="text-center py-5 col-12"><i class="bi bi-heart" style="font-size:3rem;color:#ddd;"></i><p class="mt-3 text-muted">No prayer requests yet.</p></div><?php endif;?>
  </div>
</div>
</div>

<?php adminFoot(); ?>