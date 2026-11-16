<?php
/**
 * Shared search logic used by BOTH search.php (the full results page) and
 * search-ajax.php (the instant/overlay search). Kept in one place so the
 * two never drift apart or duplicate query logic -- exactly the same real
 * database rows and the same hand-maintained static-page index power both.
 */

/** A small hand-maintained index of static/informational pages that don't live in a database table. */
function anu_search_static_pages(): array
{
    return [
        ['title' => 'About ANU',                     'desc' => 'Mission, leadership, history and governance of the University.', 'url' => 'about.php',          'icon' => 'bi-info-circle', 'kw' => ''],
        ['title' => 'Mission & Vision',               'desc' => 'Our institutional mission and vision statements.',              'url' => 'mission-vision.php', 'icon' => 'bi-bullseye', 'kw' => ''],
        ['title' => 'Our History',                    'desc' => 'The story of ANU from 1994 to today.',                          'url' => 'our-history.php',    'icon' => 'bi-clock-history', 'kw' => ''],
        ['title' => 'Core Values',                    'desc' => 'Character, Competence and Community.',                          'url' => 'core-values.php',    'icon' => 'bi-gem', 'kw' => ''],
        ['title' => 'Accreditation',                  'desc' => 'Charter status and recognition by the Commission for University Education (CUE).', 'url' => 'accreditation.php', 'icon' => 'bi-patch-check', 'kw' => 'cue charter'],
        ['title' => 'Governance',                     'desc' => 'Board of Trustees, University Council and Management.',        'url' => 'governance.php',     'icon' => 'bi-diagram-3', 'kw' => ''],
        ['title' => 'Chancellor',                     'desc' => "The University's Chancellor.",                                 'url' => 'chancellor.php',     'icon' => 'bi-person-badge', 'kw' => ''],
        ['title' => 'Vice Chancellor Message',        'desc' => 'A welcome message from the Vice Chancellor.',                  'url' => 'vc-message.php',     'icon' => 'bi-chat-square-quote', 'kw' => 'vc'],
        ['title' => 'Deputy Vice Chancellor Messages','desc' => 'Welcome messages from the Deputy Vice Chancellors.',           'url' => 'dvc-messages.php',   'icon' => 'bi-chat-square-quote', 'kw' => 'dvc'],
        ['title' => 'Corporate Social Responsibility','desc' => 'Community outreach, environmental stewardship and volunteering.', 'url' => 'csr.php',         'icon' => 'bi-heart', 'kw' => 'csr community outreach'],
        ['title' => 'Schools & Programmes',           'desc' => 'Browse all five Schools and their programmes.',                'url' => 'schools.php',        'icon' => 'bi-bank', 'kw' => ''],
        ['title' => 'All Programmes',                 'desc' => 'Search Certificate, Diploma, Undergraduate, Masters and PhD programmes.', 'url' => 'programs.php', 'icon' => 'bi-mortarboard', 'kw' => 'courses degrees'],
        ['title' => 'Admissions',                     'desc' => 'How to apply, entry requirements and the ANU application portal.', 'url' => 'admissions.php',  'icon' => 'bi-pencil-square', 'kw' => 'apply application requirements'],
        ['title' => 'Non-Degree Programmes',          'desc' => 'Certificate, Diploma and short courses via ANU Non-Degree Programmes.', 'url' => 'admissions.php', 'icon' => 'bi-mortarboard', 'kw' => 'tvet non degree certificate diploma short course'],
        ['title' => 'Institute of Research',          'desc' => 'Research grants, publications and conferences at ANU.',        'url' => 'research.php',       'icon' => 'bi-search', 'kw' => 'research grants publications'],
        ['title' => 'Life at ANU',                    'desc' => 'Halls, clubs, sports, chaplaincy, health and student services.', 'url' => 'life-at-anu.php',   'icon' => 'bi-people', 'kw' => 'campus life student life'],
        ['title' => 'Orientation',                'desc' => 'What to bring, the orientation schedule, and documents for new students.', 'url' => 'orientation.php', 'icon' => 'bi-journal-check', 'kw' => 'new student orientation week checklist'],
        ['title' => 'Student Life',                   'desc' => 'Clubs, sports, housing and student services.',                'url' => 'student-life.php',   'icon' => 'bi-people', 'kw' => ''],
        ['title' => 'Alumni',                         'desc' => 'Stay connected as an ANU graduate.',                          'url' => 'alumni.php',         'icon' => 'bi-people', 'kw' => 'graduates alumnus'],
        ['title' => 'Resources',                      'desc' => 'Policies, handbooks, past papers and financial aid forms.',    'url' => 'resources.php',      'icon' => 'bi-file-earmark-text', 'kw' => ''],
        ['title' => 'Vacancies',                      'desc' => 'Current job openings at the University.',                      'url' => 'vacancies.php',      'icon' => 'bi-briefcase', 'kw' => 'jobs careers employment'],
        ['title' => 'News',                           'desc' => 'The latest news and announcements.',                          'url' => 'news.php',           'icon' => 'bi-newspaper', 'kw' => ''],
        ['title' => 'Events',                         'desc' => 'Upcoming events and the University calendar.',                'url' => 'events.php',         'icon' => 'bi-calendar-event', 'kw' => ''],
        ['title' => 'Contact Us',                     'desc' => 'Phone, email and campus address, plus an enquiry form.',      'url' => 'contact.php',        'icon' => 'bi-telephone', 'kw' => 'phone email address location'],
        ['title' => 'Privacy Policy',                 'desc' => 'How ANU collects and protects personal data (Data Protection Act, 2019).', 'url' => 'privacy.php', 'icon' => 'bi-shield-lock', 'kw' => 'data protection gdpr cookies'],
        ['title' => 'Terms & Conditions',             'desc' => 'Terms governing use of this website.',                        'url' => 'terms.php',          'icon' => 'bi-file-text', 'kw' => 'terms of use'],
        ['title' => 'Sitemap',                        'desc' => 'A full map of every page on this website.',                   'url' => 'sitemap.php',        'icon' => 'bi-map', 'kw' => ''],
    ];
}

/**
 * Run the real search against the database + static-page index and return
 * results grouped by category, exactly as search.php has always done it.
 * $limitPerCategory caps rows per category (used to keep the instant
 * overlay short; search.php calls this with the larger default).
 */
function anu_search(string $q, int $limitPerCategory = 8): array
{
    $results = [
        'schools' => [], 'programmes' => [], 'news' => [], 'events' => [],
        'resources' => [], 'vacancies' => [], 'people' => [], 'pages' => [],
    ];
    $q = trim($q);
    if ($q === '') {
        return $results;
    }
    $like = '%' . $q . '%';
    $n = max(1, $limitPerCategory);

    $stmt = db()->prepare("SELECT name, slug, summary FROM schools WHERE is_published=1 AND (name LIKE ? OR summary LIKE ?) ORDER BY sort_order LIMIT $n");
    $stmt->execute([$like, $like]);
    $results['schools'] = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT title, slug, level, overview FROM programmes WHERE is_published=1 AND (title LIKE ? OR overview LIKE ?) ORDER BY title LIMIT $n");
    $stmt->execute([$like, $like]);
    $results['programmes'] = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT title, slug, excerpt, published_at FROM news WHERE is_published=1 AND (title LIKE ? OR excerpt LIKE ? OR body LIKE ?) ORDER BY published_at DESC LIMIT $n");
    $stmt->execute([$like, $like, $like]);
    $results['news'] = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT title, slug, location, start_datetime FROM events WHERE is_published=1 AND (title LIKE ? OR location LIKE ?) ORDER BY start_datetime ASC LIMIT $n");
    $stmt->execute([$like, $like]);
    $results['events'] = $stmt->fetchAll();

    try {
        $stmt = db()->prepare("SELECT title, description, category, file_name FROM resources WHERE is_published=1 AND (title LIKE ? OR description LIKE ?) ORDER BY uploaded_at DESC LIMIT $n");
        $stmt->execute([$like, $like]);
        $results['resources'] = $stmt->fetchAll();
    } catch (Throwable $e) { /* resources table may not exist on older installs */ }

    try {
        $stmt = db()->prepare("SELECT title, slug, department, closing_date FROM vacancies WHERE is_published=1 AND (title LIKE ? OR department LIKE ? OR description LIKE ?) ORDER BY closing_date ASC LIMIT $n");
        $stmt->execute([$like, $like, $like]);
        $results['vacancies'] = $stmt->fetchAll();
    } catch (Throwable $e) { /* vacancies table may not exist on older installs */ }

    try {
        $stmt = db()->prepare("SELECT full_name, title, 'staff' as src FROM staff WHERE is_published=1 AND (full_name LIKE ? OR title LIKE ?)
                                UNION SELECT full_name, title, 'governance' as src FROM governance_members WHERE is_published=1 AND (full_name LIKE ? OR title LIKE ?)
                                LIMIT $n");
        $stmt->execute([$like, $like, $like, $like]);
        $results['people'] = $stmt->fetchAll();
    } catch (Throwable $e) { /* ignore */ }

    foreach (anu_search_static_pages() as $p) {
        if (stripos($p['title'], $q) !== false || stripos($p['desc'], $q) !== false || stripos($p['kw'], $q) !== false) {
            $results['pages'][] = $p;
            if (count($results['pages']) >= $n) break;
        }
    }

    return $results;
}
