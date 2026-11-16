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
