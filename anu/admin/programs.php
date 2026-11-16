<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();
$msg = ''; $msgType = 'success';
$action       = $_GET['action']  ?? 'list';
$editId       = (int)($_GET['id']      ?? 0);
$filterSchool = $_GET['school']  ?? '';
$progId       = (int)($_GET['prog_id'] ?? 0);  // for courses

// ── PROGRAM ACTIONS ──────────────────────────────────────
if ($action === 'delete' && $editId) {
    $db->prepare("DELETE FROM programs WHERE id=?")->execute([$editId]);
    $msg = 'Program deleted.'; $action = 'list';
}
if ($action === 'toggle' && $editId) {
    $db->prepare("UPDATE programs SET is_active=NOT is_active WHERE id=?")->execute([$editId]);
    header('Location: ' . SITE_URL . '/admin/programs.php'); exit;
}
if ($action === 'feature' && $editId) {
    $db->prepare("UPDATE programs SET is_featured=NOT is_featured WHERE id=?")->execute([$editId]);
    header('Location: ' . SITE_URL . '/admin/programs.php'); exit;
}

// ── COURSE ACTIONS ───────────────────────────────────────
if ($action === 'del_course' && $editId) {
    $row = $db->prepare("SELECT program_id FROM program_courses WHERE id=?");
    $row->execute([$editId]); $row = $row->fetch();
    $db->prepare("DELETE FROM program_courses WHERE id=?")->execute([$editId]);
    $msg = 'Course deleted.'; $action = 'courses';
    $progId = $row['program_id'] ?? 0;
}
if ($action === 'save_course' && $_SERVER['REQUEST_METHOD']==='POST') {
    $progId  = (int)($_POST['program_id'] ?? 0);
    $cid     = (int)($_POST['course_id']  ?? 0);
    $ys      = trim($_POST['year_semester'] ?? '');
    $code    = trim($_POST['code']          ?? '');
    $name    = trim($_POST['name']          ?? '');
    $credits = (int)($_POST['credit_hours'] ?? 3);
    $sort    = (int)($_POST['sort_order']   ?? 0);
    if (!$name) { $msg = 'Course name required.'; $msgType = 'error'; }
    else {
        if ($cid) {
            $db->prepare("UPDATE program_courses SET year_semester=?,code=?,name=?,credit_hours=?,sort_order=? WHERE id=?")
               ->execute([$ys,$code,$name,$credits,$sort,$cid]);
            $msg = 'Course updated!';
        } else {
            $db->prepare("INSERT INTO program_courses (program_id,year_semester,code,name,credit_hours,sort_order) VALUES (?,?,?,?,?,?)")
               ->execute([$progId,$ys,$code,$name,$credits,$sort]);
            $msg = 'Course added!';
        }
    }
    $action = 'courses';
}

// ── PROGRAM SAVE ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $school = trim($_POST['school'] ?? '');
    $level  = trim($_POST['level']  ?? '');
    $name   = trim($_POST['name']   ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $dur    = trim($_POST['duration']    ?? '');
    $req    = trim($_POST['entry_requirements'] ?? '');
    $feat   = isset($_POST['is_featured']) ? 1 : 0;

    // Image upload
    $imgPath = null;
    if (!empty($_FILES['image']['name'])) {
        $imgPath = handleUpload('image', 'programs');
        if (!$imgPath) { $msg = 'Image upload failed.'; $msgType = 'error'; }
    }

    if (!$msg) {
        if (!$name || !$school || !$level) {
            $msg = 'Name, School and Level are required.'; $msgType = 'error';
        } else {
            if ($editId) {
                if ($imgPath) {
                    $db->prepare("UPDATE programs SET school=?,level=?,name=?,description=?,duration=?,entry_requirements=?,is_featured=?,image=? WHERE id=?")
                       ->execute([$school,$level,$name,$desc,$dur,$req,$feat,$imgPath,$editId]);
                } else {
                    $db->prepare("UPDATE programs SET school=?,level=?,name=?,description=?,duration=?,entry_requirements=?,is_featured=? WHERE id=?")
                       ->execute([$school,$level,$name,$desc,$dur,$req,$feat,$editId]);
                }
                $msg = 'Program updated!';
            } else {
                $db->prepare("INSERT INTO programs (school,level,name,description,duration,entry_requirements,is_featured,image) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$school,$level,$name,$desc,$dur,$req,$feat,$imgPath]);
                $msg = 'Program added!';
            }
            $action = 'list';
        }
    }
}

$schools = ['Religion','Business','Science','Law','Humanities','Executive'];
$levels  = ['PhD','Masters','Undergraduate','Diploma','Certificate','Executive'];
$schoolIcons = [
    'Religion'=>'bi-book-fill','Business'=>'bi-briefcase-fill',
    'Science'=>'bi-cpu-fill','Law'=>'bi-shield-fill',
    'Humanities'=>'bi-people-fill','Executive'=>'bi-star-fill',
];

// Fetch programs
$q = $filterSchool
    ? $db->prepare("SELECT * FROM programs WHERE school=? ORDER BY level,name")
    : $db->prepare("SELECT * FROM programs ORDER BY school,level,name");
$filterSchool ? $q->execute([$filterSchool]) : $q->execute();
$items = $q->fetchAll();

$grouped = [];
foreach ($items as $p) $grouped[$p['school']][] = $p;

$totals = [];
foreach ($db->query("SELECT school,COUNT(*) n FROM programs GROUP BY school")->fetchAll() as $r)
    $totals[$r['school']] = $r['n'];

// For edit
$editing = [];
if (in_array($action,['edit','new']) && $editId) {
    $s = $db->prepare("SELECT * FROM programs WHERE id=?");
    $s->execute([$editId]); $editing = $s->fetch() ?: [];
}

// For courses
$courseProgram = [];
$courses = [];
if ($action === 'courses' && $progId) {
    $s = $db->prepare("SELECT * FROM programs WHERE id=?");
    $s->execute([$progId]); $courseProgram = $s->fetch() ?: [];
    $courses = $db->prepare("SELECT * FROM program_courses WHERE program_id=? ORDER BY sort_order,year_semester,id");
    $courses->execute([$progId]); $courses = $courses->fetchAll();
}
// For edit_course
$editingCourse = [];
if ($action === 'edit_course' && $editId) {
    $s = $db->prepare("SELECT * FROM program_courses WHERE id=?");
    $s->execute([$editId]); $editingCourse = $s->fetch() ?: [];
    $progId = $editingCourse['program_id'] ?? 0;
    $action = 'courses'; // reuse courses view with pre-filled form
}

adminHead('Programs'); adminSidebar('programs');
?>
<?php adminTopBar('Programs', 'Manage academic programs and their courses'); ?>
<?php if($msg): ?><div class="alert-anu-<?=$msgType?> mb-4"><?=e($msg)?></div><?php endif; ?>

<?php // ══════════════ COURSES VIEW ══════════════
if ($action === 'courses' && $progId && $courseProgram): ?>

<div class="mb-3 d-flex align-items-center gap-3">
  <a href="?action=list" style="color:var(--primary);font-weight:600;font-size:.88rem">
    <i class="bi bi-arrow-left me-1"></i>Back to Programs
  </a>
  <span style="color:var(--border)">|</span>
  <span style="font-size:.88rem;color:var(--text-mid)">
    <i class="bi <?=$schoolIcons[$courseProgram['school']]??'bi-book'?> me-1" style="color:var(--primary)"></i>
    <?=e($courseProgram['school'])?> &rsaquo; <?=e($courseProgram['level'])?>
  </span>
</div>

<div class="admin-card mb-4" style="padding:20px 24px">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h5 style="margin:0;font-size:1.1rem"><?=e($courseProgram['name'])?></h5>
      <div style="font-size:.8rem;color:var(--text-mid);margin-top:3px">
        <?=count($courses)?> course<?=count($courses)!==1?'s':''?> in this program
      </div>
    </div>
  </div>
</div>

<!-- ADD / EDIT COURSE FORM -->
<div class="admin-card mb-4">
  <h6 style="margin-bottom:18px;font-weight:700">
    <i class="bi bi-<?=$editingCourse?'pencil-fill':'plus-circle-fill'?> me-2" style="color:var(--primary)"></i>
    <?=$editingCourse?'Edit Course':'Add Course'?>
  </h6>
  <form method="POST" action="?action=save_course" class="anu-form">
    <input type="hidden" name="program_id" value="<?=$progId?>">
    <input type="hidden" name="course_id"  value="<?=(int)($editingCourse['id']??0)?>">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Year / Semester</label>
        <input type="text" name="year_semester" class="form-control"
               placeholder="e.g. Year 1 Sem 1"
               value="<?=e($editingCourse['year_semester']??'')?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Course Code</label>
        <input type="text" name="code" class="form-control"
               placeholder="e.g. BUS101"
               value="<?=e($editingCourse['code']??'')?>">
      </div>
      <div class="col-md-5">
        <label class="form-label">Course Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" class="form-control" required
               placeholder="e.g. Introduction to Business Management"
               value="<?=e($editingCourse['name']??'')?>">
      </div>
      <div class="col-md-1">
        <label class="form-label">Credits</label>
        <input type="number" name="credit_hours" class="form-control" min="1" max="10"
               value="<?=e($editingCourse['credit_hours']??3)?>">
      </div>
      <div class="col-md-1">
        <label class="form-label">Order</label>
        <input type="number" name="sort_order" class="form-control" min="0"
               value="<?=e($editingCourse['sort_order']??0)?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:10px 28px">
          <i class="bi bi-check-circle me-1"></i>
          <?=$editingCourse?'Save Changes':'Add Course'?>
        </button>
        <?php if($editingCourse): ?>
        <a href="?action=courses&prog_id=<?=$progId?>" class="btn btn-outline-secondary" style="border-radius:50px;padding:10px 20px">Cancel</a>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>

<!-- COURSES TABLE -->
<?php
// Group courses by year_semester
$byGroup = [];
foreach ($courses as $c) {
    $g = $c['year_semester'] ?: 'General';
    $byGroup[$g][] = $c;
}
?>
<?php if (empty($courses)): ?>
<div class="admin-card text-center py-5" style="color:var(--text-mid)">
  <i class="bi bi-journal-x" style="font-size:2.5rem;display:block;margin-bottom:12px;color:#ccc"></i>
  No courses yet. Add the first one above.
</div>
<?php else: ?>
<?php foreach ($byGroup as $group => $grpCourses): ?>
<div class="admin-card mb-3" style="padding:0;overflow:hidden">
  <div class="prog-school-hdr" style="background:rgba(255,0,0,.03)">
    <i class="bi bi-calendar3"></i>
    <span><?=e($group)?></span>
    <em><?=count($grpCourses)?> course<?=count($grpCourses)!==1?'s':''?></em>
  </div>
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr>
          <th>Code</th>
          <th>Course Name</th>
          <th style="text-align:center">Credits</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grpCourses as $c): ?>
        <tr>
          <td style="font-size:.78rem;font-weight:700;color:var(--primary);font-family:monospace">
            <?=e($c['code'])?:'-'?>
          </td>
          <td><strong style="font-size:.88rem"><?=e($c['name'])?></strong></td>
          <td style="text-align:center">
            <span class="badge-status badge-active" style="font-size:.7rem"><?=e($c['credit_hours'])?> cr</span>
          </td>
          <td>
            <div class="d-flex gap-2 justify-content-end">
              <a href="?action=edit_course&id=<?=$c['id']?>&prog_id=<?=$progId?>"
                 class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.75rem;padding:4px 14px">Edit</a>
              <button onclick="confirmDelete('?action=del_course&id=<?=$c['id']?>&prog_id=<?=$progId?>','<?=e(addslashes($c['name']))?>')"
                      class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.75rem;padding:4px 14px">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php // ══════════════ ADD / EDIT PROGRAM FORM ══════════════
elseif (in_array($action, ['edit','new'])): ?>

<div class="mb-3">
  <a href="?action=list" style="color:var(--primary);font-weight:600;font-size:.88rem">
    <i class="bi bi-arrow-left me-1"></i>Back to Programs
  </a>
</div>
<div class="admin-card">
  <h5 style="margin-bottom:24px">
    <i class="bi bi-<?=$editId?'pencil-fill':'plus-circle-fill'?> me-2" style="color:var(--primary)"></i>
    <?=$editId?'Edit Program':'Add New Program'?>
  </h5>
  <form method="POST" enctype="multipart/form-data" class="anu-form">
    <input type="hidden" name="save" value="1">
    <input type="hidden" name="id"   value="<?=$editId?>">
    <div class="row g-3">

      <div class="col-md-4">
        <label class="form-label">School <span style="color:var(--red)">*</span></label>
        <select name="school" class="form-select">
          <?php foreach($schools as $s): ?>
          <option value="<?=$s?>" <?=($editing['school']??'')===$s?'selected':''?>><?=$s?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Level <span style="color:var(--red)">*</span></label>
        <select name="level" class="form-select">
          <?php foreach($levels as $l): ?>
          <option value="<?=$l?>" <?=($editing['level']??'')===$l?'selected':''?>><?=$l?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Duration</label>
        <input type="text" name="duration" class="form-control"
               placeholder="e.g. 3 Years" value="<?=e($editing['duration']??'')?>">
      </div>

      <div class="col-12">
        <label class="form-label">Program Name <span style="color:var(--red)">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?=e($editing['name']??'')?>">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?=e($editing['description']??'')?></textarea>
      </div>
      <div class="col-md-8">
        <label class="form-label">Entry Requirements</label>
        <textarea name="entry_requirements" class="form-control" rows="2"
                  placeholder="e.g. KCSE C+ or equivalent"><?=e($editing['entry_requirements']??'')?></textarea>
      </div>
      <div class="col-md-4 d-flex align-items-end pb-2">
        <div class="form-check">
          <input type="checkbox" name="is_featured" class="form-check-input" id="featChk"
                 <?=($editing['is_featured']??0)?'checked':''?>>
          <label class="form-check-label" for="featChk">
            <i class="bi bi-star-fill me-1" style="color:var(--accent)"></i>Mark as Featured
          </label>
        </div>
      </div>

      <!-- Program image upload -->
      <div class="col-12">
        <label class="form-label">Program Image <span style="color:var(--muted);font-weight:400">(optional — shown on program card)</span></label>
        <div class="prog-img-upload" id="progImgWrap" onclick="document.getElementById('progImgInput').click()">
          <?php if(!empty($editing['image'])): ?>
          <img id="progImgPreview" src="<?=imgUrl($editing['image'])?>" alt="Program image">
          <?php else: ?>
          <div class="prog-img-placeholder" id="progImgPlaceholder">
            <i class="bi bi-image"></i>
            <span>Click to upload image</span>
            <small>JPG, PNG, WEBP — max 5MB</small>
          </div>
          <img id="progImgPreview" alt="New program image preview" style="display:none">
          <?php endif; ?>
          <div class="prog-img-overlay"><i class="bi bi-camera"></i> Change</div>
        </div>
        <input type="file" name="image" id="progImgInput" accept="image/*" style="display:none">
      </div>

      <div class="col-12 pt-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 36px">
          <i class="bi bi-check-circle me-2"></i><?=$editId?'Save Changes':'Add Program'?>
        </button>
        <a href="?action=list" class="btn btn-outline-secondary" style="border-radius:50px;padding:12px 20px">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php // ══════════════ LIST VIEW ══════════════
else: ?>

<!-- SCHOOL TABS -->
<div class="prog-admin-tabs mb-4">
  <a href="?action=list" class="pat-tab <?=!$filterSchool?'active':''?>">
    All <span class="pat-n"><?=array_sum($totals)?></span>
  </a>
  <?php foreach ($schools as $sch):
    $n = $totals[$sch] ?? 0; if(!$n) continue;
  ?>
  <a href="?action=list&school=<?=urlencode($sch)?>" class="pat-tab <?=$filterSchool===$sch?'active':''?>">
    <i class="bi <?=$schoolIcons[$sch]??'bi-book'?>"></i> <?=$sch?> <span class="pat-n"><?=$n?></span>
  </a>
  <?php endforeach; ?>
  <a href="?action=new" class="pat-add ms-auto">
    <i class="bi bi-plus-lg"></i> Add Program
  </a>
</div>

<?php if (empty($items)): ?>
<div class="admin-card text-center py-5">
  <i class="bi bi-mortarboard" style="font-size:3rem;color:#ccc;display:block;margin-bottom:12px"></i>
  <p style="color:var(--muted)">No programs yet. <a href="?action=new" style="color:var(--primary)">Add one now.</a></p>
</div>
<?php else: ?>
<?php foreach ($grouped as $school => $progs): ?>
<div class="admin-card mb-3" style="padding:0;overflow:hidden">
  <div class="prog-school-hdr">
    <i class="bi <?=$schoolIcons[$school]??'bi-book'?>"></i>
    <span><?=e($school)?></span>
    <em><?=count($progs)?> program<?=count($progs)!==1?'s':''?></em>
  </div>
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr>
          <th>Program</th>
          <th>Level</th>
          <th>Duration</th>
          <th style="text-align:center">Courses</th>
          <th style="text-align:center">Featured</th>
          <th style="text-align:center">Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($progs as $p):
          // count courses
          $nCourses = $db->prepare("SELECT COUNT(*) FROM program_courses WHERE program_id=?");
          $nCourses->execute([$p['id']]); $nCourses = (int)$nCourses->fetchColumn();
        ?>
        <tr>
          <td>
            <strong style="color:var(--dark);font-size:.88rem"><?=e($p['name'])?></strong>
            <?php if($p['description']): ?>
            <div style="font-size:.73rem;color:var(--muted);margin-top:2px"><?=e(mb_strimwidth($p['description'],0,60,'…'))?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge-status badge-active" style="font-size:.68rem"><?=e($p['level'])?></span></td>
          <td style="color:var(--muted);font-size:.8rem"><?=e($p['duration'])?:'-'?></td>
          <td style="text-align:center">
            <a href="?action=courses&prog_id=<?=$p['id']?>" class="courses-badge" title="Manage courses">
              <?=$nCourses?> <i class="bi bi-journal-text"></i>
            </a>
          </td>
          <td style="text-align:center">
            <a href="?action=feature&id=<?=$p['id']?>" title="Toggle featured">
              <?=$p['is_featured']
                ? '<i class="bi bi-star-fill" style="color:var(--accent);font-size:1rem"></i>'
                : '<i class="bi bi-star" style="color:#ccc;font-size:1rem"></i>'?>
            </a>
          </td>
          <td style="text-align:center">
            <a href="?action=toggle&id=<?=$p['id']?>">
              <span class="badge-status <?=$p['is_active']?'badge-active':'badge-inactive'?>" style="font-size:.68rem">
                <?=$p['is_active']?'Active':'Hidden'?>
              </span>
            </a>
          </td>
          <td>
            <div class="d-flex gap-2 justify-content-end">
              <a href="?action=courses&prog_id=<?=$p['id']?>"
                 class="btn btn-sm" style="border-radius:50px;font-size:.73rem;padding:4px 12px;border:1.5px solid var(--primary);color:var(--primary)">
                <i class="bi bi-journal-plus me-1"></i>Courses
              </a>
              <a href="?action=edit&id=<?=$p['id']?>"
                 class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.73rem;padding:4px 12px">Edit</a>
              <button onclick="confirmDelete('?action=delete&id=<?=$p['id']?>','<?=e(addslashes($p['name']))?>')"
                      class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:.73rem;padding:4px 12px">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

</div><!-- /.admin-body -->

<script>
// Image preview for program form
var inp = document.getElementById('progImgInput');
if(inp) inp.addEventListener('change', function(){
  if(!this.files[0]) return;
  var r = new FileReader();
  r.onload = function(e){
    var ph = document.getElementById('progImgPlaceholder');
    var pr = document.getElementById('progImgPreview');
    if(ph) ph.style.display = 'none';
    pr.src = e.target.result;
    pr.style.display = 'block';
  };
  r.readAsDataURL(this.files[0]);
});
</script>

<?php adminFoot(); ?>
