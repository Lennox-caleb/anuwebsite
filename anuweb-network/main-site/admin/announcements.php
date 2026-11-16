<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$pageTitle = 'Announcements';
require __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/announcements_lib.php'; anu_ann_ensure();
$__reg = include __DIR__ . '/includes/sites_registry.php';
$rows = db()->query('SELECT * FROM announcements ORDER BY id DESC')->fetchAll();
?>
<p class="text-muted small">Each site shows one active announcement at a time (the most recent; a site's own announcement comes before an \"ALL sites\" one). Choose where each one appears with \"Show this announcement on\". Use start/end dates to schedule it automatically, or toggle Active on/off manually.</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/announcements_form.php" class="btn btn-apply">+ New Announcement</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Title</th><th>Shown on</th><th>Window</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['title']) ?></td>
          <td class="small"><?php $t = $r['target'] ?? 'main'; echo h($t === 'all' ? 'All sites' : ($t === 'main' ? 'Main website' : ($__reg[$t]['label'] ?? $t))); ?></td>
          <td class="small text-muted">
            <?= $r['starts_at'] ? date('d M Y', strtotime($r['starts_at'])) : 'Any time' ?>
            &rarr;
            <?= $r['ends_at'] ? date('d M Y', strtotime($r['ends_at'])) : 'Until turned off' ?>
          </td>
          <td><?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Off</span>' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/announcements_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <a href="<?= BASE_URL ?>/admin/announcements_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this announcement?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No announcements yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
