<?php
// ════════════════════════════════════════════════════════════════
//  WARNING: this is a one-time setup/maintenance tool with NO
//  access protection. Anyone who finds this URL can run it.
//  DELETE THIS FILE from your server once you're done using it.
// ════════════════════════════════════════════════════════════════

require_once __DIR__ . '/includes/config.php';
$db = getDB(); $r = [];

// Add streaming columns to events table
$cols = [
    "stream_url VARCHAR(1000) DEFAULT NULL COMMENT 'YouTube/Zoom/Teams live URL'",
    "stream_platform ENUM('youtube','zoom','teams','facebook','other') DEFAULT NULL",
    "recording_url VARCHAR(1000) DEFAULT NULL COMMENT 'Replay/recording URL after event'",
    "thumbnail VARCHAR(500) DEFAULT NULL",
    "category ENUM('academic','chapel','university','orientation','other') DEFAULT 'academic'",
    "is_live TINYINT(1) DEFAULT 0",
    "is_featured TINYINT(1) DEFAULT 0",
    "is_free TINYINT(1) DEFAULT 1",
    "registration_url VARCHAR(500) DEFAULT NULL",
    "max_attendees INT DEFAULT NULL",
    "speaker_name VARCHAR(255) DEFAULT NULL",
    "speaker_title VARCHAR(255) DEFAULT NULL",
    "speaker_photo VARCHAR(500) DEFAULT NULL",
    "views INT DEFAULT 0",
    "end_time TIME DEFAULT NULL",
];
foreach ($cols as $col) {
    $name = explode(' ',$col)[0];
    try {
        $db->exec("ALTER TABLE events ADD COLUMN $col");
        $r[] = "✓ Added column: $name";
    } catch(Exception $e) {
        $r[] = "— $name: already exists or ".$e->getMessage();
    }
}

// Seed sample events
$seeds = [
    ['ANU Online Open Day 2025','Discover programs, meet faculty, and learn how online learning works at ANU. Live Q&A with admissions team.',date('Y-m-d',strtotime('+7 days')),'10:00','online','Africa Nazarene University HQ','https://www.youtube.com/watch?v=dQw4w9WgXcQ','youtube','','university',0,1,1,'','Dr. James Mwangi','Director, Distance Learning',1],
    ['eNaz LMS Orientation for New Students','Step-by-step walkthrough of the eNaz learning management system. Mandatory for all new students.',date('Y-m-d',strtotime('+3 days')),'14:00','online','Online (Zoom)','https://zoom.us','zoom','','orientation',0,0,1,'https://zoom.us','Prof. Mary Wangari','ICT & E-Learning',1],
    ['Chapel Service — Faith & Purpose','Weekly online chapel service for ANU Online students. All welcome.',date('Y-m-d',strtotime('+2 days')),'09:00','online','eNaz Chapel Stream','https://www.youtube.com/watch?v=dQw4w9WgXcQ','youtube','','chapel',0,0,1,'','Rev. Charles Onyango','University Chaplain',1],
    ['Academic Writing Webinar','Improve your research writing, citation skills, and avoid plagiarism. Certificate of participation issued.',date('Y-m-d',strtotime('+14 days')),'15:00','online','Zoom','https://zoom.us','zoom','','academic',0,0,1,'https://zoom.us','Dr. Faith Kamau','School of Arts & Sciences',1],
    ['Graduation Ceremony 2025 — Live Stream','Celebrate with ANU Online Class of 2025. Watch live from anywhere in the world.',date('Y-m-d',strtotime('+30 days')),'10:00','online','ANU Grounds + Live Stream','https://www.youtube.com/watch?v=dQw4w9WgXcQ','youtube','','university',0,1,1,'','Prof. David Kariuki','Vice Chancellor',1],
];
$stmt = $db->prepare("INSERT IGNORE INTO events (title,description,event_date,event_time,type,location,stream_url,stream_platform,recording_url,category,is_live,is_featured,is_free,registration_url,speaker_name,speaker_title,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
foreach ($seeds as $s) {
    try { $stmt->execute($s); $r[] = "✓ Seeded: ".$s[0]; } catch(Exception $e) { $r[] = "— seed: ".$e->getMessage(); }
}

foreach($r as $line) echo "<p>$line</p>";
echo "<p><b>Done!</b> <a href='/anu/livestream.php'>→ Live Portal</a> | <a href='/anu/admin/livestream.php'>→ Admin</a></p>";
