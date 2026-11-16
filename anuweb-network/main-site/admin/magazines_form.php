<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/upload_doc.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$types = ['ASPIRE','ANU Voice','Graduation List','Other'];
$m = ['title'=>'','publication_type'=>'ASPIRE','issue_label'=>'','thumbnail'=>null,'pdf_file'=>null,'published_date'=>date('Y-m-d'),'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM publications_gallery WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $m = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $m['title']            = trim($_POST['title'] ?? '');
        $m['publication_type'] = in_array($_POST['publication_type'] ?? '', $types, true) ? $_POST['publication_type'] : 'Other';
        $m['issue_label']      = trim($_POST['issue_label'] ?? '');
        $m['published_date']   = $_POST['published_date'] ?? '';
        $m['sort_order']       = (int)($_POST['sort_order'] ?? 0);
        $m['is_published']     = isset($_POST['is_published']) ? 1 : 0;

        if ($m['title'] === '') $errors[] = 'Title is required.';

        try {
            $uploadedThumb = handle_image_upload('thumbnail');
            if ($uploadedThumb) $m['thumbnail'] = $uploadedThumb;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
        try {
            $uploadedPdf = handle_document_upload('pdf_file');
            if ($uploadedPdf) $m['pdf_file'] = $uploadedPdf['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            $published = $m['published_date'] ?: null;
            if ($id) {
                $stmt = db()->prepare('UPDATE publications_gallery SET title=?, publication_type=?, issue_label=?, thumbnail=?, pdf_file=?, published_date=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$m['title'],$m['publication_type'],$m['issue_label'],$m['thumbnail'],$m['pdf_file'],$published,$m['sort_order'],$m['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO publications_gallery (title, publication_type, issue_label, thumbnail, pdf_file, published_date, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([$m['title'],$m['publication_type'],$m['issue_label'],$m['thumbnail'],$m['pdf_file'],$published,$m['sort_order'],$m['is_published']]);
            }
            redirect('/admin/magazines.php');
        }
    }
}

$pageTitle = $id ? 'Edit Issue' : 'New Issue';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?= h($m['title']) ?>" placeholder="e.g. ASPIRE 2026 or ANU Voice, Semester 1" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Publication</label>
        <select name="publication_type" class="form-select">
          <?php foreach ($types as $t): ?>
            <option value="<?= h($t) ?>" <?= $m['publication_type']===$t?'selected':'' ?>><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label">Issue label (optional)</label>
        <input type="text" name="issue_label" class="form-control" value="<?= h($m['issue_label']) ?>" placeholder="e.g. Graduation 2026, Semester 1 2026">
      </div>
      <div class="col-md-6">
        <label class="form-label">Published date</label>
        <input type="date" name="published_date" class="form-control" value="<?= h($m['published_date']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Cover thumbnail (JPG/PNG/WEBP)</label>
      <?php if (!empty($m['thumbnail'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($m['thumbnail']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">PDF file (optional, for readers who want the full issue)</label>
      <?php if (!empty($m['pdf_file'])): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark-pdf"></i> Current file: <?= h($m['pdf_file']) ?></div>
      <?php endif; ?>
      <input type="file" name="pdf_file" class="form-control" accept=".pdf">
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
    <a href="<?= BASE_URL ?>/admin/magazines.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
