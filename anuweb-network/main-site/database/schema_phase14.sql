-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 14)
-- Adds a magazine/publication gallery for ASPIRE (the graduation
-- magazine, released once per graduation) and ANU Voice (the semesterly
-- newsletter) -- thumbnail-cover based, distinct from the document-shelf
-- style used for policies/handbooks/past papers.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS publications_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    publication_type ENUM('ASPIRE','ANU Voice','Other') NOT NULL DEFAULT 'Other',
    issue_label VARCHAR(150),
    thumbnail VARCHAR(255),
    pdf_file VARCHAR(255),
    published_date DATE,
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
