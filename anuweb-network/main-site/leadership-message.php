<?php
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare("SELECT * FROM leadership WHERE id = ? AND is_published = 1");
$stmt->execute([$id]);
$person = $stmt->fetch();

if (!$person) {
    redirect('/leadership.php');
}

$roleLabels = ['vc' => 'Vice Chancellor', 'dvc' => 'Deputy Vice Chancellor', 'chancellor' => 'Chancellor'];
$roleLabel = $roleLabels[$person['role_type']] ?? 'Deputy Vice Chancellor';
$pageTitle = 'Welcome Message from the ' . ($person['role_type'] === 'dvc' ? $person['full_name'] : $roleLabel);
$pageDescription = "A welcome message from Africa Nazarene University's " . $roleLabel . ".";

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2" style="color: var(--anu-red);">Office of the <?= $roleLabel ?></span>
    <h1>Welcome from <?= h($person['full_name']) ?></h1>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="leader-profile reveal">
      <div class="leader-profile-photo">
        <?php if ($person['photo']): ?>
          <img src="<?= h(UPLOADS_URL . '/' . $person['photo']) ?>" alt="<?= h($person['full_name']) ?>">
        <?php else: ?>
          <span class="placeholder-icon"><i class="bi bi-person"></i></span>
        <?php endif; ?>
        <div class="leader-profile-name">
          <strong><?= h($person['full_name']) ?></strong>
          <span><?= h(public_text($person['title'], $roleLabel)) ?>, Africa Nazarene University</span>
        </div>
      </div>
      <div class="leader-profile-body">
        <i class="bi bi-quote"></i>
        <span class="message-card-eyebrow">A Message to Our Community</span>
        <div class="message">
          <?= $person['message']
              ? nl2br(h($person['message']))
              : "A full welcome message will be added here once provided by the University's communications office." ?>
        </div>
      </div>
    </div>
    <?php if ($person['role_type'] === 'chancellor'): ?>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-anu mt-4">&larr; Back to About ANU</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/leadership.php" class="btn btn-outline-anu mt-4">&larr; Back to University Leadership</a>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
