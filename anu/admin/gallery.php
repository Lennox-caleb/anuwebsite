<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db=getDB();$msg='';$msgType='success';$action=$_GET['action']??'list';$editId=(int)($_GET['id']??0);
if($action==='delete'&&$editId){$db->prepare("DELETE FROM gallery WHERE id=?")->execute([$editId]);$msg='Image deleted.';$action='list';}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['upload_gallery'])){
  $title=trim($_POST['title']??'');$cat=trim($_POST['category']??'Campus');$page=trim($_POST['page']??'campus-life');$sort=(int)($_POST['sort_order']??0);
  $imgPath=handleUpload('image','gallery');
  if(!$imgPath){$msg='Image upload failed.';$msgType='error';}
  else{$db->prepare("INSERT INTO gallery (title,image,category,page,sort_order) VALUES (?,?,?,?,?)")->execute([$title,$imgPath,$cat,$page,$sort]);$msg='Image uploaded!';}
}
$items=$db->query("SELECT * FROM gallery ORDER BY page,sort_order,id DESC")->fetchAll();
adminHead('Gallery');adminSidebar('gallery');
?>
<?php adminTopBar('Photo Gallery'); ?>
<?php if($msg):?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif;?>
<!-- Upload Form -->
<div class="admin-card mb-4">
  <h5><i class="bi bi-cloud-upload-fill me-2" style="color:var(--primary);"></i>Upload New Image</h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="upload_gallery" value="1">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <div class="img-upload-wrap" style="height:140px;" onclick="document.getElementById('galImg').click()">
          <div class="img-placeholder"><i class="bi bi-image" style="font-size:2rem;"></i><span>Click to upload</span></div>
          <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:2rem;"></i></div>
        </div>
        <input type="file" name="image" id="galImg" class="img-upload-input" style="display:none;" accept="image/*" required>
      </div>
      <div class="col-md-8">
        <div class="row g-2">
          <div class="col-12"><label class="form-label">Image Title</label><input type="text" name="title" class="form-control" placeholder="e.g., Virtual Orientation 2025"></div>
          <div class="col-md-4"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="Campus" placeholder="Campus, Events..."></div>
          <div class="col-md-4"><label class="form-label">Show on Page</label>
            <select name="page" class="form-select"><option value="campus-life">Campus Life</option><option value="home">Home</option><option value="about">About</option></select></div>
          <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
        </div>
        <button type="submit" class="btn btn-primary-anu mt-3" style="border-radius:50px;padding:10px 32px;"><i class="bi bi-upload me-2"></i>Upload Image</button>
      </div>
    </div>
  </form>
</div>
<!-- Gallery Grid -->
<div class="admin-card">
  <h5><i class="bi bi-images me-2"></i>Gallery Images (<?=count($items)?>)</h5>
  <div class="row g-3">
    <?php foreach($items as $img):?>
    <div class="col-6 col-md-3 col-lg-2">
      <div style="position:relative;border-radius:12px;overflow:hidden;aspect-ratio:1;background:#f0f0f0;">
        <img src="/<?=e($img['image'])?>" alt="<?=e($img['title']??'Gallery image')?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='/anu/assets/images/default-news.jpg'">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0);transition:0.2s;display:flex;align-items:center;justify-content:center;"
             onmouseover="this.style.background='rgba(0,0,0,0.6)';this.querySelector('.gal-actions').style.opacity='1'"
             onmouseout="this.style.background='rgba(0,0,0,0)';this.querySelector('.gal-actions').style.opacity='0'">
          <div class="gal-actions" style="opacity:0;transition:0.2s;text-align:center;">
            <div style="color:white;font-size:0.75rem;margin-bottom:8px;"><?=e($img['title'])?></div>
            <button onclick="confirmDelete('/anu/admin/gallery.php?action=delete&id=<?=$img['id']?>','this image')"
              style="background:var(--primary);color:white;border:none;padding:5px 12px;border-radius:50px;font-size:0.75rem;cursor:pointer;">Delete</button>
          </div>
        </div>
      </div>
      <div style="font-size:0.72rem;color:#888;margin-top:4px;text-align:center;"><?=e(substr($img['page'],0,12))?></div>
    </div>
    <?php endforeach;?>
    <?php if(empty($items)):?><div class="col-12 text-center py-5"><i class="bi bi-images" style="font-size:3rem;color:#ddd;"></i><p class="mt-3 text-muted">No images uploaded yet.</p></div><?php endif;?>
  </div>
</div>
</div>

<?php adminFoot(); ?>