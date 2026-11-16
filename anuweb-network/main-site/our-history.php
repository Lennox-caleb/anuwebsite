<?php
$pageTitle = 'Our History';
$pageDescription = "From 62 students in 1994 to a chartered, multi-school university, the history of Africa Nazarene University.";
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
$milestones = [
    ['1980s&ndash;1993', "Africa Nazarene University is a private Christian university and an institution of the international Church of the Nazarene, whose mission and vision follow the Wesleyan-Holiness tradition. The Church sought to address challenges facing African society in the early 1980s through higher education, and settled on Kenya as a location following the country's University Act of 1985, which provided for the establishment of private universities."],
    ['1993', "In July 1993, the Church of the Nazarene's General Assembly in Indianapolis approved the establishment of a university in Africa. On 23 November 1993, Kenya's Commission for Higher Education issued a Letter of Interim Authority to begin developing the University, making ANU the first Nazarene university outside North America."],
    ['1994&ndash;1995', "ANU opened its doors in August 1994 under its first Vice Chancellor, Dr. Martha John, with 62 students from eleven African countries studying Theology, Business Administration or a Master of Arts in Religion. A Bachelor of Science in Computer Science followed a year later."],
    ['1996', "Prof. Leah T. Marangu became Vice Chancellor in January 1996, leading the University through a period of significant growth in enrolment and academic programmes."],
    ['2002', "The Kenyan Government granted Africa Nazarene University its Charter on 8 October 2002, making it the first private university in Kenya to be accredited under the University Act without external affiliation to another institution."],
];
?>
<section class="about-sub-hero">
  <div class="container container-narrow text-center">
    <span class="hero-eyebrow d-inline-block mb-2">About Africa Nazarene University</span>
    <h1>Our History</h1>
    <p class="lead mb-0 max-ch-60 mx-auto">From 62 pioneering students in 1994 to a chartered university with five schools.</p>
  </div>
</section>
<section class="py-5">
  <div class="container container-narrow">
    <div class="history-timeline">
      <?php foreach ($milestones as $i => [$year, $text]): ?>
        <div class="history-item reveal reveal-<?= ($i % 4) + 1 ?>">
          <div class="history-year"><?= $year ?></div>
          <div class="history-body"><p class="mb-0"><?= $text ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted mt-4">A fuller institutional history, including more recent leadership and milestones, will be added here once compiled by the University's communications office.</p>
    <div class="d-flex flex-wrap gap-3 mt-2">
      <a href="<?= BASE_URL ?>/who-we-are.php" class="btn btn-outline-anu">Who We Are &rarr;</a>
      <a href="<?= BASE_URL ?>/affiliation.php" class="btn btn-outline-anu">Our Affiliation &rarr;</a>
      <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline-dark">&larr; Back to About ANU</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
