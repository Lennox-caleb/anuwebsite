-- ============================================================
-- Africa Nazarene University (ANU) Website Revamp
-- Database Schema + Seed Data
-- Engine: MySQL 8 / MariaDB 10.4+
-- Charset: utf8mb4
--
-- Seed content below (schools, programmes, mission/vision, news
-- headlines, contact numbers) was copied directly from the live
-- site at https://www.anu.ac.ke/ on 2026-09-09. Replace/edit it
-- through the admin panel once the real content owners take
-- over -- nothing here is invented.
-- ============================================================

CREATE DATABASE IF NOT EXISTS anu_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anu_cms;

-- ------------------------------------------------------------
-- Admin users (admin panel back office)
-- ------------------------------------------------------------
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','editor','marketing') NOT NULL DEFAULT 'editor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username "admin" / password "AnuAdmin#2026"
-- This hash was actually generated and verified with PHP's password_hash()
-- / password_verify() — it works out of the box, no setup step needed.
-- You should still change it once you've logged in (Settings can't do
-- this yet — update admin_users.password_hash directly in phpMyAdmin
-- with a freshly generated hash for your own password).
INSERT INTO admin_users (full_name, email, username, password_hash, role) VALUES
('ANU Web Administrator', 'webadmin@anu.ac.ke', 'admin',
 '$2y$10$bjdTNRuiKcWeINvrujlMU.3b1VZOOxO/xpxVDbDlGaRe9dQzhFYOy', 'super_admin');
-- To set your own password: php -r "echo password_hash('YourNewPassword', PASSWORD_BCRYPT);"
-- then UPDATE admin_users SET password_hash = '<paste-hash-here>' WHERE username = 'admin';

-- ------------------------------------------------------------
-- Schools (faculties)
-- ------------------------------------------------------------
CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    summary VARCHAR(500),
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO schools (name, slug, summary, sort_order) VALUES
('School of Law', 'school-of-law', 'Legal education grounded in justice, ethics and advocacy.', 1),
('School of Business', 'school-of-business', 'Business, commerce and management programmes for East Africa and beyond.', 2),
('School of Science and Technology', 'school-of-science-and-technology', 'Computer science, IT and applied sciences.', 3),
('School of Humanities and Social Sciences', 'school-of-humanities-and-social-sciences', 'Peace, counseling, community development, media and education.', 4),
('School of Religion and Christian Ministry', 'school-of-religion-and-christian-ministry', 'Theology and ministry formation rooted in the Wesleyan-holiness tradition.', 5);

-- ------------------------------------------------------------
-- Programmes (courses) -- level + linked school
-- ------------------------------------------------------------
CREATE TABLE programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    level ENUM('PhD','Masters','Undergraduate','Diploma','Certificate','Other') NOT NULL,
    duration VARCHAR(100),
    mode VARCHAR(100) DEFAULT 'Full-time / Part-time / Online',
    overview TEXT,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO programmes (title, slug, level, school_id) VALUES
('Doctor of Philosophy in Business Administration and Management', 'phd-business-administration-and-management', 'PhD', 2),
('Doctor of Philosophy in Religion', 'phd-in-religion', 'PhD', 5),
('Doctor of Ministry', 'doctor-of-ministry', 'PhD', 5),
('Master of Business Administration', 'master-of-business-administration', 'Masters', 2),
('Master of Arts in Monitoring and Evaluation', 'ma-monitoring-and-evaluation', 'Masters', 4),
('Master of Science in Governance, Peace and Security', 'msc-governance-peace-security', 'Masters', 4),
('Master of Science in Environmental and Natural Resource Management', 'msc-environmental-natural-resource-management', 'Masters', 3),
('Master of Science in Applied Information Technology', 'msc-applied-information-technology', 'Masters', 3),
('Master of Arts in Religion', 'ma-religion', 'Masters', 5),
('Master of Education (Administration)', 'med-administration', 'Masters', 4),
('Bachelor of Laws (LLB)', 'bachelor-of-laws-llb', 'Undergraduate', 1),
('Bachelor of Arts in Criminal Justice and Security Management', 'ba-criminal-justice-security-management', 'Undergraduate', 4),
('Bachelor of Commerce', 'bachelor-of-commerce', 'Undergraduate', 2),
('Bachelor of Science in International Business Management', 'bsc-international-business-management', 'Undergraduate', 2),
('Bachelor of Science in Procurement and Supply Chain Management', 'bsc-procurement-supply-chain-management', 'Undergraduate', 2),
('Bachelor of Science in Computer Science', 'bsc-computer-science', 'Undergraduate', 3),
('Bachelor of Business and Information Technology', 'bachelor-business-information-technology', 'Undergraduate', 3),
('Bachelor of Science in Environmental Resource Management', 'bsc-environmental-resource-management', 'Undergraduate', 3),
('Bachelor of Mass Communication', 'bachelor-of-mass-communication', 'Undergraduate', 4),
('Bachelor of Education', 'bachelor-of-education', 'Undergraduate', 4),
('Bachelor of Arts in Peace and Conflict Resolution', 'ba-peace-conflict-resolution', 'Undergraduate', 4),
('Bachelor of Arts in Counseling Psychology', 'ba-counseling-psychology', 'Undergraduate', 4),
('Bachelor of Arts in Community Development', 'ba-community-development', 'Undergraduate', 4),
('Bachelor of Theology', 'bachelor-of-theology', 'Undergraduate', 5),
('Bachelor of Arts in Christian Ministry', 'ba-christian-ministry', 'Undergraduate', 5),
('Diploma in Peace & Conflict Studies', 'diploma-peace-conflict-studies', 'Diploma', 4),
('Diploma in Information Technology', 'diploma-information-technology', 'Diploma', 3),
('Diploma in Business Administration', 'diploma-business-administration', 'Diploma', 2),
('Certificate in Business Management', 'certificate-business-management', 'Certificate', 2);

-- ------------------------------------------------------------
-- News / articles
-- ------------------------------------------------------------
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    excerpt VARCHAR(500),
    body TEXT,
    cover_image VARCHAR(255),
    author VARCHAR(120) DEFAULT 'ANU Communications',
    published_at DATE NOT NULL,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO news (title, slug, excerpt, published_at) VALUES
('Found: Red Weaver Rediscovered in Kenya by Ornis-X Lost Birds Grant Winner',
 'red-weaver-rediscovered-in-kenya', 'ANU-linked research contributes to the rediscovery of a long-lost bird species in Kenya.', '2026-08-21'),
('Africa Nazarene University Tops Supreme Court Moot Competition',
 'anu-tops-supreme-court-moot-competition', 'The School of Law team places first in the national Supreme Court moot competition.', '2025-11-20'),
('UNESCO-TWAS 2023 Seed Grant for New African Principal Investigators Awarded to Dr. Micky Mwamuye',
 'unesco-twas-seed-grant-dr-micky-mwamuye', 'Dr. Micky Mwamuye receives the UNESCO-TWAS Seed Grant for New African Principal Investigators (SG-NAPI).', '2024-03-19');

-- ------------------------------------------------------------
-- Events
-- ------------------------------------------------------------
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    description TEXT,
    location VARCHAR(200),
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NULL,
    cover_image VARCHAR(255),
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO events (title, slug, description, location, start_datetime) VALUES
('28th Graduation Ceremony', '28th-graduation-ceremony', 'Africa Nazarene University graduation ceremony.', 'Main Campus, Ongata Rongai', '2025-07-25 09:00:00'),
('DOLPA-EA / ANU International Multidisciplinary Conference', 'dolpa-ea-anu-international-conference', 'International multidisciplinary academic conference.', 'Main Campus, Ongata Rongai', '2025-10-01 09:00:00');

-- ------------------------------------------------------------
-- Homepage hero slides / banners (admin panel-editable)
-- ------------------------------------------------------------
CREATE TABLE sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    headline VARCHAR(200) NOT NULL,
    subtext VARCHAR(400),
    image VARCHAR(255),
    link_url VARCHAR(255),
    link_text VARCHAR(100),
    link2_text VARCHAR(100),
    link2_url VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- No button text/link on the seed slide -- buttons on the hero are entirely
-- optional and only appear once an admin fills them in (Admin Panel -> Hero
-- Images / Sliders -> Link text / Link 2 text).
INSERT INTO sliders (headline, subtext, sort_order) VALUES
('What begins here, transforms the world', 'August 2026 intake in progress. Application fee KSh 1,500.', 1);

-- ------------------------------------------------------------
-- Site settings (key/value, editable in admin panel)
-- ------------------------------------------------------------
CREATE TABLE settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Africa Nazarene University'),
('tagline', 'What begins here, transforms the world'),
('mission', 'Our Mission is to provide a holistic education that equips individuals with excellent skills, competencies, and Christian Values.'),
('vision', 'Our Vision is to be the university of choice for individuals desiring Christ-centered academic excellence and transformative leadership.'),
('phone_primary', '+254 703 970 520'),
('phone_secondary', '+254 20 252 7173/4'),
('email_admissions', 'admissions@anu.ac.ke'),
('email_marketing', 'marketing@anu.ac.ke'),
('email_complaints', 'complaints@anu.ac.ke'),
('address', 'Off Magadi Road, near Ongata Rongai Town, 24km from Nairobi CBD, next to Nairobi National Park'),
('facebook_url', 'https://www.facebook.com/www.anu.ac.ke'),
('twitter_url', 'https://twitter.com/africanazarene'),
('instagram_url', 'https://www.instagram.com/africanazareneuni/'),
('linkedin_url', 'https://www.linkedin.com/school/africa-nazarene-university/'),
('youtube_url', 'https://www.youtube.com/channel/UCorYHiDyNHUENQZ59K8Ck-g'),
('tiktok_url', 'https://www.tiktok.com/@africanazarene'),
('whatsapp_url', 'https://wa.me/254703970520'),
('student_portal_url', 'https://records.anu.ac.ke/'),
('apply_url', 'https://applications.anu.ac.ke/modules/form_builder/published/anu_online_applications.php');

-- ------------------------------------------------------------
-- Fun facts (homepage highlight strip)
-- ------------------------------------------------------------
CREATE TABLE fun_facts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_value VARCHAR(50) NOT NULL,
    stat_label VARCHAR(200) NOT NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO fun_facts (stat_value, stat_label, sort_order) VALUES
('4,000+', 'students today -- the university opened its doors to just 62', 1),
('124', 'acres of savannah campus next to Nairobi National Park', 2),
('1st', 'private university accredited under the University Act without external affiliation', 3),
('5', 'schools spanning law, business, science, humanities and religion', 4);

-- ------------------------------------------------------------
-- Contact / enquiry form submissions
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Newsletter subscribers
-- ------------------------------------------------------------
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    email VARCHAR(150) NOT NULL UNIQUE,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
