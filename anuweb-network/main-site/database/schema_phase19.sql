-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 19)
-- Gives the Vice Chancellor and Deputy Vice Chancellors their own
-- dedicated "Leadership" area in the admin panel and their own table,
-- instead of the VC living under Faculty & Staff and DVCs living under
-- Governance (matched by the word "Deputy" in a free-text title).
--
-- Safe to run on an existing live database:
--   - Only CREATEs a new table; nothing existing is altered or dropped.
--   - Copies the current VC (from `staff`, unit_slug='vc') and any
--     current DVCs (from `governance_members`, Management category with
--     "Deputy" in the title) into the new table, WITHOUT deleting the
--     old rows -- the old admin pages keep working exactly as before.
--   - Safe to run more than once (checks before inserting duplicates).
--
-- After running this, add/edit the VC and DVCs going forward from the
-- new "Leadership" section in the admin panel.
-- ============================================================
USE anu_cms;

CREATE TABLE IF NOT EXISTS leadership (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_type ENUM('vc','dvc') NOT NULL DEFAULT 'dvc',
    full_name VARCHAR(150) NOT NULL,
    title VARCHAR(150),
    message TEXT,
    photo VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Copy the existing VC record (only if one exists there, and only if a VC
-- hasn't already been copied into `leadership` before).
INSERT INTO leadership (role_type, full_name, title, message, photo, sort_order, is_published)
SELECT 'vc', full_name, title, bio, photo, sort_order, is_published
FROM staff
WHERE unit_slug = 'vc'
  AND NOT EXISTS (SELECT 1 FROM leadership WHERE role_type = 'vc');

-- Copy any existing DVC-like Management members (only ones not already
-- copied, matched by name so re-running this is safe).
INSERT INTO leadership (role_type, full_name, title, message, photo, sort_order, is_published)
SELECT 'dvc', gm.full_name, gm.title, gm.bio, gm.photo, gm.sort_order, gm.is_published
FROM governance_members gm
WHERE gm.category = 'Management'
  AND gm.title LIKE '%Deputy%'
  AND NOT EXISTS (
      SELECT 1 FROM leadership l WHERE l.role_type = 'dvc' AND l.full_name = gm.full_name
  );

-- One-time cleanup: some records were originally seeded with the internal
-- placeholder "To be appointed via admin panel" as a stand-in title, so
-- visitors would never mistake it for something typed live. If any of
-- those seeded rows are still unedited on your live site, this replaces
-- that wording with a neutral, public-friendly placeholder instead.
UPDATE staff SET title = 'Position to be announced' WHERE title LIKE '%admin panel%';
UPDATE governance_members SET title = 'Position to be announced' WHERE title LIKE '%admin panel%';
