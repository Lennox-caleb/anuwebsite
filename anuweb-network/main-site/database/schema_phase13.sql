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
