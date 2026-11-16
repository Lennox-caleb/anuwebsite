<?php
$pageTitle = 'Page Banners';
require_once __DIR__ . '/includes/auth.php';
require_login();

$pages = [
    'about'       => 'About',
    'leadership'  => 'Leadership Message',
    'programmes'  => 'Programmes',
    'admissions'  => 'Admissions',
    'apply'       => 'Apply Online',
    'board'       => 'Board & Trainers',
    'news'        => 'News',
    'events'      => 'Events',
    'downloads'   => 'Downloads',
    'gallery'     => 'Gallery',
    'faqs'        => 'FAQs',
    'contact'     => 'Contact',
];

// Make sure the table exists even if this DB was created before this feature shipped.
db()->exec("CREATE TABLE IF NOT EXISTS page_banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(60) NOT NULL UNIQUE,
    image VARCHAR(255) NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'save_banner') {
    if (!csrf_verify()) {
        flash_set('danger', 'Session expired, please try again.');
        redirect(ADMIN_URL . '/banners.php');
    }
    $pageKey = $_POST['page_key'] ?? '';
    if (!isset($pages[$pageKey])) {
        flash_set('danger', 'Unknown page.');
        redirect(ADMIN_URL . '/banners.php');
    }
    try {
        $uploaded = handle_upload('image', 'site');
        if ($uploaded !== null) {
            $stmt = db()->prepare('INSERT INTO page_banners (page_key, image) VALUES (?,?) ON DUPLICATE KEY UPDATE image = VALUES(image)');
            $stmt->execute([$pageKey, $uploaded]);
            flash_set('success', $pages[$pageKey] . ' banner updated.');
        } else {
            flash_set('danger', 'Please choose an image to upload.');
        }
    } catch (Throwable $e) {
        flash_set('danger', 'Could not save banner: ' . $e->getMessage());
    }
    redirect(ADMIN_URL . '/banners.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'remove_banner') {
    if (csrf_verify()) {
        db()->prepare('DELETE FROM page_banners WHERE page_key = ?')->execute([$_POST['page_key'] ?? '']);
        flash_set('success', 'Banner removed -- that page will show its default plain header again.');
    }
    redirect(ADMIN_URL . '/banners.php');
}

$current = [];
foreach (db()->query('SELECT page_key, image FROM page_banners') as $row) {
    $current[$row['page_key']] = $row['image'];
}

require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-card mb-4">
  <p class="text-muted mb-0">Upload a background photo for the header of any page below (the same look the Homepage hero already has). Leave a page without a banner to keep its plain title-only header.</p>
</div>

<div class="row g-4">
  <?php foreach ($pages as $key => $label): ?>
    <div class="col-md-6 col-lg-4">
      <div class="admin-card h-100">
        <div class="admin-card-header"><h2><?= h($label) ?></h2></div>
        <?php if (!empty($current[$key])): ?>
          <img src="<?= h(upload_url('site', $current[$key])) ?>" class="img-fluid rounded mb-3" style="height:120px; width:100%; object-fit:cover;" alt="">
        <?php else: ?>
          <div class="mb-3 rounded d-flex align-items-center justify-content-center text-muted small" style="height:120px; background:#f1f1f1;">No banner set</div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="save_banner">
          <input type="hidden" name="page_key" value="<?= h($key) ?>">
          <input type="file" name="image" accept="image/*" class="form-control form-control-sm" required>
          <button type="submit" class="btn btn-sm btn-admin-primary text-nowrap"><i class="bi bi-upload"></i></button>
        </form>
        <?php if (!empty($current[$key])): ?>
          <form method="post" class="mt-2" onsubmit="return confirm('Remove this banner?');">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="remove_banner">
            <input type="hidden" name="page_key" value="<?= h($key) ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Remove Banner</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
