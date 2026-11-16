<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/includes/upload_doc.php';
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$programme = ['title'=>'','level'=>'Undergraduate','school_id'=>null,'unit_slug'=>'','image'=>null,'duration'=>'','mode'=>'Physical','intake'=>'','overview'=>'','requirements'=>'','fee_structure'=>'','fee_structure_file'=>null,'course_units'=>'','planner_file'=>null,'is_published'=>1,'is_featured'=>0];
$onlineOnlyLevels = ['Masters','PhD'];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM programmes WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $programme = $found;
}
// Defensive default in case fee_structure_file hasn't been added yet on
// this server (i.e. database/schema_phase22.sql hasn't been run there).
$programme['fee_structure_file'] = $programme['fee_structure_file'] ?? null;
// Same defensive default for intake, added in Phase 4, in case this
// server hasn't run database/phase4-programmes-filters-2026-09.sql yet.
$programme['intake'] = $programme['intake'] ?? '';
// Defensive default in case this server hasn't run
// database/phase5b-programmes-featured-dedup-2026-09.sql yet.
$programme['is_featured'] = $programme['is_featured'] ?? 0;

$schools = db()->query('SELECT id, name FROM schools ORDER BY sort_order')->fetchAll();
$levels = ['PhD','Masters','Undergraduate','Diploma','Certificate','Other'];
// ANU Online and Non-Degree (TVET) are NOT listed here -- they run their
// own separate admin panels, not this one.
$nonSchoolUnits = ['' => ', none, ', 'sepc' => 'Centre for Short Courses & Professional Studies'];

// Presets from a site console link (e.g. programmes_form.php?school_id=3
// or ?unit=sepc), so a new programme starts pre-assigned to the right
// site instead of leaving that to guesswork.
if (!$id) {
    if (isset($_GET['school_id'])) $programme['school_id'] = (int)$_GET['school_id'];
    if (isset($_GET['unit']) && isset($nonSchoolUnits[$_GET['unit']])) $programme['unit_slug'] = $_GET['unit'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $programme['title']       = trim($_POST['title'] ?? '');
        $programme['level']       = in_array($_POST['level'] ?? '', $levels, true) ? $_POST['level'] : 'Other';
        $programme['school_id']   = $_POST['school_id'] !== '' ? (int)$_POST['school_id'] : null;
        $programme['unit_slug']   = in_array($_POST['unit_slug'] ?? '', array_keys($nonSchoolUnits), true) ? $_POST['unit_slug'] : '';
        $programme['duration']    = trim($_POST['duration'] ?? '');
        $programme['mode']        = ($_POST['mode'] ?? '') === 'Online' ? 'Online' : 'Physical';
        // Masters and PhD programmes are always delivered online, regardless
        // of what was submitted -- enforced server-side, not just in the UI.
        if (in_array($programme['level'], $onlineOnlyLevels, true)) {
            $programme['mode'] = 'Online';
        }
        // Real intake month(s)/date only -- leave blank until you have this
        // confirmed from anu.ac.ke (or a future data import). Never guessed.
        $programme['intake']      = trim($_POST['intake'] ?? '') ?: null;
        $programme['overview']    = trim($_POST['overview'] ?? '');
        $programme['requirements']  = trim($_POST['requirements'] ?? '');
        $programme['fee_structure'] = trim($_POST['fee_structure'] ?? '');
        $programme['course_units']  = trim($_POST['course_units'] ?? '');
        $programme['is_published']= isset($_POST['is_published']) ? 1 : 0;
        $programme['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;

        if ($programme['title'] === '') $errors[] = 'Title is required.';

        // Duplicate guard: same normalized title + school + level already
        // exists (this is the same rule enforced by the
        // uniq_normalized_title_school_level key added in
        // database/phase6-programme-dedup-2026-09.sql). Checked here too so
        // the admin gets a clear message instead of a raw DB error, and so
        // this still works on a server that hasn't run that migration yet.
        if ($programme['title'] !== '') {
            $normalize = function ($t) {
                $t = str_replace('&', 'and', $t);
                $t = strtolower($t);
                return preg_replace('/[^a-z0-9]+/', '', $t);
            };
            $norm = $normalize($programme['title']);
            $dupStmt = db()->prepare('SELECT id, title FROM programmes WHERE school_id <=> ? AND level = ? AND id != ?');
            $dupStmt->execute([$programme['school_id'], $programme['level'], $id]);
            foreach ($dupStmt->fetchAll() as $existing) {
                if ($normalize($existing['title']) === $norm) {
                    $errors[] = 'A programme with this title already exists for this school and level ("' . $existing['title'] . '"). Edit that programme instead, or choose a different title/level/school.';
                    break;
                }
            }
        }

        try {
            $uploaded = handle_image_upload('image');
            if ($uploaded) $programme['image'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
        try {
            $uploadedPlanner = handle_document_upload('planner_file');
            if ($uploadedPlanner) $programme['planner_file'] = $uploadedPlanner['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
        try {
            $uploadedFeeDoc = handle_document_upload('fee_structure_file');
            if ($uploadedFeeDoc) $programme['fee_structure_file'] = $uploadedFeeDoc['filename'];
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        $slug = slugify($programme['title']);
        $base = $slug; $n = 1;
        while (true) {
            $check = db()->prepare('SELECT id FROM programmes WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }

        if (!$errors) {
            $unitVal = $programme['unit_slug'] !== '' ? $programme['unit_slug'] : null;
            if ($id) {
                $stmt = db()->prepare('UPDATE programmes SET title=?, slug=?, level=?, school_id=?, unit_slug=?, image=?, duration=?, mode=?, intake=?, overview=?, requirements=?, fee_structure=?, fee_structure_file=?, course_units=?, planner_file=?, is_published=?, is_featured=? WHERE id=?');
                $stmt->execute([$programme['title'],$slug,$programme['level'],$programme['school_id'],$unitVal,$programme['image'],$programme['duration'],$programme['mode'],$programme['intake'],$programme['overview'],$programme['requirements'],$programme['fee_structure'],$programme['fee_structure_file'],$programme['course_units'],$programme['planner_file'],$programme['is_published'],$programme['is_featured'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO programmes (title, slug, level, school_id, unit_slug, image, duration, mode, intake, overview, requirements, fee_structure, fee_structure_file, course_units, planner_file, is_published, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$programme['title'],$slug,$programme['level'],$programme['school_id'],$unitVal,$programme['image'],$programme['duration'],$programme['mode'],$programme['intake'],$programme['overview'],$programme['requirements'],$programme['fee_structure'],$programme['fee_structure_file'],$programme['course_units'],$programme['planner_file'],$programme['is_published'],$programme['is_featured']]);
            }
            redirect('/admin/programmes.php');
        }
    }
}

$pageTitle = $id ? 'Edit Programme' : 'New Programme';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($programme['title']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Level</label>
        <select name="level" id="levelSelect" class="form-select">
          <?php foreach ($levels as $lvl): ?>
            <option value="<?= h($lvl) ?>" data-online-only="<?= in_array($lvl, $onlineOnlyLevels, true) ? '1' : '0' ?>" <?= $programme['level']===$lvl?'selected':'' ?>><?= h($lvl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Duration</label>
        <input type="text" name="duration" class="form-control" value="<?= h($programme['duration']) ?>" placeholder="e.g. 4 years">
      </div>
      <div class="col-md-6">
        <label class="form-label">School (for the 5 academic schools)</label>
        <select name="school_id" class="form-select">
          <option value="">none</option>
          <?php foreach ($schools as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)$programme['school_id']===(int)$s['id']?'selected':'' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Or non-school unit (Non-Degree, ANU Online, SEPC)</label>
        <select name="unit_slug" class="form-select">
          <?php foreach ($nonSchoolUnits as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= ($programme['unit_slug'] ?? '')===$val?'selected':'' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <p class="small text-muted mt-2">Choose either a School or a non-school unit, not both.</p>
    <div class="mb-3 mt-3">
      <label class="form-label">Thumbnail image (used on programme listings)</label>
      <?php if (!empty($programme['image'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($programme['image']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Mode of study</label>
      <select name="mode" id="modeSelect" class="form-select">
        <option value="Physical" <?= $programme['mode']==='Physical'?'selected':'' ?>>Physical (on campus)</option>
        <option value="Online" <?= $programme['mode']==='Online'?'selected':'' ?>>Online</option>
      </select>
      <div class="form-text" id="modeHint">Masters &amp; PhD are always Online (auto-enforced).</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Intake <span class="text-muted small">(real month(s)/date only -- confirm against anu.ac.ke before entering. Leave blank if not yet confirmed; it just won't show a filter or a fact on the programme page until you fill it in.)</span></label>
      <input type="text" name="intake" class="form-control" style="max-width:24rem;" value="<?= h($programme['intake']) ?>" placeholder="e.g. September">
    </div>
    <script>
      (function(){
        var lvl = document.getElementById('levelSelect');
        var mode = document.getElementById('modeSelect');
        function sync(){
          var opt = lvl.options[lvl.selectedIndex];
          var forced = opt && opt.getAttribute('data-online-only') === '1';
          if (forced) { mode.value = 'Online'; mode.disabled = true; }
          else { mode.disabled = false; }
        }
        lvl.addEventListener('change', sync);
        sync();
      })();
    </script>
    <div class="mb-3">
      <label class="form-label">Overview</label>
      <textarea name="overview" rows="6" class="form-control"><?= h($programme['overview']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Requirements</label>
      <textarea name="requirements" rows="5" class="form-control" placeholder="e.g. KCSE mean grade C+, or equivalent qualification"><?= h($programme['requirements']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Fee Structure (summary text)</label>
      <textarea name="fee_structure" rows="5" class="form-control" placeholder="e.g. Tuition: KSh 45,000 per semester. Application fee: KSh 1,500."><?= h($programme['fee_structure']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Fee Structure document (PDF/Word/Excel, optional)</label>
      <?php if (!empty($programme['fee_structure_file'])): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark-spreadsheet"></i> Current file: <?= h($programme['fee_structure_file']) ?></div>
      <?php endif; ?>
      <input type="file" name="fee_structure_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
      <div class="form-text">Shown as a download button on the public page.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Course Units / Planner Table</label>
      <div class="form-text mb-2">
        One unit per line: <code>Year/Semester|Unit Code|Unit Name|Credit Hours</code>
      </div>
      <textarea name="course_units" rows="8" class="form-control" placeholder="Year 1, Semester 1|BUS101|Principles of Management|3"><?= h($programme['course_units']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Course Planner document (PDF/Word, optional)</label>
      <?php if (!empty($programme['planner_file'])): ?>
        <div class="mb-2 small text-muted"><i class="bi bi-file-earmark"></i> Current file: <?= h($programme['planner_file']) ?></div>
      <?php endif; ?>
      <input type="file" name="planner_file" class="form-control" accept=".pdf,.doc,.docx">
      <div class="form-text">Optional full download alongside the table above.</div>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $programme['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <div class="form-check mb-3">
      <input type="checkbox" name="is_featured" id="feat" class="form-check-input" <?= !empty($programme['is_featured']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="feat">Featured (shown as one of the 3 "Featured Programmes" cards on this school's homepage)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Programme</button>
    <a href="<?= BASE_URL ?>/admin/programmes.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
