<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);
$pageTitle = 'Sites';
require __DIR__ . '/includes/admin_header.php';

$sites = include __DIR__ . '/includes/sites_registry.php';

// Group sites in registry order, but keep a stable display order for groups.
$groupOrder = ['Schools', 'Student & Alumni Services', 'Support Units', 'Main Site Pages'];
$grouped = array_fill_keys($groupOrder, []);
foreach ($sites as $slug => $site) {
    $grouped[$site['group']][$slug] = $site;
}
?>
<p class="text-muted small mb-4">Each site below has its own page, hero image, staff, programmes and page text all in one place. (ANU Online and Non-Degree run their own separate admin, not here.)</p>

<?php foreach ($groupOrder as $groupName): ?>
  <?php if (empty($grouped[$groupName])) continue; ?>
  <h2 class="h6 text-muted text-uppercase small mb-3 mt-4"><?= h($groupName) ?></h2>
  <div class="row g-3 mb-2">
    <?php foreach ($grouped[$groupName] as $slug => $site): ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= BASE_URL ?>/admin/site.php?site=<?= h($slug) ?>" class="card-anu site-card d-block h-100 text-decoration-none">
          <div class="body">
            <h3 class="h6 mb-1"><?= h($site['label']) ?></h3>
            <span class="small text-muted">Manage this site &rarr;</span>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<style>
  .site-card{ transition: box-shadow .15s ease, transform .15s ease; }
  .site-card:hover{ box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-1px); }
  .site-card h3{ color: inherit; }
</style>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
