<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$c = ['title'=>'','description'=>'','icon'=>'bi-heart','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM chaplaincy_programs WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $c = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $c['title']       = trim($_POST['title'] ?? '');
        $c['description'] = trim($_POST['description'] ?? '');
        $c['icon']        = trim($_POST['icon'] ?? '') ?: 'bi-heart';
        $c['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $c['is_published'] = ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        if ($c['title'] === '') $errors[] = 'Title is required.';

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE chaplaincy_programs SET title=?, description=?, icon=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$c['title'],$c['description'],$c['icon'],$c['sort_order'],$c['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published) VALUES (?,?,?,?,?)');
                $stmt->execute([$c['title'],$c['description'],$c['icon'],$c['sort_order'],$c['is_published']]);
            }
            redirect('/admin/chaplaincy.php');
        }
    }
}

$pageTitle = $id ? 'Edit Ministry' : 'New Ministry';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Ministry / program title</label>
        <input type="text" name="title" class="form-control" value="<?= h($c['title']) ?>" placeholder="e.g. Christian Union" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Bootstrap icon name</label>
        <input type="text" name="icon" class="form-control" value="<?= h($c['icon']) ?>" placeholder="bi-heart">
        <div class="form-text">See icons at icons.getbootstrap.com, e.g. bi-book, bi-people.</div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="3" class="form-control"><?= h($c['description']) ?></textarea>
    </div>
    <div class="mb-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$c['sort_order'] ?>">
    </div>
    <input type="hidden" name="is_published" value="0">
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" value="1" class="form-check-input" <?= $c['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/chaplaincy.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
