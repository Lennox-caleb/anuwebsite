<?php
if (!function_exists('orientation_admin_groups')) {
    /** Editable-field groups for the Orientation page, generated from its defaults (includes/orientation_defaults.php). */
    function orientation_admin_groups(): array
    {
        $d = include __DIR__ . '/../../includes/orientation_defaults.php';
        $steps = []; foreach ($d['steps'] as $i => [$t, $x]) {
            $n = $i + 1;
            $steps["step{$n}_title"] = ['label' => "Step $n title", 'type' => 'text', 'default' => $t];
            $steps["step{$n}_text"]  = ['label' => "Step $n description", 'type' => 'textarea', 'default' => $x];
        }
        $chk = []; foreach ($d['checklist'] as $i => [$ic, $t, $x]) {
            $n = $i + 1;
            $chk["chk{$n}_title"] = ['label' => "Item $n title", 'type' => 'text', 'default' => $t];
            $chk["chk{$n}_text"]  = ['label' => "Item $n description", 'type' => 'textarea', 'default' => $x];
        }
        $faq = []; foreach ($d['faqs'] as $i => [$q, $a]) {
            $n = $i + 1;
            $faq["faq{$n}_q"] = ['label' => "Question $n", 'type' => 'text', 'default' => $q];
            $faq["faq{$n}_a"] = ['label' => "Answer $n", 'type' => 'textarea', 'default' => $a];
        }
        return [
            'schedule'  => ['label' => 'Orientation Week steps (leave a box empty to use the standard text)', 'fields' => $steps],
            'checklist' => ['label' => 'What to Bring checklist', 'fields' => $chk],
            'faq'       => ['label' => 'Frequently Asked Questions', 'fields' => $faq],
        ];
    }
}

/**
 * Single source of truth for every unit micro-site this shared admin panel
 * manages. ANU Online and the Non-Degree (TVET) site are NOT listed here --
 * they run their own, separate admin panels, not this one.
 *
 * 'content' lists this site's editable text groups. Each group is a
 * labelled set of fields stored in the shared `site_content` table, keyed
 * by (unit_slug, content_key). Every field's default is the exact text the
 * page already shipped with, so leaving a field blank keeps the page
 * looking exactly as it does today -- nothing goes empty by surprise.
 */
return [

    // Phase 7b (2026-09): rebuilt inside main-site itself
    // (admissions.php + subpages), Daystar-style, replacing the old
    // separate admissions-site micro-site as the live/linked Admissions
    // section. admissions-site's own files are kept on disk (not
    // deleted) and now 301-redirect to these new pages -- see its
    // .htaccess. This entry's 'content' groups cover every new subpage.
    'admissions' => [
        'label' => 'Admissions',
        'group' => 'Student & Alumni Services',
        'url'   => '/anuweb-network/main-site/admissions.php',
        'has_programmes' => false,
        'leader_slug' => 'registrar', 'leader_label' => 'Registrar',
        'content' => [
            'overview' => ['label' => 'Overview (hub page)', 'fields' => [
                'hero_headline' => ['label' => 'Hero headline', 'type' => 'text',
                    'default' => 'Your future starts with one application'],
                'hero_subtext' => ['label' => 'Hero subtext', 'type' => 'textarea',
                    'default' => 'Applications for the current intake are open.'],
                'fee_local' => ['label' => 'Application fee (East Africa)', 'type' => 'text',
                    'default' => 'KES 1,500'],
                'fee_international' => ['label' => 'Application fee (International)', 'type' => 'text',
                    'default' => 'USD 35'],
                'feedback_time' => ['label' => 'Typical response time', 'type' => 'text',
                    'default' => '5 working days'],
                'registrar_message' => ['label' => "Message from the Registrar (used if no Registrar bio is set under Leadership)", 'type' => 'textarea',
                    'default' => ''],
            ]],
            'how_to_apply' => ['label' => 'How to Apply page', 'fields' => [
                'application_portal_url' => ['label' => 'Online application portal URL', 'type' => 'text',
                    'default' => 'https://records.anu.ac.ke/'],
            ]],
            'requirements' => ['label' => 'Requirements & Application Fees page', 'fields' => [
                'requirements_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => "Specific entry requirements vary by programme and level (certificate, diploma, undergraduate, masters, PhD). The list below covers what's generally needed to start an application, the Admissions office will confirm exact requirements for your chosen programme."],
                'application_form_url' => ['label' => 'Downloadable application form URL', 'type' => 'text',
                    'default' => 'https://www.anu.ac.ke/download/application-form/'],
            ]],
            'international' => ['label' => 'International Students (ANUISA) page', 'fields' => [
                'international_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Africa Nazarene University welcomes students from outside Kenya. This page covers what international applicants need to know, and the exchange partnership between ANU and Mount Vernon Nazarene University (MVNU).'],
            ]],
            'tuition_payment' => ['label' => 'Tuition Payment Options page', 'fields' => [
                'payment_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Tuition and fees can be paid by M-Pesa, direct bank deposit, international wire transfer, or PayPal. Always include your student/admission number as the payment reference.'],
            ]],
            'faqs' => ['label' => 'FAQs page', 'fields' => [
                'faqs_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Answers to the questions applicants ask most often. For anything not covered here, contact the Admissions office directly.'],
                'faqs_full_list_url' => ['label' => 'Link to the full FAQ list on anu.ac.ke', 'type' => 'text',
                    'default' => 'https://www.anu.ac.ke/frequently-asked-questions/'],
            ]],
        ],
    ],

    'alumni' => [
        'label' => 'Alumni',
        'group' => 'Student & Alumni Services',
        'url'   => '/anuweb-network/alumni-site/index.php',
        'has_programmes' => false,
        'leader_slug' => 'head-alumni', 'leader_label' => 'Alumni Relations Officer',
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => "The Alumni office keeps every ANU graduate connected, to each other, to current students, and to the university's ongoing mission, through networking, mentorship and giving."],
            ]],
            'alumni_card' => ['label' => 'Alumni Card page', 'fields' => [
                'alumni_card_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Register or update your alumni information and apply for your official ANU Alumni Card, your ongoing connection to the University community.'],
                'alumni_card_payment_note' => ['label' => 'Card & payment note', 'type' => 'textarea',
                    'default' => 'Application steps and payment details for the Alumni Card should be confirmed with the Alumni Affairs Office.'],
            ]],
            'chapters' => ['label' => 'Regional Chapters page', 'fields' => [
                'chapters_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'ANU alumni stay connected through regional and departmental chapters around the world.'],
                'chapters_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => 'Chapter leader contact details should be confirmed and published here by the Alumni Affairs Office.'],
            ]],
            'scholarship_fund' => ['label' => 'Scholarship Fund page', 'fields' => [
                'scholarship_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'The Alumni Scholarship Fund helps empower, encourage and motivate current students. Alumni and friends of the University can give directly to support the next generation of ANU graduates.'],
                'scholarship_closing' => ['label' => 'Payment details note', 'type' => 'textarea',
                    'default' => 'Exact Paybill and bank account numbers should be confirmed with the Alumni Affairs Office before publishing, so donors always see current, correct details.'],
            ]],
        ],
    ],

    'business' => [
        'has_departments' => true,
        'label' => 'School of Business',
        'group' => 'Schools',
        'url'   => '/anuweb-network/business-school-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'dean-business', 'leader_label' => 'Dean, School of Business',
        'programme_filter' => ['type' => 'school_slug', 'value' => 'sob'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'Applied to this School specifically: the School of Business forms graduates who combine sound commercial judgement with ethical leadership, ready for enterprise, management and public-sector roles across Kenya and the region.'],
                'accreditation_body' => ['label' => 'Accreditation paragraph', 'type' => 'textarea',
                    'default' => "Accreditation and professional-body recognition details (e.g. KASNEB, CPA pathways) should be confirmed and published here by the School's administration."],
            ]],
        ],
    ],

    'chaplaincy' => [
        'label' => 'Chaplaincy',
        'group' => 'Student & Alumni Services',
        'url'   => '/anuweb-network/chaplaincy-site/index.php',
        'has_programmes' => false,
        'leader_slug' => 'head-chaplaincy', 'leader_label' => 'University Chaplain',
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => "The Chaplaincy carries ANU's Church of the Nazarene heritage into daily campus life, worship, discipleship and pastoral care for every student, of every faith background."],
            ]],
        ],
    ],

    'humanities' => [
        'has_departments' => true,
        'label' => 'School of Humanities & Social Sciences',
        'group' => 'Schools',
        'url'   => '/anuweb-network/humanities-school-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'dean-humanities', 'leader_label' => 'Dean, School of Humanities & Social Sciences',
        'programme_filter' => ['type' => 'school_slug', 'value' => 'shss'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'Applied to this School specifically: the School forms graduates who understand people, communities and society well enough to serve them -- in counseling rooms, classrooms, newsrooms and peacebuilding work across Kenya and the region.'],
            ]],
        ],
    ],

    'law' => [
        'has_departments' => true,
        'label' => 'School of Law',
        'group' => 'Schools',
        'url'   => '/anuweb-network/law-school-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'dean-law', 'leader_label' => 'Dean, School of Law',
        'programme_filter' => ['type' => 'school_slug', 'value' => 'sol'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'Applied to this School specifically: the School of Law forms graduates who combine rigorous legal reasoning with a Christian ethical foundation, prepared for practice, policy and public service across Kenya and the region.'],
                'accreditation_body' => ['label' => 'Accreditation paragraph', 'type' => 'textarea',
                    'default' => "Accreditation details (Council of Legal Education, Commission for University Education) should be confirmed and published here by the School's administration."],
            ]],
        ],
    ],

    'library' => [
        'label' => 'Library',
        'group' => 'Support Units',
        'url'   => '/anuweb-network/library-site/index.php',
        'has_programmes' => false,
        'leader_slug' => 'head-library', 'leader_label' => 'University Librarian',
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'The Library supports every school and unit at ANU with physical and digital collections, research databases, and quiet and collaborative study spaces.'],
            ]],
            'catalog' => ['label' => 'Catalog page', 'fields' => [
                'catalog_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => "A searchable library catalog (books, journals, e-resources) is a separate system from this website and should be linked here once the University's library management system is confirmed."],
            ]],
        ],
    ],

    'religion' => [
        'has_departments' => true,
        'label' => 'School of Religion & Christian Ministry',
        'group' => 'Schools',
        'url'   => '/anuweb-network/religion-school-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'dean-religion', 'leader_label' => 'Dean, School of Religion & Christian Ministry',
        'programme_filter' => ['type' => 'school_slug', 'value' => 'scrm'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'Applied here: the School forms pastors, chaplains, missionaries and religion scholars grounded in the Wesleyan-holiness tradition of the Church of the Nazarene.'],
            ]],
        ],
    ],

    'research' => [
        'label' => 'Institute of Research',
        'group' => 'Support Units',
        'url'   => '/anuweb-network/research-site/index.php',
        'has_programmes' => false,
        'leader_slug' => 'head-research', 'leader_label' => 'Director of Research',
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => "The Institute coordinates faculty and student research, secures and manages research grants, and connects ANU's scholarship to the communities it serves."],
            ]],
        ],
    ],

    'science-technology' => [
        'has_departments' => true,
        'label' => 'School of Science & Technology',
        'group' => 'Schools',
        'url'   => '/anuweb-network/scitech-school-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'dean-scitech', 'leader_label' => 'Dean, School of Science & Technology',
        'programme_filter' => ['type' => 'school_slug', 'value' => 'sst'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => 'Applied to this School specifically: the School of Science & Technology forms graduates fluent in computing, IT and applied environmental science, equipped to build and maintain the technical systems the region depends on.'],
                'facilities_body' => ['label' => 'Labs & Facilities paragraph', 'type' => 'textarea',
                    'default' => "Details on computer labs, equipment and facilities should be added here by the School's administration."],
            ]],
        ],
    ],

    'sepc' => [
        'label' => 'SEPC (Short Courses & Professional Studies)',
        'group' => 'Schools',
        'url'   => '/anuweb-network/sepc-site/index.php',
        'has_programmes' => true,
        'leader_slug' => 'head-sepc', 'leader_label' => 'Head of SEPC',
        'programme_filter' => ['type' => 'unit_slug', 'value' => 'sepc'],
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => "SEPC extends ANU's mission of holistic, values-driven education to working professionals through flexible, industry-relevant short courses and certifications."],
            ]],
        ],
    ],

    'student-life' => [
        'label' => 'Student Life',
        'group' => 'Student & Alumni Services',
        'url'   => '/anuweb-network/student-life-site/index.php',
        'has_programmes' => false,
        'leader_slug' => 'head-student-life', 'leader_label' => 'Dean of Students',
        'content' => [
            'about' => ['label' => 'About page', 'fields' => [
                'about_body' => ['label' => 'About paragraph', 'type' => 'textarea',
                    'default' => "Student Life supports every student's holistic growth outside the classroom, socially, spiritually and physically, through clubs, sports, leadership and mentorship."],
            ]],
            'accommodation' => ['label' => 'Accommodation page', 'fields' => [
                'accommodation_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'On-campus housing information, room types and how to apply for a place, kept current by the Housing office.'],
                'accommodation_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "This page's content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready."],
            ]],
            'career_services' => ['label' => 'Career Services page', 'fields' => [
                'career_services_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Internship placement, career coaching and employer connections.'],
                'career_services_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "This page's content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready."],
            ]],
            'clubs' => ['label' => 'Clubs & Societies page', 'fields' => [
                'clubs_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Academic, cultural and interest-based clubs run by students, coordinated through the Dean of Students office.'],
                'clubs_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "This page's content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready."],
            ]],
            'counseling' => ['label' => 'Counseling page', 'fields' => [
                'counseling_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Confidential counseling and wellness support for students.'],
                'counseling_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "This page's content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready."],
            ]],
            'cultural_week' => ['label' => 'Cultural Week page', 'fields' => [
                'cultural_week_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => "An annual celebration of the cultural diversity of ANU's student body, drawn from across Africa and beyond."],
                'cultural_week_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "Dates and this year's programme should be confirmed and published here by the Student Life office."],
            ]],
            'exchange_program' => ['label' => 'Exchange Program page', 'fields' => (function () {
                $d = include __DIR__ . '/../../includes/exchange_defaults.php';
                $meta = ["exchange_v2_intro" => ["Introduction (blank line = new paragraph)", "textarea"], "exchange_v2_overview" => ["Program Overview (blank line = new paragraph)", "textarea"], "exchange_v2_academic" => ["Academic Excellence", "textarea"], "exchange_v2_growth" => ["Individual growth", "textarea"], "exchange_v2_apply_email" => ["Application e-mail address", "text"], "exchange_v2_apply" => ["Application Process (blank line = new paragraph)", "textarea"], "exchange_v2_eligibility" => ["Eligibility criteria (one per line)", "textarea"], "exchange_v2_faqs" => ["FAQs (one per line: Question | Answer)", "textarea"], "exchange_v2_conclusion" => ["Conclusion", "textarea"]];
                $out = [];
                foreach ($meta as $k => [$label, $type]) { $out[$k] = ['label' => $label, 'type' => $type, 'default' => $d[$k]]; }
                return $out;
            })()],
            'financial_aid' => ['label' => 'Financial Aid page', 'fields' => [
                'financial_aid_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => "Africa Nazarene University offers several financial aid packages to needy and deserving students, alumni and their families, as part of its commitment to supporting students' educational journeys."],
                'financial_aid_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => 'Specific packages, amounts and application deadlines should be confirmed and published here by the Financial Aid office.'],
            ]],
            'health_clinic' => ['label' => 'Health Clinic page', 'fields' => [
                'health_clinic_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => "The University's on-campus health clinic, supporting students' physical wellbeing alongside academics."],
                'health_clinic_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => 'Clinic hours, contact details and specific services should be confirmed and published here by the Clinic administration.'],
            ]],
            'sports' => ['label' => 'Sports & Recreation page', 'fields' => [
                'sports_intro' => ['label' => 'Intro paragraph', 'type' => 'textarea',
                    'default' => 'Campus sports teams, facilities and intramural activities.'],
                'sports_closing' => ['label' => 'Closing note', 'type' => 'textarea',
                    'default' => "This page's content sections are placeholders showing the intended structure, the Dean of Students office should populate each with current names, photos and contacts once ready."],
            ]],
        ],
    ],

    // Phase 3 (2026-09): the main site's own Orientation page, added
    // here so it can reuse the same Hero Image / featured-message / page
    // content tools every unit site already gets, rather than one-off
    // admin screens just for this page.
    'orientation-pack' => [
        'label' => 'Orientation',
        'group' => 'Main Site Pages',
        'url'   => '/anuweb-network/main-site/orientation.php',
        'has_programmes' => false,
        'leader_slug' => 'vc-welcome', 'leader_label' => "Vice Chancellor's Welcome Message",
        'content' => [
            'meeting' => ['label' => 'Orientation Meeting / Zoom Details', 'fields' => [
                'meeting_zoom_link' => ['label' => 'Meeting / Zoom link', 'type' => 'text',
                    'default' => ''],
                'meeting_zoom_details' => ['label' => 'Meeting details (date, time, passcode, etc.)', 'type' => 'textarea',
                    'default' => ''],
            ]],
        ] + orientation_admin_groups(),
    ],

];
