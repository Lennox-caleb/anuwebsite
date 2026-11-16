-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 6)
-- Adds a Partners table for a homepage logo strip. No partner names are
-- seeded here -- add real partner organisations via the admin panel once
-- confirmed by the University (nothing invented).
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    website_url VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
