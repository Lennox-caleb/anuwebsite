-- ============================================================
-- ANU Main Site -- unit-site links (2026-09, v3)
-- Safe to run on your existing anu_cms database.
-- Non-destructive: only updates existing `settings` rows.
-- Safe to re-run (idempotent). Re-running this file supersedes
-- update-unit-links-2026-09.sql (v2) -- all 13 units + TVET are
-- now real, built sites.
--
-- CURRENT STATE -- every unit now has a real local site:
--   Main site              -> /anuweb-network/main-site
--   Non-Degree / TVET      -> /tvet-institute
--   School of Law          -> /anuweb-network/law-school-site
--   School of Business     -> /anuweb-network/business-school-site
--   School of Sci & Tech   -> /anuweb-network/scitech-school-site
--   School of Humanities   -> /anuweb-network/humanities-school-site
--   School of Religion     -> /anuweb-network/religion-school-site
--   Admissions             -> /anuweb-network/admissions-site
--   SEPC                   -> /anuweb-network/sepc-site
--   Institute of Research  -> /anuweb-network/research-site
--   Student Life           -> /anuweb-network/student-life-site
--   Chaplaincy             -> /anuweb-network/chaplaincy-site
--   ANU Online             -> /anuweb-network/anu-online-site
--   Library                -> /anuweb-network/library-site
--   Alumni                 -> /anuweb-network/alumni-site
--
-- IMPORTANT: place each of the 7 new folders directly under
-- htdocs/anuweb-network/ alongside the others, e.g.
--   C:\xampp1\htdocs\anuweb-network\sepc-site\
--   C:\xampp1\htdocs\anuweb-network\research-site\
-- etc. They all read the SAME anu_cms database as the main site --
-- no separate database or extra setup needed for them.
-- ============================================================

INSERT INTO settings (setting_key, setting_value) VALUES
  ('url_sepc', '/anuweb-network/sepc-site/index.php'),
  ('url_research', '/anuweb-network/research-site/index.php'),
  ('url_student_life', '/anuweb-network/student-life-site/index.php'),
  ('url_chaplaincy', '/anuweb-network/chaplaincy-site/index.php'),
  ('url_anu_online', '/anuweb-network/anu-online-site/index.php'),
  ('url_library', '/anuweb-network/library-site/index.php'),
  ('alumni_url', '/anuweb-network/alumni-site/index.php')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
