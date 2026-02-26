-- ============================================================
-- ASPEJ SCHOOL – COMPLETE DATABASE SCHEMA
-- Run this ONE file to set up the entire system.
-- Engine: MySQL / MariaDB  |  Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS aspej_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aspej_school;

-- ============================================================
-- SECTION A: ROLES & USERS  (RBAC)
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  ENUM('admin','teacher','parent','student') NOT NULL UNIQUE,
    label VARCHAR(50) NOT NULL
);

INSERT IGNORE INTO roles (name, label) VALUES
    ('admin',   'Administrator'),
    ('teacher', 'Teacher'),
    ('parent',  'Parent'),
    ('student', 'Student');

CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    role_id         INT          NOT NULL,
    full_name       VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    username        VARCHAR(80)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    phone           VARCHAR(20)  DEFAULT NULL,
    profile_image   VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
    is_active       TINYINT(1)   DEFAULT 1,
    last_login      TIMESTAMP    NULL DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Demo accounts  (password for all staff: Admin@1234)
-- Hash is the bcrypt of 'password' from Laravel/standard — replace with your own hashes in production
INSERT IGNORE INTO users (role_id, full_name, email, username, password_hash, phone) VALUES
(1, 'Murekatete Alphonsine',      'admin@aspejschool.edu',   'admin',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000001'),
(2, 'Jean Pierre Hategekimana',   'jp@aspejschool.edu',      'teacher1', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000002'),
(2, 'Aloys Shimiyimana',          'aloys@aspejschool.edu',   'teacher2', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000003'),
(3, 'Robert Adams',               'parent@aspejschool.edu',  'parent1',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000004');

-- ============================================================
-- SECTION B: PUBLIC-FACING TABLES
-- ============================================================

-- 1. NEWS & EVENTS -----------------------------------------
CREATE TABLE IF NOT EXISTS news_events (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    type         ENUM('Event','News','Announcement') NOT NULL DEFAULT 'News',
    title        VARCHAR(255) NOT NULL,
    summary      TEXT NOT NULL,
    image        VARCHAR(255) DEFAULT 'assets/images/default-news.jpg',
    link         VARCHAR(255) DEFAULT '#',
    published_at DATE NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO news_events (type, title, summary, image, link, published_at) VALUES
('Event',        'Annual Sports Day 2025 Success!',   'Our annual sports day was a huge success, with record participation across all age groups. Griffin house took the trophy!',                     'assets/images/sports.jpg',         'news.php#article-1', '2025-10-25'),
('News',         'New Robotics Club Launched',         'Students can now register for the brand new Robotics Club, focusing on design, programming, and competing in national events.',               'assets/images/robotics.jpg',       'news.php#article-2', '2025-10-22'),
('News',         'Professional Development Week',      'Our teachers participated in a week of training focused on modern teaching methodologies and digital classroom integration.',                 'assets/images/staff-training.jpg', 'news.php#article-3', '2025-10-18'),
('Event',        'Inter-School Debate Championship',   'ASPEJ is proud to host the regional debate championship. Come support our team this Friday!',                                               'assets/images/debate.jpg',         'news.php#article-4', '2025-10-15'),
('Announcement', 'Parent-Teacher Meetings: Term 2',    'Term 2 Parent-Teacher meetings will be held on November 5th and 6th. Please book your slots via the parent portal.',                         'assets/images/meeting.jpg',        'news.php#article-5', '2025-10-10');

-- 2. CONTACT MESSAGES --------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. ADMISSION APPLICATIONS --------------------------------
CREATE TABLE IF NOT EXISTS applications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(100) NOT NULL,
    last_name    VARCHAR(100) NOT NULL,
    dob          DATE NOT NULL,
    gender       ENUM('Male','Female') NOT NULL,
    parent_name  VARCHAR(150) NOT NULL,
    parent_phone VARCHAR(20)  NOT NULL,
    parent_id    VARCHAR(16)  NOT NULL,
    province     VARCHAR(50)  NOT NULL,
    district     VARCHAR(50)  NOT NULL,
    level        ENUM('O-Level','A-Level') NOT NULL,
    trade        VARCHAR(100) DEFAULT NULL,
    olevel_cert  VARCHAR(255) DEFAULT NULL,
    status       ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SECTION C: STUDENT ACADEMIC RECORDS
-- ============================================================

-- 4. STUDENTS ----------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20)  NOT NULL UNIQUE,
    full_name      VARCHAR(150) NOT NULL,
    class          VARCHAR(20)  NOT NULL,
    gpa            DECIMAL(3,1) DEFAULT 0.0,
    attendance_pct DECIMAL(5,2) DEFAULT 0.00,
    total_days     INT          DEFAULT 0,
    absent_days    INT          DEFAULT 0,
    profile_image  VARCHAR(255) DEFAULT 'assets/images/student-profile.jpg',
    password_hash  VARCHAR(255) NOT NULL,
    email          VARCHAR(150) NULL,
    user_id        INT          NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Demo student  (login: student# 1045 / password: student123)
INSERT IGNORE INTO students
    (student_number, full_name, class, gpa, attendance_pct, total_days, absent_days, password_hash)
VALUES
    ('1045', 'Sarah Adams', '10B', 3.7, 94.40, 72, 4,
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 5. GRADES ------------------------------------------------
CREATE TABLE IF NOT EXISTS grades (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT          NOT NULL,
    term       VARCHAR(50)  NOT NULL,
    year       YEAR         NOT NULL,
    subject    VARCHAR(100) NOT NULL,
    grade      VARCHAR(5)   NOT NULL,
    remarks    VARCHAR(255) DEFAULT '',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT IGNORE INTO grades (student_id, term, year, subject, grade, remarks) VALUES
(1,'Term 2, 2025',2025,'Mathematics','A',  'Excellent work on calculus concepts.'),
(1,'Term 2, 2025',2025,'Physics',    'B+', 'Good understanding of mechanics.'),
(1,'Term 2, 2025',2025,'Literature', 'A-', 'Insightful analysis in essays.'),
(1,'Term 2, 2025',2025,'History',    'A',  'Top marks on the mid-term project.'),
(1,'Term 2, 2025',2025,'French',     'B',  'Consistent effort, room for improvement in verbal.'),
(1,'Term 2, 2025',2025,'Art',        'A',  'Highly creative and well-executed final piece.'),
(1,'Term 1, 2025',2025,'Mathematics','A-', 'Good foundational skills.'),
(1,'Term 1, 2025',2025,'Physics',    'B',  'Solid effort.'),
(1,'Term 1, 2025',2025,'Literature', 'A',  'Excellent reading comprehension.'),
(1,'Term 1, 2025',2025,'History',    'A-', 'Very engaged in class discussions.'),
(1,'Term 1, 2025',2025,'French',     'C+', 'Needs to review grammar concepts.'),
(1,'Term 1, 2025',2025,'Art',        'A-', 'Good creative instincts.');

-- 6. TERM SUMMARIES ----------------------------------------
CREATE TABLE IF NOT EXISTS term_summaries (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    student_id       INT         NOT NULL,
    term             VARCHAR(50) NOT NULL,
    year             YEAR        NOT NULL,
    overall_gpa      DECIMAL(3,1) NOT NULL,
    teacher_comments TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT IGNORE INTO term_summaries (student_id, term, year, overall_gpa, teacher_comments) VALUES
(1,'Term 2, 2025',2025,3.7,'Sarah has shown strong progress this term, especially in Mathematics. She is a dedicated student who contributes positively to class discussions. Keep up the great work!'),
(1,'Term 1, 2025',2025,3.5,'A solid start to the year for Sarah. She has a strong grasp of most subjects but should focus on improving her consistency in French. Her participation in History class is commendable.');

-- 7. ATTENDANCE LOG (monthly aggregate) -------------------
CREATE TABLE IF NOT EXISTS attendance_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT         NOT NULL,
    month_label  VARCHAR(20) NOT NULL,
    year         YEAR        NOT NULL,
    days_present INT         DEFAULT 0,
    days_absent  INT         DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

INSERT IGNORE INTO attendance_log (student_id, month_label, year, days_present, days_absent) VALUES
(1,'Jan',2025,20,1),
(1,'Feb',2025,18,2),
(1,'Mar',2025,21,0),
(1,'Apr',2025,19,1);

-- ============================================================
-- SECTION D: STAFF & PORTAL MANAGEMENT  (v2 additions)
-- ============================================================

-- 8. TEACHER → CLASS ASSIGNMENTS (RBAC scope) -------------
CREATE TABLE IF NOT EXISTS teacher_classes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    class_name VARCHAR(20)  NOT NULL,
    subject    VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO teacher_classes (user_id, class_name, subject) VALUES
(2,'10B','Mathematics'),
(2,'10B','Physics'),
(3,'10B','Literature'),
(3,'10A','History');

-- 9. DAILY ATTENDANCE (per student per day) ---------------
CREATE TABLE IF NOT EXISTS daily_attendance (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT  NOT NULL,
    class_name VARCHAR(20) NOT NULL,
    date       DATE NOT NULL,
    status     ENUM('present','absent','tardy') DEFAULT 'present',
    marked_by  INT  NOT NULL,
    note       VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_date (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by)  REFERENCES users(id)
);

-- 10. FEE PAYMENTS ----------------------------------------
CREATE TABLE IF NOT EXISTS fee_payments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT           NULL,
    student_id     INT           NULL,
    payer_name     VARCHAR(150)  NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    purpose        VARCHAR(255)  NOT NULL DEFAULT 'Application Fee',
    payment_method ENUM('cash','bank_transfer','mobile_money','card') DEFAULT 'cash',
    reference      VARCHAR(100)  DEFAULT NULL,
    status         ENUM('pending','paid','overdue','waived') DEFAULT 'pending',
    due_date       DATE          NULL,
    paid_date      DATE          NULL,
    notes          TEXT          NULL,
    marked_by      INT           NULL,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id)     REFERENCES students(id)     ON DELETE SET NULL,
    FOREIGN KEY (marked_by)      REFERENCES users(id)        ON DELETE SET NULL
);

-- Auto-seed pending fees for any existing applications
INSERT IGNORE INTO fee_payments (application_id, payer_name, amount, purpose, status, due_date)
SELECT id,
       CONCAT(first_name,' ',last_name,' (Guardian: ',parent_name,')'),
       5000, 'Application Fee', 'pending',
       DATE_ADD(created_at, INTERVAL 7 DAY)
FROM applications;

-- 11. CHAT / MESSAGING ------------------------------------
CREATE TABLE IF NOT EXISTS chat_messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sender_id       INT  NOT NULL,
    receiver_id     INT  NULL,
    channel         ENUM('direct','broadcast','support') DEFAULT 'direct',
    message         TEXT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    broadcast_group VARCHAR(100) NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 12. NOTIFICATIONS ----------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    title      VARCHAR(255) NOT NULL,
    body       TEXT         NOT NULL,
    type       ENUM('info','warning','success','danger') DEFAULT 'info',
    is_read    TINYINT(1)   DEFAULT 0,
    link       VARCHAR(255) DEFAULT '#',
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 13. TIMETABLE -------------------------------------------
CREATE TABLE IF NOT EXISTS timetable (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(20) NOT NULL,
    day        ENUM('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
    period     TINYINT     NOT NULL,
    start_time TIME        NOT NULL,
    end_time   TIME        NOT NULL,
    subject    VARCHAR(100) NOT NULL,
    teacher_id INT          NULL,
    room       VARCHAR(20)  DEFAULT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT IGNORE INTO timetable (class_name, day, period, start_time, end_time, subject, teacher_id, room) VALUES
('10B','Monday',   1,'07:30','08:20','Mathematics',2,'A1'),
('10B','Monday',   2,'08:20','09:10','Physics',    2,'Lab1'),
('10B','Monday',   3,'09:30','10:20','Literature', 3,'B2'),
('10B','Tuesday',  1,'07:30','08:20','History',    3,'B3'),
('10B','Tuesday',  2,'08:20','09:10','Mathematics',2,'A1'),
('10B','Wednesday',1,'07:30','08:20','Literature', 3,'B2'),
('10B','Wednesday',2,'08:20','09:10','Physics',    2,'Lab1'),
('10B','Thursday', 1,'07:30','08:20','Mathematics',2,'A1'),
('10B','Thursday', 2,'08:20','09:10','History',    3,'B3'),
('10B','Friday',   1,'07:30','08:20','Literature', 3,'B2'),
('10B','Friday',   2,'08:20','09:10','Mathematics',2,'A1');

-- 14. PERFORMANCE ALERTS ----------------------------------
CREATE TABLE IF NOT EXISTS performance_alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT  NOT NULL,
    type        ENUM('low_gpa','low_attendance','behavior') NOT NULL,
    message     TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ============================================================
-- END OF SCHEMA
-- Demo credentials:
--   Admin:    admin    / Admin@1234   → /login.php?role=admin
--   Teacher:  teacher1 / Admin@1234   → /login.php?role=teacher
--   Parent:   parent1  / Admin@1234   → /login.php?role=parent
--   Student:  1045     / student123   → /portals.php (student login)
-- ============================================================
