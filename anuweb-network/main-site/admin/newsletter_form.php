<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['marketing','super_admin']);
$admin = current_admin();
require_once __DIR__ . '/../includes/newsletter_template.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$n = ['subject'=>'','preheader'=>'','headline'=>'','body'=>'','button_text'=>'','button_url'=>'','include_news'=>0,'include_events'=>0,'status'=>'draft'];
$errors = [];
if ($id) {
    $st = db()->prepare('SELECT * FROM newsletters WHERE id = ?'); $st->execute([$id]);
    $n = $st->fetch() ?: $n;
    if (($n['status'] ?? 'draft') !== 'draft') { flash_set('danger', 'A newsletter that has been sent cannot be edited. Use Copy to make a new draft from it.'); redirect('/admin/newsletters.php'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $n['subject']      = mb_substr(trim($_POST['subject'] ?? ''), 0, 200);
        $n['preheader']    = mb_substr(trim($_POST['preheader'] ?? ''), 0, 200);
        $n['headline']     = mb_substr(trim($_POST['headline'] ?? ''), 0, 200);
        $cleanBody         = anu_nl_sanitize((string)($_POST['body'] ?? ''));
        $hasContent        = trim(str_replace("\xC2\xA0", ' ', html_entity_decode(strip_tags($cleanBody), ENT_QUOTES, 'UTF-8'))) !== '' || stripos($cleanBody, '<img') !== false;
        $n['body']         = $hasContent ? '<!--rte-->' . $cleanBody : '';
        $n['button_text']  = mb_substr(trim($_POST['button_text'] ?? ''), 0, 80);
        $n['button_url']   = mb_substr(trim($_POST['button_url'] ?? ''), 0, 255);
        $n['include_news']   = isset($_POST['include_news']) ? 1 : 0;
        $n['include_events'] = isset($_POST['include_events']) ? 1 : 0;
        if ($n['subject'] === '' || $n['headline'] === '' || $n['body'] === '') $errors[] = 'Subject, headline and message are required.';
        if (($n['button_text'] === '') !== ($n['button_url'] === '')) $errors[] = 'Fill in both the button text and the button link, or leave both empty.';
        if ($n['button_url'] !== '' && !preg_match('~^https?://~i', $n['button_url'])) $errors[] = 'The button link must start with http:// or https://';
        if (!$errors) {
            if ($id) {
                db()->prepare('UPDATE newsletters SET subject=?, preheader=?, headline=?, body=?, button_text=?, button_url=?, include_news=?, include_events=? WHERE id=? AND status="draft"')
                    ->execute([$n['subject'],$n['preheader'],$n['headline'],$n['body'],$n['button_text'] ?: null,$n['button_url'] ?: null,$n['include_news'],$n['include_events'],$id]);
            } else {
                db()->prepare('INSERT INTO newsletters (subject, preheader, headline, body, button_text, button_url, include_news, include_events, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)')
                    ->execute([$n['subject'],$n['preheader'],$n['headline'],$n['body'],$n['button_text'] ?: null,$n['button_url'] ?: null,$n['include_news'],$n['include_events'],'draft',$admin['id']]);
                $id = (int) db()->lastInsertId();
            }
            flash_set('success', 'Draft saved. Preview it, send yourself a test, then send.');
            redirect(isset($_POST['go_send']) ? '/admin/newsletter_send.php?id=' . $id : '/admin/newsletters.php');
        }
    }
}

$pageTitle = $id ? 'Edit Newsletter' : 'New Newsletter';
require __DIR__ . '/includes/admin_header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/quill/quill.snow.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/rich-editor.css">
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu rt-form">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Email subject <span class="text-muted small">(what people see in their inbox)</span></label>
      <input type="text" name="subject" class="form-control" maxlength="200" value="<?= h($n['subject']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Preview line <span class="text-muted small">(optional short text shown next to the subject)</span></label>
      <input type="text" name="preheader" class="form-control" maxlength="200" value="<?= h($n['preheader']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Headline <span class="text-muted small">(big title at the top of the email)</span></label>
      <input type="text" name="headline" class="form-control" maxlength="200" value="<?= h($n['headline']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label" id="nl-body-label">Message</label>
      <div class="rt-editor-wrap">
        <div id="nl-editor" aria-labelledby="nl-body-label"><?= anu_nl_to_editor_html((string)$n['body']) ?></div>
      </div>
      <textarea name="body" id="nl-body" style="display:none" tabindex="-1" aria-hidden="true"></textarea>
      <div id="nl-img-msg" class="small mt-1" role="status" aria-live="polite"></div>
      <div class="form-text">Use the toolbar for headings, bold, italic, lists, colours, links and pictures (the picture button uploads from your computer; you can also paste or drag a picture in). The ANU logo, the greeting (&ldquo;Dear Amina,&rdquo;) and the sign-off are added automatically, so don&rsquo;t type them.</div>
    </div>
    <div class="row g-3">
      <div class="col-md-5"><label class="form-label">Button text (optional)</label><input type="text" name="button_text" class="form-control" maxlength="80" value="<?= h($n['button_text']) ?>" placeholder="e.g. Apply Now"></div>
      <div class="col-md-7"><label class="form-label">Button link</label><input type="text" name="button_url" class="form-control" maxlength="255" value="<?= h($n['button_url']) ?>" placeholder="https://…"></div>
    </div>
    <div class="mt-3 mb-3">
      <div class="form-check"><input class="form-check-input" type="checkbox" name="include_news" id="incN" <?= $n['include_news'] ? 'checked' : '' ?>><label class="form-check-label" for="incN">Add the 3 latest published news stories</label></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="include_events" id="incE" <?= $n['include_events'] ? 'checked' : '' ?>><label class="form-check-label" for="incE">Add the next 3 upcoming events</label></div>
    </div>
    <div class="rt-actionbar">
      <button class="btn btn-outline-anu" type="submit">Save draft</button>
      <button class="btn btn-apply" type="submit" name="go_send" value="1">Save &amp; go to preview / send</button>
      <a href="<?= BASE_URL ?>/admin/newsletters.php" class="btn btn-link">Cancel</a>
    </div>
  </div>
</form>
<script src="<?= BASE_URL ?>/assets/vendor/quill/quill.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/rich-editor.js"></script>
<script>
AnuRichEditor.init({ editor: '#nl-editor', textarea: '#nl-body', message: '#nl-img-msg', form: 'form.rt-form',
  endpoint: <?= json_encode(BASE_URL . '/admin/newsletter_image.php') ?>, csrf: <?= json_encode(csrf_token()) ?>, placeholder: 'Write your message here…' });
</script>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
