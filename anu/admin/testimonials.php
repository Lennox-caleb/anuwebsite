<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db  = getDB();
$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// DELETE
if ($action === 'delete' && $editId) {
    $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([$editId]);
    header('Location: testimonials.php?msg=deleted'); exit;
}
// TOGGLE
if ($action === 'toggle' && $editId) {
    $db->prepare("UPDATE testimonials SET is_active=1-is_active WHERE id=?")->execute([$editId]);
    header('Location: testimonials.php'); exit;
}
// SAVE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save'])) {
    $name  = trim($_POST['name']    ?? '');
    $prog  = trim($_POST['program'] ?? '');
    $quote = trim($_POST['quote']   ?? '');
    $rating= min(5, max(1, (int)($_POST['rating'] ?? 5)));
    $feat  = isset($_POST['is_featured']) ? 1 : 0;
    $actv  = isset($_POST['is_active'])   ? 1 : 0;

    if (!$name || !$quote) {
        $msg = 'Name and quote are required.'; $msgType = 'danger';
    } else {
        // handleUpload returns string path or null
        $imgPath = handleUpload('photo', 'testimonials');
        if (!$imgPath && $editId) {
            $c = $db->prepare("SELECT photo FROM testimonials WHERE id=?");
            $c->execute([$editId]); $c = $c->fetch();
            $imgPath = $c ? $c['photo'] : null;
        }
        if ($editId) {
            $db->prepare("UPDATE testimonials SET name=?,program=?,quote=?,rating=?,is_featured=?,is_active=?,photo=? WHERE id=?")
               ->execute([$name,$prog,$quote,$rating,$feat,$actv,$imgPath,$editId]);
        } else {
            $db->prepare("INSERT INTO testimonials (name,program,quote,rating,is_featured,is_active,photo) VALUES (?,?,?,?,?,?,?)")
               ->execute([$name,$prog,$quote,$rating,$feat,$actv,$imgPath]);
        }
        header('Location: testimonials.php?msg=saved'); exit;
    }
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg']==='deleted' ? 'Testimonial deleted.' : 'Testimonial saved successfully!';
    $msgType = 'success';
}

$editing = [];
if (in_array($action,['edit','new']) && $editId) {
    $s = $db->prepare("SELECT * FROM testimonials WHERE id=?");
    $s->execute([$editId]); $editing = $s->fetch() ?: [];
}
$items = $db->query("SELECT * FROM testimonials ORDER BY is_featured DESC, id DESC")->fetchAll();

adminHead('Testimonials');
adminSidebar('testimonials');
adminTopBar('Testimonials', 'Student voices');
?>

<?php if ($msg): ?>
<div class="alert alert-<?=$msgType?> alert-dismissible mb-4">
  <i class="bi bi-<?=$msgType==='success'?'check-circle-fill':'exclamation-circle-fill'?> me-2"></i>
  <?=e($msg)?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ── FORM ── -->
  <div class="col-xl-4 col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><?=$editId ? '<i class="bi bi-pencil me-2"></i>Edit' : '<i class="bi bi-plus-circle me-2"></i>Add'?> Testimonial</h5>
        <?php if ($editId): ?>
        <a href="testimonials.php" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.75rem">Cancel</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="save" value="1">
          <input type="hidden" name="id" value="<?=$editId?>">

          <!-- Photo upload with live preview -->
          <div class="mb-4 text-center">
            <div id="photoWrap" onclick="document.getElementById('photoInput').click()" style="
              width:110px;height:110px;border-radius:50%;overflow:hidden;cursor:pointer;
              margin:0 auto 10px;position:relative;border:3px solid #eee;
              background:linear-gradient(135deg,#ff0000,#fac823);
              display:flex;align-items:center;justify-content:center;">
              <?php if (!empty($editing['photo'])): ?>
              <img id="photoPreview" src="<?=imgUrl($editing['photo'])?>" alt="Testimonial photo preview" style="width:100%;height:100%;object-fit:cover">
              <?php else: ?>
              <img id="photoPreview" src="" alt="Testimonial photo preview" style="width:100%;height:100%;object-fit:cover;display:none">
              <i id="photoIcon" class="bi bi-person-fill" style="font-size:3rem;color:rgba(255,255,255,.7)"></i>
              <?php endif; ?>
              <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.4);padding:5px;text-align:center">
                <i class="bi bi-camera-fill" style="color:#fff;font-size:.85rem"></i>
              </div>
            </div>
            <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none" onchange="previewPhoto(this)">
            <small class="text-muted d-block">Click circle to upload photo</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Full Name <span style="color:#ff0000">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?=e($editing['name']??'')?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Program / Title</label>
            <input type="text" name="program" class="form-control" placeholder="e.g. MBA Graduate, 2024" value="<?=e($editing['program']??'')?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Quote <span style="color:#ff0000">*</span></label>
            <textarea name="quote" class="form-control" rows="4" required><?=e($editing['quote']??'')?></textarea>
          </div>

          <!-- Star picker -->
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <div id="starPicker" style="display:flex;gap:6px;margin-bottom:4px">
              <?php $r=(int)($editing['rating']??5); for($i=1;$i<=5;$i++): ?>
              <i class="bi bi-star<?=$i<=$r?'-fill':''?>" data-val="<?=$i?>"
                 onclick="setRating(<?=$i?>)"
                 style="font-size:1.6rem;color:#fac823;cursor:pointer;transition:.15s"></i>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="<?=$r?>">
          </div>

          <div class="mb-4 d-flex gap-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_featured" id="featChk" <?=($editing['is_featured']??1)?'checked':''?>>
              <label class="form-check-label" for="featChk">
                <i class="bi bi-star-fill me-1" style="color:#fac823"></i>Featured
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" id="activeChk" <?=($editing['is_active']??1)?'checked':''?>>
              <label class="form-check-label" for="activeChk">Active</label>
            </div>
          </div>

          <button type="submit" class="btn w-100" style="padding:11px;background:#ff0000;color:#fff;border-color:#ff0000;border-radius:10px;font-weight:700">
            <i class="bi bi-<?=$editId?'check-circle':'plus-circle'?> me-2"></i>
            <?=$editId?'Update Testimonial':'Add Testimonial'?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── LIST ── -->
  <div class="col-xl-8 col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="bi bi-chat-quote-fill me-2" style="color:#ff0000"></i>All Testimonials (<?=count($items)?>)</h5>
        <a href="testimonials.php?action=new&id=0" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.75rem">
          <i class="bi bi-plus me-1"></i>Add New
        </a>
      </div>
      <div class="admin-card-body">
        <?php if (empty($items)): ?>
        <div class="text-center py-5" style="color:#ccc">
          <i class="bi bi-chat-quote" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3"></i>
          No testimonials yet. Add your first one using the form.
        </div>
        <?php else: ?>
        <div class="row g-3">
          <?php foreach ($items as $t): ?>
          <div class="col-sm-6">
            <div style="border:1.5px solid <?=$t['is_active']?'#eaeaee':'#ffd0d0'?>;border-radius:14px;padding:18px;background:<?=$t['is_active']?'#fff':'#fff8f8'?>;position:relative">
              <?php if ($t['is_featured']): ?>
              <span style="position:absolute;top:12px;right:12px;background:#fff8e1;color:#c49000;font-size:.62rem;font-weight:800;padding:2px 8px;border-radius:50px">FEATURED</span>
              <?php endif; ?>
              <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px">
                <?php if (!empty($t['photo'])): ?>
                <img src="<?=imgUrl($t['photo'])?>" alt="<?=e($t['name'] ?? 'Testimonial photo')?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #fac823;flex-shrink:0" onerror="this.style.display='none'">
                <?php else: ?>
                <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff0000,#fac823);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;flex-shrink:0">
                  <?=strtoupper($t['name'][0])?>
                </div>
                <?php endif; ?>
                <div style="min-width:0">
                  <div style="font-weight:700;font-size:.9rem;color:#1e1e2e"><?=e($t['name'])?></div>
                  <div style="font-size:.76rem;color:#ff0000;font-weight:600;margin-top:1px"><?=e($t['program'])?></div>
                  <div style="color:#fac823;font-size:.78rem;margin-top:2px;letter-spacing:1px">
                    <?=str_repeat('★',(int)$t['rating']).str_repeat('☆',5-(int)$t['rating'])?>
                  </div>
                </div>
              </div>
              <p style="font-style:italic;font-size:.83rem;color:#555;line-height:1.65;margin-bottom:14px">
                "<?=e(mb_strimwidth($t['quote'],0,110,'…'))?>"
              </p>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="testimonials.php?action=edit&id=<?=$t['id']?>" class="btn btn-sm btn-outline-primary" style="border-radius:50px;font-size:.76rem"><i class="bi bi-pencil me-1"></i>Edit</a>
                <a href="testimonials.php?action=toggle&id=<?=$t['id']?>" class="btn btn-sm <?=$t['is_active']?'btn-outline-success':'btn-outline-warning'?>" style="border-radius:50px;font-size:.76rem">
                  <i class="bi bi-<?=$t['is_active']?'eye':'eye-slash'?> me-1"></i><?=$t['is_active']?'Active':'Off'?>
                </a>
                <a href="testimonials.php?action=delete&id=<?=$t['id']?>" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.76rem" onclick="return confirm('Delete this testimonial?')">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('photoPreview');
            var ico = document.getElementById('photoIcon');
            img.src = e.target.result;
            img.style.display = 'block';
            if (ico) ico.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('#starPicker i').forEach(function(s, i) {
        s.className = 'bi bi-star' + (i < val ? '-fill' : '');
    });
}
</script>

<?php adminFoot(); ?>
