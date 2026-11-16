-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 16)
-- Adds Requirements, Fee Structure, a structured Course Units table
-- (rendered as an actual HTML table on the programme page), and an
-- optional Course Planner document upload to programmes.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

ALTER TABLE programmes
    ADD COLUMN IF NOT EXISTS requirements TEXT AFTER overview,
    ADD COLUMN IF NOT EXISTS fee_structure TEXT AFTER requirements,
    ADD COLUMN IF NOT EXISTS course_units TEXT AFTER fee_structure,
    ADD COLUMN IF NOT EXISTS planner_file VARCHAR(255) AFTER course_units;
