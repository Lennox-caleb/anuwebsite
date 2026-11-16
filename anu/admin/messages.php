<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$action=$_GET['action']??'list';$viewId=(int)($_GET['id']??0);
if($action==='delete'&&$viewId){$db->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$viewId]);header('Location: ' . SITE_URL . '/admin/messages.php');exit;}
if($action==='read'&&$viewId){$db->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$viewId]);}
$item=null;
if($viewId){$s=$db->prepare("SELECT * FROM contact_messages WHERE id=?");$s->execute([$viewId]);$item=$s->fetch();
  if($item&&$item['status']==='new')$db->prepare("UPDATE contact_messages SET status='read' WHERE id=?")->execute([$viewId]);}
$items=$db->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC")->fetchAll();
adminHead('Messages');adminSidebar('messages');
?>
<?php adminTopBar('Contact Messages'); ?>
<div class="row g-4">
  <!-- List -->
  <div class="col-lg-<?=$item?'4':'12'?>">
    <div class="admin-card" style="padding:0;overflow:hidden;">
      <div style="padding:18px 20px;border-bottom:1px solid rgba(0,0,0,0.07);display:flex;justify-content:space-between;align-items:center;">
        <h5 style="margin:0;font-size:1rem;"><i class="bi bi-envelope-fill me-2" style="color:var(--primary);"></i>Messages (<?=count($items)?>)</h5>
        <select onchange="filterMsgs(this.value)" style="border:1.5px solid rgba(0,0,0,0.1);border-radius:50px;padding:4px 14px;font-size:0.8rem;">
          <option value="">All</option><option value="new">New</option><option value="read">Read</option><option value="replied">Replied</option>
        </select>
      </div>
      <?php foreach($items as $m):?>
      <a href="?id=<?=$m['id']?>" style="display:block;padding:14px 20px;border-bottom:1px solid rgba(0,0,0,0.05);text-decoration:none;color:inherit;transition:0.2s;background:<?=$m['id']==$viewId?'rgba(255,0,0,0.03)':($m['status']==='new'?'rgba(250,200,35,0.06)':'white')?>;<?=$m['status']==='new'?'font-weight:600;':''?>"
         onmouseover="this.style.background='rgba(255,0,0,0.03)'" onmouseout="this.style.background='<?=$m['id']==$viewId?'rgba(255,0,0,0.03)':($m['status']==='new'?'rgba(250,200,35,0.06)':'white')?>'">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div>
            <div style="font-size:0.88rem;"><?=e($m['name'])?> <?=$m['status']==='new'?'<span style="background:var(--primary);color:white;font-size:0.65rem;padding:1px 7px;border-radius:50px;vertical-align:middle;margin-left:4px;">NEW</span>':''?></div>
            <div style="font-size:0.8rem;color:#888;margin-top:2px;"><?=e(substr($m['subject'],0,45))?></div>
          </div>
          <div style="font-size:0.72rem;color:#aaa;white-space:nowrap;"><?=date('M d',strtotime($m['submitted_at']))?></div>
        </div>
      </a>
      <?php endforeach;?>
    </div>
  </div>
  
  <?php if($item):?>
  <!-- Detail -->
  <div class="col-lg-8">
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
        <div>
          <h5 style="margin:0;"><?=e($item['subject'])?></h5>
          <div style="color:#888;font-size:0.85rem;margin-top:4px;">
            From: <strong><?=e($item['name'])?></strong> &lt;<?=e($item['email'])?>&gt; · <?=date('M d, Y g:ia',strtotime($item['submitted_at']))?>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="mailto:<?=e($item['email'])?>" class="btn btn-sm btn-primary-anu" style="border-radius:50px;font-size:0.8rem;">Reply</a>
          <button onclick="confirmDelete('/anu/admin/messages.php?action=delete&id=<?=$item['id']?>','this message')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.8rem;">Delete</button>
        </div>
      </div>
      <div style="background:#f8f8f8;border-radius:12px;padding:24px;line-height:1.8;color:#444;white-space:pre-wrap;font-size:0.95rem;"><?=e($item['message'])?></div>
      <?php if($item['file_path']):?>
      <div style="margin-top:16px;padding:12px;background:rgba(250,200,35,0.08);border-radius:10px;border:1px solid rgba(250,200,35,0.2);">
        <i class="bi bi-paperclip me-2" style="color:var(--accent-dk);"></i>
        <a href="/<?=e($item['file_path'])?>" target="_blank" style="color:var(--accent-dk);">View Attachment</a>
      </div>
      <?php endif;?>
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(0,0,0,0.07);">
        <label style="font-size:0.82rem;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:0.5px;">Mark as:</label>
        <div class="d-flex gap-2 mt-2">
          <?php foreach(['new','read','replied'] as $st):?>
          <a href="?id=<?=$item['id']?>&status=<?=$st?>" onclick="fetch('/anu/api/update-status?table=contact_messages&id=<?=$item['id']?>&status=<?=$st?>&csrf=<?=urlencode(csrfToken())?>');return false;"
             class="btn btn-sm" style="border-radius:50px;font-size:0.78rem;border:1.5px solid <?=$item['status']===$st?'var(--primary)':'rgba(0,0,0,0.1)';?>;color:<?=$item['status']===$st?'var(--primary)':'inherit';?>;"><?=ucfirst($st)?></a>
          <?php endforeach;?>
        </div>
      </div>
    </div>
  </div>
  <?php endif;?>
</div>
</div>
<script>
function filterMsgs(status) {
  document.querySelectorAll('[href*="?id="]').forEach(el => {
    if(!status) { el.style.display='block'; return; }
    const badge = el.querySelector('span');
    if(status==='new'&&badge) el.style.display='block';
    else if(status==='new'&&!badge) el.style.display='none';
    else el.style.display = 'block';
  });
}
</script>

<?php adminFoot(); ?>