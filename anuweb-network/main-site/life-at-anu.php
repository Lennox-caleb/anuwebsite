<?php
$pageTitle = 'Life at ANU';
$pageDescription = "Student life at Africa Nazarene University, accommodation, clubs & societies, sports, health services, counseling and chaplaincy.";
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';

$clubs = ['Drama (Light Theater Productions)', 'Music & Choir', 'Sing Africa', 'Enactus (SIFE)', 'Oikos Kenya',
    'Law Assembly of ANU (LAANU)', 'Peer Counselors', 'Wildlife Club', 'Environmental Club', 'Peace Club',
    'Christian Union', 'Red Cross', 'Marketing Club', 'Kenya Model United Nations', 'Accounting Club',
    'ANU Coders', 'Compassionate Ministries', 'Afrinaz'];

$halls = ['Miriam Crawford Hall (Women)', 'Johnson Hall (Men)', 'Zanner Hall (Men)', 'Cashman Hall (Men)', 'JB Flats (Men)'];

$sportsClubs = ['Basketball (Men & Women)', 'Volleyball (Men & Women)', 'Rugby', 'Soccer', 'Softball (Men & Women)', 'Handball (Men & Women)', 'Netball (Women)'];
?>

<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">Student Life</span>
    <h1>Life at ANU</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">Beyond the classroom, accommodation, clubs, sports,
    chaplaincy and support services that make up everyday life on campus.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 reveal reveal-1">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-house-door"></i></div>
          <span class="tag">Accommodation</span>
          <h3 class="h5">On-Campus Housing</h3>
          <p class="small text-muted mb-2">Hostel accommodation at our Ongata Rongai main campus, next to
          Nairobi National Park.</p>
          <ul class="small text-muted mb-0 ps-3">
            <?php foreach ($halls as $h): ?><li><?= h($h) ?></li><?php endforeach; ?>
          </ul>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-2">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-heart"></i></div>
          <span class="tag">Spiritual Life</span>
          <h3 class="h5">Chaplaincy</h3>
          <p class="small text-muted mb-2">Chapel services, worship, Bible studies, mentoring and the
          student-led Christian Union, spiritual life woven into everyday campus life.</p>
          <a href="<?= h(setting('url_chaplaincy')) ?>" class="tile-go">Visit Chaplaincy &rarr;</a>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-3">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-trophy"></i></div>
          <span class="tag">Sports</span>
          <h3 class="h5">Recreation &amp; Sports</h3>
          <p class="small text-muted mb-2">Intramural activities, aerobics, Zumba, weight training,
          table tennis, plus competitive club sports:</p>
          <ul class="small text-muted mb-0 ps-3">
            <?php foreach ($sportsClubs as $s): ?><li><?= h($s) ?></li><?php endforeach; ?>
          </ul>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-1">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-people"></i></div>
          <span class="tag">Get Involved</span>
          <h3 class="h5">Clubs &amp; Societies</h3>
          <p class="small text-muted mb-2">18 registered clubs and societies to join, members enjoy
          benefits like 24/7 internet access and vehicle hire for club events.</p>
          <p class="small mb-0"><?= h(implode(' &middot; ', array_slice($clubs, 0, 6))) ?> &hellip; and more</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-2">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-heart-pulse"></i></div>
          <span class="tag">Wellbeing</span>
          <h3 class="h5">Health Services</h3>
          <p class="small text-muted mb-2">The Nellie Montague Clinic, on campus near Crawford Ladies
          Hostel, offers diagnosis and treatment, lab testing, minor surgery, HIV/AIDS counselling and
          testing, first aid and ambulance referral, weekdays 8:30am&ndash;5pm, on-call otherwise.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-3">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-chat-heart"></i></div>
          <span class="tag">Wellbeing</span>
          <h3 class="h5">Guidance &amp; Counseling</h3>
          <p class="small text-muted mb-2">Individual and group sessions supporting students through
          personal, educational and career challenges, Harmon's Building, 2nd Floor, Room 11.
          Weekdays 8am&ndash;7pm, weekend on-call.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-1">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-briefcase"></i></div>
          <span class="tag">Careers</span>
          <h3 class="h5">Student Career Services</h3>
          <p class="small text-muted mb-2">Career workshops, career fairs and interview preparation, a
          Work Study Program, internship placement support, and the ALVOC alumni mentoring program.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-2">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-stars"></i></div>
          <span class="tag">Community</span>
          <h3 class="h5">Cultural Week</h3>
          <p class="small text-muted mb-2">An annual campus-wide celebration of the many nationalities and
          cultures represented at ANU, with themed days throughout the week.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-3">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-shield-check"></i></div>
          <span class="tag">Safety</span>
          <h3 class="h5">Campus Security</h3>
          <p class="small text-muted mb-2">A staffed, gated campus with round-the-clock security presence
          across hostels, lecture blocks and common areas, so students can focus on campus life with
          peace of mind.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-1">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-megaphone"></i></div>
          <span class="tag">Student Voice</span>
          <h3 class="h5">Student Leadership</h3>
          <p class="small text-muted mb-2">Elected student leadership represents the student body,
          organises campus-wide activities and liaises directly with the University administration on
          students' behalf.</p>
        </div></div>
      </div>
      <div class="col-md-6 col-lg-4 reveal reveal-2">
        <div class="card-anu feature-card h-100"><div class="body">
          <div class="icon-badge"><i class="bi bi-journal-check"></i></div>
          <span class="tag">New Students</span>
          <h3 class="h5">Orientation</h3>
          <p class="small text-muted mb-2">Reporting for your first semester? Get the orientation
          schedule, a what-to-bring checklist, and answers to common first-week questions in one place.</p>
          <a href="<?= BASE_URL ?>/orientation.php" class="tile-go">View Orientation &rarr;</a>
        </div></div>
      </div>
    </div>

    <div class="text-center mt-5 reveal">
      <a target="_blank" rel="noopener" href="<?= h(setting('url_student_life')) ?>" class="btn btn-apply btn-lg">Full Student Life Portal &rarr;</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
