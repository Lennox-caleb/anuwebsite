-- ============================================================
-- ANU Main Site -- Phase 23 (2026-09)
-- Adds "Graduation List" as its own publication type, alongside
-- ASPIRE and ANU Voice, for Senate-approved graduation lists.
-- Safe to run on your existing database. Idempotent.
-- ============================================================

ALTER TABLE publications_gallery
  MODIFY COLUMN publication_type ENUM('ASPIRE','ANU Voice','Graduation List','Other') NOT NULL DEFAULT 'Other';
