<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$g = ['title'=>'','category'=>'','photo'=>null,'caption'=>'','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM csr_gallery WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $g = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $g['title']       = trim($_POST['title'] ?? '');
        $g['category']    = trim($_POST['category'] ?? '');
        $g['caption']     = trim($_POST['caption'] ?? '');
        $g['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $g['is_published'] = ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        if ($g['title'] === '') $errors[] = 'Title is required.';
        if (!$id && empty($_FILES['photo']['name'])) $errors[] = 'A photo is required.';

        try {
            $uploadedPhoto = handle_image_upload('photo');
            if ($uploadedPhoto) $g['photo'] = $uploadedPhoto;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE csr_gallery SET title=?, category=?, photo=?, caption=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$g['title'],$g['category'],$g['photo'],$g['caption'],$g['sort_order'],$g['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO csr_gallery (title, category, photo, caption, sort_order, is_published) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$g['title'],$g['category'],$g['photo'],$g['caption'],$g['sort_order'],$g['is_published']]);
            }
            redirect('/admin/csr_gallery.php');
        }
    }
}

$pageTitle = $id ? 'Edit Photo' : 'New Photo';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?= h($g['title']) ?>" placeholder="e.g. Medical Outreach Camp 2026" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category (optional)</label>
        <input type="text" name="category" class="form-control" value="<?= h($g['category']) ?>" placeholder="e.g. Health &amp; Wellness, Environment">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Caption (optional)</label>
      <textarea name="caption" rows="2" class="form-control" placeholder="A short line about this photo"><?= h($g['caption']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Photo (JPG/PNG/WEBP)</label>
      <?php if (!empty($g['photo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($g['photo']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$g['sort_order'] ?>">
    </div>
    <input type="hidden" name="is_published" value="0">
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" value="1" class="form-check-input" <?= $g['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/csr_gallery.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
