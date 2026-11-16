<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = ['name'=>'','logo'=>null,'website_url'=>'','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM partners WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $p = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $p['name']         = trim($_POST['name'] ?? '');
        $p['website_url']  = trim($_POST['website_url'] ?? '');
        $p['sort_order']   = (int)($_POST['sort_order'] ?? 0);
        $p['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if ($p['name'] === '') $errors[] = 'Name is required.';

        try {
            $uploaded = handle_image_upload('logo');
            if ($uploaded) $p['logo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$id && empty($p['logo'])) $errors[] = 'Please upload a logo.';

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE partners SET name=?, logo=?, website_url=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$p['name'],$p['logo'],$p['website_url'],$p['sort_order'],$p['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO partners (name, logo, website_url, sort_order, is_published) VALUES (?,?,?,?,?)');
                $stmt->execute([$p['name'],$p['logo'],$p['website_url'],$p['sort_order'],$p['is_published']]);
            }
            redirect('/admin/partners.php');
        }
    }
}

$pageTitle = $id ? 'Edit Partner' : 'New Partner';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Partner name</label>
      <input type="text" name="name" class="form-control" value="<?= h($p['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Logo (ideally a transparent PNG)</label>
      <?php if (!empty($p['logo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($p['logo']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Website URL (optional)</label>
      <input type="text" name="website_url" class="form-control" value="<?= h($p['website_url']) ?>">
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$p['sort_order'] ?>">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $p['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/partners.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
