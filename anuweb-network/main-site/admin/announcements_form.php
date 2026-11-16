<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/../includes/announcements_lib.php';
anu_ann_ensure();
$__reg = include __DIR__ . '/includes/sites_registry.php';
$__targets = ['main' => 'Main website only', 'all' => 'ALL sites (main website and every unit site)'];
foreach ($__reg as $__k => $__v) { if ($__k !== 'orientation-pack') $__targets[$__k] = $__v['label'] . ' site only'; }
current_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$a = ['title'=>'','message'=>'','image'=>null,'cta_text'=>'Apply Now','cta_url'=>'','starts_at'=>'','ends_at'=>'','is_active'=>0,'target'=>'main'];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) {
        $found['starts_at'] = $found['starts_at'] ? date('Y-m-d\TH:i', strtotime($found['starts_at'])) : '';
        $found['ends_at'] = $found['ends_at'] ? date('Y-m-d\TH:i', strtotime($found['ends_at'])) : '';
        $a = $found;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $a['title']    = trim($_POST['title'] ?? '');
        $a['message']  = trim($_POST['message'] ?? '');
        $a['cta_text'] = trim($_POST['cta_text'] ?? '');
        $a['cta_url']  = trim($_POST['cta_url'] ?? '');
        $a['starts_at']= $_POST['starts_at'] ?? '';
        $a['ends_at']  = $_POST['ends_at'] ?? '';
        $a['is_active']= isset($_POST['is_active']) ? 1 : 0;
        $a['target']   = isset($__targets[$_POST['target'] ?? '']) ? $_POST['target'] : 'main';

        if ($a['title'] === '' || $a['message'] === '') $errors[] = 'Title and message are required.';

        try {
            $uploaded = handle_image_upload('image');
            if ($uploaded) $a['image'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            $starts = $a['starts_at'] ?: null;
            $ends = $a['ends_at'] ?: null;
            if ($id) {
                $stmt = db()->prepare('UPDATE announcements SET title=?, message=?, image=?, cta_text=?, cta_url=?, starts_at=?, ends_at=?, is_active=?, target=? WHERE id=?');
                $stmt->execute([$a['title'],$a['message'],$a['image'],$a['cta_text'],$a['cta_url'],$starts,$ends,$a['is_active'],$a['target'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO announcements (title, message, image, cta_text, cta_url, starts_at, ends_at, is_active, target) VALUES (?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$a['title'],$a['message'],$a['image'],$a['cta_text'],$a['cta_url'],$starts,$ends,$a['is_active'],$a['target']]);
            }
            redirect('/admin/announcements.php');
        }
    }
}

$pageTitle = $id ? 'Edit Announcement' : 'New Announcement';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= h($a['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Show this announcement on</label>
      <select name="target" class="form-select">
        <?php foreach ($__targets as $tk => $tl): ?><option value="<?= h($tk) ?>" <?= ($a['target'] ?? 'main') === $tk ? 'selected' : '' ?>><?= h($tl) ?></option><?php endforeach; ?>
      </select>
      <div class="form-text">Each site's own admin can also set a separate announcement for their site. If a site has its own active announcement, it is shown instead of the "ALL sites" one. For "ALL sites", give the button link as a full address (https://...).</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Image (optional, shown at the top of the popup)</label>
      <?php if (!empty($a['image'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($a['image']) ?>" class="img-preview-sm" class="rounded border"></div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Message</label>
      <textarea name="message" rows="4" class="form-control" required><?= h($a['message']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Button text</label>
        <input type="text" name="cta_text" class="form-control" value="<?= h($a['cta_text']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Button link</label>
        <input type="text" name="cta_url" class="form-control" value="<?= h($a['cta_url']) ?>" placeholder="/admissions.php or full URL">
      </div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label">Show from (optional)</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="<?= h($a['starts_at']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Show until (optional)</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="<?= h($a['ends_at']) ?>">
      </div>
    </div>
    <div class="form-check mb-3 mt-3">
      <input type="checkbox" name="is_active" id="act" class="form-check-input" <?= $a['is_active'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="act">Active (visible on the site now)</label>
    </div>
    <button class="btn btn-apply" type="submit">Save Announcement</button>
    <a href="<?= BASE_URL ?>/admin/announcements.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
