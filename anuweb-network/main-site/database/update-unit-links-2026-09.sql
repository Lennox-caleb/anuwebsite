-- ============================================================
-- ANU Main Site -- fix unit-site links (2026-09, v2)
-- Safe to run on your existing anu_cms database.
-- Non-destructive: only updates existing `settings` rows.
-- Safe to re-run (idempotent). Re-running this file replaces the
-- effect of the earlier update-unit-links-2026-09.sql you may have
-- already run -- this version supersedes it.
--
-- CURRENT STATE (as of the 6 real unit sites you've now provided):
--   Real, working local sites:
--     - Main site            -> /anuweb-network/main-site
--     - Non-Degree / TVET    -> /tvet-institute  (its own separate project)
--     - School of Law        -> /anuweb-network/law-school-site
--     - School of Business   -> /anuweb-network/business-school-site
--     - School of Sci & Tech -> /anuweb-network/scitech-school-site
--     - School of Humanities -> /anuweb-network/humanities-school-site
--     - School of Religion   -> /anuweb-network/religion-school-site
--     - Admissions           -> /anuweb-network/admissions-site
--
--   Not yet built (still point at the main site's "Coming Soon" page
--   until you provide/build them, same as before):
--     - SEPC, ANU Online, Student Life, Chaplaincy, Library,
--       Institute of Research, Alumni
--
-- IMPORTANT: place each of the 6 uploaded folders directly under
-- htdocs/anuweb-network/ alongside main-site, e.g.
--   C:\xampp1\htdocs\anuweb-network\law-school-site\
--   C:\xampp1\htdocs\anuweb-network\business-school-site\
-- etc. They all read the SAME anu_cms database as the main site --
-- no separate database or extra setup needed for them.
-- ============================================================

INSERT INTO settings (setting_key, setting_value) VALUES
  -- Real sites now provided
  ('url_tvet', '/tvet-institute/index.php'),
  ('url_school_law', '/anuweb-network/law-school-site/index.php'),
  ('url_school_business', '/anuweb-network/business-school-site/index.php'),
  ('url_school_scitech', '/anuweb-network/scitech-school-site/index.php'),
  ('url_school_humanities', '/anuweb-network/humanities-school-site/index.php'),
  ('url_school_religion', '/anuweb-network/religion-school-site/index.php'),
  ('url_admissions', '/anuweb-network/admissions-site/index.php'),

  -- Still not built -- point at the main site's Coming Soon page
  ('url_sepc', '/anuweb-network/main-site/coming-soon.php?unit=Centre%20for%20Short%20Courses%20%26%20Professional%20Studies&icon=bi-journal-bookmark'),
  ('url_research', '/anuweb-network/main-site/coming-soon.php?unit=Institute%20of%20Research&icon=bi-search'),
  ('url_student_life', '/anuweb-network/main-site/coming-soon.php?unit=Student%20Life&icon=bi-people'),
  ('url_chaplaincy', '/anuweb-network/main-site/coming-soon.php?unit=Chaplaincy&icon=bi-heart'),
  ('url_anu_online', '/anuweb-network/main-site/coming-soon.php?unit=ANU%20Online&icon=bi-laptop'),
  ('url_library', '/anuweb-network/main-site/coming-soon.php?unit=Library&icon=bi-book-half'),
  ('alumni_url', '/anuweb-network/main-site/coming-soon.php?unit=Alumni&icon=bi-people')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
