<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$t = ['quote'=>'','person_name'=>'','person_role'=>'','photo'=>null,'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $t = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $t['quote']        = trim($_POST['quote'] ?? '');
        $t['person_name']  = trim($_POST['person_name'] ?? '');
        $t['person_role']  = trim($_POST['person_role'] ?? '');
        $t['sort_order']   = (int)($_POST['sort_order'] ?? 0);
        $t['is_published'] = isset($_POST['is_published']) ? 1 : 0;
        if ($t['quote'] === '') $errors[] = 'The quote is required.';
        if ($t['person_name'] === '') $errors[] = 'The person\'s name is required.';
        try {
            $uploaded = handle_image_upload('photo');
            if ($uploaded) $t['photo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
        if (!$errors) {
            if ($id) {
                db()->prepare('UPDATE testimonials SET quote=?, person_name=?, person_role=?, photo=?, sort_order=?, is_published=? WHERE id=?')
                    ->execute([$t['quote'],$t['person_name'],$t['person_role'],$t['photo'],$t['sort_order'],$t['is_published'],$id]);
            } else {
                db()->prepare('INSERT INTO testimonials (quote, person_name, person_role, photo, sort_order, is_published) VALUES (?,?,?,?,?,?)')
                    ->execute([$t['quote'],$t['person_name'],$t['person_role'],$t['photo'],$t['sort_order'],$t['is_published']]);
            }
            redirect('/admin/testimonials.php');
        }
    }
}

$pageTitle = $id ? 'Edit Testimonial' : 'New Testimonial';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Quote</label>
      <textarea name="quote" rows="4" class="form-control" required><?= h($t['quote']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Person's name</label>
      <input type="text" name="person_name" class="form-control" value="<?= h($t['person_name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Role / programme / year (e.g. "Alumnus, Business")</label>
      <input type="text" name="person_role" class="form-control" value="<?= h($t['person_role']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Photo (optional)</label>
      <?php if (!empty($t['photo'])): ?><div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($t['photo']) ?>" class="img-preview-sm rounded border"></div><?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$t['sort_order'] ?>">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $t['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published (only with the person's permission)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/testimonials.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
