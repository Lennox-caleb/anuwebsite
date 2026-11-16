-- ============================================================
-- ANU Main Site -- Real content import (2026-09)
-- Sourced from the live anu.ac.ke site: governance body members,
-- current Leadership (VC/DVCs/Chancellor), Chaplaincy ministries,
-- and each School's actual programme/course catalog.
--
-- Run schema_phase21.sql and schema_phase22.sql FIRST -- this file
-- assumes the tenders, csr_gallery, fee_structure_file and
-- chaplaincy_programs additions already exist.
--
-- Safe to re-run: every row is guarded so it is only inserted once.
--
-- IMPORTANT -- read before running:
-- 1. Photos: anu.ac.ke shows real photos for every governance member
--    and for the VC/DVCs/Chancellor, but this script cannot download
--    or copy those images (they belong to anu.ac.ke and this tool has
--    no way to fetch binary files from there). Every row below is
--    inserted with photo = NULL. Please upload each person's real
--    photo via the admin panel (Governance / Leadership) once this
--    runs -- the pages already display gracefully without a photo in
--    the meantime, so nothing will look broken.
-- 2. Leadership (VC/DVC/Chancellor) rows are inserted UNPUBLISHED
--    (is_published = 0) on purpose: a welcome message is personal,
--    real text attributed to a real person, and this script cannot
--    write that on their behalf. Please add each person's actual
--    welcome message via admin, then publish.
-- 3. Programme rows are inserted PUBLISHED with a placeholder
--    overview and blank requirements/fee structure, since anu.ac.ke's
--    published catalog does not expose those specifics. Please fill
--    those in via admin (Programmes) when available.
-- 4. University Council titles were not reliably readable on the
--    source page -- names only, title left blank. Please correct via
--    admin if you have the official title for each member.
-- ============================================================


-- ---------------- Leadership (VC / DVCs / Chancellor) ----------------
-- Current as shown on anu.ac.ke as of Sep 2026. Unpublished until a
-- photo + real welcome message are added.

INSERT INTO leadership (role_type, full_name, title, message, sort_order, is_published)
SELECT 'vc', 'Prof. Corlis McGee', 'Interim Vice Chancellor', '', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM leadership WHERE full_name = 'Prof. Corlis McGee');

INSERT INTO leadership (role_type, full_name, title, message, sort_order, is_published)
SELECT 'dvc', 'Prof. Bob Brower', 'Interim Deputy Vice Chancellor, Strategy', '', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM leadership WHERE full_name = 'Prof. Bob Brower');

INSERT INTO leadership (role_type, full_name, title, message, sort_order, is_published)
SELECT 'dvc', 'Dr. Rhonda Slingerland', 'Deputy Vice Chancellor, Finance & Administration', '', 2, 0
WHERE NOT EXISTS (SELECT 1 FROM leadership WHERE full_name = 'Dr. Rhonda Slingerland');

INSERT INTO leadership (role_type, full_name, title, message, sort_order, is_published)
SELECT 'dvc', 'Prof. Simon Obwatho', 'Deputy Vice Chancellor, Academic & Student Affairs', '', 3, 0
WHERE NOT EXISTS (SELECT 1 FROM leadership WHERE full_name = 'Prof. Simon Obwatho');

INSERT INTO leadership (role_type, full_name, title, message, sort_order, is_published)
SELECT 'chancellor', 'Dr. Eugenio Duarte', 'Chancellor', '', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM leadership WHERE full_name = 'Dr. Eugenio Duarte');


-- ---------------- Board of Trustees ----------------

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Dr. Daniel A.K.L. Gomis', 'Chair, Board of Trustees', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Daniel A.K.L. Gomis' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Mrs. Marie Madeleine Marième Mansaly', 'Vice Chair, Board of Trustees', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mrs. Marie Madeleine Marième Mansaly' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Dr. Filimao Chambo', 'Trustee', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Filimao Chambo' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Prof. John Paul Marangu', 'Trustee', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Prof. John Paul Marangu' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Rev. Jim Ritchie', 'Trustee', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Rev. Jim Ritchie' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Mr. Cornelius Muthuri', 'Trustee', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mr. Cornelius Muthuri' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Mrs. Jane Bisieri Omari', 'Trustee', 7, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mrs. Jane Bisieri Omari' AND category = 'Board of Trustees');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Board of Trustees', 'Dr. Stanley Makhosi Bhebhe', 'Secretary, Board of Trustees', 8, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Stanley Makhosi Bhebhe' AND category = 'Board of Trustees');


-- ---------------- University Council ----------------
-- Titles were not reliably readable on the source page -- names only.

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Mrs. Emily Chege', NULL, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mrs. Emily Chege' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'CPA Job Ruiru Ngunu', NULL, 2, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'CPA Job Ruiru Ngunu' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Dr. Stanley Ushe', NULL, 3, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Stanley Ushe' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Dr. Angela Maria Pereira Barreto Da Veiga Moreno', NULL, 4, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Angela Maria Pereira Barreto Da Veiga Moreno' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Mr. Mike Musho Ageng''o', NULL, 5, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mr. Mike Musho Ageng''o' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Dr. Kevin Ulmet', NULL, 6, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Kevin Ulmet' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Dr. Shikoh Gitau', NULL, 7, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Shikoh Gitau' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Rev. John Haines', NULL, 8, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Rev. John Haines' AND category = 'University Council');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'University Council', 'Prof. Maria Matsidisho Kanjere', NULL, 9, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Prof. Maria Matsidisho Kanjere' AND category = 'University Council');


-- ---------------- Management Board ----------------
-- The VC and DVCs shown on anu.ac.ke's Management Board page are
-- deliberately NOT duplicated here -- they live in the Leadership
-- table above and the site already excludes them from this list.

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Management', 'Regina Kamwenji', 'Director, Human Capital', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Regina Kamwenji' AND category = 'Management');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Management', 'Dr. Faith K. Mwenda', 'University Registrar', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Faith K. Mwenda' AND category = 'Management');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Management', 'Prof. Patrick Mburu Kamau', 'Senate Representative', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Prof. Patrick Mburu Kamau' AND category = 'Management');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Management', 'Mr. David Jones', 'Interim Dean of Students', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Mr. David Jones' AND category = 'Management');

INSERT INTO governance_members (category, full_name, title, sort_order, is_published)
SELECT 'Management', 'Dr. Jane Nyokabi Njuguna', 'Director, Corporate Services', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM governance_members WHERE full_name = 'Dr. Jane Nyokabi Njuguna' AND category = 'Management');


-- ---------------- Chaplaincy ministries ----------------

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Chapel Services', 'Chapel attendance is part of everyday campus life, with regular services throughout each term.', 'bi-building', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Chapel Services');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Holiness Week', 'A dedicated week each January and September term with daily chapel services, Monday to Friday.', 'bi-calendar-week', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Holiness Week');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Prayer & Spiritual Counseling', 'One-on-one prayer and spiritual guidance for students at any stage of their faith journey.', 'bi-chat-heart', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Prayer & Spiritual Counseling');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Bible Studies & Discipleship Groups', 'Small groups meeting regularly for Bible study and discipleship.', 'bi-book', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Bible Studies & Discipleship Groups');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Mentoring Relationships', 'Ongoing mentoring that walks alongside students as they grow in faith and character.', 'bi-people', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Mentoring Relationships');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Bereavement Support', 'Pastoral care and support for students walking through loss and grief.', 'bi-heart', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Bereavement Support');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Celebrate Recovery', 'A Christ-centred recovery program for anyone working through hurt, habits or hang-ups.', 'bi-arrow-repeat', 7, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Celebrate Recovery');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Missions & Compassionate Ministries', 'Service opportunities and mission trips that put faith into action in the wider community.', 'bi-globe2', 8, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Missions & Compassionate Ministries');

INSERT INTO chaplaincy_programs (title, description, icon, sort_order, is_published)
SELECT 'Christian Union', 'Student-led ministry and the central space for student-led spiritual life on campus -- weekly Wednesday evening gatherings for worship, Bible study and prayer, open to all.', 'bi-people-fill', 9, 1
WHERE NOT EXISTS (SELECT 1 FROM chaplaincy_programs WHERE title = 'Christian Union');


-- ============================================================
-- Programmes -- real catalog per school, from anu.ac.ke.
-- Uses INSERT IGNORE (programmes.slug is UNIQUE) so re-running
-- this file is safe. Mode of study: Masters/PhD -> Online,
-- everything else -> Physical, per house rule. Overview is a
-- short honest placeholder; requirements/fee structure are left
-- blank for admin to complete with official figures.
-- ============================================================

-- ---------------- School of Law (id 1) ----------------
INSERT IGNORE INTO programmes (title, slug, level, school_id, mode, overview, is_published) VALUES
('Bachelor of Laws (LL.B)', 'bachelor-of-laws-ll-b', 'Undergraduate', 1, 'Physical', 'Bachelor of Laws offered by the School of Law, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Arts in Criminal Justice and Security Management', 'bachelor-of-arts-in-criminal-justice-and-security-management', 'Undergraduate', 1, 'Physical', 'Offered by the School of Law, Africa Nazarene University. Full overview to be added by the School.', 1);

-- ---------------- School of Business (id 2) ----------------
INSERT IGNORE INTO programmes (title, slug, level, school_id, mode, overview, is_published) VALUES
('Doctor of Philosophy in Business Administration and Management', 'doctor-of-philosophy-in-business-administration-and-management', 'PhD', 2, 'Online', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Business Administration (MBA)', 'master-of-business-administration-mba', 'Masters', 2, 'Online', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Arts in Monitoring and Evaluation', 'master-of-arts-in-monitoring-and-evaluation', 'Masters', 2, 'Online', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Commerce', 'bachelor-of-commerce', 'Undergraduate', 2, 'Physical', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Science in International Business Management', 'bachelor-of-science-in-international-business-management', 'Undergraduate', 2, 'Physical', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Science in Procurement and Supply Chain Management', 'bachelor-of-science-in-procurement-and-supply-chain-management', 'Undergraduate', 2, 'Physical', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Diploma in Business Administration', 'diploma-in-business-administration', 'Diploma', 2, 'Physical', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Certificate in Business Management', 'certificate-in-business-management', 'Certificate', 2, 'Physical', 'Offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1),
('Certified Procurement and Supply Professional of Kenya (CPSP-K)', 'certified-procurement-and-supply-professional-of-kenya-cpsp-k', 'Other', 2, 'Physical', 'Professional certification offered by the School of Business, Africa Nazarene University. Full overview to be added by the School.', 1);

-- ---------------- School of Science & Technology (id 3) ----------------
INSERT IGNORE INTO programmes (title, slug, level, school_id, mode, overview, is_published) VALUES
('Master of Science in Applied Information Technology (MSc. AIT)', 'master-of-science-in-applied-information-technology-msc-ait', 'Masters', 3, 'Online', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Science in Environmental Resource Management (MSc. ERM)', 'master-of-science-in-environmental-resource-management-msc-erm', 'Masters', 3, 'Online', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Science in Computer Science', 'bachelor-of-science-in-computer-science', 'Undergraduate', 3, 'Physical', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Business and Information Technology (BBIT)', 'bachelor-of-business-and-information-technology-bbit', 'Undergraduate', 3, 'Physical', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Science in Environmental Resource Management (BSc. ERM)', 'bachelor-of-science-in-environmental-resource-management-bsc-erm', 'Undergraduate', 3, 'Physical', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Diploma in Information Technology', 'diploma-in-information-technology', 'Diploma', 3, 'Physical', 'Offered by the School of Science and Technology, Africa Nazarene University. Full overview to be added by the School.', 1),
('Environmental Impact Assessment and Environmental Audit', 'environmental-impact-assessment-and-environmental-audit', 'Other', 3, 'Physical', 'Professional short course offered by the School of Science and Technology, Africa Nazarene University.', 1),
('Strategic Environmental Assessment (SEA)', 'strategic-environmental-assessment-sea', 'Other', 3, 'Physical', 'Professional short course offered by the School of Science and Technology, Africa Nazarene University.', 1);

-- ---------------- School of Humanities & Social Sciences (id 4) ----------------
INSERT IGNORE INTO programmes (title, slug, level, school_id, mode, overview, is_published) VALUES
('Master of Arts in Counseling Psychology', 'master-of-arts-in-counseling-psychology', 'Masters', 4, 'Online', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Education in Educational Administration and Leadership', 'master-of-education-in-educational-administration-and-leadership', 'Masters', 4, 'Online', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Science in Peace, Governance and Security Studies', 'master-of-science-in-peace-governance-and-security-studies', 'Masters', 4, 'Online', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Counseling Psychology', 'bachelor-of-counseling-psychology', 'Undergraduate', 4, 'Physical', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Education', 'bachelor-of-education', 'Undergraduate', 4, 'Physical', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Mass Communication', 'bachelor-of-mass-communication', 'Undergraduate', 4, 'Physical', 'Media Council of Kenya-accredited. Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Arts in Peace and Conflict Resolution', 'bachelor-of-arts-in-peace-and-conflict-resolution', 'Undergraduate', 4, 'Physical', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Arts in Community Development', 'bachelor-of-arts-in-community-development', 'Undergraduate', 4, 'Physical', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Diploma in Peace and Conflict Studies', 'diploma-in-peace-and-conflict-studies', 'Diploma', 4, 'Physical', 'Offered by the School of Humanities and Social Sciences, Africa Nazarene University. Full overview to be added by the School.', 1),
('Professional Mediation and Reconciliation', 'professional-mediation-and-reconciliation', 'Other', 4, 'Physical', 'Professional short course offered by the School of Humanities and Social Sciences, Africa Nazarene University.', 1);

-- ---------------- School of Religion & Christian Ministry (id 5) ----------------
INSERT IGNORE INTO programmes (title, slug, level, school_id, mode, overview, is_published) VALUES
('Doctor of Philosophy in Religion', 'doctor-of-philosophy-in-religion', 'PhD', 5, 'Online', 'Offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1),
('Doctor of Ministry', 'doctor-of-ministry', 'PhD', 5, 'Online', 'Professional doctorate offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1),
('Master of Arts in Religion', 'master-of-arts-in-religion', 'Masters', 5, 'Online', 'A two-year programme offered by the School of Religion and Christian Ministry, Africa Nazarene University.', 1),
('Bachelor of Theology', 'bachelor-of-theology', 'Undergraduate', 5, 'Physical', 'Offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1),
('Bachelor of Arts in Christian Ministry', 'bachelor-of-arts-in-christian-ministry', 'Undergraduate', 5, 'Physical', 'Offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1),
('Diploma in Theology', 'diploma-in-theology', 'Diploma', 5, 'Physical', 'Offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1),
('Certificate of Leadership in Youth Ministry', 'certificate-of-leadership-in-youth-ministry', 'Certificate', 5, 'Physical', 'Offered by the School of Religion and Christian Ministry, Africa Nazarene University. Full overview to be added by the School.', 1);
