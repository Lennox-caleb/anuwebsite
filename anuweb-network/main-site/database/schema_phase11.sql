-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 11)
-- Adds a second, independent button (text + link) to hero slides,
-- so the homepage carousel no longer auto-shows a hardcoded
-- "Apply Now" / "Explore Schools" pair on every slide. Both buttons
-- are now entirely optional and only appear when an admin fills
-- them in on that slide (Admin Panel -> Hero Images / Sliders).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE sliders ADD COLUMN IF NOT EXISTS link2_text VARCHAR(100) NULL AFTER link_text;
ALTER TABLE sliders ADD COLUMN IF NOT EXISTS link2_url  VARCHAR(255) NULL AFTER link2_text;
