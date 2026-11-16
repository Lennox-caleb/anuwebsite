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
