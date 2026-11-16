<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$school = ['name'=>'','slug'=>'','summary'=>'','description'=>'','image'=>null,'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM schools WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $school = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $school['name']        = trim($_POST['name'] ?? '');
        $school['summary']     = trim($_POST['summary'] ?? '');
        $school['description'] = trim($_POST['description'] ?? '');
        $school['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $school['is_published']= isset($_POST['is_published']) ? 1 : 0;

        if ($school['name'] === '') $errors[] = 'Name is required.';

        try {
            $uploaded = handle_image_upload('image');
            if ($uploaded) $school['image'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        // URL slug: used for routing only (e.g. /schools/sol). If left blank,
        // falls back to a slug generated from the name. Either way it never
        // appears as visible text -- the full name is what's shown everywhere.
        $manualSlug = trim($_POST['slug'] ?? '');
        $slug = slugify($manualSlug !== '' ? $manualSlug : $school['name']);
        $base = $slug; $n = 1;
        while (true) {
            $check = db()->prepare('SELECT id FROM schools WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }
        $school['slug'] = $slug;

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE schools SET name=?, slug=?, summary=?, description=?, image=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$school['name'],$slug,$school['summary'],$school['description'],$school['image'],$school['sort_order'],$school['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO schools (name, slug, summary, description, image, sort_order, is_published) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$school['name'],$slug,$school['summary'],$school['description'],$school['image'],$school['sort_order'],$school['is_published']]);
            }
            redirect('/admin/schools.php');
        }
    }
}

$pageTitle = $id ? 'Edit School' : 'New School';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" value="<?= h($school['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">URL slug <span class="text-muted small">(used in web addresses only -- e.g. "sol" for /schools/sol. The full name above is always what visitors see. Leave blank to auto-generate from the name.)</span></label>
      <input type="text" name="slug" class="form-control" style="max-width:16rem;" maxlength="60" value="<?= h($school['slug'] ?? '') ?>" placeholder="e.g. sol">
    </div>
    <div class="mb-3">
      <label class="form-label">Short summary (for listings)</label>
      <input type="text" name="summary" class="form-control" value="<?= h($school['summary']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Full description</label>
      <textarea name="description" rows="6" class="form-control"><?= h($school['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Thumbnail image (used on the schools listing and homepage)</label>
      <?php if (!empty($school['image'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($school['image']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$school['sort_order'] ?>">
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $school['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save School</button>
    <a href="<?= BASE_URL ?>/admin/schools.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
