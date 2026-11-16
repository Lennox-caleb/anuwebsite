-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 17)
-- Adds a real Research Proposal submission feature -- previously
-- "Submit a Proposal" just linked to the generic contact form.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS research_proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    research_area VARCHAR(150),
    proposal_title VARCHAR(255) NOT NULL,
    abstract TEXT,
    attachment VARCHAR(255),
    status ENUM('New','Under Review','Accepted','Declined') DEFAULT 'New',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
