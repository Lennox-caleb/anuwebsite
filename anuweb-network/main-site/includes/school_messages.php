<?php
/**
 * Dean and Head-of-Department messages for the school mini-sites.
 * Used by each school's home page (cards) and message page (full text).
 * Only real, admin-entered, published entries are shown; nothing here is invented.
 * Needs the calling site's db(), h() and excerpt() helpers.
 */
if (!function_exists('sm_dean')) {

    function sm_dean(string $leaderSlug, ?string $fallbackTitle = null): ?array
    {
        $s = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_published = 1 ORDER BY sort_order, id LIMIT 1");
        $s->execute([$leaderSlug]);
        $r = $s->fetch();
        if (!$r && $fallbackTitle) {
            // no entry in Staff: use the person already listed under Governance with this title (e.g. the University Registrar)
            try {
                $g = db()->prepare("SELECT id, full_name, title, bio, photo FROM governance_members WHERE is_published = 1 AND title LIKE ? ORDER BY sort_order, id LIMIT 1");
                $g->execute(['%' . $fallbackTitle . '%']);
                $r = $g->fetch() ?: false;
            } catch (Throwable $e) { $r = false; }
        }
        return ($r && (trim((string)$r['full_name']) !== '' || trim((string)$r['bio']) !== '')) ? $r : null;
    }

    function sm_heads(string $unit): array
    {
        try {
            $s = db()->prepare("SELECT * FROM staff WHERE unit_slug = ? AND is_dept_head = 1 AND is_published = 1 ORDER BY sort_order, full_name");
            $s->execute([$unit]);
            return $s->fetchAll();
        } catch (Throwable $e) {
            return [];   // column not migrated yet: show nothing rather than fail
        }
    }

    function sm_photo(?array $p, string $uploads): string
    {
        if ($p && !empty($p['photo'])) {
            return '<img src="' . h($uploads . '/' . $p['photo']) . '" alt="' . h($p['full_name']) . '" loading="lazy">';
        }
        return '<span class="sm-ph" aria-hidden="true"><i class="bi bi-person"></i></span>';
    }

    /** Home page block: Dean card + Heads of Department cards. Each card links to the full message. */
    function sm_render_home(array $o): void
    {
        $base = $o['base']; $uploads = $o['uploads'];
        $dean = sm_dean($o['leader_slug'], $o['fallback_title'] ?? null);
        $heads = (($o['heads'] ?? true) === false) ? [] : sm_heads($o['unit']);
        if ($dean && trim((string)$dean['bio']) === '' && trim((string)($o['fallback_bio'] ?? '')) !== '') $dean['bio'] = (string)$o['fallback_bio'];
        $deanReal = (bool)$dean;
        if (!$dean) $dean = ['full_name' => '', 'title' => '', 'bio' => trim((string)($o['fallback_bio'] ?? '')), 'photo' => ''];
        $msgPage = $o['message_page'] ?? 'message.php';
        ?>
<link rel="stylesheet" href="<?= h($o['css']) ?>">
<section class="sm-section">
  <div class="container">
    <?php if ($dean): ?>
      <a class="sm-dean" href="<?= h($base) ?>/<?= h($msgPage) ?>?who=dean">
        <div class="sm-dean-photo"><?= sm_photo($dean, $uploads) ?></div>
        <div class="sm-dean-body">
          <span class="sm-eyebrow"><?= h($o['eyebrow'] ?? 'Welcome') ?></span>
          <h2><?= $o['heading'] ?? 'Dean&rsquo;s Welcome Message' ?></h2>
          <?php if (trim((string)$dean['bio']) !== ''): ?><p class="sm-quote"><?= h(excerpt((string)$dean['bio'], 280)) ?></p><?php endif; ?>
          <?php if ($deanReal && trim((string)$dean['full_name']) !== ''): ?><p class="sm-sign"><strong><?= h($dean['full_name']) ?></strong><?php if ($dean['title']): ?><span><?= h($dean['title']) ?></span><?php endif; ?></p><?php endif; ?>
          <span class="sm-more">Read the full message <i class="bi bi-arrow-right"></i></span>
        </div>
      </a>
    <?php endif; ?>

    <?php if ($heads): ?>
      <div class="sm-heads-title"><span class="sm-eyebrow">Our Departments</span><h2>Messages from Heads of Department</h2></div>
      <div class="sm-heads">
        <?php foreach ($heads as $p): ?>
          <a class="sm-head" href="<?= h($base) ?>/message.php?id=<?= (int)$p['id'] ?>">
            <div class="sm-head-photo"><?= sm_photo($p, $uploads) ?></div>
            <div class="sm-head-body">
              <?php if (!empty($p['department'])): ?><span class="sm-dept"><?= h($p['department']) ?></span><?php endif; ?>
              <strong><?= h($p['full_name']) ?></strong>
              <?php if ($p['title']): ?><span class="sm-role"><?= h($p['title']) ?></span><?php endif; ?>
              <?php if (trim((string)$p['bio']) !== ''): ?><p><?= h(excerpt((string)$p['bio'], 120)) ?></p><?php endif; ?>
              <span class="sm-more">Read message <i class="bi bi-arrow-right"></i></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
        <?php
    }

    /** Looks up the person for message.php (?who=dean or ?id=N). */
    function sm_find(array $o): ?array
    {
        if (($_GET['who'] ?? '') === 'dean') return sm_dean($o['leader_slug'], $o['fallback_title'] ?? null) ?? ['full_name' => '', 'title' => '', 'bio' => '', 'photo' => '', 'id' => 0];
        $id = (int)($_GET['id'] ?? 0);
        if ($id < 1) return null;
        foreach (sm_heads($o['unit']) as $p) { if ((int)$p['id'] === $id) return $p; }
        return null;
    }

    /** Full message page body. */
    function sm_render_page(array $o, ?array $p): void
    {
        $base = $o['base']; $uploads = $o['uploads'];
        $isDean = ($_GET['who'] ?? '') === 'dean';
        if ($p && $isDean && trim((string)$p['bio']) === '' && trim((string)($o['fallback_bio'] ?? '')) !== '') $p['bio'] = (string)$o['fallback_bio'];
        $backUrl = $o['back_url'] ?? ($base . '/index.php');
        $backLabel = $o['back_label'] ?? 'Back to the school home page';
        ?>
<link rel="stylesheet" href="<?= h($o['css']) ?>">
<section class="sm-hero">
  <div class="container">
    <span class="sm-eyebrow"><?= $isDean ? h($o['eyebrow'] ?? 'Welcome') : 'Message' ?></span>
    <h1><?= $p ? ($isDean ? ($o['heading'] ?? 'Dean&rsquo;s Welcome Message') : 'Message from the Head of Department') : 'Message not found' ?></h1>
  </div>
</section>
<section class="sm-section">
  <div class="container sm-narrow">
    <?php if (!$p): ?>
      <p class="text-muted">This message is not available. <a href="<?= h($backUrl) ?>"><?= h($backLabel) ?></a></p>
    <?php else: ?>
      <article class="sm-article">
        <div class="sm-article-photo"><?= sm_photo($p, $uploads) ?></div>
        <div>
          <?php if (trim((string)$p['full_name']) !== ''): ?><h2><?= h($p['full_name']) ?></h2><?php endif; ?>
          <?php if ($p['title']): ?><p class="sm-role"><?= h($p['title']) ?></p><?php endif; ?>
          <?php if (!empty($p['department'])): ?><p class="sm-dept"><?= h($p['department']) ?></p><?php endif; ?>
          <?php if (!$isDean && !empty($p['email'])): ?><p class="small"><a href="mailto:<?= h($p['email']) ?>"><?= h($p['email']) ?></a></p><?php endif; ?>
        </div>
      </article>
      <div class="sm-text"><?= trim((string)$p['bio']) !== '' ? nl2br(h((string)$p['bio'])) : '<p class="text-muted">' . ($isDean ? '' . ($o['soon'] ?? 'The Dean&rsquo;s message will be published here soon.') . '' : 'The full message will be added soon.') . '</p>' ?></div>
      <p class="mt-4"><a class="sm-back" href="<?= h($backUrl) ?>"><i class="bi bi-arrow-left"></i> <?= h($backLabel) ?></a></p>
    <?php endif; ?>
  </div>
</section>
        <?php
    }
}
