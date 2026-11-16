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
