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
