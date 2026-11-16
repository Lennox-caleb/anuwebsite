-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 12)
-- Adds a research grants table for the Institute of Research site.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE research_grants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    funder VARCHAR(200),
    amount VARCHAR(100),
    grant_year YEAR,
    description TEXT,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Real grant already referenced elsewhere on the main site's news feed
-- (UNESCO-TWAS 2023 Seed Grant awarded to Dr. Micky Mwamuye) -- included
-- here too since it's confirmed, not invented.
INSERT INTO research_grants (title, funder, grant_year, description) VALUES
('UNESCO-TWAS 2023 Seed Grant for New African Principal Investigators', 'UNESCO-TWAS', 2023, 'Awarded to Dr. Micky Mwamuye of Africa Nazarene University.');
