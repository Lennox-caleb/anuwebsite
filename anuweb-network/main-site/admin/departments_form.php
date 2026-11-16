<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
current_admin();

$schoolUnits = [
    'law' => 'School of Law', 'business' => 'School of Business',
    'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences',
    'religion' => 'School of Religion & Christian Ministry',
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$dept = ['unit_slug'=>'law','name'=>'','summary'=>'','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM departments WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $dept = $found;
} elseif (isset($_GET['unit']) && isset($schoolUnits[$_GET['unit']])) {
    $dept['unit_slug'] = $_GET['unit'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $dept['unit_slug']    = isset($schoolUnits[$_POST['unit_slug'] ?? '']) ? $_POST['unit_slug'] : 'law';
        $dept['name']         = trim($_POST['name'] ?? '');
        $dept['summary']      = trim($_POST['summary'] ?? '');
        $dept['sort_order']   = (int) ($_POST['sort_order'] ?? 0);
        $dept['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        if ($dept['name'] === '') $errors[] = 'Name is required.';

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE departments SET unit_slug=?, name=?, summary=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$dept['unit_slug'],$dept['name'],$dept['summary'],$dept['sort_order'],$dept['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO departments (unit_slug, name, summary, sort_order, is_published) VALUES (?,?,?,?,?)');
                $stmt->execute([$dept['unit_slug'],$dept['name'],$dept['summary'],$dept['sort_order'],$dept['is_published']]);
            }
            redirect('/admin/departments.php?unit=' . urlencode($dept['unit_slug']));
        }
    }
}

$pageTitle = $id ? 'Edit Department' : 'New Department';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">School</label>
        <select name="unit_slug" class="form-select">
          <?php foreach ($schoolUnits as $slug => $label): ?>
            <option value="<?= h($slug) ?>" <?= $dept['unit_slug']===$slug?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Display order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int) $dept['sort_order'] ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Department name</label>
      <input type="text" name="name" class="form-control" value="<?= h($dept['name']) ?>" required placeholder="e.g. Department of Private Law">
    </div>
    <div class="mb-3">
      <label class="form-label">Summary (optional)</label>
      <textarea name="summary" rows="4" class="form-control"><?= h($dept['summary']) ?></textarea>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $dept['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Department</button>
    <a href="<?= BASE_URL ?>/admin/departments.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
