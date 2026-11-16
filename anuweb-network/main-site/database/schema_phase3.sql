-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 3)
-- Run this once, after schema.sql and schema_phase2.sql.
-- Adds support for programmes belonging to a non-school unit
-- (TVET, ANU Online, SEPC) alongside the existing school_id link.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes ADD COLUMN unit_slug VARCHAR(60) NULL AFTER school_id;

INSERT INTO staff (unit_slug, full_name, title, sort_order) VALUES
('tvet', 'Director, TVET Institute', 'To be appointed via admin panel', 1);
