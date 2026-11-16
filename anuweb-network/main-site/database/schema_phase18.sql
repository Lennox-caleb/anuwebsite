-- ============================================================
-- ANU Website Network -- Schema Additions (Phase 18)
-- Clears the "Apply Now" button that was auto-seeded onto the
-- homepage's hero slide during setup, so the hero shows just the
-- headline until an admin deliberately adds a button (Admin Panel ->
-- Hero Images / Sliders -> Link text / Link 2 text). Safe to run
-- more than once; it only touches slides that still have the exact
-- seeded default and leaves any custom button text you've already
-- added alone.
-- Note: if you're doing a fresh install, just use install_all.sql --
-- it already includes everything in this file.
-- ============================================================
USE anu_cms;

UPDATE sliders
SET link_text = NULL, link_url = NULL
WHERE unit_slug IS NULL
  AND link_text = 'Apply Now'
  AND link_url = '/admissions.php';
