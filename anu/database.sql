-- ANU Online Database Schema
-- Run this entire file as MySQL root, or use setup.php

-- Create the application database user
CREATE USER IF NOT EXISTS 'ANUonline'@'localhost' IDENTIFIED BY 'admin123';
CREATE USER IF NOT EXISTS 'ANUonline'@'%' IDENTIFIED BY 'admin123';

-- Create and select the database
CREATE DATABASE IF NOT EXISTS anu_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant all privileges to the app user
GRANT ALL PRIVILEGES ON anu_online.* TO 'ANUonline'@'localhost';
GRANT ALL PRIVILEGES ON anu_online.* TO 'ANUonline'@'%';
FLUSH PRIVILEGES;

USE anu_online;

-- Site Settings (hero images, colors, general config)
CREATE TABLE IF NOT EXISTS site_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  setting_type ENUM('text','image','color','textarea','boolean') DEFAULT 'text',
  label VARCHAR(150),
  page VARCHAR(100) DEFAULT 'global',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hero Sections (per page)
CREATE TABLE IF NOT EXISTS hero_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_slug VARCHAR(100) UNIQUE NOT NULL,
  title VARCHAR(255),
  subtitle TEXT,
  cta_text VARCHAR(100),
  cta_link VARCHAR(255),
  bg_image VARCHAR(255),
  overlay_color VARCHAR(20) DEFAULT 'rgba(0,0,0,0.55)',
  is_active TINYINT(1) DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Programs
CREATE TABLE IF NOT EXISTS programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school VARCHAR(100) NOT NULL,
  level ENUM('PhD','Masters','Undergraduate','Diploma','Certificate','Executive') NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  duration VARCHAR(100),
  entry_requirements TEXT,
  tuition_per_trimester DECIMAL(10,2),
  is_featured TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- News & Stories
CREATE TABLE IF NOT EXISTS news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE,
  excerpt TEXT,
  content LONGTEXT,
  featured_image VARCHAR(255),
  category VARCHAR(100),
  author VARCHAR(150) DEFAULT 'ANU Online',
  is_featured TINYINT(1) DEFAULT 0,
  is_published TINYINT(1) DEFAULT 1,
  published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  event_date DATE NOT NULL,
  event_time TIME,
  location VARCHAR(255),
  type ENUM('online','physical','hybrid') DEFAULT 'online',
  image VARCHAR(255),
  link VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clubs
CREATE TABLE IF NOT EXISTS clubs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  icon VARCHAR(100),
  description TEXT,
  leader_name VARCHAR(150),
  leader_email VARCHAR(150),
  image VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0
);

-- Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  program VARCHAR(200),
  quote TEXT NOT NULL,
  photo VARCHAR(255),
  rating TINYINT DEFAULT 5,
  is_featured TINYINT(1) DEFAULT 1,
  is_active TINYINT(1) DEFAULT 1
);

-- Staff / Leadership
CREATE TABLE IF NOT EXISTS staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  title VARCHAR(200),
  department VARCHAR(150),
  bio TEXT,
  photo VARCHAR(255),
  email VARCHAR(150),
  is_featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0
);

-- Contact Messages
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  email VARCHAR(150),
  subject VARCHAR(255),
  message TEXT,
  file_path VARCHAR(255),
  status ENUM('new','read','replied') DEFAULT 'new',
  ip_address VARCHAR(50),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Prayer Requests
CREATE TABLE IF NOT EXISTS prayer_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  email VARCHAR(150),
  request TEXT NOT NULL,
  status ENUM('new','prayed') DEFAULT 'new',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FAQ
CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  category VARCHAR(100) DEFAULT 'General',
  page VARCHAR(100) DEFAULT 'general',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

-- Gallery
CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200),
  image VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  page VARCHAR(100) DEFAULT 'campus-life',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

-- Admin Users
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(200),
  role ENUM('super_admin','editor','viewer') DEFAULT 'editor',
  last_login DATETIME,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Application Inquiries
CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(200) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30),
  program_interest VARCHAR(255),
  education_level VARCHAR(100),
  message TEXT,
  status ENUM('new','contacted','enrolled','declined') DEFAULT 'new',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
-- NOTE (2026-09): the hash originally shipped here did not actually match
-- "password" (password_verify() failed against it -- confirmed by testing),
-- so a fresh install would have been unable to log in at all. Regenerated
-- correctly below so the documented default (admin / password) works.
INSERT INTO admin_users (username, email, password, full_name, role) VALUES
('admin', 'admin@anu.ac.ke', '$2y$12$YW25qkHb/QlhLYwUjHvRm.G453S0wjdnBflo2vvmpaAJxR30havM2', 'Super Admin', 'super_admin')
ON DUPLICATE KEY UPDATE password=VALUES(password);
-- Default password: password

-- Insert default hero sections
-- NOTE (2026-09): the bg_image paths originally shipped here (home.jpg,
-- programs.jpg, etc.) never existed anywhere in the uploads folder — a
-- pre-existing mismatch in the package, which is why no hero photo ever
-- showed on any page even before real photos were supplied. Fixed below
-- to point at the real photo files shipped in uploads/hero/, and rows
-- added for the new about/team/support pages. The UPDATE clause now
-- actually updates bg_image/overlay_color on re-import (the previous
-- "id=id" was a no-op that silently did nothing on a second import).
INSERT INTO hero_sections (page_slug, title, subtitle, cta_text, cta_link, bg_image, overlay_color) VALUES
('home', 'Transform Your Future with ANU Online', 'Accredited. Flexible. Faith-Centered. Start your journey with Africa Nazarene University from anywhere in the world.', 'Explore Programs', 'programs.php', 'uploads/hero/anu-online-student-campus-building.jpg', 'rgba(10,20,60,0.6)'),
('programs', 'Discover Your Academic Path', 'Undergraduate, Postgraduate & Professional programs designed for a changing world.', 'Apply Now', 'https://www.anu.ac.ke', 'uploads/hero/anu-online-portal-laptop.jpg', 'rgba(10,0,0,0.62)'),
('campus-life', 'Life Beyond the Classroom', 'A thriving virtual community built for growth, excellence, and purpose.', 'Get Involved', '#highlights', 'uploads/hero/anu-online-student-laptop-outdoor.jpg', 'rgba(20,0,0,0.55)'),
('contact', 'We\'d Love to Hear From You', 'Reach out to our dedicated team for support, admissions, and more.', 'Send Message', '#contact-form', 'uploads/hero/anu-online-student-headphones.jpg', 'rgba(10,0,0,0.6)'),
('faq', 'Got Questions? We Have Answers', 'Everything you need to know about studying at ANU Online.', 'Browse FAQs', '#faq-section', 'uploads/hero/anu-online-portal-laptop.jpg', 'rgba(10,0,0,0.6)'),
('support', 'How Can We Help?', 'Guides, tutorials and a support team ready to help you succeed.', 'Get Support', '#support-topics', 'uploads/hero/anu-online-student-smiling-indoor.jpg', 'rgba(10,0,0,0.6)'),
('chaplaincy', 'Find Peace, Purpose & Prayer', 'Virtual spiritual support, devotionals, and community for every ANU Online student.', 'Request Prayer', '#prayer-form', 'uploads/hero/anu-online-student-smiling-indoor.jpg', 'rgba(20,0,0,0.6)'),
('clubs', 'Join a Club. Find Your Tribe.', 'Connect with like-minded students through ANU Online\'s vibrant clubs and societies.', 'Explore Clubs', '#clubs-grid', 'uploads/hero/anu-online-students-group.jpg', 'rgba(10,0,0,0.6)'),
('news', 'Stories, News & Updates', 'Stay connected with the latest from ANU Online community.', 'Read Stories', '#news-grid', 'uploads/hero/anu-online-student-campus-building.jpg', 'rgba(10,0,0,0.6)'),
('about', 'About ANU Online', 'The distance-learning arm of Africa Nazarene University.', '', '', 'uploads/hero/anu-online-student-memorial-wall.jpg', 'rgba(10,0,0,0.6)'),
('team', 'Meet ANU Online Team', 'The leadership, faculty and support staff behind your learning experience.', '', '', 'uploads/hero/anu-online-students-group.jpg', 'rgba(10,0,0,0.6)')
ON DUPLICATE KEY UPDATE
  bg_image = VALUES(bg_image),
  overlay_color = VALUES(overlay_color);

-- Site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, label, page) VALUES
('site_name', 'ANU Online', 'text', 'Site Name', 'global'),
('site_tagline', 'Africa Nazarene University | Online Learning', 'text', 'Tagline', 'global'),
('primary_color', '#ff0000', 'color', 'Primary Color', 'global'),
('accent_color', '#fac823', 'color', 'Accent Color', 'global'),
('phone', '+254 714 785 249', 'text', 'Phone Number', 'global'),
('email', 'distancelearning@anu.ac.ke', 'text', 'Email', 'global'),
('address', 'Africa Nazarene University, Ongata Rongai, Kenya', 'text', 'Address', 'global'),
('facebook_url', 'https://www.facebook.com/anu.ac.ke/', 'text', 'Facebook URL', 'global'),
('twitter_url', 'https://x.com/africanazarene', 'text', 'Twitter URL', 'global'),
('linkedin_url', 'https://www.linkedin.com/school/africa-nazarene-university/', 'text', 'LinkedIn URL', 'global'),
('instagram_url', 'https://www.instagram.com/africanazareneuni/', 'text', 'Instagram URL', 'global'),
('portal_url', 'https://records.anu.ac.ke/', 'text', 'Student Portal URL (records)', 'global'),
('apply_url', 'https://records.anu.ac.ke/wStartUpCreateUserNamePassword.jsp', 'text', 'Apply Now URL', 'global'),
('footer_about', 'Africa Nazarene University offers accessible, flexible, and high-quality online education. Learn anywhere, anytime, with our dedicated support team.', 'textarea', 'Footer About Text', 'global'),
('home_stats_students', '5,200+', 'text', 'Students Enrolled', 'home'),
('home_stats_programs', '50+', 'text', 'Programs Offered', 'home'),
('home_stats_countries', '20+', 'text', 'Countries Reached', 'home'),
('home_stats_years', '15+', 'text', 'Years of Excellence', 'home')
ON DUPLICATE KEY UPDATE id=id;

-- Default FAQs
INSERT INTO faqs (question, answer, category, page, sort_order) VALUES
('What programs are available online at ANU?', 'ANU offers a wide range of undergraduate and postgraduate online programs including Business, IT, Theology, Law, and Education. Visit our Programs page for the full list.', 'Programs', 'general', 1),
('How do I apply for an online program?', 'Apply through our Admissions portal. It contains a complete step-by-step guide. Visit https://admissions.anu.ac.ke to begin your application.', 'Admissions', 'general', 2),
('What are the minimum entry requirements for undergraduate programs?', 'A minimum of C+ (plus) in KCSE or equivalent qualifications is required for undergraduate programs. Specific courses may have additional requirements.', 'Admissions', 'admissions', 1),
('What documents are required during the application?', 'You need scanned copies of your academic certificates, national ID/passport, passport-size photo, and transcripts (for postgraduate applicants).', 'Admissions', 'admissions', 2),
('Is financial aid available for online students?', 'Yes, we offer scholarships and financial assistance programs. Contact the Admissions Office for personalized guidance.', 'Fees', 'general', 3),
('How do I access online classes?', 'You will access classes via our e-learning platform, eNaz, where you can engage with course content, discussions, assignments, and more.', 'Technical', 'general', 4),
('Are the online programs accredited?', 'Yes. All ANU Online programs are accredited by recognized education authorities in Kenya and uphold strict academic standards.', 'Academic', 'general', 5),
('How long does the admission process take?', 'Once you have submitted your application and all documents, processing typically takes 3-7 working days. You will be notified via email.', 'Admissions', 'admissions', 3)
ON DUPLICATE KEY UPDATE id=id;

-- Default Programs
INSERT INTO programs (school, level, name, description, duration, entry_requirements, tuition_per_trimester) VALUES
('Religion', 'PhD', 'PhD in Religion', 'Advanced doctoral research in religious studies and theology.', '3-4 Years', 'Relevant Master\'s degree, GPA 3.0+', 45000),
('Religion', 'Masters', 'Master of Arts in Religion', 'Deepen your theological understanding through advanced study.', '2 Years', 'Recognized Bachelor\'s degree, GPA 2.7+', 35000),
('Religion', 'Undergraduate', 'Bachelor of Theology', 'A comprehensive undergraduate program in Christian theology.', '4 Years', 'KCSE C+ or equivalent', 25000),
('Business', 'PhD', 'PhD in Business Administration', 'Advanced research in business management and leadership.', '3-4 Years', 'Relevant Master\'s degree, GPA 3.0+', 50000),
('Business', 'Masters', 'MBA', 'Develop executive leadership and strategic management skills.', '2 Years', 'Bachelor\'s degree, 2+ years work experience', 40000),
('Business', 'Undergraduate', 'Bachelor of Commerce', 'A broad-based commerce degree for the modern business world.', '4 Years', 'KCSE C+', 22000),
('Science', 'Masters', 'Master of Science in Applied IT', 'Advanced applied information technology for industry leaders.', '2 Years', 'IT-related Bachelor\'s degree', 38000),
('Science', 'Undergraduate', 'Bachelor of Science in Computer Science', 'Foundation in computing, programming, and software engineering.', '4 Years', 'KCSE C+', 28000),
('Law', 'Undergraduate', 'Bachelor of Laws (LLB)', 'Rigorous legal education preparing graduates for legal practice.', '4 Years', 'KCSE C+ with B in English', 30000),
('Humanities', 'Masters', 'Master of Arts in Counselling Psychology', 'Professional counselling training for mental health practice.', '2 Years', 'Bachelor\'s degree, GPA 2.7+', 35000),
('Humanities', 'Undergraduate', 'Bachelor of Arts in Education', 'Qualify as a secondary school teacher with specialization options.', '4 Years', 'KCSE C+', 22000)
ON DUPLICATE KEY UPDATE id=id;

-- Default Testimonials
INSERT INTO testimonials (name, program, quote, rating) VALUES
('Grace Kamau', 'Bachelor of Theology', 'Studying at ANU Online changed my life. The flexibility allowed me to serve in ministry while earning my degree. The faculty are world-class.', 5),
('Brian Mwangi', 'BSc Computer Science', 'ANU Online gave me the technical foundation I needed. The e-learning platform is excellent and the support team is always available.', 5),
('Alice Njeri', 'MBA', 'The MBA program at ANU is rigorous and practical. I now lead a team of 50 people thanks to the leadership skills I gained here.', 5),
('David Ochieng', 'Master of Arts in Counselling', 'The counselling program is deeply transformative. The online format did not compromise quality one bit — if anything, it was richer.', 4)
ON DUPLICATE KEY UPDATE id=id;

-- Default Clubs
INSERT INTO clubs (name, icon, description, sort_order) VALUES
('Christian Union (CU)', 'bi-heart-fill', 'A vibrant spiritual fellowship where students gather for worship, discipleship, prayer, and leadership development in Christ.', 1),
('Cybercerene', 'bi-laptop', 'A tech-driven innovation hub empowering students in coding, cybersecurity, robotics, and emerging digital trends through workshops and hackathons.', 2),
('WECANU', 'bi-people-fill', 'Women Empowered for Change at ANU provides mentorship and networking focused on leadership and gender equity initiatives.', 3),
('Peer Counselling Club', 'bi-chat-heart-fill', 'Mental health awareness, peer support systems, and wellness events that create a safe space for student growth and empathy.', 4),
('Sports Club', 'bi-trophy-fill', 'Promoting fitness, teamwork, and healthy competition for all students in basketball, soccer, athletics and more.', 5)
ON DUPLICATE KEY UPDATE id=id;

-- Events
INSERT INTO events (title, description, event_date, location, type) VALUES
('Trimester Registration Deadline', 'Last day to register for the upcoming trimester. Ensure all documents are submitted.', '2025-06-30', 'Online (eNaz Portal)', 'online'),
('Virtual Orientation', 'Welcome new and continuing students with orientation sessions, program introductions and meet-the-faculty.', '2025-07-15', 'Zoom Online', 'online'),
('First Day of Classes', 'Trimester begins. Log in to eNaz and join your first sessions.', '2025-08-01', 'eNaz Platform', 'online'),
('WECANU Leadership Workshop', 'Empowering women in leadership through interactive sessions and mentorship.', '2025-07-12', 'Zoom Online', 'online'),
('Cyber Challenge Hackathon', 'Annual hackathon for tech students to showcase innovation and problem-solving skills.', '2025-08-03', 'ANU Main Campus', 'hybrid')
ON DUPLICATE KEY UPDATE id=id;

-- Default News
INSERT INTO news (title, slug, excerpt, content, category, is_featured) VALUES
('New Programs Launched for 2025', 'new-programs-2025', 'ANU Online expands offerings to include new flexible undergraduate and graduate programs for working professionals.', '<p>Africa Nazarene University Online is proud to announce the launch of several new programs for the 2025 academic year. These programs have been carefully designed to meet the needs of working professionals and students across Africa and beyond.</p><p>The new offerings include advanced programs in data science, public health, and Christian leadership development.</p>', 'Academics', 1),
('Student Spotlight: Caleb''s Journey', 'student-spotlight-caleb', 'Discover how Caleb balances online studies and a thriving freelance media career, thanks to ANU''s flexible platform.', '<p>Caleb Mutua, a third-year Bachelor of Arts in Communication student, has built a thriving freelance media business while studying full-time at ANU Online. His story is one of resilience, faith, and the power of flexible education.</p>', 'Student Stories', 1),
('Join Our 2025 Webinar Series', 'webinar-series-2025', 'Interactive expert-led webinars on digital skills, leadership, and navigating online learning — open to all ANU Online learners.', '<p>ANU Online is launching an exciting webinar series for 2025, featuring industry experts, faculty, and alumni sharing insights on technology, leadership, mental wellness, and faith integration in professional life.</p>', 'Events', 0)
ON DUPLICATE KEY UPDATE id=id;
