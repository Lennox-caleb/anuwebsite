<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db  = getDB();
$msg = $msgType = '';

// Ensure hero_slides table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `hero_slides` (
        `id` int AUTO_INCREMENT PRIMARY KEY,
        `page_slug` varchar(100) NOT NULL DEFAULT 'home',
        `title` varchar(255) DEFAULT '',
        `subtitle` text,
        `cta_text` varchar(100) DEFAULT '',
        `cta_link` varchar(255) DEFAULT '',
        `cta2_text` varchar(100) DEFAULT '',
        `cta2_link` varchar(255) DEFAULT '',
        `bg_image` varchar(500) DEFAULT '',
        `overlay_color` varchar(50) DEFAULT 'rgba(10,20,60,0.70)',
        `badge_text` varchar(150) DEFAULT '',
        `sort_order` int DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(Exception $e) {}

$pages = [
    'home'=>'Home Page','programs'=>'Programs','campus-life'=>'Campus Life',
    'contact'=>'Contact','chaplaincy'=>'Chaplaincy','clubs'=>'Clubs',
    'news'=>'News','faq'=>'FAQs',
];

// ── ACTIONS ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    if ($act === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'page_slug'     => $_POST['page_slug']     ?? 'home',
            'title'         => trim($_POST['title']     ?? ''),
            'subtitle'      => trim($_POST['subtitle']  ?? ''),
            'cta_text'      => trim($_POST['cta_text']  ?? ''),
            'cta_link'      => trim($_POST['cta_link']  ?? ''),
            'cta2_text'     => trim($_POST['cta2_text'] ?? ''),
            'cta2_link'     => trim($_POST['cta2_link'] ?? ''),
            'overlay_color' => trim($_POST['overlay_color'] ?? 'rgba(10,20,60,0.70)'),
            'badge_text'    => trim($_POST['badge_text'] ?? ''),
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Image upload — handleUpload returns string path or null
        $upload = handleUpload('bg_image', 'hero');
        if ($upload) {
            $data['bg_image'] = $upload;
        } elseif (!empty($_POST['bg_image_current'])) {
            $data['bg_image'] = $_POST['bg_image_current'];
        } else {
            $data['bg_image'] = '';
        }

        try {
            if ($id > 0) {
                $sets = implode(', ', array_map(fn($k) => "`{$k}`=?", array_keys($data)));
                $db->prepare("UPDATE hero_slides SET {$sets} WHERE id=?")
                   ->execute([...array_values($data), $id]);
                $msg = 'Slide updated! Changes are now live on the website.';
            } else {
                $cols = implode(',', array_map(fn($k) => "`{$k}`", array_keys($data)));
                $phs  = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO hero_slides ({$cols}) VALUES ({$phs})")
                   ->execute(array_values($data));
                $msg = 'New slide added successfully.';
            }
            $msgType = 'success';
            // Redirect to avoid re-submit on refresh
            header('Location: slides.php?saved=1&page_slug=' . urlencode($data['page_slug']));
            exit;
        } catch (Exception $e) {
            $msg = 'Error: ' . $e->getMessage(); $msgType = 'danger';
        }
    }

    if ($act === 'delete') {
        $db->prepare("DELETE FROM hero_slides WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: slides.php?deleted=1'); exit;
    }

    if ($act === 'toggle') {
        $db->prepare("UPDATE hero_slides SET is_active = 1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: ' . $_SERVER['HTTP_REFERER']); exit;
    }
}

// Flash messages from redirect
if (isset($_GET['saved']))   { $msg = 'Slide saved! Changes are live.'; $msgType = 'success'; }
if (isset($_GET['deleted'])) { $msg = 'Slide deleted.'; $msgType = 'success'; }

// Fetch
$filterPage = $_GET['page_slug'] ?? '';
$editId     = (int)($_GET['edit'] ?? 0);
$editSlide  = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM hero_slides WHERE id=?");
    $s->execute([$editId]);
    $editSlide = $s->fetch() ?: null;
}

$stmt = $filterPage
    ? $db->prepare("SELECT * FROM hero_slides WHERE page_slug=? ORDER BY sort_order ASC")
    : $db->prepare("SELECT * FROM hero_slides ORDER BY page_slug ASC, sort_order ASC");
$filterPage ? $stmt->execute([$filterPage]) : $stmt->execute();
$slides = $stmt->fetchAll();

// ── OUTPUT ───────────────────────────────────────────────────
adminHead('Hero Slides');
adminSidebar('slides');
adminTopBar('Hero Slides', 'Manage carousel slides');
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible mb-4" role="alert">
    <i class="bi bi-<?= $msgType==='success'?'check-circle-fill':'exclamation-circle-fill' ?> me-2"></i>
    <?= e($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- FORM -->
  <div class="col-xl-4 col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><?= $editSlide ? '<i class="bi bi-pencil me-2"></i>Edit Slide' : '<i class="bi bi-plus-circle me-2"></i>Add New Slide' ?></h5>
        <?php if ($editSlide): ?>
        <a href="slides.php" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:.75rem">Cancel</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= $editSlide ? $editSlide['id'] : 0 ?>">
          <?php if ($editSlide && $editSlide['bg_image']): ?>
          <input type="hidden" name="bg_image_current" value="<?= e($editSlide['bg_image']) ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Page <span style="color:#cc0000">*</span></label>
            <select name="page_slug" class="form-select">
              <?php foreach ($pages as $slug => $label): ?>
              <option value="<?= $slug ?>" <?= ($editSlide ? $editSlide['page_slug'] : 'home') === $slug ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Title <span style="color:#cc0000">*</span></label>
            <input type="text" name="title" class="form-control" required placeholder="e.g. Transform Your Future" value="<?= e($editSlide['title'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Subtitle</label>
            <textarea name="subtitle" class="form-control" rows="2" placeholder="Short description shown below the title"><?= e($editSlide['subtitle'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Badge Text <span style="color:#888;font-weight:400;font-size:.75rem">(small pill above title)</span></label>
            <input type="text" name="badge_text" class="form-control" placeholder="e.g. Accredited · Faith-Centered" value="<?= e($editSlide['badge_text'] ?? '') ?>">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Button 1 Text</label>
              <input type="text" name="cta_text" class="form-control" placeholder="Explore Programs" value="<?= e($editSlide['cta_text'] ?? '') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Button 1 Link</label>
              <input type="text" name="cta_link" class="form-control" placeholder="/anu/programs.php" value="<?= e($editSlide['cta_link'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Button 2 Text</label>
              <input type="text" name="cta2_text" class="form-control" placeholder="Apply Now" value="<?= e($editSlide['cta2_text'] ?? '') ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Button 2 Link</label>
              <input type="text" name="cta2_link" class="form-control" placeholder="/anu/contact.php" value="<?= e($editSlide['cta2_link'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Background Image</label>
            <?php if ($editSlide && !empty($editSlide['bg_image'])): ?>
            <img src="<?= imgUrl($editSlide['bg_image']) ?>" class="img-preview mb-2" alt="Current image" onerror="this.style.display='none'">
            <?php endif; ?>
            <input type="file" name="bg_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="form-text">JPG/PNG/WebP up to 10MB. Leave blank to keep existing.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Overlay Color</label>
            <input type="text" name="overlay_color" class="form-control" placeholder="rgba(10,20,60,0.70)" value="<?= e($editSlide['overlay_color'] ?? 'rgba(10,20,60,0.70)') ?>">
            <div class="form-text">Dark overlay on top of image. Use rgba() format.</div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= (int)($editSlide['sort_order'] ?? 0) ?>" min="0">
            </div>
            <div class="col-6 d-flex align-items-end pb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= (!$editSlide || $editSlide['is_active']) ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="isActive">Active</label>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-danger w-100" style="padding:11px">
            <i class="bi bi-<?= $editSlide ? 'check-circle' : 'plus-circle' ?> me-2"></i>
            <?= $editSlide ? 'Update Slide' : 'Add Slide' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="col-xl-8 col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="bi bi-images me-2"></i>Slides (<?= count($slides) ?>)</h5>
        <select class="form-select form-select-sm" style="width:auto" onchange="location='slides.php?page_slug='+this.value">
          <option value="">All Pages</option>
          <?php foreach ($pages as $slug => $label): ?>
          <option value="<?= $slug ?>" <?= $filterPage === $slug ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="admin-card-body p-0">
        <?php if (empty($slides)): ?>
        <div class="text-center py-5" style="color:#bbb">
          <i class="bi bi-collection" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3"></i>
          No slides yet. Use the form to add your first slide.
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th style="width:90px">Image</th>
                <th>Title / Page</th>
                <th style="width:60px">Order</th>
                <th style="width:80px">Status</th>
                <th style="width:90px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($slides as $sl): ?>
              <tr>
                <td>
                  <?php if (!empty($sl['bg_image'])): ?>
                  <img src="<?= imgUrl($sl['bg_image']) ?>" alt="<?= e($sl['title'] ?? 'Slide background') ?>" style="width:80px;height:50px;object-fit:cover;border-radius:7px" onerror="this.style.display='none'">
                  <?php else: ?>
                  <div style="width:80px;height:50px;border-radius:7px;background:linear-gradient(135deg,#0a1440,#cc0000);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-image" style="color:rgba(255,255,255,.4)"></i>
                  </div>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="font-weight:600;font-size:.87rem;color:#1e1e2e"><?= e(mb_strimwidth($sl['title'], 0, 48, '…')) ?></div>
                  <div style="font-size:.75rem;color:#aaa;margin-top:2px">
                    <i class="bi bi-file-earmark me-1"></i><?= e($pages[$sl['page_slug']] ?? $sl['page_slug']) ?>
                    <?php if ($sl['badge_text']): ?><span style="margin-left:6px;color:#cc0000">· <?= e($sl['badge_text']) ?></span><?php endif; ?>
                  </div>
                </td>
                <td style="font-size:.87rem;color:#888"><?= (int)$sl['sort_order'] ?></td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $sl['id'] ?>">
                    <button type="submit" class="anu-badge <?= $sl['is_active'] ? 'badge-success' : 'badge-muted' ?>" style="border:none;cursor:pointer;padding:4px 10px">
                      <?= $sl['is_active'] ? 'Active' : 'Off' ?>
                    </button>
                  </form>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="slides.php?edit=<?= $sl['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form method="POST" onsubmit="return confirm('Delete this slide?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $sl['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="alert alert-info mt-3" style="font-size:.82rem;border-radius:10px">
      <i class="bi bi-lightbulb-fill me-2"></i>
      <strong>How it works:</strong> Slides show as a rotating carousel on each page. Add multiple slides per page and use <strong>Sort Order</strong> (0 = first) to control the sequence. Toggle <strong>Active/Off</strong> to hide a slide without deleting it. Changes go live instantly.
    </div>
  </div>

</div>

<?php adminFoot(); ?>
