<?php
// Item 9c (phase upgrade): "News & Events" as one admin screen (tabs),
// matching the merged public-facing homepage section. Reuses the exact
// same list queries as the original admin/news.php and admin/events.php,
// and every Edit/Delete/Add link points straight at those two files --
// their working add/edit/delete logic is untouched and fully reused, so
// nothing about how News or Events are actually saved changes at all.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();
$db = getDB();
$tab = $_GET['tab'] ?? 'news';

$newsList = $db->query("SELECT * FROM news ORDER BY is_featured DESC, published_at DESC")->fetchAll();
$eventsList = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();

adminHead('News & Events');
adminSidebar('news-events');
?>
<?php adminTopBar('News & Events', 'Merged with the homepage "News & Events" section'); ?>

<div class="ne-tabs mb-4" role="tablist" aria-label="News or Events">
  <a href="?tab=news" class="ne-tab<?= $tab==='news'?' active':'' ?>" role="tab" aria-selected="<?= $tab==='news'?'true':'false' ?>">News (<?= count($newsList) ?>)</a>
  <a href="?tab=events" class="ne-tab<?= $tab==='events'?' active':'' ?>" role="tab" aria-selected="<?= $tab==='events'?'true':'false' ?>">Events (<?= count($eventsList) ?>)</a>
</div>

<?php if ($tab === 'news'): ?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-newspaper me-2" style="color:var(--primary);"></i>News Stories</h5>
    <a href="/anu/admin/news.php?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus-circle me-1"></i>Add Story</a>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($newsList as $n): ?>
        <tr>
          <td style="width:60px;">
            <img src="<?= $n['featured_image'] ? '/'.$n['featured_image'] : '/anu/assets/images/default-news.jpg' ?>"
                 alt="<?= e($n['title']) ?>" style="width:50px;height:40px;object-fit:cover;border-radius:8px;" onerror="this.src='/anu/assets/images/default-news.jpg'">
          </td>
          <td><strong><?= e(substr($n['title'],0,50)) ?><?= strlen($n['title'])>50?'...':'' ?></strong></td>
          <td><?= e($n['category']) ?></td>
          <td>
            <a href="/anu/admin/news.php?action=toggle&id=<?= $n['id'] ?>" title="Toggle">
              <span class="badge-status <?= $n['is_published'] ? 'badge-active' : 'badge-inactive' ?>"><?= $n['is_published'] ? 'Published' : 'Draft' ?></span>
            </a>
          </td>
          <td style="font-size:0.82rem;"><?= formatDate($n['published_at'], 'M d, Y') ?></td>
          <td>
            <div class="d-flex gap-2">
              <a href="/anu/admin/news.php?action=edit&id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
              <a href="/anu/news.php?id=<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:50px;font-size:0.78rem;">View</a>
              <button onclick="confirmDelete('/anu/admin/news.php?action=delete&id=<?= $n['id'] ?>', '<?= e(addslashes($n['title'])) ?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$newsList): ?><tr><td colspan="6" class="admin-empty">No news stories yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h5 style="margin:0;"><i class="bi bi-calendar-event-fill me-2" style="color:var(--primary);"></i>Events</h5>
    <a href="/anu/admin/events.php?action=new" class="btn btn-primary-anu btn-sm" style="border-radius:50px;padding:8px 20px;"><i class="bi bi-plus me-1"></i>Add Event</a>
  </div>
  <div class="table-responsive">
    <table class="table admin-table">
      <thead><tr><th>Title</th><th>Date</th><th>Location</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($eventsList as $ev): $isPast=strtotime($ev['event_date'])<time(); $isLive = date('Y-m-d')===$ev['event_date']; ?>
        <tr style="<?=$isPast?'opacity:0.65':''?>">
          <td><strong><?=e($ev['title'])?></strong> <?php if($isLive):?><span class="ne-live-tag" style="position:static;display:inline-flex;margin-left:6px">Live</span><?php endif;?></td>
          <td><?=formatDate($ev['event_date'],'M d, Y')?><?=$isPast?' <span style="color:#bbb;font-size:0.75rem;">(past)</span>':''?></td>
          <td><?=e($ev['location'])?></td>
          <td><span class="event-type-badge"><?=e($ev['type'])?></span></td>
          <td><a href="/anu/admin/events.php?action=toggle&id=<?=$ev['id']?>"><span class="badge-status <?=$ev['is_active']?'badge-active':'badge-inactive'?>"><?=$ev['is_active']?'Active':'Hidden'?></span></a></td>
          <td><div class="d-flex gap-2">
            <a href="/anu/admin/events.php?action=edit&id=<?=$ev['id']?>" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;font-size:0.78rem;">Edit</a>
            <button onclick="confirmDelete('/anu/admin/events.php?action=delete&id=<?=$ev['id']?>','<?=e(addslashes($ev['title']))?>')" class="btn btn-sm btn-outline-danger" style="border-radius:50px;font-size:0.78rem;">Delete</button>
          </div></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$eventsList): ?><tr><td colspan="6" class="admin-empty">No events yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php adminFoot(); ?>
