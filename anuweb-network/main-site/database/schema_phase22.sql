-- ============================================================
-- ANU Main Site -- Phase 22 (2026-09)
-- Adds: uploadable Fee Structure document per programme,
-- and a Chaplaincy ministries/programs table (admin-manageable,
-- shown on the new public Chaplaincy page).
-- Safe to run on your existing anu_cms database. Idempotent.
-- ============================================================

ALTER TABLE programmes ADD COLUMN IF NOT EXISTS fee_structure_file VARCHAR(255) DEFAULT NULL AFTER fee_structure;

-- Normalise mode of study on every existing programme to the house rule:
-- Masters and PhD are always Online; everything else is Physical unless
-- already correctly set to Online. Safe to re-run.
UPDATE programmes SET mode = 'Online'   WHERE level IN ('Masters','PhD') AND mode <> 'Online';
UPDATE programmes SET mode = 'Physical' WHERE level NOT IN ('Masters','PhD') AND mode NOT IN ('Online','Physical');

CREATE TABLE IF NOT EXISTS chaplaincy_programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  icon VARCHAR(60) DEFAULT 'bi-heart',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
