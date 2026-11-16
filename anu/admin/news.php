<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db  = getDB();
$msg = '';
$msgType = 'success';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// Handle delete
if ($action === 'delete' && $editId) {
  $db->prepare("DELETE FROM news WHERE id=?")->execute([$editId]);
  $msg = 'Story deleted.';
  $action = 'list';
}

// Handle toggle publish
if ($action === 'toggle' && $editId) {
  $db->prepare("UPDATE news SET is_published = NOT is_published WHERE id=?")->execute([$editId]);
  $msg = 'Published status updated.';
  $action = 'list';
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
  $id       = (int)($_POST['id'] ?? 0);
  $title    = trim($_POST['title'] ?? '');
  $excerpt  = trim($_POST['excerpt'] ?? '');
  $content  = trim($_POST['content'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $author   = trim($_POST['author'] ?? 'ANU Online');
  $featured = isset($_POST['is_featured']) ? 1 : 0;
  $published= isset($_POST['is_published']) ? 1 : 0;
  
  // Generate slug
  $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
  $slug = trim($slug, '-');
  
  $imgPath = null;
  if (!empty($_FILES['featured_image']['name'])) {
    $imgPath = handleUpload('featured_image', 'news');
  }
  
  if (!$title) {
    $msg = 'Title is required.';
    $msgType = 'error';
  } else {
    if ($id) {
      // Get current image
      if (!$imgPath) {
        $curr = $db->prepare("SELECT featured_image FROM news WHERE id=?");
        $curr->execute([$id]);
        $curr = $curr->fetch();
        $imgPath = $curr ? $curr['featured_image'] : null;
      }
      $stmt = $db->prepare("UPDATE news SET title=?,slug=?,excerpt=?,content=?,category=?,author=?,featured_image=?,is_featured=?,is_published=? WHERE id=?");
      $stmt->execute([$title,$slug,$excerpt,$content,$category,$author,$imgPath,$featured,$published,$id]);
      $msg = 'Story updated!';
    } else {
      $stmt = $db->prepare("INSERT INTO news (title,slug,excerpt,content,category,author,featured_image,is_featured,is_published) VALUES (?,?,?,?,?,?,?,?,?)");
      $stmt->execute([$title,$slug,$excerpt,$content,$category,$author,$imgPath,$featured,$published]);
      $msg = 'Story published!';
    }
    $action = 'list';
  }
}

// Load edit item
$editing = [];
if ($action === 'edit' && $editId) {
  $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
  $stmt->execute([$editId]);
  $editing = $stmt->fetch() ?: [];
}

// Get all news
$newsList = $db->query("SELECT * FROM news ORDER BY published_at DESC")->fetchAll();

adminHead('News');
adminSidebar('news');
?>

<?php adminTopBar('News & Stories'); ?>

<?php if ($msg): ?>
  <div class="alert-anu-<?= $msgType ?> mb-4"><?= e($msg) ?></div>
  <?php endif; ?>

  <?php if ($action === 'list'): ?>
  <div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h5 style="margin:0;"><i class="bi bi-newspaper me-2" style="color:var(--primary);"></i>All Stories</h5>
      <a href="?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;">
        <i class="bi bi-plus-circle me-1"></i>Add Story
      </a>
    </div>
    
    <div class="table-responsive">
      <table class="table admin-table">
        <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($newsList as $n): ?>
          <tr>
            <td style="width:60px;">
              <img src="<?= $n['featured_image'] ? '/'.$n['featured_image'] : '/anu/assets/images/default-news.jpg' ?>"
                   alt="<?= e($n['title']) ?>" style="width:50px;height:40px;object-fit:cover;border-radius:8px;" onerror="this.src='/anu/assets/images/default-news.jpg'">
            </td>
            <td>
              <strong><?= e(substr($n['title'],0,50)) ?><?= strlen($n['title'])>50?'...':'' ?></strong>
              <?php if ($n['is_featured']): ?><br><small style="color:var(--accent-dk);">★ Featured</small><?php endif; ?>
            </td>
            <td><?= e($n['category']) ?></td>
            <td>
              <a href="?action=toggle&id=<?= $n['id'] ?>" title="Toggle">
                <span class="badge-status <?= $n['is_published'] ? 'badge-active' : 'badge-inactive' ?>">
                  <?= $n['is_published'] ? 'Published' : 'Draft' ?>
                </span>
              </a>
            </td>
            <td style="font-size:0.82rem;"><?= formatDate($n['published_at'], 'M d, Y') ?></td>
            <td>
              <div class="d-flex gap-2">
                <a href="?action=edit&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
                <a href="/anu/news.php?id=<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:50px;font-size:0.78rem;">View</a>
                <button onclick="confirmDelete('/anu/admin/news.php?action=delete&id=<?= $n['id'] ?>', '<?= e(addslashes($n['title'])) ?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <?php else: // New or Edit form ?>
  
  <div style="margin-bottom:20px;">
    <a href="?action=list" style="color:var(--primary);font-weight:600;"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
  </div>
  
  <div class="admin-card">
    <h5><i class="bi bi-pencil-fill me-2"></i><?= $editId ? 'Edit Story' : 'New Story' ?></h5>
    
    <form method="POST" enctype="multipart/form-data" class="anu-form">
      <input type="hidden" name="save_news" value="1">
      <input type="hidden" name="id" value="<?= $editId ?>">
      
      <div class="row g-4">
        <div class="col-md-8">
          <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($editing['title']??'') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Excerpt (short summary)</label>
            <textarea name="excerpt" class="form-control" rows="2"><?= e($editing['excerpt']??'') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Content (HTML allowed)</label>
            <textarea name="content" class="form-control" rows="12" style="font-family:monospace;"><?= e($editing['content']??'') ?></textarea>
            <small class="text-muted">You can write plain text or use HTML tags for formatting.</small>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Featured Image</label>
            <div class="img-upload-wrap" onclick="document.getElementById('newsImg').click()">
              <?php if (!empty($editing['featured_image'])): ?>
              <img src="/<?= e($editing['featured_image']) ?>" alt="Current featured image" style="width:100%;height:200px;object-fit:cover;">
              <?php else: ?>
              <div class="img-placeholder"><i class="bi bi-image"></i><span>Upload Image</span></div>
              <?php endif; ?>
              <div class="img-upload-overlay"><i class="bi bi-camera" style="font-size:2rem;"></i><span>Change Image</span></div>
            </div>
            <input type="file" name="featured_image" id="newsImg" class="img-upload-input" style="display:none;" accept="image/*">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <?php foreach(['Academics','Student Stories','Events','Campus Life','Faith','Technology','Announcements'] as $cat): ?>
              <option <?= ($editing['category']??'')===$cat?'selected':'' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Author</label>
            <input type="text" name="author" class="form-control" value="<?= e($editing['author']??'ANU Online') ?>">
          </div>
          
          <div class="mb-3 d-flex gap-4">
            <div class="form-check">
              <input type="checkbox" name="is_published" class="form-check-input" id="chkPublished" <?= ($editing['is_published']??1)?'checked':'' ?>>
              <label class="form-check-label" for="chkPublished">Published</label>
            </div>
            <div class="form-check">
              <input type="checkbox" name="is_featured" class="form-check-input" id="chkFeatured" <?= ($editing['is_featured']??0)?'checked':'' ?>>
              <label class="form-check-label" for="chkFeatured">Featured</label>
            </div>
          </div>
          
          <button type="submit" class="btn-submit">
            <i class="bi bi-check-circle me-2"></i><?= $editId ? 'Save Changes' : 'Publish Story' ?>
          </button>
        </div>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<?php adminFoot(); ?>