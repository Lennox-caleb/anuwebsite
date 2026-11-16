<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/../includes/campus_tour_lib.php';
ct_ensure();

$errors = []; $notice = '';
$edit = ['id'=>0,'title'=>'','description'=>'','time'=>'','sort_order'=>0,'is_published'=>1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $act = $_POST['action'] ?? '';
        if ($act === 'video') {
            $url = trim($_POST['video_url'] ?? '');
            if ($url !== '' && ct_video_id($url) === '') {
                $errors[] = 'That does not look like a YouTube link. Paste the link from the YouTube Share button.';
            } else {
                $st = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
                $st->execute(['campus_tour_video', $url]);
                redirect('/admin/campus_tour.php?saved=video');
            }
        } elseif ($act === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $edit = ['id'=>$id,'title'=>trim($_POST['title'] ?? ''),'description'=>trim($_POST['description'] ?? ''),
                     'time'=>trim($_POST['time'] ?? ''),'sort_order'=>(int)($_POST['sort_order'] ?? 0),
                     'is_published'=>isset($_POST['is_published']) ? 1 : 0];
            $secs = ct_parse_time($edit['time']);
            if ($edit['title'] === '') $errors[] = 'Enter the name of the place.';
            if ($secs === null) $errors[] = 'Enter the start time like 1:25 (minutes:seconds) or 1:02:30 (hours:minutes:seconds).';
            if (!$errors) {
                if ($id) {
                    $st = db()->prepare('UPDATE campus_tour_places SET title=?, description=?, start_seconds=?, sort_order=?, is_published=? WHERE id=?');
                    $st->execute([$edit['title'],$edit['description'],$secs,$edit['sort_order'],$edit['is_published'],$id]);
                } else {
                    $st = db()->prepare('INSERT INTO campus_tour_places (title, description, start_seconds, sort_order, is_published) VALUES (?,?,?,?,?)');
                    $st->execute([$edit['title'],$edit['description'],$secs,$edit['sort_order'],$edit['is_published']]);
                }
                redirect('/admin/campus_tour.php?saved=place');
            }
        } elseif ($act === 'delete') {
            $st = db()->prepare('DELETE FROM campus_tour_places WHERE id = ?');
            $st->execute([(int)($_POST['id'] ?? 0)]);
            redirect('/admin/campus_tour.php?saved=deleted');
        } elseif ($act === 'examples') {
            if (!db()->query('SELECT COUNT(*) FROM campus_tour_places')->fetchColumn()) {
                $ex = [['Main Gate',0,'Your first view of the campus.'],['Administration Block',28,'Admissions, finance and the registrar.'],
                       ['Library',63,'Study floors and quiet reading rooms.'],['Lecture Halls',98,'Where classes for all five schools meet.'],
                       ['Science Laboratories',136,'Hands-on learning in science and technology.'],['Chapel',172,'Worship, reflection and community life.'],
                       ['Student Centre',205,'Meals, clubs and student services.'],['Sports Grounds',228,'Football, athletics and open fields.']];
                $st = db()->prepare('INSERT INTO campus_tour_places (title, description, start_seconds, sort_order, is_published) VALUES (?,?,?,?,1)');
                foreach ($ex as $i => $e) $st->execute([$e[0], $e[2], $e[1], $i]);
            }
            redirect('/admin/campus_tour.php?saved=examples');
        } elseif ($act === 'toggle') {
            $st = db()->prepare('UPDATE campus_tour_places SET is_published = 1 - is_published WHERE id = ?');
            $st->execute([(int)($_POST['id'] ?? 0)]);
            redirect('/admin/campus_tour.php');
        }
    }
}

if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = db()->prepare('SELECT * FROM campus_tour_places WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    if ($r = $st->fetch()) {
        $edit = ['id'=>(int)$r['id'],'title'=>$r['title'],'description'=>$r['description'],
                 'time'=>ct_fmt_time((int)$r['start_seconds']),'sort_order'=>(int)$r['sort_order'],'is_published'=>(int)$r['is_published']];
    }
}
if (isset($_GET['saved'])) {
    $notice = ['video'=>'Video link saved.','place'=>'Place saved.','deleted'=>'Place deleted.','examples'=>'Example places added. Change their names and times to match your video.'][$_GET['saved']] ?? '';
}

$rows = ct_places(false);
$videoUrl = ct_video_url();
$pageTitle = 'Campus Tour';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<?php if ($notice): ?><div class="alert alert-success"><?= h($notice) ?></div><?php endif; ?>

<p class="text-muted small">The Campus Tour page shows your YouTube video with a list of places. Visitors tap a place to jump to that moment. Only places that are switched on are shown.
  <a href="<?= BASE_URL ?>/campus-tour.php" target="_blank" rel="noopener">Open the page</a></p>

<form method="post" class="card-anu mb-4">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="video">
    <label class="form-label" for="video_url">YouTube video link</label>
    <div class="d-flex gap-2 flex-wrap">
      <input type="text" id="video_url" name="video_url" class="form-control" style="flex:1;min-width:240px" value="<?= h($videoUrl) ?>" placeholder="https://youtu.be/...">
      <button class="btn btn-apply" type="submit">Save video</button>
    </div>
  </div>
</form>

<form method="post" class="card-anu mb-4">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
    <h5 class="mb-3"><?= $edit['id'] ? 'Edit place' : 'Add a place' ?></h5>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label" for="title">Place name</label>
        <input type="text" id="title" name="title" class="form-control" maxlength="120" value="<?= h($edit['title']) ?>" required placeholder="Library"></div>
      <div class="col-md-2"><label class="form-label" for="time">Starts at</label>
        <input type="text" id="time" name="time" class="form-control" value="<?= h($edit['time']) ?>" required placeholder="1:25"></div>
      <div class="col-md-6"><label class="form-label" for="description">Short description (optional)</label>
        <input type="text" id="description" name="description" class="form-control" maxlength="255" value="<?= h($edit['description']) ?>" placeholder="Study floors and quiet reading rooms."></div>
    </div>
    <div class="form-check mt-3 mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $edit['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Show on the website</label>
    </div>
    <input type="hidden" name="sort_order" value="<?= (int)$edit['sort_order'] ?>">
    <button class="btn btn-apply" type="submit"><?= $edit['id'] ? 'Save changes' : 'Add place' ?></button>
    <?php if ($edit['id']): ?><a href="<?= BASE_URL ?>/admin/campus_tour.php" class="btn btn-outline-anu">Cancel</a><?php endif; ?>
  </div>
</form>

<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Starts</th><th>Place</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h(ct_fmt_time((int)$r['start_seconds'])) ?></td>
          <td><?= h($r['title']) ?><?php if ($r['description'] !== ''): ?><div class="small text-muted"><?= h($r['description']) ?></div><?php endif; ?></td>
          <td><?= $r['is_published'] ? '<span class="badge bg-success">Shown</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
          <td class="text-end">
            <form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-anu" type="submit"><?= $r['is_published'] ? 'Hide' : 'Show' ?></button></form>
            <a href="<?= BASE_URL ?>/admin/campus_tour.php?edit=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this place?');"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-muted">No places yet. Add the first place above, or
          <form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="examples"><button class="btn btn-sm btn-outline-anu" type="submit">load 8 example places</button></form>
          to see how the page looks (the times are only examples until you change them).</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
