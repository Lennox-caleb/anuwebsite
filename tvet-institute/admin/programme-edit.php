<?php
$pageTitle = 'Add / Edit Programme';
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$programme = ['level' => 'Diploma', 'mode' => 'Full-Time, Evening & Weekend', 'intake' => 'January, May, August', 'examining_body' => 'Africa Nazarene University', 'is_published' => 1];
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM programmes WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash_set('danger', 'Programme not found.'); redirect(ADMIN_URL . '/programmes.php'); }
    $programme = $found;
}

$departments = db()->query('SELECT * FROM departments ORDER BY sort_order')->fetchAll();
$levels = ['Certificate','Diploma','Higher Diploma','Short Course'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('danger', 'Session expired, please try again.');
        redirect(ADMIN_URL . '/programme-edit.php' . ($id ? "?id=$id" : ''));
    }
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);
    $department_id = (int)($_POST['department_id'] ?? 0) ?: null;
    $level = $_POST['level'] ?? $levels[1];
    $duration = trim($_POST['duration'] ?? '');
    $mode = trim($_POST['mode'] ?? '');
    $intake = trim($_POST['intake'] ?? '');
    $examining_body = trim($_POST['examining_body'] ?? '');
    $fee_per_year = trim($_POST['fee_per_year'] ?? '');
    $entry_requirements = trim($_POST['entry_requirements'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $career_prospects = trim($_POST['career_prospects'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') {
        flash_set('danger', 'Title is required.');
        redirect(ADMIN_URL . '/programme-edit.php' . ($id ? "?id=$id" : ''));
    }

    try {
        $image = null;
        $uploaded = handle_upload('image', 'programmes');
        if ($uploaded !== null) $image = $uploaded;

        if ($id > 0) {
            $sql = "UPDATE programmes SET title=?, slug=?, department_id=?, level=?, duration=?, mode=?, intake=?, examining_body=?, fee_per_year=?, entry_requirements=?, overview=?, career_prospects=?, is_featured=?, is_published=?, sort_order=?" . ($image ? ", image=?" : "") . " WHERE id=?";
            $params = [$title,$slug,$department_id,$level,$duration,$mode,$intake,$examining_body,$fee_per_year,$entry_requirements,$overview,$career_prospects,$is_featured,$is_published,$sort_order];
            if ($image) $params[] = $image;
            $params[] = $id;
            db()->prepare($sql)->execute($params);
            flash_set('success', 'Programme updated successfully.');
        } else {
            $stmt = db()->prepare("INSERT INTO programmes (title,slug,department_id,level,duration,mode,intake,examining_body,fee_per_year,entry_requirements,overview,career_prospects,image,is_featured,is_published,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title,$slug,$department_id,$level,$duration,$mode,$intake,$examining_body,$fee_per_year,$entry_requirements,$overview,$career_prospects,$image,$is_featured,$is_published,$sort_order]);
            flash_set('success', 'Programme created successfully.');
        }
    } catch (Throwable $e) {
        flash_set('danger', 'Could not save programme: ' . $e->getMessage());
        redirect(ADMIN_URL . '/programme-edit.php' . ($id ? "?id=$id" : ''));
    }
    redirect(ADMIN_URL . '/programmes.php');
}
require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-card">
  <div class="admin-card-header">
    <h2><?= $id ? 'Edit' : 'Add New' ?> Programme</h2>
    <a href="programmes.php" class="btn btn-sm btn-outline-secondary">&larr; Back to list</a>
  </div>
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Programme Title *</label>
        <input required type="text" name="title" class="form-control" value="<?= h($programme['title'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= h($programme['slug'] ?? '') ?>" placeholder="auto-generated if blank">
      </div>

      <div class="col-md-4">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select">
          <option value="">None</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= (($programme['department_id'] ?? null) == $d['id']) ? 'selected' : '' ?>><?= h($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Level *</label>
        <select required name="level" class="form-select">
          <?php foreach ($levels as $lvl): ?>
            <option <?= ($programme['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= h($lvl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Duration</label>
        <input type="text" name="duration" class="form-control" value="<?= h($programme['duration'] ?? '') ?>" placeholder="e.g. 2 Years">
      </div>

      <div class="col-md-4">
        <label class="form-label">Mode of Study</label>
        <input type="text" name="mode" class="form-control" value="<?= h($programme['mode'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Intakes</label>
        <input type="text" name="intake" class="form-control" value="<?= h($programme['intake'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Examining Body</label>
        <input type="text" name="examining_body" class="form-control" value="<?= h($programme['examining_body'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Fee Guide (per year)</label>
        <input type="text" name="fee_per_year" class="form-control" value="<?= h($programme['fee_per_year'] ?? '') ?>" placeholder="e.g. KES 55,000">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= h((string)($programme['sort_order'] ?? 0)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Cover Image</label>
        <?php if (!empty($programme['image'])): ?><div class="mb-1"><img src="<?= h(upload_url('programmes', $programme['image'])) ?>" style="height:50px;border-radius:6px" alt=""></div><?php endif; ?>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>

      <div class="col-12"><label class="form-label">Entry Requirements</label><textarea name="entry_requirements" rows="2" class="form-control"><?= h($programme['entry_requirements'] ?? '') ?></textarea></div>
      <div class="col-12"><label class="form-label">Overview</label><textarea name="overview" rows="4" class="form-control"><?= h($programme['overview'] ?? '') ?></textarea></div>
      <div class="col-12"><label class="form-label">Career Prospects</label><textarea name="career_prospects" rows="3" class="form-control"><?= h($programme['career_prospects'] ?? '') ?></textarea></div>

      <div class="col-md-3">
        <div class="form-check"><input class="form-check-input" type="checkbox" id="feat" name="is_featured" value="1" <?= !empty($programme['is_featured']) ? 'checked' : '' ?>><label class="form-check-label" for="feat">Featured on Homepage</label></div>
      </div>
      <div class="col-md-3">
        <div class="form-check"><input class="form-check-input" type="checkbox" id="pub" name="is_published" value="1" <?= !empty($programme['is_published']) ? 'checked' : '' ?>><label class="form-check-label" for="pub">Published</label></div>
      </div>
    </div>
    <button type="submit" class="btn btn-admin-primary mt-4"><i class="bi bi-check-circle"></i> Save Programme</button>
  </form>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
