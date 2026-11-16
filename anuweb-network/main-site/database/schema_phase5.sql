-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 5)
-- Adds an image column to programmes (schools already had one, it just
-- wasn't wired up to the admin form or the public pages until now).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes ADD COLUMN image VARCHAR(255) NULL AFTER unit_slug;
