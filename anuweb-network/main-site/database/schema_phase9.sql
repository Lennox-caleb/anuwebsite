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
