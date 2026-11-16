<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$roles = ['vc' => 'Vice Chancellor', 'dvc' => 'Deputy Vice Chancellor', 'chancellor' => 'Chancellor'];
$l = ['role_type'=>'dvc','full_name'=>'','title'=>'','message'=>'','photo'=>null,'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM leadership WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $l = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $l['role_type']    = array_key_exists($_POST['role_type'] ?? '', $roles) ? $_POST['role_type'] : 'dvc';
        $l['full_name']    = trim($_POST['full_name'] ?? '');
        $l['title']        = trim($_POST['title'] ?? '');
        $l['message']      = trim($_POST['message'] ?? '');
        $l['sort_order']   = (int)($_POST['sort_order'] ?? 0);
        // A hidden fallback field guarantees is_published is always present
        // in $_POST ("0" or "1"), so an unchecked box reliably saves as
        // hidden instead of silently keeping whatever it was before.
        $l['is_published'] = ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        if ($l['full_name'] === '') $errors[] = 'Full name is required.';

        try {
            $uploaded = handle_image_upload('photo');
            if ($uploaded) $l['photo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE leadership SET role_type=?, full_name=?, title=?, message=?, photo=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$l['role_type'],$l['full_name'],$l['title'],$l['message'],$l['photo'],$l['sort_order'],$l['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO leadership (role_type, full_name, title, message, photo, sort_order, is_published) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$l['role_type'],$l['full_name'],$l['title'],$l['message'],$l['photo'],$l['sort_order'],$l['is_published']]);
            }
            redirect('/admin/leadership.php');
        }
    }
}

$pageTitle = $id ? 'Edit Leader' : 'New Leader';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="<?= h($l['full_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role_type" class="form-select">
          <?php foreach ($roles as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= $l['role_type']===$val?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Only one "Vice Chancellor" and one "Chancellor" should be published at a time. Any number of Deputy Vice Chancellors is fine.</div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Title (as shown publicly)</label>
      <input type="text" name="title" class="form-control" value="<?= h($l['title']) ?>" placeholder="e.g. Deputy Vice Chancellor, Academic &amp; Student Affairs">
    </div>
    <div class="mb-3">
      <label class="form-label">Photo</label>
      <?php if (!empty($l['photo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($l['photo']) ?>" class="img-preview-circle rounded-circle border"></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
      <div class="form-text">Use a square, front-facing photo (at least 500&times;500px) for the best fit, it will be cropped to a consistent size automatically wherever it's shown.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Welcome message</label>
      <textarea name="message" rows="10" class="form-control"><?= h($l['message']) ?></textarea>
      <div class="form-text">This can be as long as needed, the public page is designed to handle a full, multi-paragraph message without the photo stretching or looking odd.</div>
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$l['sort_order'] ?>">
      <div class="form-text">Lowest number first. Only used to order multiple Deputy Vice Chancellors.</div>
    </div>
    <div class="form-check mb-3">
      <input type="hidden" name="is_published" value="0">
      <input type="checkbox" name="is_published" value="1" id="pub" class="form-check-input" <?= $l['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published (visible on the public Leadership page)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/leadership.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
