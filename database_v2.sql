-- ============================================================
-- ASPEJ SCHOOL – DATABASE SCHEMA v2
-- Run this AFTER database.sql (it extends the existing schema)
-- ============================================================

USE aspej_school;

-- -----------------------------------------------------------
-- 1. USERS & ROLES (RBAC)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        ENUM('admin','teacher','parent','student') NOT NULL UNIQUE,
    label       VARCHAR(50) NOT NULL
);
INSERT IGNORE INTO roles (name, label) VALUES
    ('admin',   'Administrator'),
    ('teacher', 'Teacher'),
    ('parent',  'Parent'),
    ('student', 'Student');

CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    role_id         INT NOT NULL,
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

-- Demo accounts (password for all: Admin@1234)
INSERT IGNORE INTO users (role_id, full_name, email, username, password_hash, phone) VALUES
(1, 'Murekatete Alphonsine', 'admin@aspejschool.edu',    'admin',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000001'),
(2, 'Jean Pierre Hategekimana','teacher@aspejschool.edu','teacher1', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000002'),
(2, 'Aloys Shimiyimana',       'aloys@aspejschool.edu', 'teacher2', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000003'),
(3, 'Robert Adams',            'parent@aspejschool.edu','parent1',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788000004');

-- Link students table to users table
ALTER TABLE students
    ADD COLUMN IF NOT EXISTS user_id INT NULL,
    ADD COLUMN IF NOT EXISTS email   VARCHAR(150) NULL,
    ADD FOREIGN KEY IF NOT EXISTS fk_student_user (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- -----------------------------------------------------------
-- 2. TEACHER → CLASS ASSIGNMENTS (RBAC scope)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS teacher_classes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,          -- teacher's user ID
    class_name  VARCHAR(20) NOT NULL,  -- e.g. '10B'
    subject     VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
INSERT IGNORE INTO teacher_classes (user_id, class_name, subject) VALUES
(2, '10B', 'Mathematics'),
(2, '10B', 'Physics'),
(3, '10B', 'Literature'),
(3, '10A', 'History');

-- -----------------------------------------------------------
-- 3. DAILY ATTENDANCE (per student per day)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS daily_attendance (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    class_name  VARCHAR(20) NOT NULL,
    date        DATE NOT NULL,
    status      ENUM('present','absent','tardy') DEFAULT 'present',
    marked_by   INT NOT NULL,          -- teacher user_id
    note        VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_date (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by)  REFERENCES users(id)
);

-- -----------------------------------------------------------
-- 4. FEE PAYMENTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS fee_payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    application_id  INT          NULL,   -- links to applications table
    student_id      INT          NULL,   -- links to enrolled students
    payer_name      VARCHAR(150) NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    purpose         VARCHAR(255) NOT NULL DEFAULT 'Application Fee',
    payment_method  ENUM('cash','bank_transfer','mobile_money','card') DEFAULT 'cash',
    reference       VARCHAR(100) DEFAULT NULL,
    status          ENUM('pending','paid','overdue','waived') DEFAULT 'pending',
    due_date        DATE         NULL,
    paid_date       DATE         NULL,
    notes           TEXT         NULL,
    marked_by       INT          NULL,   -- admin user_id who marked it
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id)     REFERENCES students(id)     ON DELETE SET NULL,
    FOREIGN KEY (marked_by)      REFERENCES users(id)        ON DELETE SET NULL
);

-- Auto-create pending fee records for existing applications
INSERT IGNORE INTO fee_payments (application_id, payer_name, amount, purpose, status, due_date)
SELECT id,
       CONCAT(first_name, ' ', last_name, ' (Guardian: ', parent_name, ')'),
       5000,
       'Application Fee',
       'pending',
       DATE_ADD(created_at, INTERVAL 7 DAY)
FROM applications;

-- -----------------------------------------------------------
-- 5. CHAT / MESSAGING
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS chat_messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sender_id       INT NOT NULL,
    receiver_id     INT NULL,           -- NULL = broadcast
    channel         ENUM('direct','broadcast','support') DEFAULT 'direct',
    message         TEXT NOT NULL,
    is_read         TINYINT(1) DEFAULT 0,
    broadcast_group VARCHAR(100) NULL,  -- 'all_teachers','all_parents', etc.
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------------
-- 6. NOTIFICATIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(255) NOT NULL,
    body        TEXT NOT NULL,
    type        ENUM('info','warning','success','danger') DEFAULT 'info',
    is_read     TINYINT(1) DEFAULT 0,
    link        VARCHAR(255) DEFAULT '#',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- -----------------------------------------------------------
-- 7. TIMETABLE
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS timetable (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_name  VARCHAR(20) NOT NULL,
    day         ENUM('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
    period      TINYINT NOT NULL,       -- 1–8
    start_time  TIME NOT NULL,
    end_time    TIME NOT NULL,
    subject     VARCHAR(100) NOT NULL,
    teacher_id  INT NULL,
    room        VARCHAR(20) DEFAULT NULL,
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

-- -----------------------------------------------------------
-- 8. PERFORMANCE ALERTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS performance_alerts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    type        ENUM('low_gpa','low_attendance','behavior') NOT NULL,
    message     TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
