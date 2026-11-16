<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/crud-config.php';

$entities = crud_entities();
$entityKey = $_GET['entity'] ?? '';
if (!isset($entities[$entityKey])) {
    flash_set('danger', 'Unknown content type.');
    redirect(ADMIN_URL . '/index.php');
}
$cfg = $entities[$entityKey];
$table = $cfg['table'];
$fields = $cfg['fields'];
$pageTitle = $cfg['label'];
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

/* ---------------------------------------------------------------------
 * Handle POST: create / update
 * ------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'crud') {
    if (!csrf_verify()) {
        flash_set('danger', 'Session expired, please try again.');
        redirect(ADMIN_URL . "/crud.php?entity=$entityKey");
    }
    $postId = (int)($_POST['id'] ?? 0);
    $cols = [];
    $vals = [];
    try {
        foreach ($fields as $name => $def) {
            $type = $def['type'];
            if ($type === 'image' || $type === 'file') {
                $allowed = $def['allowed'] ?? ['jpg','jpeg','png','webp','gif'];
                $uploaded = handle_upload($name, $cfg['upload_folder'] ?? $entityKey, $allowed);
                if ($uploaded !== null) {
                    $cols[] = $name;
                    $vals[$name] = $uploaded;
                }
                continue; // only write column if a new file was uploaded
            }
            if ($type === 'checkbox') {
                $cols[] = $name;
                $vals[$name] = isset($_POST[$name]) ? 1 : 0;
                continue;
            }
            $value = trim($_POST[$name] ?? '');
            if ($type === 'number') $value = $value === '' ? null : (int)$value;
            if (!empty($def['auto_slug_from'])) {
                $sourceVal = trim($_POST[$def['auto_slug_from']] ?? '');
                $value = $value !== '' ? slugify($value) : slugify($sourceVal);
            }
            if ($type === 'datetime-local' && $value !== '') {
                $value = str_replace('T', ' ', $value) . ':00';
            }
            $cols[] = $name;
            $vals[$name] = $value === '' ? null : $value;
        }

        if ($postId > 0) {
            $setSql = implode(', ', array_map(fn($c) => "`$c` = :$c", $cols));
            $stmt = db()->prepare("UPDATE `$table` SET $setSql WHERE id = :id");
            $vals['id'] = $postId;
            $stmt->execute($vals);
            flash_set('success', $cfg['label'] . ' updated successfully.');
        } else {
            $colSql = implode(', ', array_map(fn($c) => "`$c`", $cols));
            $placeholderSql = implode(', ', array_map(fn($c) => ":$c", $cols));
            $stmt = db()->prepare("INSERT INTO `$table` ($colSql) VALUES ($placeholderSql)");
            $stmt->execute($vals);
            flash_set('success', $cfg['label'] . ' created successfully.');
        }
    } catch (Throwable $e) {
        flash_set('danger', 'Could not save: ' . $e->getMessage());
        redirect(ADMIN_URL . "/crud.php?entity=$entityKey&action=" . ($postId ? "edit&id=$postId" : 'new'));
    }
    redirect(ADMIN_URL . "/crud.php?entity=$entityKey");
}

/* ---------------------------------------------------------------------
 * Handle delete
 * ------------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $delId = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM `$table` WHERE id = ?")->execute([$delId]);
        flash_set('success', $cfg['label'] . ' deleted.');
    }
    redirect(ADMIN_URL . "/crud.php?entity=$entityKey");
}

/* ---------------------------------------------------------------------
 * Load record for edit
 * ------------------------------------------------------------------- */
$record = [];
if ($action === 'edit' && $id > 0) {
    $stmt = db()->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    $record = $stmt->fetch() ?: [];
    if (!$record) { flash_set('danger', 'Record not found.'); redirect(ADMIN_URL . "/crud.php?entity=$entityKey"); }
}

require __DIR__ . '/includes/admin-header.php';
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= $action === 'edit' ? 'Edit' : 'Add New' ?> <?= h(rtrim($cfg['label'], 's')) ?></h2>
      <a href="crud.php?entity=<?= h($entityKey) ?>" class="btn btn-sm btn-outline-secondary">&larr; Back to list</a>
    </div>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="form" value="crud">
      <input type="hidden" name="id" value="<?= (int)($record['id'] ?? 0) ?>">
      <div class="row g-3">
        <?php foreach ($fields as $name => $def):
          $value = $record[$name] ?? ($def['default'] ?? '');
          if (($def['default_now'] ?? false) && !$value) $value = date('Y-m-d\TH:i');
          if ($def['type'] === 'datetime-local' && $value && strpos($value, 'T') === false) $value = str_replace(' ', 'T', substr($value, 0, 16));
        ?>
          <?php if ($def['type'] === 'checkbox'): ?>
            <div class="col-12">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="f_<?= h($name) ?>" name="<?= h($name) ?>" value="1" <?= $value ? 'checked' : '' ?>>
                <label class="form-check-label" for="f_<?= h($name) ?>"><?= h($def['label']) ?></label>
              </div>
            </div>
          <?php elseif ($def['type'] === 'textarea'): ?>
            <div class="col-12">
              <label class="form-label"><?= h($def['label']) ?><?= !empty($def['required']) ? ' *' : '' ?></label>
              <textarea name="<?= h($name) ?>" class="form-control" rows="<?= (int)($def['rows'] ?? 4) ?>" <?= !empty($def['required']) ? 'required' : '' ?>><?= h((string)$value) ?></textarea>
              <?php if (!empty($def['help'])): ?><div class="form-text"><?= h($def['help']) ?></div><?php endif; ?>
            </div>
          <?php elseif ($def['type'] === 'select'): ?>
            <div class="col-md-6">
              <label class="form-label"><?= h($def['label']) ?></label>
              <select name="<?= h($name) ?>" class="form-select">
                <?php foreach ($def['options'] as $optVal => $optLabel): ?>
                  <option value="<?= h($optVal) ?>" <?= (string)$value === (string)$optVal ? 'selected' : '' ?>><?= h($optLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php elseif ($def['type'] === 'image'): ?>
            <div class="col-md-6">
              <label class="form-label"><?= h($def['label']) ?><?= !empty($def['required']) && !$value ? ' *' : '' ?></label>
              <?php if ($value): ?><div class="mb-2"><img src="<?= h(upload_url($cfg['upload_folder'] ?? $entityKey, $value)) ?>" style="height:70px;border-radius:6px" alt=""></div><?php endif; ?>
              <input type="file" name="<?= h($name) ?>" class="form-control" accept="image/*">
            </div>
          <?php elseif ($def['type'] === 'file'): ?>
            <div class="col-md-6">
              <label class="form-label"><?= h($def['label']) ?></label>
              <?php if ($value): ?><div class="mb-2 small"><i class="bi bi-file-earmark"></i> <?= h($value) ?></div><?php endif; ?>
              <input type="file" name="<?= h($name) ?>" class="form-control">
            </div>
          <?php elseif ($def['type'] === 'number'): ?>
            <div class="col-md-3">
              <label class="form-label"><?= h($def['label']) ?></label>
              <input type="number" name="<?= h($name) ?>" class="form-control" value="<?= h((string)$value) ?>">
            </div>
          <?php elseif ($def['type'] === 'datetime-local'): ?>
            <div class="col-md-4">
              <label class="form-label"><?= h($def['label']) ?><?= !empty($def['required']) ? ' *' : '' ?></label>
              <input type="datetime-local" name="<?= h($name) ?>" class="form-control" value="<?= h((string)$value) ?>" <?= !empty($def['required']) ? 'required' : '' ?>>
            </div>
          <?php elseif (!empty($def['auto_slug_from'])): ?>
            <div class="col-md-6">
              <label class="form-label"><?= h($def['label']) ?></label>
              <input type="text" name="<?= h($name) ?>" class="form-control" value="<?= h((string)$value) ?>" placeholder="auto-generated if left blank">
            </div>
          <?php else: ?>
            <div class="col-md-6">
              <label class="form-label"><?= h($def['label']) ?><?= !empty($def['required']) ? ' *' : '' ?></label>
              <input type="text" name="<?= h($name) ?>" class="form-control" value="<?= h((string)$value) ?>" <?= !empty($def['required']) ? 'required' : '' ?>>
              <?php if (!empty($def['help'])): ?><div class="form-text"><?= h($def['help']) ?></div><?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-admin-primary mt-4"><i class="bi bi-check-circle"></i> Save <?= h(rtrim($cfg['label'], 's')) ?></button>
    </form>
  </div>

<?php else: /* ---------------- LIST VIEW ---------------- */
  $rows = db()->query("SELECT * FROM `$table` ORDER BY {$cfg['order_by']}")->fetchAll();
?>
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= h($cfg['label']) ?> <span class="text-muted small">(<?= count($rows) ?>)</span></h2>
      <a href="crud.php?entity=<?= h($entityKey) ?>&action=new" class="btn btn-admin-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New</a>
    </div>
    <div class="table-responsive">
      <table class="table table-admin table-hover align-middle">
        <thead><tr>
          <?php foreach ($cfg['list_columns'] as $col): ?>
            <th><?= h($fields[$col]['label'] ?? ucfirst(str_replace('_',' ',$col))) ?></th>
          <?php endforeach; ?>
          <th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td colspan="<?= count($cfg['list_columns']) + 1 ?>" class="text-center text-muted py-4">No records yet. Click "Add New" to create one.</td></tr><?php endif; ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($cfg['list_columns'] as $col):
              $def = $fields[$col] ?? null;
              $val = $row[$col] ?? '';
            ?>
              <td>
                <?php if ($def && $def['type'] === 'image'): ?>
                  <?php if ($val): ?><img class="thumb-sm" src="<?= h(upload_url($cfg['upload_folder'] ?? $entityKey, $val)) ?>" alt=""><?php else: ?><span class="text-muted small">-</span><?php endif; ?>
                <?php elseif ($def && $def['type'] === 'file'): ?>
                  <?php if ($val): ?><a href="<?= h(upload_url($cfg['upload_folder'] ?? $entityKey, $val)) ?>" target="_blank"><i class="bi bi-file-earmark-arrow-down"></i></a><?php else: ?>, <?php endif; ?>
                <?php elseif ($def && $def['type'] === 'checkbox'): ?>
                  <?= $val ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?>
                <?php elseif ($col === 'is_published'): ?>
                  <?= $val ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?>
                <?php else: ?>
                  <?= h(is_string($val) ? excerpt($val, 60) : (string)$val) ?>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
            <td class="text-end">
              <a href="crud.php?entity=<?= h($entityKey) ?>&action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this <?= h(strtolower(rtrim($cfg['label'],'s'))) ?>? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="form" value="delete">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
