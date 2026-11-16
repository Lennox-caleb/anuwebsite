<?php
$pageTitle = 'Events';
$pageDescription = 'Upcoming events and the university calendar at Africa Nazarene University.';
require_once __DIR__ . '/includes/functions.php';

$eventsScope = unit_scope_sql('events'); // Phase 5b fix: falls back to '' instead of a fatal error pre-migration.
$upcoming = db()->query("SELECT * FROM events WHERE is_published=1{$eventsScope} AND start_datetime >= NOW() ORDER BY start_datetime ASC")->fetchAll();
$past = db()->query("SELECT * FROM events WHERE is_published=1{$eventsScope} AND start_datetime < NOW() ORDER BY start_datetime DESC LIMIT 12")->fetchAll();

require __DIR__ . '/includes/header.php';

function anu_event_card($e, $isPast = false) {
    $day = date('d', strtotime($e['start_datetime']));
    $mon = date('M', strtotime($e['start_datetime']));
    ob_start();
    ?>
    <div class="col-md-6 col-lg-4" id="<?= h($e['slug']) ?>">
      <div class="card-anu reveal <?= $isPast ? 'event-card-past' : '' ?> h-100">
        <div class="body">
          <div class="d-flex align-items-start gap-3 mb-2">
            <span class="event-date-badge"><span class="day"><?= h($day) ?></span><span class="mon"><?= h($mon) ?></span></span>
            <div>
              <h3 class="mb-1" style="font-size:1rem;"><?= h($e['title']) ?></h3>
              <p class="small text-muted mb-0"><i class="bi bi-clock"></i> <?= date('g:i A', strtotime($e['start_datetime'])) ?></p>
            </div>
          </div>
          <p class="small text-muted mb-1"><i class="bi bi-geo-alt"></i> <?= h($e['location']) ?></p>
          <?php if ($e['description']): ?><p class="small mb-0"><?= h(excerpt($e['description'], 110)) ?></p><?php endif; ?>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
<section class="page-hero">
  <div class="container container-narrow text-center">
    <h1>Events</h1>
    <p class="text-muted mb-0">What's happening on campus, and what's already happened.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="anu-tabs">
      <button type="button" class="active" data-tab-target="upcoming">Upcoming (<?= count($upcoming) ?>)</button>
      <button type="button" data-tab-target="past">Past Events</button>
    </div>

    <div data-tab-pane="upcoming">
      <div class="row g-4">
        <?php foreach ($upcoming as $e): echo anu_event_card($e); endforeach; ?>
        <?php if (!$upcoming): ?>
          <div class="col-12 text-center py-5">
            <i class="bi bi-calendar-event" style="font-size:2.2rem; color: var(--anu-line);"></i>
            <p class="text-muted mt-3">No upcoming events posted right now. Check back soon, or see past events below.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div data-tab-pane="past" hidden>
      <div class="row g-4">
        <?php foreach ($past as $e): echo anu_event_card($e, true); endforeach; ?>
        <?php if (!$past): ?><p class="text-muted">No past events on record yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
