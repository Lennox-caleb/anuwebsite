<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload_doc.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$statuses = ['open' => 'Open', 'closed' => 'Closed', 'awarded' => 'Awarded'];
$t = ['title'=>'','reference_no'=>'','category'=>'','summary'=>'','document'=>null,'closing_date'=>'','status'=>'open','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM tenders WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $t = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $t['title']        = trim($_POST['title'] ?? '');
        $t['reference_no'] = trim($_POST['reference_no'] ?? '');
        $t['category']     = trim($_POST['category'] ?? '');
        $t['summary']      = trim($_POST['summary'] ?? '');
        $t['closing_date'] = $_POST['closing_date'] ?? '';
        $t['status']       = array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'open';
        $t['sort_order']   = (int)($_POST['sort_order'] ?? 0);
        $t['is_published'] = ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        if ($t['title'] === '') $errors[] = 'Title is required.';

        try {
            $uploadedDoc = handle_document_upload('document');
            if ($uploadedDoc) $t['document'] = $uploadedDoc['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            $closing = $t['closing_date'] ?: null;
            if ($id) {
                $stmt = db()->prepare('UPDATE tenders SET title=?, reference_no=?, category=?, summary=?, document=?, closing_date=?, status=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$t['title'],$t['reference_no'],$t['category'],$t['summary'],$t['document'],$closing,$t['status'],$t['sort_order'],$t['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO tenders (title, reference_no, category, summary, document, closing_date, status, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$t['title'],$t['reference_no'],$t['category'],$t['summary'],$t['document'],$closing,$t['status'],$t['sort_order'],$t['is_published']]);
            }
            redirect('/admin/tenders.php');
        }
    }
}

$pageTitle = $id ? 'Edit Tender' : 'New Tender';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Tender title</label>
      <input type="text" name="title" class="form-control" value="<?= h($t['title']) ?>" placeholder="e.g. Supply of Laboratory Equipment" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Reference number</label>
        <input type="text" name="reference_no" class="form-control" value="<?= h($t['reference_no']) ?>" placeholder="e.g. ANU/T/2026/012">
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="<?= h($t['category']) ?>" placeholder="e.g. Supplies, Works, Consultancy">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Summary / scope of work</label>
      <textarea name="summary" rows="4" class="form-control"><?= h($t['summary']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Closing date</label>
        <input type="date" name="closing_date" class="form-control" value="<?= h($t['closing_date']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php foreach ($statuses as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= $t['status']===$val?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Display order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int)$t['sort_order'] ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Tender document (PDF, Word or Excel)</label>
      <?php if (!empty($t['document'])): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark-text"></i> Current file: <?= h($t['document']) ?></div>
      <?php endif; ?>
      <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
    </div>
    <input type="hidden" name="is_published" value="0">
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" value="1" class="form-check-input" <?= $t['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/tenders.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
