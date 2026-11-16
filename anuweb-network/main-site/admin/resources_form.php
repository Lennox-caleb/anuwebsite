<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload_doc.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$categories = ['Policies & Guidelines','Handbooks & Forms','Past Papers','Financial Aid Forms','Publications','Orientation','Other'];
$r = ['title'=>'','description'=>'','category'=>'Other','file_name'=>'','file_size'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM resources WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $r = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $r['title']       = trim($_POST['title'] ?? '');
        $r['description'] = trim($_POST['description'] ?? '');
        $r['category']    = in_array($_POST['category'] ?? '', $categories, true) ? $_POST['category'] : 'Other';
        // The hidden fallback input below means is_published is now always
        // present in $_POST ("0" or "1") -- checking isset() alone would
        // always be true, so this must compare the actual submitted value.
        $r['is_published'] = ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        if ($r['title'] === '') $errors[] = 'Title is required.';

        try {
            $uploaded = handle_document_upload('document');
            if ($uploaded) {
                $r['file_name'] = $uploaded['filename'];
                $r['file_size'] = $uploaded['size'];
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$id && $r['file_name'] === '') $errors[] = 'Please choose a file to upload.';

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE resources SET title=?, description=?, category=?, file_name=?, file_size=?, is_published=? WHERE id=?');
                $stmt->execute([$r['title'],$r['description'],$r['category'],$r['file_name'],$r['file_size'],$r['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO resources (title, description, category, file_name, file_size, is_published) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$r['title'],$r['description'],$r['category'],$r['file_name'],$r['file_size'],$r['is_published']]);
            }
            redirect('/admin/resources.php');
        }
    }
}

$pageTitle = $id ? 'Edit Resource' : 'Upload Resource';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($r['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Category</label>
      <select name="category" class="form-select">
        <?php foreach ($categories as $c): ?>
          <option value="<?= h($c) ?>" <?= $r['category']===$c?'selected':'' ?>><?= h($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Short description</label>
      <textarea name="description" rows="2" class="form-control"><?= h($r['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">File (PDF, Word or Excel, max 100MB)</label>
      <?php if ($r['file_name']): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark"></i> Current file: <?= h($r['file_name']) ?> (<?= format_bytes((int)$r['file_size']) ?>)</div>
      <?php endif; ?>
      <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
    </div>
    <div class="form-check mb-3">
      <!-- Hidden fallback: an UNCHECKED checkbox sends no value at all, so
           without this, a stale cached copy of this page (or a form
           submitted from a browser extension that strips unchecked boxes)
           could silently save as unpublished with no way to tell why. The
           hidden input guarantees "0" is always sent unless the checkbox
           itself overrides it with "1". -->
      <input type="hidden" name="is_published" value="0">
      <input type="checkbox" name="is_published" value="1" id="pub" class="form-check-input" <?= $r['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published (visible in the library)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Resource</button>
    <a href="<?= BASE_URL ?>/admin/resources.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
