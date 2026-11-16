<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload_doc.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$v = ['title'=>'','department'=>'','employment_type'=>'Full-time','description'=>'','requirements'=>'','how_to_apply'=>'','closing_date'=>'','attachment'=>null,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM vacancies WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $v = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $v['title']           = trim($_POST['title'] ?? '');
        $v['department']      = trim($_POST['department'] ?? '');
        $v['employment_type'] = trim($_POST['employment_type'] ?? 'Full-time');
        $v['description']     = trim($_POST['description'] ?? '');
        $v['requirements']    = trim($_POST['requirements'] ?? '');
        $v['how_to_apply']    = trim($_POST['how_to_apply'] ?? '');
        $v['closing_date']    = $_POST['closing_date'] ?? '';
        $v['is_published']    = isset($_POST['is_published']) ? 1 : 0;

        if ($v['title'] === '') $errors[] = 'Title is required.';

        try {
            $uploaded = handle_document_upload('attachment');
            if ($uploaded) $v['attachment'] = $uploaded['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        $slug = slugify($v['title']);
        $base = $slug; $n = 1;
        while (true) {
            $check = db()->prepare('SELECT id FROM vacancies WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }

        if (!$errors) {
            $closing = $v['closing_date'] ?: null;
            if ($id) {
                $stmt = db()->prepare('UPDATE vacancies SET title=?, slug=?, department=?, employment_type=?, description=?, requirements=?, how_to_apply=?, closing_date=?, attachment=?, is_published=? WHERE id=?');
                $stmt->execute([$v['title'],$slug,$v['department'],$v['employment_type'],$v['description'],$v['requirements'],$v['how_to_apply'],$closing,$v['attachment'],$v['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO vacancies (title, slug, department, employment_type, description, requirements, how_to_apply, closing_date, attachment, is_published) VALUES (?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$v['title'],$slug,$v['department'],$v['employment_type'],$v['description'],$v['requirements'],$v['how_to_apply'],$closing,$v['attachment'],$v['is_published']]);
            }
            redirect('/admin/vacancies.php');
        }
    }
}

$pageTitle = $id ? 'Edit Vacancy' : 'New Vacancy';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Job Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($v['title']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" value="<?= h($v['department']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Employment Type</label>
        <select name="employment_type" class="form-select">
          <?php foreach (['Full-time','Part-time','Contract'] as $t): ?>
            <option value="<?= h($t) ?>" <?= $v['employment_type']===$t?'selected':'' ?>><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Closing Date</label>
        <input type="date" name="closing_date" class="form-control" value="<?= h($v['closing_date']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Job Description</label>
      <textarea name="description" rows="5" class="form-control"><?= h($v['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Requirements</label>
      <textarea name="requirements" rows="5" class="form-control"><?= h($v['requirements']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Job Description Attachment (PDF/Word, optional)</label>
      <?php if ($v['attachment']): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark"></i> Current file: <?= h($v['attachment']) ?></div>
      <?php endif; ?>
      <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx">
    </div>
    <div class="mb-3">
      <label class="form-label">How to Apply</label>
      <textarea name="how_to_apply" rows="4" class="form-control"><?= h($v['how_to_apply']) ?></textarea>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $v['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Vacancy</button>
    <a href="<?= BASE_URL ?>/admin/vacancies.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
