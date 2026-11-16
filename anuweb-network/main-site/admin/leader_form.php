<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','super_admin']);
require_once __DIR__ . '/includes/upload.php';
current_admin();

$sites = include __DIR__ . '/includes/sites_registry.php';
$unit  = $_GET['unit'] ?? '';

// Find which site this leader slot belongs to -- the slot is fixed by the
// link you clicked, never chosen from a list, so there is nothing to mix up.
$site = null; $siteSlug = null;
foreach ($sites as $slug => $s) {
    if (($s['leader_slug'] ?? null) === $unit) { $site = $s; $siteSlug = $slug; break; }
}
if (!$site) {
    redirect('/admin/unit_sites.php');
}

$stmt = db()->prepare('SELECT * FROM staff WHERE unit_slug = ? LIMIT 1');
$stmt->execute([$unit]);
$existing = $stmt->fetch();
$id = $existing['id'] ?? 0;
$p = $existing ?: ['unit_slug'=>$unit,'full_name'=>'','title'=>$site['leader_label'],'bio'=>'','email'=>'','photo'=>null,'sort_order'=>0,'is_published'=>1];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $p['full_name']    = trim($_POST['full_name'] ?? '');
        $p['title']        = trim($_POST['title'] ?? $site['leader_label']);
        $p['bio']          = trim($_POST['bio'] ?? '');
        $p['email']        = trim($_POST['email'] ?? '');
        $p['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        try {
            $uploaded = handle_image_upload('photo');
            if ($uploaded) $p['photo'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (!$errors) {
            if ($id) {
                $stmt = db()->prepare('UPDATE staff SET full_name=?, title=?, bio=?, email=?, photo=?, is_published=? WHERE id=?');
                $stmt->execute([$p['full_name'],$p['title'],$p['bio'],$p['email'],$p['photo'],$p['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO staff (unit_slug, full_name, title, bio, email, photo, sort_order, is_published) VALUES (?,?,?,?,?,?,0,?)');
                $stmt->execute([$unit,$p['full_name'],$p['title'],$p['bio'],$p['email'],$p['photo'],$p['is_published']]);
            }
            redirect('/admin/site.php?site=' . urlencode($siteSlug));
        }
    }
}

$pageTitle = $site['label'] . '-' . $site['leader_label'];
require __DIR__ . '/includes/admin_header.php';
?>
<nav class="small text-muted mb-3">
  <a href="<?= BASE_URL ?>/admin/unit_sites.php">Sites</a> &rsaquo;
  <a href="<?= BASE_URL ?>/admin/site.php?site=<?= h($siteSlug) ?>"><?= h($site['label']) ?></a> &rsaquo;
  <?= h($site['leader_label']) ?>
</nav>
<p class="text-muted small">This is the one featured photo-and-message shown on the <?= h($site['label']) ?> About page as "<?= h($site['leader_label']) ?>". It's a single, fixed slot for this site, not a staff listing.</p>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Full name</label>
        <input type="text" name="full_name" class="form-control" value="<?= h($p['full_name']) ?>" placeholder="Leave blank to hide this message until a name is added">
      </div>
      <div class="col-md-6">
        <label class="form-label">Title / role</label>
        <input type="text" name="title" class="form-control" value="<?= h($p['title']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Photo</label>
      <?php if (!empty($p['photo'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($p['photo']) ?>" class="img-preview-circle rounded border"></div>
      <?php endif; ?>
      <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
    </div>
    <div class="mb-3">
      <label class="form-label">Message / bio</label>
      <textarea name="bio" rows="5" class="form-control"><?= h($p['bio']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Email (optional)</label>
      <input type="email" name="email" class="form-control" value="<?= h($p['email']) ?>">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $p['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/site.php?site=<?= h($siteSlug) ?>" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
