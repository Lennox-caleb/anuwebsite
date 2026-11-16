<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();
$msg = ''; $msgType = 'success';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// Make sure table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `support_resources` (
        `id` int AUTO_INCREMENT PRIMARY KEY,
        `category` varchar(100) NOT NULL DEFAULT 'General',
        `type` enum('video','pdf','link','article') DEFAULT 'video',
        `title` varchar(255) NOT NULL,
        `description` text,
        `thumbnail` varchar(500) DEFAULT NULL,
        `url` varchar(1000) NOT NULL,
        `duration` varchar(30) DEFAULT NULL,
        `audience` enum('all','students','staff','new') DEFAULT 'all',
        `sort_order` int DEFAULT 0,
        `views` int DEFAULT 0,
        `is_featured` tinyint(1) DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(Exception $e) {}

// DELETE
if ($action === 'delete' && $editId) {
    $db->prepare("DELETE FROM support_resources WHERE id=?")->execute([$editId]);
    $msg = 'Resource deleted.'; $action = 'list';
}
// TOGGLE active
if ($action === 'toggle' && $editId) {
    $db->prepare("UPDATE support_resources SET is_active=NOT is_active WHERE id=?")->execute([$editId]);
    header('Location: ' . SITE_URL . '/admin/support_resources.php'); exit;
}
// TOGGLE featured
if ($action === 'feature' && $editId) {
    $db->prepare("UPDATE support_resources SET is_featured=NOT is_featured WHERE id=?")->execute([$editId]);
    header('Location: ' . SITE_URL . '/admin/support_resources.php'); exit;
}

// SAVE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_res'])) {
    $id       = (int)($_POST['id']          ?? 0);
    $category = trim($_POST['category']     ?? 'General');
    $type     = trim($_POST['type']         ?? 'video');
    $title    = trim($_POST['title']        ?? '');
    $desc     = trim($_POST['description']  ?? '');
    $url      = trim($_POST['url']          ?? '');
    $duration = trim($_POST['duration']     ?? '');
    $audience = trim($_POST['audience']     ?? 'all');
    $sort     = (int)($_POST['sort_order']  ?? 0);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $active   = isset($_POST['is_active'])   ? 1 : 0;

    // Optional thumbnail upload
    $thumb = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumb = handleUpload('thumbnail', 'resources');
    }

    if (!$title || !$url) {
        $msg = 'Title and URL are required.'; $msgType = 'error';
    } else {
        if ($id) {
            if ($thumb) {
                $db->prepare("UPDATE support_resources SET category=?,type=?,title=?,description=?,url=?,thumbnail=?,duration=?,audience=?,sort_order=?,is_featured=?,is_active=? WHERE id=?")
                   ->execute([$category,$type,$title,$desc,$url,$thumb,$duration,$audience,$sort,$featured,$active,$id]);
            } else {
                $db->prepare("UPDATE support_resources SET category=?,type=?,title=?,description=?,url=?,duration=?,audience=?,sort_order=?,is_featured=?,is_active=? WHERE id=?")
                   ->execute([$category,$type,$title,$desc,$url,$duration,$audience,$sort,$featured,$active,$id]);
            }
            $msg = 'Resource updated!';
        } else {
            $db->prepare("INSERT INTO support_resources (category,type,title,description,url,thumbnail,duration,audience,sort_order,is_featured,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$category,$type,$title,$desc,$url,$thumb,$duration,$audience,$sort,$featured,$active]);
            $msg = 'Resource added!';
        }
        $action = 'list';
    }
}

// Fetch for list
$resources = $db->query("SELECT * FROM support_resources ORDER BY sort_order, category, id")->fetchAll();
$cats = [];
foreach ($resources as $r) { if($r['category']) $cats[$r['category']] = true; }
$cats = array_keys($cats);

// For edit
$editing = [];
if ($action === 'edit' && $editId) {
    $s = $db->prepare("SELECT * FROM support_resources WHERE id=?");
    $s->execute([$editId]); $editing = $s->fetch() ?: [];
}

// YouTube thumbnail extractor helper
function adminYtThumb(string $url): string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/', $url, $m)) {
        return 'https://img.youtube.com/vi/'.$m[1].'/hqdefault.jpg';
    }
    return '';
}

$typeIcons  = ['video'=>'bi-play-circle-fill','pdf'=>'bi-file-pdf-fill','link'=>'bi-link-45deg','article'=>'bi-newspaper'];
$typeColors = ['video'=>'#c1121f','pdf'=>'#1565C0','link'=>'#2e7d32','article'=>'#6d4c41'];
$audienceLabels = ['all'=>'Everyone','students'=>'Students','staff'=>'Staff','new'=>'New Students'];

adminHead('Support Resources'); adminSidebar('support_resources');
?>
<?php adminTopBar('Support Resources', 'Manage tutorials, guides, videos and PDFs for students'); ?>
<?php if($msg): ?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif; ?>

<?php if(in_array($action,['edit','new'])): ?>
<!-- ══ ADD / EDIT FORM ══ -->
<div class="mb-3">
  <a href="?action=list" style="color:var(--primary);font-weight:600;font-size:.88rem">
    <i class="bi bi-arrow-left me-1"></i>Back to Resources
  </a>
</div>

<div class="admin-card">
  <div style="padding:24px">
  <h5 style="margin-bottom:24px">
    <i class="bi bi-<?=$editId?'pencil-fill':'plus-circle-fill'?> me-2" style="color:var(--primary)"></i>
    <?=$editId?'Edit Resource':'Add New Resource'?>
  </h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form" id="resForm">
    <input type="hidden" name="save_res" value="1">
    <input type="hidden" name="id" value="<?=$editId?>">
    <div class="row g-4">

      <!-- Left column -->
      <div class="col-lg-8">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Resource Type <span style="color:var(--red)">*</span></label>
            <select name="type" class="form-select" id="resType" onchange="onTypeChange(this.value)">
              <?php foreach(['video'=>'🎬 Video','pdf'=>'📄 PDF Document','link'=>'🔗 External Link','article'=>'📰 Article / Guide'] as $v=>$l): ?>
              <option value="<?=$v?>" <?=($editing['type']??'video')===$v?'selected':''?>><?=$l?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Category <span style="color:var(--red)">*</span></label>
            <input type="text" name="category" class="form-control" list="catSuggestions"
                   value="<?=e($editing['category']??'Getting Started')?>" placeholder="e.g. Getting Started">
            <datalist id="catSuggestions">
              <?php foreach(['Getting Started','Academic','Finance','Technical','Library','Career','Wellness'] as $c): ?>
              <option value="<?=$c?>">
              <?php endforeach; ?>
            </datalist>
          </div>
          <div class="col-12">
            <label class="form-label">Title <span style="color:var(--red)">*</span></label>
            <input type="text" name="title" class="form-control" required
                   value="<?=e($editing['title']??'')?>" placeholder="e.g. How to Log Into eNaz">
          </div>
          <div class="col-12">
            <label class="form-label" id="urlLabel">URL / Link <span style="color:var(--red)">*</span></label>
            <div style="display:flex;gap:8px">
              <input type="url" name="url" class="form-control" required id="resUrl"
                     value="<?=e($editing['url']??'')?>" placeholder="https://..."
                     oninput="previewYt(this.value)">
              <button type="button" onclick="previewYt(document.getElementById('resUrl').value)"
                      style="flex-shrink:0;background:var(--primary);color:#fff;border:none;border-radius:8px;padding:0 16px;font-size:.8rem;font-weight:700;cursor:pointer">
                Preview
              </button>
            </div>
            <small class="text-muted" id="urlHint">Paste a YouTube URL to auto-generate thumbnail preview</small>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Brief description of what this resource covers..."><?=e($editing['description']??'')?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Duration / Length</label>
            <input type="text" name="duration" class="form-control"
                   value="<?=e($editing['duration']??'')?>" placeholder="e.g. 4:32 or 8 pages">
          </div>
          <div class="col-md-4">
            <label class="form-label">Target Audience</label>
            <select name="audience" class="form-select">
              <?php foreach($audienceLabels as $v=>$l): ?>
              <option value="<?=$v?>" <?=($editing['audience']??'all')===$v?'selected':''?>><?=$l?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" min="0"
                   value="<?=e($editing['sort_order']??0)?>">
          </div>
          <div class="col-12 d-flex gap-4">
            <div class="form-check">
              <input type="checkbox" name="is_featured" class="form-check-input" id="featChk"
                     <?=($editing['is_featured']??0)?'checked':''?>>
              <label class="form-check-label" for="featChk">
                <i class="bi bi-star-fill me-1" style="color:var(--accent)"></i>Featured Resource
              </label>
            </div>
            <div class="form-check">
              <input type="checkbox" name="is_active" class="form-check-input" id="activeChk"
                     <?=($editing['is_active']??1)?'checked':''?>>
              <label class="form-check-label" for="activeChk">Active (visible to students)</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column — thumbnail -->
      <div class="col-lg-4">
        <label class="form-label">Thumbnail Image</label>
        <!-- YouTube preview auto-loads here -->
        <div id="ytThumbPreview" style="border-radius:12px;overflow:hidden;aspect-ratio:16/9;background:#000;margin-bottom:12px;display:<?=!empty($editing['thumbnail'])||(!empty($editing['url'])&&str_contains($editing['url']??'','youtube'))?'block':'none'?>">
          <?php
          $existingThumb = '';
          if(!empty($editing['thumbnail'])) $existingThumb = imgUrl($editing['thumbnail']);
          elseif(!empty($editing['url'])) $existingThumb = adminYtThumb($editing['url']);
          ?>
          <img id="ytThumbImg" src="<?=e($existingThumb)?>" alt="YouTube thumbnail preview" style="width:100%;height:100%;object-fit:cover;display:block" onerror="this.closest('div').style.display='none'">
        </div>
        <p style="font-size:.75rem;color:var(--muted);margin-bottom:8px">
          For videos: auto-generated from YouTube. Upload a custom thumbnail to override.
        </p>
        <!-- Custom thumbnail upload -->
        <div class="prog-img-upload" style="max-width:100%;height:140px" onclick="document.getElementById('thumbInput').click()">
          <div class="prog-img-placeholder" id="thumbPlaceholder">
            <i class="bi bi-image"></i>
            <span>Upload thumbnail</span>
            <small>JPG, PNG, WEBP</small>
          </div>
          <img id="thumbPreviewCustom" alt="Custom thumbnail preview" style="display:none;width:100%;height:100%;object-fit:cover">
          <div class="prog-img-overlay"><i class="bi bi-camera"></i> Change</div>
        </div>
        <input type="file" name="thumbnail" id="thumbInput" accept="image/*" style="display:none">

        <!-- Live preview card -->
        <div style="margin-top:20px;border:1.5px solid rgba(0,0,0,.07);border-radius:14px;overflow:hidden;background:#fff">
          <div style="padding:10px 14px;background:var(--primary);font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#fff">
            Preview — Student View
          </div>
          <div style="aspect-ratio:16/9;background:linear-gradient(135deg,#0d0000,#3a0000);position:relative;overflow:hidden">
            <img id="prevThumb" src="<?=e($existingThumb)?>" alt="Resource thumbnail preview" style="width:100%;height:100%;object-fit:cover;opacity:.7" onerror="this.style.display='none'">
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
              <i class="bi bi-play-circle-fill" style="font-size:2.5rem;color:#fff;opacity:.9"></i>
            </div>
          </div>
          <div style="padding:12px 14px">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--primary);margin-bottom:4px" id="prevCat"><?=e($editing['category']??'Category')?></div>
            <div style="font-size:.82rem;font-weight:700;color:#111;line-height:1.3" id="prevTitle"><?=e($editing['title']??'Resource title here')?></div>
          </div>
        </div>
      </div>

      <div class="col-12 pt-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 36px">
          <i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add Resource'?>
        </button>
        <a href="?action=list" class="btn btn-outline-secondary" style="border-radius:50px;padding:12px 20px">Cancel</a>
      </div>
    </div>
  </form>
  </div><!-- /padding wrapper -->
</div>

<script>
// Auto-preview YouTube thumbnail
function previewYt(url) {
  var match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([A-Za-z0-9_\-]{11})/);
  if (match) {
    var thumb = 'https://img.youtube.com/vi/' + match[1] + '/hqdefault.jpg';
    document.getElementById('ytThumbImg').src = thumb;
    document.getElementById('ytThumbPreview').style.display = 'block';
    document.getElementById('prevThumb').src = thumb;
    document.getElementById('prevThumb').style.display = 'block';
  }
}
// Update live preview on title/category type
document.querySelector('[name="title"]').addEventListener('input', function(){
  document.getElementById('prevTitle').textContent = this.value || 'Resource title here';
});
document.querySelector('[name="category"]').addEventListener('input', function(){
  document.getElementById('prevCat').textContent = this.value || 'Category';
});
// Custom thumbnail preview
document.getElementById('thumbInput').addEventListener('change', function(){
  if(!this.files[0]) return;
  var r = new FileReader();
  r.onload = function(e){
    document.getElementById('thumbPlaceholder').style.display='none';
    var img = document.getElementById('thumbPreviewCustom');
    img.src = e.target.result; img.style.display='block';
    document.getElementById('prevThumb').src = e.target.result;
    document.getElementById('prevThumb').style.display='block';
  };
  r.readAsDataURL(this.files[0]);
});
// Type change hints
function onTypeChange(t) {
  var hints = {
    video: 'Paste a YouTube URL to auto-generate thumbnail preview',
    pdf: 'Paste the full URL to your PDF file or relative path (e.g. /uploads/docs/file.pdf)',
    link: 'Paste any external URL (e.g. HELB portal, library database)',
    article: 'Paste a URL to the article or internal page link'
  };
  document.getElementById('urlHint').textContent = hints[t] || '';
}
</script>

<?php else: ?>
<!-- ══ LIST ══ -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
  <div style="font-size:.85rem;color:var(--muted)"><?=count($resources)?> resources total</div>
  <a href="?action=new" class="btn btn-primary-anu" style="border-radius:50px;padding:10px 26px;font-size:.85rem">
    <i class="bi bi-plus-lg me-1"></i>Add Resource
  </a>
</div>

<?php if(empty($resources)): ?>
<div class="admin-card text-center py-5">
  <i class="bi bi-collection-play" style="font-size:3rem;color:#ccc;display:block;margin-bottom:14px"></i>
  <p style="color:var(--muted)">No resources yet. <a href="?action=new" style="color:var(--primary);font-weight:700">Add the first one.</a></p>
  <p style="font-size:.8rem;color:#aaa;margin-top:8px">Or run <a href="/migrate_support.php" style="color:var(--primary)">migrate_support.php</a> to seed sample data.</p>
</div>
<?php else: ?>

<?php
// Group by category
$grouped = [];
foreach($resources as $r) $grouped[$r['category']][] = $r;
?>
<?php foreach($grouped as $cat => $rows): ?>
<div class="admin-card mb-3" style="padding:0;overflow:hidden">
  <div class="prog-school-hdr">
    <i class="bi bi-folder-fill"></i>
    <span><?=e($cat)?></span>
    <em><?=count($rows)?> resource<?=count($rows)!==1?'s':''?></em>
  </div>
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr>
          <th style="width:60px">Thumb</th>
          <th>Title</th>
          <th style="text-align:center">Type</th>
          <th style="text-align:center">Audience</th>
          <th style="text-align:center;width:50px">Views</th>
          <th style="text-align:center;width:70px">Featured</th>
          <th style="text-align:center;width:80px">Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r):
          $thumb = !empty($r['thumbnail']) ? imgUrl($r['thumbnail']) : ($r['type']==='video' ? adminYtThumb($r['url']) : '');
          $ic = $typeIcons[$r['type']] ?? 'bi-link';
          $col= $typeColors[$r['type']] ?? '#555';
        ?>
        <tr>
          <td>
            <div style="width:52px;height:36px;border-radius:6px;overflow:hidden;background:var(--light)">
              <?php if($thumb): ?>
              <img src="<?=e($thumb)?>" alt="Support resource thumbnail" style="width:100%;height:100%;object-fit:cover" onerror="this.closest('div').style.background='#eee'">
              <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1rem;color:<?=$col?>">
                <i class="bi <?=$ic?>"></i>
              </div>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <strong style="font-size:.85rem;color:var(--dark)"><?=e($r['title'])?></strong>
            <?php if($r['description']): ?>
            <div style="font-size:.72rem;color:var(--muted);margin-top:2px"><?=e(mb_strimwidth($r['description'],0,55,'…'))?></div>
            <?php endif; ?>
            <?php if($r['duration']): ?>
            <div style="font-size:.65rem;color:var(--muted);margin-top:2px"><i class="bi bi-clock me-1" style="color:var(--primary)"></i><?=e($r['duration'])?></div>
            <?php endif; ?>
          </td>
          <td style="text-align:center">
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:.7rem;font-weight:700;color:<?=$col?>;background:<?=$col?>14;padding:4px 10px;border-radius:50px">
              <i class="bi <?=$ic?>"></i><?=ucfirst($r['type'])?>
            </span>
          </td>
          <td style="text-align:center;font-size:.72rem;color:var(--muted)"><?=e($audienceLabels[$r['audience']]??'All')?></td>
          <td style="text-align:center;font-size:.8rem;color:var(--muted)"><?=number_format($r['views'])?></td>
          <td style="text-align:center">
            <a href="?action=feature&id=<?=$r['id']?>" title="Toggle featured">
              <?=$r['is_featured']
                ? '<i class="bi bi-star-fill" style="color:var(--accent);font-size:1rem"></i>'
                : '<i class="bi bi-star" style="color:#ccc;font-size:1rem"></i>'?>
            </a>
          </td>
          <td style="text-align:center">
            <a href="?action=toggle&id=<?=$r['id']?>">
              <span class="badge-status <?=$r['is_active']?'badge-active':'badge-inactive'?>" style="font-size:.65rem">
                <?=$r['is_active']?'Active':'Hidden'?>
              </span>
            </a>
          </td>
          <td>
            <div class="d-flex gap-2 justify-content-end">
              <?php if($r['type']==='video'): ?>
              <a href="<?=e($r['url'])?>" target="_blank" class="btn btn-sm" style="border-radius:50px;font-size:.7rem;padding:4px 12px;background:rgba(193,18,31,.08);color:var(--primary)">
                <i class="bi bi-play-fill me-1"></i>Watch
              </a>
              <?php endif; ?>
              <a href="?action=edit&id=<?=$r['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.7rem;padding:4px 12px">Edit</a>
              <button onclick="confirmDelete('?action=delete&id=<?=$r['id']?>','<?=e(addslashes($r['title']))?>')"
                      class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.7rem;padding:4px 12px">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>
</div>
<?php adminFoot(); ?>
