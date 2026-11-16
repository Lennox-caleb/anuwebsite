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
