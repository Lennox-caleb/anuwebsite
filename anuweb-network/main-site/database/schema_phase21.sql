-- ============================================================
-- ANU Main Site -- Phase 21 (2026-09)
-- Adds: Tenders & Procurement, CSR photo gallery.
-- Safe to run on your existing anu_cms database.
-- Non-destructive: only CREATE TABLE IF NOT EXISTS.
-- Safe to re-run (idempotent).
-- ============================================================

CREATE TABLE IF NOT EXISTS tenders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  reference_no VARCHAR(100) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  summary TEXT,
  document VARCHAR(255) DEFAULT NULL,
  closing_date DATE DEFAULT NULL,
  status ENUM('open','closed','awarded') NOT NULL DEFAULT 'open',
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS csr_gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  caption TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
