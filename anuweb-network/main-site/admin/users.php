<?php
require_once __DIR__ . '/includes/auth.php';
require_super_admin();

$pageTitle = 'Admin Users';
require __DIR__ . '/includes/admin_header.php';
$rows = db()->query('SELECT * FROM admin_users ORDER BY full_name')->fetchAll();
$me = current_admin();
?>
<p class="text-muted small">Only Super Admins can see this page and manage other admin accounts. Roles: <strong>Super Admin</strong> (full access, including managing other admins), <strong>Editor</strong> (content only, news, events, programmes, staff), <strong>Marketing</strong> (announcements, resources, messages).</p>
<div class="d-flex justify-content-end mb-3">
  <a href="<?= BASE_URL ?>/admin/users_form.php" class="btn btn-apply">+ New Admin User</a>
</div>
<div class="card-anu">
  <div class="body p-0">
    <table class="table table-anu mb-0">
      <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= h($r['full_name']) ?><?php if ((int)$r['id'] === (int)$me['id']): ?> <span class="badge bg-secondary">You</span><?php endif; ?></td>
          <td><?= h($r['username']) ?></td>
          <td><?= h($r['email']) ?></td>
          <td><span class="badge bg-secondary"><?= h($r['role']) ?></span></td>
          <td><?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Deactivated</span>' ?></td>
          <td class="small text-muted"><?= $r['last_login'] ? date('d M Y, g:i A', strtotime($r['last_login'])) : 'Never' ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/admin/users_form.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-anu">Edit</a>
            <?php if ((int)$r['id'] !== (int)$me['id']): ?>
              <a href="<?= BASE_URL ?>/admin/users_delete.php?id=<?= (int)$r['id'] ?>&amp;t=<?= urlencode(delete_token()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this admin account?');">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
