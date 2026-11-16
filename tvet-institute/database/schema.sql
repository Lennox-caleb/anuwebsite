-- ============================================================================
--  Africa Nazarene University -- Non-Degree Programmes
--  Full database schema + seed data
--  Engine: MySQL 5.7+/8 or MariaDB 10.4+   |  Charset: utf8mb4
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `anu_tvet` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `anu_tvet`;

-- ----------------------------------------------------------------------------
-- Admin users (backend accounts)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','editor','registrar') NOT NULL DEFAULT 'editor',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default login -> username: admin   password: TvetAnu#2026
-- IMPORTANT: change this password immediately after your first login
-- (Admin panel -> Admin Users -> edit your account -> set a new password).
INSERT INTO `admins` (`full_name`,`username`,`email`,`password_hash`,`role`) VALUES
('Non-Degree Programmes Administrator','admin','tvet.admin@anu.ac.ke','$2b$10$90h/9OcxUeDQMJs7GTvWd.bHlrMOIgLn5uP/7wJlP2tqooov6hzl.','super_admin');

-- ----------------------------------------------------------------------------
-- Site-wide settings (key/value) -- powers topbar, footer, contact info etc.
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_key` VARCHAR(80) PRIMARY KEY,
  `setting_value` TEXT NULL
) ENGINE=InnoDB;

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('site_name','Africa Nazarene University -- Non-Degree Programmes'),
('site_tagline','Technical & Vocational Education and Training'),
('main_site_url','https://www.anu.ac.ke'),
('enaz_url','https://enaz.anu.ac.ke'),
('student_portal_url','https://portal.anu.ac.ke'),
('apply_url','https://apply.anu.ac.ke'),
('whatsapp_url','https://wa.me/254700000000'),
('phone_primary','+254 700 000 000'),
('phone_secondary','+254 730 000 000'),
('email_admissions','tvet.admissions@anu.ac.ke'),
('email_general','tvet@anu.ac.ke'),
('email_dpo','dataprotection@anu.ac.ke'),
('address','Africa Nazarene University, Ongata Rongai -- Magadi Road, P.O. Box 53067-00200, Nairobi, Kenya'),
('facebook_url','https://facebook.com/africanazareneuniversity'),
('twitter_url','https://x.com/anu_kenya'),
('instagram_url','https://instagram.com/africanazareneuniversity'),
('linkedin_url','https://linkedin.com/school/africa-nazarene-university'),
('youtube_url','https://youtube.com/@africanazareneuniversity'),
('mission','To equip learners with practical, industry-relevant, career-focused skills for self-reliance and national development, delivered within a Christ-centred, values-driven learning environment.'),
('vision','To be a leading Christian centre of excellence in technical, vocational and entrepreneurial training in the region.'),
('url_school_law','https://www.anu.ac.ke/school-of-law'),
('url_school_business','https://www.anu.ac.ke/school-of-business'),
('url_school_scitech','https://www.anu.ac.ke/school-of-science-technology'),
('google_map_embed','https://www.google.com/maps?q=Africa+Nazarene+University&output=embed'),
('total_students','1200'),
('total_programmes','12'),
('total_trainers','45'),
('years_experience','12');

-- ----------------------------------------------------------------------------
-- Departments (career clusters)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `icon` VARCHAR(60) DEFAULT 'bi-mortarboard',
  `description` VARCHAR(255) NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO `departments` (`name`,`slug`,`icon`,`description`,`sort_order`) VALUES
('Business & Management','business-management','bi-briefcase','Administration, sales, HR and supply chain','1'),
('Information Technology','information-technology','bi-cpu','Practical, industry-relevant IT skills','2'),
('Social Sciences & International Relations','social-sciences-international-relations','bi-globe','Public relations, counselling, peace, diplomacy','3'),
('Theology & Ministry','theology-ministry','bi-heart','Christian leadership and ministry formation','4'),
('Languages & Communication','languages-communication','bi-chat-square-text','English proficiency and communication skills','5');

-- ----------------------------------------------------------------------------
-- Programmes
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `programmes`;
CREATE TABLE `programmes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `department_id` INT UNSIGNED NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `level` ENUM('Certificate','Diploma','Higher Diploma','Short Course') NOT NULL DEFAULT 'Diploma',
  `duration` VARCHAR(60) DEFAULT NULL,
  `mode` VARCHAR(80) DEFAULT 'Full-Time, Evening & Weekend',
  `intake` VARCHAR(120) DEFAULT 'January, May, August',
  `examining_body` VARCHAR(150) DEFAULT 'Africa Nazarene University',
  `fee_per_year` VARCHAR(60) DEFAULT NULL,
  `entry_requirements` TEXT NULL,
  `overview` TEXT NULL,
  `career_prospects` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `programmes` (`department_id`,`title`,`slug`,`level`,`duration`,`intake`,`examining_body`,`fee_per_year`,`entry_requirements`,`overview`,`career_prospects`,`is_featured`,`sort_order`) VALUES
(1,'Diploma in Business Administration','diploma-business-administration','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','A broad-based programme covering management principles, business operations, marketing and finance, preparing learners for supervisory and administrative roles.','Business/office administrator, operations assistant, entrepreneur.',1,1),
(3,'Diploma in Public Relations','diploma-public-relations','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Covers corporate communication, media relations, event management and reputation management for organisations.','Public relations officer, communications assistant, corporate affairs assistant.',0,2),
(3,'Diploma in Counselling Psychology','diploma-counselling-psychology','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Introduces the principles and practice of counselling, human development and basic psychological support skills within a Christian ethical framework.','Counselling assistant, peer counsellor, pastoral care assistant, further study in psychology.',1,3),
(1,'Diploma in Sales and Marketing','diploma-sales-and-marketing','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Practical training in modern sales techniques, marketing strategy, digital marketing and customer relationship management.','Sales executive, marketing assistant, brand/customer relations officer.',0,4),
(1,'Diploma in Supply Chain Management','diploma-supply-chain-management','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Covers procurement, logistics, warehousing and inventory management principles used across industry and public sector.','Procurement assistant, logistics/warehouse officer, supply chain assistant.',0,5),
(1,'Diploma in Human Resource Management','diploma-human-resource-management','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Covers recruitment, training and development, employee relations and labour law fundamentals.','HR assistant, recruitment officer, training coordinator.',1,6),
(2,'Diploma in Information Technology','diploma-information-technology','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','A practical, hands-on programme covering computing fundamentals, networking, web development and IT support skills.','IT support officer, junior web developer, network support technician.',1,7),
(3,'Diploma in Peace and Conflict Studies','diploma-peace-and-conflict-studies','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Examines the causes and resolution of conflict, peacebuilding and community mediation practice.','Peacebuilding/mediation assistant, community development officer, NGO programme assistant.',0,8),
(3,'Diploma in Diplomacy and International Relations','diploma-diplomacy-and-international-relations','Diploma','2 Years','January, May, August','Africa Nazarene University',NULL,'KCSE mean grade C- or a relevant Certificate.','Introduces global affairs, diplomatic practice and international organisations.','Foreign affairs support roles, NGO/international relations assistant, further degree study.',0,9),
(4,'Certificate of Leadership in Youth Ministry','certificate-leadership-in-youth-ministry','Certificate','1 Year','January, May, August','Africa Nazarene University',NULL,'KCSE certificate or equivalent.','Equips learners for effective, Christ-centred leadership and discipleship of young people in church and community settings.','Youth pastor/leader, church ministry worker, community youth programme officer.',0,10),
(1,'Certificate in Business Management','certificate-business-management','Certificate','1 Year','January, May, August','Africa Nazarene University',NULL,'KCSE certificate or equivalent.','Foundational business and management skills for learners entering the world of work or small business ownership.','Junior business/administrative assistant, small business owner.',0,11),
(5,'Certificate / Proficiency in English Course','certificate-proficiency-in-english-course','Short Course','3–6 Months','Rolling intake','Africa Nazarene University',NULL,'Open entry; placement test may apply.','Builds spoken and written English proficiency for academic, professional and everyday communication.','Improved readiness for further study and employment; meets English proficiency requirements.',0,12);

-- ----------------------------------------------------------------------------
-- Staff / Board members / trainers
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `title` VARCHAR(150) NULL,
  `category` ENUM('leadership','board','trainer') NOT NULL DEFAULT 'trainer',
  `bio` TEXT NULL,
  `photo` VARCHAR(255) NULL,
  `email` VARCHAR(150) NULL,
  `phone` VARCHAR(60) NULL,
  `sort_order` INT DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `staff` (`full_name`,`title`,`category`,`bio`,`sort_order`) VALUES
('Revd. Charles Onyango','Head of Non-Degree Programmes','leadership','On behalf of the entire Non-Degree Programmes team, welcome. Our programmes are designed to give you practical, industry-ready competencies within a supportive, Christ-centred community. Whichever trade or profession you choose to pursue, our trainers and modern workshops are here to help you succeed and serve with excellence.','1'),
('Mr. Samuel Kiptoo','Chairman, Non-Degree Programmes Board','board','Brings over 20 years of experience in technical education administration and industry partnerships.','2'),
('Mrs. Nancy Achieng','Board Member -- Industry Liaison','board','Represents the interests of employer partners and industrial attachment hosts on the Institute board.','3'),
('Mr. Peter Mwangi','Board Member -- Quality Assurance','board','Oversees curriculum quality and academic standards across all programmes.','4'),
('Eng. Daniel Otieno','Senior Trainer -- Information Technology','trainer','Experienced IT professional and trainer specialising in networking and web development.','5'),
('Ms. Faith Nyambura','Senior Trainer -- Business & Management','trainer','KASNEB-registered accountant with a passion for entrepreneurship and HR training.','6'),
('Rev. Samuel Kiplangat','Senior Trainer -- Theology & Ministry','trainer','Ordained minister and trainer with a heart for raising the next generation of youth leaders.','7');

-- ----------------------------------------------------------------------------
-- Hero sliders
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `subtitle` VARCHAR(255) NULL,
  `image` VARCHAR(255) NULL,
  `cta_text` VARCHAR(60) DEFAULT 'Apply Now',
  `cta_link` VARCHAR(255) DEFAULT '#',
  `sort_order` INT DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO `sliders` (`title`,`subtitle`,`cta_text`,`cta_link`,`sort_order`) VALUES
('Skills that build careers.','Industry-aligned, competency-based TVET training within a Christ-centred learning community.','Apply Now','apply.php',1),
('12 career-focused programmes. One Institute.','Diploma and Certificate courses in Business, IT, Social Sciences and Ministry.','View Programmes','programmes.php',2);

-- ----------------------------------------------------------------------------
-- News / blog
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL UNIQUE,
  `excerpt` VARCHAR(400) NULL,
  `body` LONGTEXT NULL,
  `image` VARCHAR(255) NULL,
  `category` VARCHAR(80) DEFAULT 'News',
  `is_published` TINYINT(1) DEFAULT 1,
  `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `news` (`title`,`slug`,`excerpt`,`body`,`category`,`published_at`) VALUES
('August 2026 intake now open','august-2026-intake-now-open','Applications are now open for the August 2026 intake across our Diploma and Certificate programmes.','Non-Degree Programmes is now receiving applications for the August 2026 intake. Prospective students can choose from Diploma programmes in Business Administration, Public Relations, Counselling Psychology, Sales and Marketing, Supply Chain Management, Human Resource Management, Information Technology, Peace and Conflict Studies, and Diplomacy and International Relations, alongside Certificate programmes including Leadership in Youth Ministry, Business Management, and the Proficiency in English Course. Visit the Programmes page for full details or apply online today.','Admissions','2026-06-20 09:00:00'),
('Call for entrepreneurship as graduates commissioned','call-for-entrepreneurship-graduates-commissioned','This year''s graduation ceremony celebrated the commissioning of certified artisans, craft and diploma holders.','Guided by the theme "The Entrepreneurial Mindset: Cultivating Innovation and Creativity," graduates from Level 4 to Level 6 programmes were celebrated for completing their training. Speakers urged graduates to consider self-employment and enterprise creation alongside formal job placement.','Graduation','2026-06-15 09:00:00'),
('New Cybersecurity short course launched','new-cybersecurity-short-course-launched','A 3-month intensive short course in cybersecurity fundamentals is now open for enrolment.','Responding to growing regional demand for digital-security skills, the ICT & Engineering department has launched a rolling-intake short course covering network defence, ethical hacking basics and security operations fundamentals.','Programmes','2026-08-01 09:00:00');

-- ----------------------------------------------------------------------------
-- Events
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(220) NOT NULL,
  `slug` VARCHAR(240) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `location` VARCHAR(200) NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NULL,
  `image` VARCHAR(255) NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `events` (`title`,`slug`,`description`,`location`,`start_date`,`end_date`) VALUES
('January 2027 Intake Orientation','january-2027-intake-orientation','Orientation day for all new Diploma and Certificate students joining the January intake.','ANU Main Campus, Ongata Rongai','2027-01-12 08:00:00','2027-01-12 17:00:00'),
('Industry & Careers Open Day','industry-careers-open-day','Meet industry partners, tour the workshops and speak with current trainers and students.','ANU Main Campus, Ongata Rongai','2026-11-20 09:00:00','2026-11-20 16:00:00'),
('TVET Skills & Innovation Week','tvet-skills-innovation-week','A week-long showcase of student projects, trade demonstrations and guest industry talks.','ANU Main Campus, Ongata Rongai','2026-10-05 08:00:00','2026-10-09 17:00:00');

-- ----------------------------------------------------------------------------
-- Online applications
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_no` VARCHAR(30) NOT NULL UNIQUE,
  `full_name` VARCHAR(150) NOT NULL,
  `gender` ENUM('Male','Female') NULL,
  `dob` DATE NULL,
  `id_or_birth_cert_no` VARCHAR(60) NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `county` VARCHAR(80) NULL,
  `nationality` VARCHAR(80) DEFAULT 'Kenyan',
  `programme_id` INT UNSIGNED NULL,
  `preferred_intake` VARCHAR(40) NULL,
  `mode_of_study` VARCHAR(60) NULL,
  `education_background` TEXT NULL,
  `sponsor` VARCHAR(150) NULL,
  `status` ENUM('submitted','under_review','shortlisted','admitted','rejected') NOT NULL DEFAULT 'submitted',
  `admin_notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`programme_id`) REFERENCES `programmes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Contact messages
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `subject` VARCHAR(200) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Downloads (prospectus, forms, fee structures)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `description` VARCHAR(255) NULL,
  `file` VARCHAR(255) NOT NULL,
  `category` VARCHAR(80) DEFAULT 'General',
  `is_published` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Gallery
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NULL,
  `image` VARCHAR(255) NOT NULL,
  `category` VARCHAR(80) DEFAULT 'Campus Life',
  `sort_order` INT DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- Testimonials
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `programme` VARCHAR(200) NULL,
  `photo` VARCHAR(255) NULL,
  `quote` TEXT NOT NULL,
  `is_published` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO `testimonials` (`full_name`,`programme`,`quote`,`sort_order`) VALUES
('Esther M.','Diploma in Information Technology','The hands-on approach here meant I was already building real projects before I graduated. I secured an internship within a month.',1),
('Kevin O.','Diploma in Business Administration','Small class sizes and dedicated trainers made all the difference. I now run my own small business with confidence.',2),
('Mercy W.','Certificate of Leadership in Youth Ministry','The trainers don''t just teach theory -- they mentor you. I felt fully ready to serve in ministry by the time I graduated.',3);

-- ----------------------------------------------------------------------------
-- FAQs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO `faqs` (`question`,`answer`,`sort_order`) VALUES
('Are the Non-Degree Programmes accredited?','Non-Degree Programmes are offered by Africa Nazarene University, a university chartered by the Commission for University Education (CUE) under the Universities Act, 2012. Where a programme leads to a professional or regulatory qualification, this is indicated on the individual programme page.',1),
('What are the minimum entry requirements?','Requirements vary by programme: most Diploma programmes require a KCSE mean grade of C- (or a relevant Certificate), while Certificate programmes generally require a KCSE certificate. See each programme page for specifics.',2),
('When do intakes take place?','The Institute admits new students in January, May and August each year, with select short courses on rolling admission.',3),
('Can I pay fees in instalments?','Yes, subject to a payment plan approved by the Finance Office at the time of admission.',4),
('Do programmes include industrial attachment?','Yes, most Diploma programmes include a practicum or industrial attachment component to build workplace-ready experience.',5),
('Can international students apply?','Yes. International applicants are welcome and should provide equivalent academic documentation plus a valid passport; contact the Admissions Office for guidance on credential recognition.',6);

-- ----------------------------------------------------------------------------
-- Partners / accreditation bodies
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `logo` VARCHAR(255) NULL,
  `url` VARCHAR(255) NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO `partners` (`name`,`url`,`sort_order`) VALUES
('Commission for University Education (CUE)','https://www.cue.or.ke',1),
('Church of the Nazarene','https://nazarene.org',2),
('Kenya National Qualifications Authority (KNQA)','https://knqa.go.ke',3);

-- ----------------------------------------------------------------------------
-- Page banners -- optional hero/header background image per inner page
-- (Home uses `sliders` instead; this covers About, Programmes, Admissions, etc.)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `page_banners`;
CREATE TABLE `page_banners` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `page_key` VARCHAR(60) NOT NULL UNIQUE,
  `image` VARCHAR(255) NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
