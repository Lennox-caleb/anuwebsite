<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$presetUnit = trim($_GET['unit'] ?? '');
$s = ['unit_slug'=>($presetUnit ?: 'law'),'full_name'=>'','title'=>'','bio'=>'','email'=>'','photo'=>null,'sort_order'=>0,'is_published'=>1,'is_dept_head'=>0,'department'=>''];
$errors = [];
// This form is for regular staff/faculty listings only -- one entry per
// site this admin panel manages. ANU Online and Non-Degree (TVET) are not
// listed here, they run their own separate admin panels. Featured "Dean's
// Message" / "Registrar" / etc. photo-and-bio slots are a separate, single
// fixed slot per site -- manage those from that site's page instead of
// here, so the two are never mixed in one list or one form again.
$regularUnits = [
    'law' => 'School of Law', 'business' => 'School of Business', 'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences', 'religion' => 'School of Religion & Christian Ministry',
    'sepc' => 'SEPC', 'admissions' => 'Admissions', 'alumni' => 'Alumni', 'chaplaincy' => 'Chaplaincy',
    'library' => 'Library', 'research' => 'Research', 'student-life' => 'Student Life',
];
$knownUnits = $regularUnits;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM staff WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $s = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $s['unit_slug']   = trim($_POST['unit_slug'] ?? 'law');
        $s['full_name']   = trim($_POST['full_name'] ?? '');
        $s['title']       = trim($_POST['title'] ?? '');
        $s['bio']         = trim($_POST['bio'] ?? '');
        $s['email']       = trim($_POST['email'] ?? '');
        $s['sort_order']  = (int)($_POST['sort_order'] ?? 0);
        $s['is_published']= isset($_POST['is_published']) ? 1 : 0;
        $s['is_dept_head']= isset($_POST['is_dept_head']) ? 1 : 0;
        $s['department']  = trim($_POST['department'] ?? '');

        if ($s['full_name'] === '') $errors[] = 'Name is required.';

        try {
            $uploaded = handle_image_upload('photo');
            if ($uploaded) $s['photo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE staff SET unit_slug=?, full_name=?, title=?, bio=?, email=?, photo=?, sort_order=?, is_published=?, is_dept_head=?, department=? WHERE id=?');
                $stmt->execute([$s['unit_slug'],$s['full_name'],$s['title'],$s['bio'],$s['email'],$s['photo'],$s['sort_order'],$s['is_published'],$s['is_dept_head'],$s['department'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO staff (unit_slug, full_name, title, bio, email, photo, sort_order, is_published, is_dept_head, department) VALUES (?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$s['unit_slug'],$s['full_name'],$s['title'],$s['bio'],$s['email'],$s['photo'],$s['sort_order'],$s['is_published'],$s['is_dept_head'],$s['department']]);
            }
            redirect('/admin/staff.php');
        }
    }
}

$pageTitle = $id ? 'Edit Person' : 'New Person';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="<?= h($s['full_name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Unit</label>
        <select name="unit_slug" class="form-select">
          <?php foreach ($regularUnits as $u => $label): ?>
            <option value="<?= h($u) ?>" <?= $s['unit_slug']===$u?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">This person appears in the regular staff/faculty listing on that site. Looking for a Dean's Message, Registrar or other featured welcome photo instead? Manage that from the site's own page (Admin -> Sites -> that site), not here.</div>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Title / role</label>
      <input type="text" name="title" class="form-control" value="<?= h($s['title']) ?>" placeholder="e.g. Dean, School of Law">
    </div>
    <div class="mb-3">
      <label class="form-label">Photo</label>
      <?php if (!empty($s['photo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($s['photo']) ?>" class="img-preview-circle rounded border"></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Short bio / message</label>
      <textarea name="bio" rows="4" class="form-control"><?= h($s['bio']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Email (optional)</label>
        <input type="email" name="email" class="form-control" value="<?= h($s['email']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Display order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int)$s['sort_order'] ?>">
      </div>
    </div>
    <div class="border rounded p-3 my-3">
      <div class="form-check mb-2">
        <input type="checkbox" name="is_dept_head" id="dh" class="form-check-input" <?= !empty($s['is_dept_head']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="dh">This person is a Head of Department. Show them, with the text above as their message, on the school home page.</label>
      </div>
      <label class="form-label small">Department name</label>
      <input type="text" name="department" class="form-control" value="<?= h($s['department'] ?? '') ?>" placeholder="e.g. Department of Counselling Psychology">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $s['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/staff.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
