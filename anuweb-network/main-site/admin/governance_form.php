<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$categories = ['Board of Trustees', 'University Council', 'Management'];
$m = ['category'=>'Management','full_name'=>'','title'=>'','bio'=>'','photo'=>null,'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM governance_members WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $m = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $m['category']     = in_array($_POST['category'] ?? '', $categories, true) ? $_POST['category'] : 'Management';
        $m['full_name']     = trim($_POST['full_name'] ?? '');
        $m['title']         = trim($_POST['title'] ?? '');
        $m['bio']           = trim($_POST['bio'] ?? '');
        $m['sort_order']    = (int)($_POST['sort_order'] ?? 0);
        $m['is_published']  = isset($_POST['is_published']) ? 1 : 0;

        if ($m['full_name'] === '') $errors[] = 'Name is required.';

        try {
            $uploaded = handle_image_upload('photo');
            if ($uploaded) $m['photo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE governance_members SET category=?, full_name=?, title=?, bio=?, photo=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$m['category'],$m['full_name'],$m['title'],$m['bio'],$m['photo'],$m['sort_order'],$m['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO governance_members (category, full_name, title, bio, photo, sort_order, is_published) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$m['category'],$m['full_name'],$m['title'],$m['bio'],$m['photo'],$m['sort_order'],$m['is_published']]);
            }
            redirect('/admin/governance.php');
        }
    }
}

$pageTitle = $id ? 'Edit Member' : 'New Member';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="<?= h($m['full_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <?php foreach ($categories as $c): ?>
            <option value="<?= h($c) ?>" <?= $m['category']===$c?'selected':'' ?>><?= h($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Title / role</label>
      <input type="text" name="title" class="form-control" value="<?= h($m['title']) ?>" placeholder="e.g. Chair, Board of Trustees">
    </div>
    <div class="mb-3">
      <label class="form-label">Photo</label>
      <?php if (!empty($m['photo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($m['photo']) ?>" class="img-preview-circle rounded border"></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Short bio</label>
      <textarea name="bio" rows="4" class="form-control"><?= h($m['bio']) ?></textarea>
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$m['sort_order'] ?>">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $m['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/governance.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
