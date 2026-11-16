-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 20)
-- Adds the Chancellor to the same Leadership admin section as the
-- Vice Chancellor and Deputy Vice Chancellors (previously the
-- Chancellor page was hardcoded with no admin panel connection at
-- all -- this gives it one).
--
-- Safe to run on an existing live database: only widens the existing
-- `role_type` column to allow 'chancellor' as a value; no data is
-- changed or removed. Safe to run more than once.
-- ============================================================
USE anu_cms;

ALTER TABLE leadership MODIFY COLUMN role_type ENUM('vc','dvc','chancellor') NOT NULL DEFAULT 'dvc';
