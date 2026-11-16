<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload_doc.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$p = ['title'=>'','authors'=>'','publication_year'=>date('Y'),'summary'=>'','file_name'=>'','external_link'=>'','is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM research_publications WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $p = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $p['title']            = trim($_POST['title'] ?? '');
        $p['authors']          = trim($_POST['authors'] ?? '');
        $p['publication_year'] = (int)($_POST['publication_year'] ?? date('Y'));
        $p['summary']          = trim($_POST['summary'] ?? '');
        $p['external_link']    = trim($_POST['external_link'] ?? '');
        $p['is_published']     = isset($_POST['is_published']) ? 1 : 0;

        if ($p['title'] === '') $errors[] = 'Title is required.';

        try {
            $uploaded = handle_document_upload('document');
            if ($uploaded) $p['file_name'] = $uploaded['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE research_publications SET title=?, authors=?, publication_year=?, summary=?, file_name=?, external_link=?, is_published=? WHERE id=?');
                $stmt->execute([$p['title'],$p['authors'],$p['publication_year'],$p['summary'],$p['file_name'],$p['external_link'],$p['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO research_publications (title, authors, publication_year, summary, file_name, external_link, is_published) VALUES (?,?,?,?,?,?,?)');
                $stmt->execute([$p['title'],$p['authors'],$p['publication_year'],$p['summary'],$p['file_name'],$p['external_link'],$p['is_published']]);
            }
            redirect('/admin/research.php');
        }
    }
}

$pageTitle = $id ? 'Edit Publication' : 'New Publication';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($p['title']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Authors</label>
        <input type="text" name="authors" class="form-control" value="<?= h($p['authors']) ?>" placeholder="e.g. Dr. Jane Mwangi, Dr. Peter Otieno">
      </div>
      <div class="col-md-4">
        <label class="form-label">Year</label>
        <input type="number" name="publication_year" class="form-control" value="<?= h($p['publication_year']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Summary / abstract</label>
      <textarea name="summary" rows="4" class="form-control"><?= h($p['summary']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">PDF file (optional)</label>
      <?php if ($p['file_name']): ?><div class="mb-2 small text-muted">Current: <?= h($p['file_name']) ?></div><?php endif; ?>
      <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx">
    </div>
    <div class="mb-3">
      <label class="form-label">Or an external link (DOI, journal page, etc.)</label>
      <input type="text" name="external_link" class="form-control" value="<?= h($p['external_link']) ?>">
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $p['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Publication</button>
    <a href="<?= BASE_URL ?>/admin/research.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
