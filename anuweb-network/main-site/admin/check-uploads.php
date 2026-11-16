<?php
/**
 * One-off diagnostic: lists every image/photo/file filename the database
 * currently expects, and flags any that are missing from assets/uploads
 * on THIS server. Run this after deploying an update if photos have gone
 * missing, to see exactly which ones need re-uploading (rather than
 * guessing). Safe to run any time -- it only reads, never changes anything.
 *
 * Visit: yoursite.com/admin/check-uploads.php  (must be logged into admin)
 * Delete this file once you're done with it.
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['editor', 'marketing', 'super_admin']);

$checks = [
    'sliders'                => ['column' => 'image',        'label' => 'Hero image'],
    'staff'                  => ['column' => 'photo',        'label' => 'Staff photo'],
    'governance_members'     => ['column' => 'photo',        'label' => 'Governance photo'],
    'leadership'             => ['column' => 'photo',        'label' => 'Leadership photo'],
    'schools'                => ['column' => 'image',        'label' => 'School image'],
    'programmes'             => ['column' => 'image',        'label' => 'Programme image'],
    'news'                   => ['column' => 'cover_image',  'label' => 'News cover image'],
    'events'                 => ['column' => 'cover_image',  'label' => 'Event cover image'],
    'announcements'          => ['column' => 'image',        'label' => 'Announcement image'],
    'partners'               => ['column' => 'logo',         'label' => 'Partner logo'],
    'resources'              => ['column' => 'file_name',    'label' => 'Resource file'],
    'research_publications'  => ['column' => 'file_name',    'label' => 'Research publication file'],
];

$missing = [];
$total = 0;

foreach ($checks as $table => $info) {
    $col = $info['column'];
    try {
        $stmt = db()->query("SELECT id, $col FROM $table WHERE $col IS NOT NULL AND $col != ''");
    } catch (PDOException $e) {
        continue; // table/column doesn't exist on this install yet -- skip quietly
    }
    foreach ($stmt->fetchAll() as $row) {
        $total++;
        $path = UPLOADS_PATH . '/' . $row[$col];
        if (!is_file($path)) {
            $missing[] = [
                'table' => $table,
                'label' => $info['label'],
                'id' => $row['id'],
                'filename' => $row[$col],
            ];
        }
    }
}

$pageTitle = 'Upload Check';
require __DIR__ . '/includes/admin_header.php';
?>
<p class="text-muted small">Checked <?= $total ?> image/file references across the site against what's actually
present in <code>assets/uploads/</code> on this server.</p>

<?php if (!$missing): ?>
  <div class="alert alert-success">Everything checks out, every image/file the database expects is present on disk.</div>
<?php else: ?>
  <div class="alert alert-danger">
    <strong><?= count($missing) ?> file(s) are referenced in the database but missing from <code>assets/uploads/</code>.</strong>
    This is almost always caused by a deployment that replaced the whole site folder, including uploads. The fix is
    to re-upload each one below through its normal edit form (the database record is fine, only the file itself is gone).
  </div>
  <div class="card-anu">
    <div class="body p-0">
      <table class="table table-anu mb-0">
        <thead><tr><th>Where</th><th>Record ID</th><th>Missing filename</th></tr></thead>
        <tbody>
        <?php foreach ($missing as $m): ?>
          <tr>
            <td><?= h($m['label']) ?></td>
            <td>#<?= (int)$m['id'] ?></td>
            <td><code><?= h($m['filename']) ?></code></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div class="alert alert-warning mt-4 small">
  <strong>To stop this happening again:</strong> when you (or whoever deploys updates) replace the site's files on
  the server, never overwrite or delete the <code>assets/uploads/</code> folder. Only the PHP/CSS/JS code files
  need to be replaced, that folder holds everything anyone has ever uploaded through the admin panel and
  should be left untouched by every future update.
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
