<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/../includes/rich_text.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = ['title'=>'','slug'=>'','excerpt'=>'','body'=>'','author'=>'ANU Communications','published_at'=>date('Y-m-d'),'is_published'=>1,'cover_image'=>null,'unit_slug'=>null,'category'=>null,'is_featured'=>0];
$errors = [];

// Phase 5: a news item can be posted to the main site's News page (blank/
// "Main Site") or scoped to a single school site's own "News & Updates"
// page. Same one `news` table either way -- just which visitors see it.
$unitOptions = [
    '' => 'Main Site News',
    'law' => 'School of Law',
    'business' => 'School of Business',
    'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences',
    'religion' => 'School of Religion & Christian Ministry',
];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $article = $found;
} elseif (isset($_GET['unit']) && isset($unitOptions[$_GET['unit']])) {
    $article['unit_slug'] = $_GET['unit'];
}
// Defensive default in case this server hasn't run
// database/phase5-departments-news-2026-09.sql yet.
$article['unit_slug'] = $article['unit_slug'] ?? null;
// Defensive defaults in case this server hasn't run
// database/phase-news-events-import-2026-09.sql yet.
$article['category'] = $article['category'] ?? null;
$article['is_featured'] = $article['is_featured'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $article['title']         = trim($_POST['title'] ?? '');
        $article['excerpt']       = trim($_POST['excerpt'] ?? '');
        $cleanBody                = anu_nl_sanitize((string)($_POST['body'] ?? ''));
        $hasContent               = trim(str_replace("\xC2\xA0", ' ', html_entity_decode(strip_tags($cleanBody), ENT_QUOTES, 'UTF-8'))) !== '' || stripos($cleanBody, '<img') !== false;
        $article['body']          = $hasContent ? '<!--rte-->' . $cleanBody : '';
        $article['author']        = trim($_POST['author'] ?? 'ANU Communications');
        $article['published_at']  = $_POST['published_at'] ?? date('Y-m-d');
        $article['is_published']  = isset($_POST['is_published']) ? 1 : 0;
        $article['unit_slug']     = isset($unitOptions[$_POST['unit_slug'] ?? '']) && ($_POST['unit_slug'] ?? '') !== '' ? $_POST['unit_slug'] : null;
        $article['category']      = trim($_POST['category'] ?? '') ?: null;
        $article['is_featured']   = isset($_POST['is_featured']) ? 1 : 0;

        if ($article['title'] === '') $errors[] = 'Title is required.';

        $slug = slugify($article['title']);
        // ensure uniqueness
        $base = $slug; $n = 1;
        while (true) {
            $check = db()->prepare('SELECT id FROM news WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }
        $article['slug'] = $slug;

        try {
            $uploaded = handle_image_upload('cover_image');
            if ($uploaded) $article['cover_image'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE news SET title=?, slug=?, excerpt=?, body=?, author=?, published_at=?, is_published=?, cover_image=?, unit_slug=?, category=?, is_featured=? WHERE id=?');
                $stmt->execute([$article['title'],$article['slug'],$article['excerpt'],$article['body'],$article['author'],$article['published_at'],$article['is_published'],$article['cover_image'],$article['unit_slug'],$article['category'],$article['is_featured'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO news (title, slug, excerpt, body, author, published_at, is_published, cover_image, unit_slug, category, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$article['title'],$article['slug'],$article['excerpt'],$article['body'],$article['author'],$article['published_at'],$article['is_published'],$article['cover_image'],$article['unit_slug'],$article['category'],$article['is_featured']]);
            }
            redirect('/admin/news.php' . ($article['unit_slug'] ? '?unit=' . urlencode($article['unit_slug']) : ''));
        }
    }
}

$pageTitle = $id ? 'Edit Article' : 'New Article';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/quill/quill.snow.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/rich-editor.css">
<form method="post" class="card-anu rt-form" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($article['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Cover image (JPG/PNG/WEBP, max 4MB)</label>
      <?php if (!empty($article['cover_image'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($article['cover_image']) ?>" class="img-preview-sm" class="rounded border"></div>
      <?php endif; ?>
      <input type="file" name="cover_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Author</label>
        <input type="text" name="author" class="form-control" value="<?= h($article['author']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Published Date</label>
        <input type="date" name="published_at" class="form-control" value="<?= h($article['published_at']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Posted to</label>
        <select name="unit_slug" class="form-select">
          <?php foreach ($unitOptions as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= ($article['unit_slug'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">A school choice shows this only on that school's own "News &amp; Updates" page, not the main site's News page.</div>
      </div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-6">
        <label class="form-label">Category (optional)</label>
        <input type="text" name="category" class="form-control" value="<?= h($article['category'] ?? '') ?>" placeholder="e.g. Announcements, Press Release">
        <div class="form-text">Leave blank if this article doesn't need one. A filter chip only appears on the News &amp; Events page for categories you actually use.</div>
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
          <input type="checkbox" name="is_featured" id="feat" class="form-check-input" <?= !empty($article['is_featured']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="feat">Featured (shown as the large story on the homepage and News &amp; Events page)</label>
        </div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Excerpt (used in listings)</label>
      <textarea name="excerpt" rows="2" class="form-control"><?= h($article['excerpt']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label" id="news-body-label">Article text</label>
      <div class="rt-editor-wrap">
        <div id="news-editor" aria-labelledby="news-body-label"><?= anu_nl_to_editor_html((string)$article['body']) ?></div>
      </div>
      <textarea name="body" id="news-body" style="display:none" tabindex="-1" aria-hidden="true"></textarea>
      <div id="news-img-msg" class="small mt-1" role="status" aria-live="polite"></div>
      <div class="form-text">Use the toolbar for headings, bold, italic, lists, colours, links and pictures (the picture button uploads from your computer; you can also paste or drag a picture in). The title, date and author are shown above the text automatically.</div>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $article['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published (visible on the public site)</label>
    </div>
    <div class="rt-actionbar">
      <button class="btn btn-apply" type="submit">Save Article</button>
      <a href="<?= BASE_URL ?>/admin/news.php" class="btn btn-outline-anu">Cancel</a>
    </div>
  </div>
</form>
<script src="<?= BASE_URL ?>/assets/vendor/quill/quill.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/rich-editor.js"></script>
<script>
AnuRichEditor.init({ editor: '#news-editor', textarea: '#news-body', message: '#news-img-msg', form: 'form.rt-form',
  endpoint: <?= json_encode(BASE_URL . '/admin/news_image.php') ?>, csrf: <?= json_encode(csrf_token()) ?>, placeholder: 'Write the article here…' });
</script>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
