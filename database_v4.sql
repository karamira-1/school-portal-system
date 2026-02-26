-- ============================================================
-- ASPEJ SCHOOL – DATABASE v4 (run after v3)
-- New: report_template, student_accounts, inbox, announcements,
--      announcement_files, secretary_messages
-- ============================================================
USE aspej_school;

-- ============================================================
-- 1. REPORT TEMPLATE (designed by Master/Mistress)
-- Stores school info shown on every report card
-- ============================================================
CREATE TABLE IF NOT EXISTS report_template (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    school_name         VARCHAR(150) NOT NULL DEFAULT 'ASPEJ School',
    school_motto        VARCHAR(200) NULL,
    school_address      VARCHAR(255) NULL,
    school_phone        VARCHAR(60)  NULL,
    school_email        VARCHAR(100) NULL,
    school_po_box       VARCHAR(60)  NULL,
    school_logo_path    VARCHAR(255) NULL,    -- uploaded logo file path
    principal_name      VARCHAR(100) NULL,
    principal_title     VARCHAR(80)  NULL DEFAULT 'Principal',
    dos_name            VARCHAR(100) NULL,    -- Director of Studies signature name
    dod_name            VARCHAR(100) NULL,    -- Director of Discipline name
    class_teacher_label VARCHAR(60)  NULL DEFAULT 'Class Teacher',
    footer_note         TEXT         NULL,    -- printed at bottom of report
    stamp_note          VARCHAR(100) NULL DEFAULT 'School Stamp',
    academic_year       VARCHAR(20)  NULL,    -- e.g. "2024 – 2025"
    last_updated_by     INT          NULL,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (last_updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Default template row
INSERT IGNORE INTO report_template (id, school_name, school_motto, school_address, school_phone,
    school_email, academic_year, principal_name, principal_title, footer_note)
VALUES (1,
    'ASPEJ School',
    'Excellence, Discipline, Innovation',
    'Kigali, Rwanda',
    '+250 788 000 000',
    'info@aspej.edu',
    '2024 – 2025',
    'School Principal',
    'Principal / Head Teacher',
    'This report is computer-generated and is official only with school stamp and authorized signatures.');

-- ============================================================
-- 2. STUDENT PORTAL ACCOUNTS
-- Students register with student_number + full_name (must match DB)
-- Admin/librarian activates account
-- ============================================================
ALTER TABLE students
    ADD COLUMN IF NOT EXISTS portal_username  VARCHAR(60)  NULL,
    ADD COLUMN IF NOT EXISTS portal_password  VARCHAR(255) NULL,   -- bcrypt
    ADD COLUMN IF NOT EXISTS account_status   ENUM('pending','active','suspended') DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS registered_at    TIMESTAMP    NULL,
    ADD UNIQUE KEY IF NOT EXISTS uq_portal_username (portal_username);

-- Registration requests (before activation)
CREATE TABLE IF NOT EXISTS student_registrations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_number  VARCHAR(30) NOT NULL,
    full_name       VARCHAR(120) NOT NULL,      -- must exactly match students.full_name
    username        VARCHAR(60)  NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    email           VARCHAR(100) NULL,
    phone           VARCHAR(30)  NULL,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    rejection_reason VARCHAR(255) NULL,
    reviewed_by     INT         NULL,
    reviewed_at     TIMESTAMP   NULL,
    created_at      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reg_number (student_number),
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 3. INBOX / CHAT MESSAGES (universal – any user to any user)
-- ============================================================
CREATE TABLE IF NOT EXISTS inbox_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT          NOT NULL,          -- users.id (staff/student)
    receiver_id INT          NOT NULL,          -- users.id
    subject     VARCHAR(255) NOT NULL DEFAULT '(no subject)',
    body        TEXT         NOT NULL,
    is_read     TINYINT(1)   DEFAULT 0,
    read_at     TIMESTAMP    NULL,
    parent_id   INT          NULL,              -- reply thread
    sent_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)   REFERENCES inbox_messages(id) ON DELETE SET NULL
);

-- ============================================================
-- 4. SECRETARY MESSAGES (no account required – public form)
-- For parents / visitors to contact the school secretary
-- ============================================================
CREATE TABLE IF NOT EXISTS secretary_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(120) NOT NULL,
    sender_email VARCHAR(100) NULL,
    sender_phone VARCHAR(30)  NULL,
    subject      VARCHAR(255) NOT NULL,
    message      TEXT         NOT NULL,
    status       ENUM('unread','read','replied') DEFAULT 'unread',
    reply_text   TEXT         NULL,
    replied_by   INT          NULL,
    replied_at   TIMESTAMP    NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 5. ANNOUNCEMENTS (blog-style, with optional downloadable file)
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    slug         VARCHAR(255) NOT NULL UNIQUE,
    body         LONGTEXT     NOT NULL,          -- HTML content
    excerpt      TEXT         NULL,              -- short preview
    cover_image  VARCHAR(255) NULL,
    file_path    VARCHAR(255) NULL,              -- downloadable attachment
    file_name    VARCHAR(255) NULL,              -- display name of file
    file_size    INT          NULL,              -- bytes
    is_published TINYINT(1)   DEFAULT 1,
    is_pinned    TINYINT(1)   DEFAULT 0,
    audience     ENUM('all','students','staff') DEFAULT 'all',
    posted_by    INT          NOT NULL,
    published_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id)
);

-- Sample announcements
INSERT IGNORE INTO announcements (title, slug, body, excerpt, is_published, posted_by, audience) VALUES
('Welcome to the New Academic Year 2024–2025',
 'welcome-2024-2025',
 '<p>Dear students, parents, and staff,</p><p>We are pleased to welcome everyone to the new academic year 2024–2025. This year brings exciting new programs, improved facilities, and a renewed commitment to excellence.</p><p>Classes begin on Monday 2 September 2024. All students should report by 7:30 AM in full school uniform.</p><p>We wish everyone a productive and successful year!</p>',
 'Welcome to the new academic year. Classes begin Monday 2 September 2024.',
 1,
 (SELECT id FROM users WHERE username=\'admin\' LIMIT 1),
 'all'
),
('Term 1 Examination Schedule Released',
 'term1-exam-schedule',
 '<p>The Term 1 examination timetable has been finalized and approved by the Director of Studies.</p><p>Examinations will be held from <strong>15 November to 29 November 2024</strong>. Students are advised to prepare thoroughly and arrive 30 minutes before their scheduled exam time.</p><p>A downloadable PDF of the full timetable is attached below.</p>',
 'Term 1 exams: 15 November to 29 November 2024. Download timetable below.',
 1,
 (SELECT id FROM users WHERE username=\'admin\' LIMIT 1),
 'all'
);

-- ============================================================
-- 6. USER NOTIFICATIONS (bell icon)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    title      VARCHAR(255) NOT NULL,
    message    TEXT         NULL,
    type       ENUM('info','success','warning','danger') DEFAULT 'info',
    link       VARCHAR(255) NULL,
    is_read    TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- END DATABASE v4
-- ============================================================
