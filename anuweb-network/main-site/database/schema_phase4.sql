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
