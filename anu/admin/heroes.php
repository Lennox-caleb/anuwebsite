<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/includes/admin-layout.php';
requireAdmin();

$db = getDB();
$msg = '';
$msgType = 'success';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero'])) {
  $slug = $_POST['slug'] ?? '';
  $title = trim($_POST['title'] ?? '');
  $subtitle = trim($_POST['subtitle'] ?? '');
  $cta_text = trim($_POST['cta_text'] ?? '');
  $cta_link = trim($_POST['cta_link'] ?? '');
  $overlay = trim($_POST['overlay_color'] ?? 'rgba(0,0,0,0.55)');
  
  // Handle image upload
  $imagePath = null;
  if (!empty($_FILES['bg_image']['name'])) {
    $imagePath = handleUpload('bg_image', 'hero');
    if (!$imagePath) {
      $msg = 'Image upload failed. Check file type and size (max 10MB).';
      $msgType = 'error';
    }
  }

  // Item 8: optional background video, always paired with the image
  // above as its poster/fallback (used on mobile and prefers-reduced-motion).
  $videoPath = null;
  if (!empty($_FILES['bg_video']['name'])) {
    $videoPath = handleUpload('bg_video', 'hero', ['mp4','webm','mov']);
    if (!$videoPath) {
      $msg = 'Video upload failed. Use mp4/webm/mov, max 10MB.';
      $msgType = 'error';
    }
  }

  if (!$msg) {
    // Get current image/video if not uploading a new one
    $curr = $db->prepare("SELECT bg_image, bg_video FROM hero_sections WHERE page_slug=?");
    $curr->execute([$slug]);
    $curr = $curr->fetch();
    if (!$imagePath) $imagePath = $curr ? $curr['bg_image'] : null;
    if (!$videoPath) $videoPath = $curr ? ($curr['bg_video'] ?? null) : null;

    // Save to hero_sections
    $stmt = $db->prepare("INSERT INTO hero_sections (page_slug, title, subtitle, cta_text, cta_link, bg_image, bg_video, poster_image, overlay_color)
      VALUES (?,?,?,?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE title=VALUES(title), subtitle=VALUES(subtitle), cta_text=VALUES(cta_text), cta_link=VALUES(cta_link), bg_image=COALESCE(VALUES(bg_image), bg_image), bg_video=COALESCE(VALUES(bg_video), bg_video), poster_image=COALESCE(VALUES(poster_image), poster_image), overlay_color=VALUES(overlay_color)");
    $stmt->execute([$slug, $title, $subtitle, $cta_text, $cta_link, $imagePath, $videoPath, $imagePath, $overlay]);

    // SYNC to hero_slides — this is what the frontend carousel reads
    try {
        $check = $db->prepare("SELECT COUNT(*) FROM hero_slides WHERE page_slug=?");
        $check->execute([$slug]);
        if ($check->fetchColumn() > 0) {
            if ($imagePath) {
                $db->prepare("UPDATE hero_slides SET title=?,subtitle=?,cta_text=?,cta_link=?,overlay_color=?,bg_image=?,bg_video=?,poster_image=? WHERE page_slug=?")->execute([$title,$subtitle,$cta_text,$cta_link,$overlay,$imagePath,$videoPath,$imagePath,$slug]);
            } else {
                $db->prepare("UPDATE hero_slides SET title=?,subtitle=?,cta_text=?,cta_link=?,overlay_color=?,bg_video=? WHERE page_slug=?")->execute([$title,$subtitle,$cta_text,$cta_link,$overlay,$videoPath,$slug]);
            }
        } else {
            $db->prepare("INSERT INTO hero_slides (page_slug,title,subtitle,cta_text,cta_link,bg_image,bg_video,poster_image,overlay_color,badge_text,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,0,1)")->execute([$slug,$title,$subtitle,$cta_text,$cta_link,$imagePath??'',$videoPath??'',$imagePath??'',$overlay,'ANU Online']);
        }
    } catch (Exception $e) {}

    $msg = 'Hero updated! Changes are now live on the website.';
  }
}

// Get all heroes
$heroes = $db->query("SELECT * FROM hero_sections ORDER BY page_slug")->fetchAll();
$heroMap = [];
foreach ($heroes as $h) $heroMap[$h['page_slug']] = $h;

$pages = ['home','programs','campus-life','contact','faq','chaplaincy','clubs','news'];

adminHead('Hero Sections');
adminSidebar('heroes');
?>

<?php adminTopBar('Hero Sections', 'Manage page hero backgrounds and content'); ?>

<?php if ($msg): ?>
  <div class="alert-anu-<?= $msgType ?> mb-4"><?= e($msg) ?></div>
  <?php endif; ?>

  <!-- Page selector tabs -->
  <div class="admin-card mb-4">
    <h5><i class="bi bi-layout-text-sidebar-reverse me-2" style="color:var(--primary);"></i>Select a Page to Edit</h5>
    <div class="d-flex flex-wrap gap-2" id="heroTabs">
      <?php foreach ($pages as $pg): ?>
      <button onclick="showHeroTab('<?= $pg ?>')" id="tab-<?= $pg ?>"
              class="btn btn-sm" style="border-radius:50px;border:1.5px solid rgba(0,0,0,0.12);font-size:0.85rem;padding:8px 20px;transition:0.2s;" >
        <?= ucwords(str_replace('-',' ',$pg)) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Hero Edit Forms -->
  <?php foreach ($pages as $pg):
    $h = $heroMap[$pg] ?? [];
  ?>
  <div id="hero-<?= $pg ?>" class="hero-tab-content" style="display:none;">
    <div class="admin-card">
      <h5><i class="bi bi-image me-2"></i>Hero: <?= ucwords(str_replace('-',' ',$pg)) ?></h5>
      
      <form method="POST" enctype="multipart/form-data" class="anu-form">
        <input type="hidden" name="save_hero" value="1">
        <input type="hidden" name="slug" value="<?= $pg ?>">
        
        <div class="row g-4">
          <!-- Image Upload -->
          <div class="col-md-5">
            <label class="form-label">Background Image</label>
            <div class="img-upload-wrap" onclick="document.getElementById('img_<?= $pg ?>').click()">
              <?php if (!empty($h['bg_image'])): ?>
              <img src="/<?= e($h['bg_image']) ?>" alt="Current hero background for <?= e($pg) ?>" onerror="this.style.display='none'">
              <?php else: ?>
              <div class="img-placeholder">
                <i class="bi bi-cloud-upload"></i>
                <span>Click to upload hero image</span>
                <small style="color:#aaa;">JPG, PNG, WEBP — Max 10MB</small>
              </div>
              <?php endif; ?>
              <div class="img-upload-overlay">
                <i class="bi bi-camera" style="font-size:2rem;"></i>
                <span>Change Image</span>
              </div>
            </div>
            <input type="file" name="bg_image" id="img_<?= $pg ?>" class="img-upload-input" style="display:none;" accept="image/*">
            <small class="text-muted mt-2 d-block">Recommended: 1920×1080px or larger. Also used as the video poster/fallback below.</small>

            <div class="mt-3">
              <label class="form-label">Background Video (optional — item 8)</label>
              <input type="file" name="bg_video" class="form-control" accept="video/mp4,video/webm,video/quicktime">
              <small class="text-muted">
                mp4/webm/mov, max 10MB. When set, this plays behind the hero on desktop, muted and looping,
                with the image above as its poster. Hidden automatically on small screens and for visitors
                who have "reduce motion" turned on — the image is shown instead in both cases.
                <?php if (!empty($h['bg_video'])): ?><br>Current: <code><?= e($h['bg_video']) ?></code><?php endif; ?>
              </small>
            </div>

            <div class="mt-3">
              <label class="form-label">Overlay Color/Opacity</label>
              <input type="text" name="overlay_color" class="form-control" 
                     value="<?= e($h['overlay_color'] ?? 'rgba(0,0,0,0.55)') ?>"
                     placeholder="rgba(0,0,0,0.55)">
              <small class="text-muted">CSS color value, e.g., rgba(0,0,0,0.6) for dark overlay</small>
            </div>
          </div>
          
          <!-- Text Content -->
          <div class="col-md-7">
            <div class="mb-3">
              <label class="form-label">Hero Title</label>
              <input type="text" name="title" class="form-control" 
                     value="<?= e($h['title'] ?? '') ?>" placeholder="Bold headline for this page">
            </div>
            <div class="mb-3">
              <label class="form-label">Subtitle / Description</label>
              <textarea name="subtitle" class="form-control" rows="3" 
                        placeholder="Supporting text below the title"><?= e($h['subtitle'] ?? '') ?></textarea>
            </div>
            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label">CTA Button Text</label>
                <input type="text" name="cta_text" class="form-control" 
                       value="<?= e($h['cta_text'] ?? 'Learn More') ?>" placeholder="e.g., Explore Programs">
              </div>
              <div class="col-md-7">
                <label class="form-label">CTA Button Link</label>
                <input type="text" name="cta_link" class="form-control" 
                       value="<?= e($h['cta_link'] ?? '#') ?>" placeholder="e.g., /programs.php or https://...">
              </div>
            </div>
            
            <!-- Preview -->
            <div style="margin-top:20px;border-radius:12px;overflow:hidden;height:140px;position:relative;background:#111;display:flex;align-items:center;justify-content:center;">
              <?php if (!empty($h['bg_image'])): ?>
              <img src="/<?= e($h['bg_image']) ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0.5;" onerror="this.remove()">
              <?php endif; ?>
              <div style="position:relative;z-index:1;text-align:center;color:white;padding:0 20px;">
                <div style="font-size:1.1rem;font-weight:700;font-family:'Playfair Display',serif;"><?= e($h['title'] ?? '(No title set)') ?></div>
                <div style="font-size:0.78rem;margin-top:6px;opacity:0.75;"><?= e(substr($h['subtitle']??'',0,60)) ?></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="d-flex gap-3 mt-4">
          <button type="submit" class="btn btn-primary-anu" style="border-radius:50px;padding:12px 36px;">
            <i class="bi bi-check-circle me-2"></i>Save Changes
          </button>
          <a href="/<?= $pg ?>.php" target="_blank" class="btn btn-outline-secondary" style="border-radius:50px;padding:12px 24px;font-size:0.9rem;">
            <i class="bi bi-eye me-1"></i>Preview
          </a>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>


<script>
function showHeroTab(pg) {
  document.querySelectorAll('.hero-tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('#heroTabs button').forEach(btn => {
    btn.style.background = 'white';
    btn.style.color = 'inherit';
    btn.style.borderColor = 'rgba(0,0,0,0.12)';
  });
  document.getElementById('hero-' + pg).style.display = 'block';
  const tab = document.getElementById('tab-' + pg);
  tab.style.background = 'var(--primary)';
  tab.style.color = 'white';
  tab.style.borderColor = 'var(--primary)';
}

// Show first tab by default
document.addEventListener('DOMContentLoaded', () => showHeroTab('home'));

// Image preview on upload
document.querySelectorAll('.img-upload-input').forEach(input => {
  input.addEventListener('change', function() {
    if (!this.files[0]) return;
    const wrap = this.closest('.admin-card').querySelector('.img-upload-wrap');
    const reader = new FileReader();
    reader.onload = e => {
      let img = wrap.querySelector('img');
      if (!img) {
        wrap.querySelector('.img-placeholder')?.remove();
        img = document.createElement('img');
        img.style.cssText = 'width:100%;height:200px;object-fit:cover;display:block;';
        wrap.prepend(img);
      }
      img.src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
  });
});
</script>

<?php adminFoot(); ?>