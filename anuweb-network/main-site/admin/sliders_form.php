<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['editor','marketing','super_admin']);
require_once __DIR__ . '/includes/upload.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$presetUnit = $_GET['unit'] ?? null;
// ANU Online and Non-Degree (TVET) are NOT listed here -- they run their
// own separate admin panels, not this one.
$units = [
    '' => 'Main site (homepage carousel)',
    'law' => 'School of Law', 'business' => 'School of Business', 'science-technology' => 'School of Science & Technology',
    'humanities' => 'School of Humanities & Social Sciences', 'religion' => 'School of Religion & Christian Ministry',
    'sepc' => 'SEPC', 'research' => 'Institute of Research', 'admissions' => 'Admissions',
    'student-life' => 'Student Life', 'chaplaincy' => 'Chaplaincy', 'library' => 'Library', 'alumni' => 'Alumni',
];
$s = ['unit_slug'=>($presetUnit ?? ''),'headline'=>'','subtext'=>'','image'=>null,'video_url'=>null,'link_url'=>'','link_text'=>'','link2_url'=>'','link2_text'=>'','sort_order'=>0,'is_published'=>1];
$errors = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM sliders WHERE id = ?');
    $stmt->execute([$id]);
    if ($found = $stmt->fetch()) $s = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = csrf_error_message();
    } else {
        $s['unit_slug']    = array_key_exists($_POST['unit_slug'] ?? '', $units) ? $_POST['unit_slug'] : '';
        $s['headline']     = trim($_POST['headline'] ?? '');
        $s['subtext']      = trim($_POST['subtext'] ?? '');
        $s['link_url']     = trim($_POST['link_url'] ?? '');
        $s['link_text']    = trim($_POST['link_text'] ?? '');
        $s['link2_url']    = trim($_POST['link2_url'] ?? '');
        $s['link2_text']   = trim($_POST['link2_text'] ?? '');
        $s['sort_order']   = (int)($_POST['sort_order'] ?? 0);
        $s['is_published'] = isset($_POST['is_published']) ? 1 : 0;

        try {
            $uploaded = handle_image_upload('image');
            if ($uploaded) $s['image'] = $uploaded;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        // Video is optional and additive to the image above -- the image
        // always stays in place as the poster frame / fallback for
        // browsers that block autoplay or don't support video. Precedence:
        // an uploaded file wins over a pasted URL; typing a pasted URL
        // with "Remove video" left unchecked keeps whichever was set
        // before if neither is provided again.
        if (!empty($_POST['remove_video'])) {
            $s['video_url'] = null;
        }
        try {
            $uploadedVideo = handle_video_upload('video');
            $chunked = trim($_POST['video_chunked_file'] ?? '');
            if (!$uploadedVideo && $chunked !== '') {
                // finished by admin/video_chunk_upload; the name is generated server-side, so only accept that exact shape and an existing file
                if (preg_match('/^[a-f0-9]{16}\.(mp4|webm)$/', $chunked) && is_file(UPLOADS_PATH . '/' . $chunked)) $uploadedVideo = $chunked;
                else $errors[] = 'The large video upload could not be found on the server. Please upload it again.';
            }
            if ($uploadedVideo) $s['video_url'] = $uploadedVideo;
            elseif (trim($_POST['video_url_external'] ?? '') !== '') {
                $pasted = trim($_POST['video_url_external']);
                if (!preg_match('#^https?://#i', $pasted)) {
                    $errors[] = 'The video URL must start with http:// or https://.';
                } else {
                    $s['video_url'] = $pasted;
                }
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        // Phase 2 (2026-09): wording (eyebrow/headline/subtext/button) is
        // ALWAYS optional now -- a hero can be media-only, with no empty
        // container rendered for the missing text (see index.php). The
        // only real requirement is that there's SOME media to show:
        // an image, an uploaded video, or a pasted video/YouTube URL.
        // This replaces the old "headline or image" rule, which wrongly
        // rejected a video-only slide with no headline.
        if (empty($s['image']) && empty($s['video_url'])) {
            $errors[] = 'Add an image, an uploaded video, or a video/YouTube URL -- the hero needs some media to show (wording is optional).';
        }

        if (!$errors) {
            $unitVal = $s['unit_slug'] !== '' ? $s['unit_slug'] : null;
            if ($id) {
                $stmt = db()->prepare('UPDATE sliders SET unit_slug=?, headline=?, subtext=?, image=?, video_url=?, link_url=?, link_text=?, link2_url=?, link2_text=?, sort_order=?, is_published=? WHERE id=?');
                $stmt->execute([$unitVal,$s['headline'],$s['subtext'],$s['image'],$s['video_url'],$s['link_url'],$s['link_text'],$s['link2_url'],$s['link2_text'],$s['sort_order'],$s['is_published'],$id]);
            } else {
                $stmt = db()->prepare('INSERT INTO sliders (unit_slug, headline, subtext, image, video_url, link_url, link_text, link2_url, link2_text, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$unitVal,$s['headline'],$s['subtext'],$s['image'],$s['video_url'],$s['link_url'],$s['link_text'],$s['link2_url'],$s['link2_text'],$s['sort_order'],$s['is_published']]);
            }
            redirect('/admin/sliders.php');
        }
    }
}

$pageTitle = $id ? 'Edit Hero Image' : 'New Hero Image';
require __DIR__ . '/includes/admin_header.php';
?>
<?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
<form method="post" class="card-anu" enctype="multipart/form-data">
  <div class="body">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Site</label>
      <select name="unit_slug" class="form-select">
        <?php foreach ($units as $val => $label): ?>
          <option value="<?= h($val) ?>" <?= ($s['unit_slug'] ?? '')===$val?'selected':'' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">The main site can have several of these shown as a slideshow. Other sites use one, the most recent published one for that site is used.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Headline (optional once a hero image is set)</label>
      <input type="text" name="headline" class="form-control" value="<?= h($s['headline']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Subtext (optional)</label>
      <input type="text" name="subtext" class="form-control" value="<?= h($s['subtext']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Hero image</label>
      <?php if (!empty($s['image'])): ?>
        <div class="mb-2"><img src="<?= BASE_URL ?>/assets/uploads/<?= h($s['image']) ?>" class="img-preview-sm rounded border"></div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/svg+xml">
      <div class="form-text">Always used as the poster frame shown while a hero video (below) loads, and as the fallback shown to visitors whose browser blocks autoplay video.</div>
    </div>
    <div class="mb-3 p-3 border rounded">
      <label class="form-label fw-bold">Hero video (optional)</label>
      <div class="form-text mb-2">Plays muted and on a loop behind the headline (or on its own -- headline, subtext and buttons are all optional too), in place of a still photo. Upload a short (a few seconds), already-compressed clip, paste a direct link to one hosted elsewhere, or paste a YouTube link (youtu.be or youtube.com -- it's converted to a background embed automatically). Leave both blank to keep using the photo above.</div>
      <?php if (!empty($s['video_url'])): ?>
        <div class="mb-2 small">
          Current video: <code><?= h($s['video_url']) ?></code>
          <label class="ms-2"><input type="checkbox" name="remove_video" value="1"> Remove it</label>
        </div>
      <?php endif; ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Upload a video file</label>
          <input type="file" name="video" class="form-control" accept="video/mp4,video/webm">
          <div class="form-text">Small clips (under about 20 MB) upload with the form. For anything bigger, use the large-file uploader below.</div>
          <div class="mt-3 p-3 border rounded bg-white" data-chunk-upload data-endpoint="<?= BASE_URL ?>/admin/video_chunk_upload.php" data-token="<?= h(csrf_token()) ?>" data-target="#video_chunked_file">
            <label class="form-label fw-bold mb-1">Large video (up to 2 GB, resumable)</label>
            <input type="file" class="form-control" accept="video/mp4,video/webm">
            <div class="progress mt-2" style="height:8px"><div class="cu-bar progress-bar bg-warning" style="width:0"></div></div>
            <div class="cu-msg small text-muted mt-1" aria-live="polite"></div>
            <div class="form-text">Tip: a hero is a muted background loop, a 10&ndash;30 second MP4 under ~10 MB plays smoothest on phones. Very large files make the page slow for visitors.</div>
          </div>
          <input type="hidden" name="video_chunked_file" id="video_chunked_file" value="">
          <script src="<?= asset_v(BASE_URL, 'assets/js/chunk-upload.js') ?>" defer></script>
          <div class="form-text">MP4 or WEBM, max 20MB.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">...or paste a video/YouTube URL</label>
          <input type="text" name="video_url_external" class="form-control" placeholder="https://youtu.be/... or https://...video.mp4">
          <div class="form-text">Only used if no file is uploaded above. A youtu.be/youtube.com link becomes a background embed; any other https:// link is treated as a direct video file.</div>
        </div>
      </div>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Button 1 text (optional)</label>
        <input type="text" name="link_text" class="form-control" value="<?= h($s['link_text']) ?>" placeholder="e.g. Apply Now">
      </div>
      <div class="col-md-6">
        <label class="form-label">Button 1 link (optional)</label>
        <input type="text" name="link_url" class="form-control" value="<?= h($s['link_url']) ?>" placeholder="e.g. /admissions.php">
      </div>
    </div>
    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <label class="form-label">Button 2 text (optional)</label>
        <input type="text" name="link2_text" class="form-control" value="<?= h($s['link2_text']) ?>" placeholder="e.g. Explore Schools">
      </div>
      <div class="col-md-6">
        <label class="form-label">Button 2 link (optional)</label>
        <input type="text" name="link2_url" class="form-control" value="<?= h($s['link2_url']) ?>" placeholder="e.g. /schools.php">
      </div>
    </div>
    <div class="form-text mt-1">Both buttons are optional and only show on this slide if you fill them in here -- nothing is added automatically.</div>
    <div class="mb-3 mt-3 field-narrow">
      <label class="form-label">Display order</label>
      <input type="number" name="sort_order" class="form-control" value="<?= (int)$s['sort_order'] ?>">
    </div>
    <div class="form-check my-3">
      <input type="checkbox" name="is_published" id="pub" class="form-check-input" <?= $s['is_published'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="pub">Published</label>
    </div>
    <button class="btn btn-apply" type="submit">Save</button>
    <a href="<?= BASE_URL ?>/admin/sliders.php" class="btn btn-outline-anu">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
