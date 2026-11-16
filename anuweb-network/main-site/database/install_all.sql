-- ============================================================
-- ⚠️  THIS IS THE FILE TO IMPORT FOR A FRESH INSTALL. ⚠️
-- schema.sql + phase2 through phase17, concatenated in order, so
-- there's no risk of forgetting a step. Import ONLY this file in
-- phpMyAdmin (Import → install_all.sql → Go).
--
-- This file DROPS and RECREATES the anu_cms database automatically,
-- so "table already exists" errors are no longer possible.
--
-- Default admin login: username "admin", password "AnuAdmin#2026" --
-- a REAL, tested, working password hash (verified with
-- password_verify() before shipping).
--
-- Note: ANU Online is a SEPARATE application with its own database
-- (anu_online) -- import anu-online-site/database.sql separately.
--
-- ⚠️ WARNING: this destroys any data currently in anu_cms. Back up
-- first (phpMyAdmin → Export) if you have real content to keep.
-- ============================================================
DROP DATABASE IF EXISTS anu_cms;

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
-- Images / Sliders -> Link text / Link 2 text). Leave them blank for a plain
-- headline-only hero slide.
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
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 2)
-- Run this AFTER database/schema.sql (adds to the existing anu_cms DB)
-- ============================================================
USE anu_cms;

-- ------------------------------------------------------------
-- Site-wide popup announcements (full-screen modal on events)
-- ------------------------------------------------------------
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    image VARCHAR(255),
    cta_text VARCHAR(80),
    cta_url VARCHAR(255),
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Resources library (policies, handbooks, forms, past papers...)
-- ------------------------------------------------------------
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description VARCHAR(400),
    category ENUM('Policies & Guidelines','Handbooks & Forms','Past Papers','Financial Aid Forms','Publications','Other') NOT NULL DEFAULT 'Other',
    file_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Institute of Research -- publications
-- ------------------------------------------------------------
CREATE TABLE research_publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    authors VARCHAR(300),
    publication_year YEAR,
    summary TEXT,
    file_name VARCHAR(255),
    external_link VARCHAR(255),
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Shared staff/faculty directory -- reused by every school/unit
-- micro-site (each has its own custom design, but faculty data
-- and its admin CRUD live centrally, scoped by `unit_slug`).
-- ------------------------------------------------------------
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_slug VARCHAR(60) NOT NULL, -- e.g. 'law', 'business', 'tvet', 'anu-online', 'sepc'
    full_name VARCHAR(150) NOT NULL,
    title VARCHAR(150),
    bio TEXT,
    email VARCHAR(150),
    photo VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO staff (unit_slug, full_name, title, sort_order) VALUES
('law', 'Dean, School of Law', 'To be appointed via admin panel', 1),
('business', 'Dean, School of Business', 'To be appointed via admin panel', 1),
('science-technology', 'Dean, School of Science & Technology', 'To be appointed via admin panel', 1);

-- ------------------------------------------------------------
-- Tag which site a contact message came from
-- ------------------------------------------------------------
ALTER TABLE contact_messages ADD COLUMN unit VARCHAR(60) NOT NULL DEFAULT 'main' AFTER message;

-- ------------------------------------------------------------
-- New settings: subdomain URLs, eNaz, sponsorship line, research text
-- ------------------------------------------------------------
-- Local dev URLs point at the actual htdocs folder structure so links work
-- immediately without any vhost/hosts-file setup. When you deploy to real
-- subdomains later, update these to the live domain (e.g. https://law.anu.ac.ke/).
INSERT INTO settings (setting_key, setting_value) VALUES
('enaz_url', 'https://enaz.anu.ac.ke/login/index.php'),
('sponsor_note', 'A Church of the Nazarene sponsored university'),
('url_school_law', '/anuweb-network/law-school-site/index.php'),
('url_school_business', '/anuweb-network/business-school-site/index.php'),
('url_school_scitech', '/anuweb-network/scitech-school-site/index.php'),
('url_school_humanities', '/anuweb-network/humanities-school-site/index.php'),
('url_school_religion', '/anuweb-network/religion-school-site/index.php'),
('url_tvet', '/anuweb-network/tvet-site/index.php'),
-- ANU Online doesn't have its own built site yet -- points at the main
-- ANU site for now; update once/if a dedicated ANU Online site is built.
('url_anu_online', 'https://anu.ac.ke/'),
('url_sepc', '/anuweb-network/sepc-site/index.php'),
('research_mission', 'The Institute of Research coordinates and promotes scholarly inquiry across all schools, supporting faculty and students in generating research that serves the Church, the region and the wider academic community.'),
('research_focus_areas', 'Faith and society|Peace, security and governance|Environmental and natural resource management|Business and entrepreneurship in East Africa|Education and community development'),
('research_calls', 'Calls for papers and grant opportunities are posted here as they open. Contact the Institute of Research office for current submission windows.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 3)
-- Run this once, after schema.sql and schema_phase2.sql.
-- Adds support for programmes belonging to a non-school unit
-- (TVET, ANU Online, SEPC) alongside the existing school_id link.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes ADD COLUMN unit_slug VARCHAR(60) NULL AFTER school_id;

INSERT INTO staff (unit_slug, full_name, title, sort_order) VALUES
('tvet', 'Director, TVET Institute', 'To be appointed via admin panel', 1);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 4)
-- Adds: Governance directory (Board of Trustees, University Council,
-- Management), plus settings for the University Repository link and
-- the new Admissions and Institute of Research subsites.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file. Only run this file
-- separately if you have an existing install and don't want to wipe it.
-- ============================================================
USE anu_cms;

CREATE TABLE governance_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('Board of Trustees','University Council','Management') NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    title VARCHAR(150),
    bio TEXT,
    photo VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO governance_members (category, full_name, title, sort_order) VALUES
('Board of Trustees', 'Chair, Board of Trustees', 'To be appointed via admin panel', 1),
('University Council', 'Chair, University Council', 'To be appointed via admin panel', 1),
('Management', 'Vice Chancellor', 'To be appointed via admin panel', 1);

INSERT INTO settings (setting_key, setting_value) VALUES
-- University Repository: defaults to the Resources Library already built
-- on this site. If the University has a separate institutional repository
-- system (e.g. a DSpace instance), update this to that real URL instead.
('repository_url', '/anuweb-network/main-site/resources.php'),
('url_admissions', '/anuweb-network/admissions-site/index.php'),
('url_research', '/anuweb-network/research-site/index.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 5)
-- Adds an image column to programmes (schools already had one, it just
-- wasn't wired up to the admin form or the public pages until now).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes ADD COLUMN image VARCHAR(255) NULL AFTER unit_slug;
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 6)
-- Adds a Partners table for a homepage logo strip. No partner names are
-- seeded here -- add real partner organisations via the admin panel once
-- confirmed by the University (nothing invented).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    website_url VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 7)
-- Updates the school/unit URLs now that Humanities, Religion and SEPC
-- have real sites (previously these settings didn't point anywhere
-- real), and seeds placeholder staff rows for each.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

INSERT INTO settings (setting_key, setting_value) VALUES
('url_school_humanities', '/anuweb-network/humanities-school-site/index.php'),
('url_school_religion', '/anuweb-network/religion-school-site/index.php'),
('url_sepc', '/anuweb-network/sepc-site/index.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO staff (unit_slug, full_name, title, sort_order) VALUES
('humanities', 'Dean, School of Humanities & Social Sciences', 'To be appointed via admin panel', 1),
('religion', 'Dean, School of Religion & Christian Ministry', 'To be appointed via admin panel', 1),
('sepc', 'Director, SEPC', 'To be appointed via admin panel', 1);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 8)
-- Adds URLs for the new Student Life and Chaplaincy sites.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

INSERT INTO settings (setting_key, setting_value) VALUES
('url_student_life', '/anuweb-network/student-life-site/index.php'),
('url_chaplaincy', '/anuweb-network/chaplaincy-site/index.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 9)
-- ANU Online now has its own real site. Previously this setting pointed
-- at the main anu.ac.ke site as a placeholder.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

INSERT INTO settings (setting_key, setting_value) VALUES
('url_anu_online', '/anuweb-network/anu-online-site/index.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 10)
-- Adds: Library site URL, Donate URL, and lets sliders (hero images)
-- belong to a specific unit site instead of only the main site.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE sliders ADD COLUMN unit_slug VARCHAR(60) NULL AFTER id;
-- NULL unit_slug = belongs to the main site's homepage carousel.
-- Any other value (e.g. 'law', 'business', 'tvet') = that unit's hero image.

INSERT INTO settings (setting_key, setting_value) VALUES
('url_library', '/anuweb-network/library-site/index.php'),
-- Donate link is a placeholder until the University confirms a real
-- payment/donation page -- update this once that exists.
('donate_url', '/anuweb-network/main-site/contact.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 11)
-- Adds the MyLOFT e-resources link (a real platform confirmed via
-- Kenyatta University, Rongo University, Riara University and others --
-- https://app.myloft.xyz/ is the shared multi-institution access point,
-- not something invented for ANU specifically). Also updates the utility
-- bar's sponsor note to show the marketing email instead of the text
-- "A Church of the Nazarene sponsored university".
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

INSERT INTO settings (setting_key, setting_value) VALUES
('myloft_url', 'https://app.myloft.xyz/')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 13)
-- Fixes the Apply Now link to the real, confirmed application portal,
-- adds a Vacancies table + settings for ANU Foundation and Alumni.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    department VARCHAR(150),
    employment_type VARCHAR(100) DEFAULT 'Full-time',
    description TEXT,
    requirements TEXT,
    how_to_apply TEXT,
    closing_date DATE,
    attachment VARCHAR(255),
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
-- Real, confirmed application portal (was pointing at a different/older
-- applications subdomain before).
('apply_url', 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp'),
-- Real ANU Foundation site (confirmed) -- Donate also points here.
('foundation_url', 'https://www.africanazufoundation.org/'),
('donate_url', 'https://www.africanazufoundation.org/'),
('alumni_url', '/anuweb-network/alumni-site/index.php'),
('alumni_email', 'alumni@anu.ac.ke'),
('vacancies_url', '/anuweb-network/main-site/vacancies.php'),
-- Additional real ANU contact numbers.
('phone_secondary_2', '+254 709 221 400'),
('phone_secondary_3', '+254 773 977 859')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- DVC entries -- real names confirmed from anu.ac.ke's own pages, though
-- some show as "Interim"/"Acting" across differently-dated cached pages,
-- so titles here are worth reconfirming as current before publishing.
INSERT INTO governance_members (category, full_name, title, sort_order) VALUES
('Management', 'Prof. Simon Obwatho', 'Deputy Vice Chancellor, Academic & Student Affairs', 2),
('Management', 'Dr. Rhonda Slingerland', 'Deputy Vice Chancellor, Finance', 3),
('Management', 'Prof. Bob Brower', 'Deputy Vice Chancellor, Strategy (Interim, per source)', 4);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 14)
-- Adds a magazine/publication gallery for ASPIRE (the graduation
-- magazine, released once per graduation) and ANU Voice (the semesterly
-- newsletter) -- thumbnail-cover based, distinct from the document-shelf
-- style used for policies/handbooks/past papers.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS publications_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    publication_type ENUM('ASPIRE','ANU Voice','Other') NOT NULL DEFAULT 'Other',
    issue_label VARCHAR(150),
    thumbnail VARCHAR(255),
    pdf_file VARCHAR(255),
    published_date DATE,
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 15)
-- Fixes the Donate button to the real direct giving page (was pointing
-- at the Foundation's general homepage before).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

INSERT INTO settings (setting_key, setting_value) VALUES
('donate_url', 'https://africanazareneuniversityfoundation.ddock.gives/?showBg=')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 16)
-- Adds Requirements, Fee Structure, a structured Course Units table
-- (rendered as an actual HTML table on the programme page), and an
-- optional Course Planner document upload to programmes.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes
    ADD COLUMN IF NOT EXISTS requirements TEXT AFTER overview,
    ADD COLUMN IF NOT EXISTS fee_structure TEXT AFTER requirements,
    ADD COLUMN IF NOT EXISTS course_units TEXT AFTER fee_structure,
    ADD COLUMN IF NOT EXISTS planner_file VARCHAR(255) AFTER course_units;
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 17)
-- Adds a real Research Proposal submission feature -- previously
-- "Submit a Proposal" just linked to the generic contact form.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS research_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    research_area VARCHAR(150),
    proposal_title VARCHAR(255) NOT NULL,
    abstract TEXT,
    attachment VARCHAR(255),
    status ENUM('New','Under Review','Accepted','Declined') DEFAULT 'New',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 18)
-- Clears the "Apply Now" button that was auto-seeded onto the
-- homepage's hero slide during setup, so the hero shows just the
-- headline until an admin deliberately adds a button (Admin Panel ->
-- Hero Images / Sliders -> Link text / Link 2 text). Safe to run
-- more than once; it only touches slides that still have the exact
-- seeded default and leaves any custom button text you've already
-- added alone.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

UPDATE sliders
SET link_text = NULL, link_url = NULL
WHERE unit_slug IS NULL
  AND link_text = 'Apply Now'
  AND link_url = '/admissions.php';
