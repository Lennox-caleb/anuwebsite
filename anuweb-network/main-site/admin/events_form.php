<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = ['title'=>'','description'=>'','location'=>'','start_datetime'=>date('Y-m-d\TH:i'),'end_datetime'=>'','is_published'=>1,'unit_slug'=>null,'category'=>null];
$errors = [];

// Phase 5: an event can be posted to the main site's Events page (blank/
// "Main Site") or scoped to a single school site's own "Upcoming Events".
$unitOptions = [
    '' => 'Main Site Events',
    'law' => 'School of Law', 'business' => 'School of Business',
    'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences',
    'religion' => 'School of Religion & Christian Ministry',
];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) {
        $found['start_datetime'] = date('Y-m-d\TH:i', strtotime($found['start_datetime']));
        $found['end_datetime'] = $found['end_datetime'] ? date('Y-m-d\TH:i', strtotime($found['end_datetime'])) : '';
        $event = $found;
    }
} elseif (isset($_GET['unit']) && isset($unitOptions[$_GET['unit']])) {
    $event['unit_slug'] = $_GET['unit'];
}
// Defensive default in case this server hasn't run
// database/phase5-departments-news-2026-09.sql yet.
$event['unit_slug'] = $event['unit_slug'] ?? null;
// Defensive default in case this server hasn't run
// database/phase-news-events-import-2026-09.sql yet.
$event['category'] = $event['category'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $event['title']          = trim($_POST['title'] ?? '');
        $event['description']    = trim($_POST['description'] ?? '');
        $event['location']       = trim($_POST['location'] ?? '');
        $event['start_datetime'] = $_POST['start_datetime'] ?? '';
        $event['end_datetime']   = $_POST['end_datetime'] ?? '';
        $event['is_published']   = isset($_POST['is_published']) ? 1 : 0;
        $event['unit_slug']      = isset($unitOptions[$_POST['unit_slug'] ?? '']) && ($_POST['unit_slug'] ?? '') !== '' ? $_POST['unit_slug'] : null;
        $event['category']       = trim($_POST['category'] ?? '') ?: null;

        if ($event['title'] === '') $errors[] = 'Title is required.';
        if (!$event['start_datetime']) $errors[] = 'Start date/time is required.';

        $slug = slugify($event['title']);
        $base = $slug; $n = 1;
        while (true) {
            $check = db()->prepare('SELECT id FROM events WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }

        if (!$errors) {
            $endVal = $event['end_datetime'] ?: null;
            if ($id) {
                $stmt = db()->prepare('UPDATE events SET title=?, slug=?, description=?, location=?, start_datetime=?, end_datetime=?, is_published=?, unit_slug=?, category=? WHERE id=?');
                $stmt->execute([$event['title'],$slug,$event['description'],$event['location'],$event['start_datetime'],$endVal,$event['is_published'],$event['unit_slug'],$event['category'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO events (title, slug, description, location, start_datetime, end_datetime, is_published, unit_slug, category) VALUES (?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$event['title'],$slug,$event['description'],$event['location'],$event['start_datetime'],$endVal,$event['is_published'],$event['unit_slug'],$event['category']]);
            }
            redirect('/admin/events.php' . ($event['unit_slug'] ? '?unit=' . urlencode($event['unit_slug']) : ''));
        }
    }
}

$pageTitle = $id ? 'Edit Event' : 'New Event';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($event['title']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Starts</label>
        <input type="datetime-local" name="start_datetime" class="form-control" value="<?= h($event['start_datetime']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Ends (optional)</label>
        <input type="datetime-local" name="end_datetime" class="form-control" value="<?= h($event['end_datetime']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" value="<?= h($event['location']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="5" class="form-control"><?= h($event['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Posted to</label>
      <select name="unit_slug" class="form-select" style="max-width:24rem;">
        <?php foreach ($unitOptions as $val => $label): ?>
          <option value="<?= h($val) ?>" <?= ($event['unit_slug'] ?? '') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">A school choice shows this only in that school's own "Upcoming Events", not the main site's Events page.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Category (optional)</label>
      <input type="text" name="category" class="form-control" style="max-width:24rem;" value="<?= h($event['category'] ?? '') ?>" placeholder="e.g. Academic, Alumni">
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $event['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Event</button>
    <a href="<?= BASE_URL ?>/admin/events.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
