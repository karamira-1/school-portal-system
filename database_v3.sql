-- ============================================================
-- ASPEJ SCHOOL – DATABASE v3
-- Run AFTER database.sql (extends existing schema)
-- ============================================================
USE aspej_school;

-- ============================================================
-- 1. NEW ROLES
-- ============================================================
INSERT IGNORE INTO roles (name, label) VALUES
    ('master',              'Master / Mistress'),
    ('librarian',           'Librarian'),
    ('director_studies',    'Director of Studies'),
    ('director_discipline', 'Director of Discipline'),
    ('accountant',          'Accountant');

-- Demo accounts (password: Admin@1234)
INSERT IGNORE INTO users (role_id, full_name, email, username, password_hash, phone) VALUES
((SELECT id FROM roles WHERE name='master'),              'Claudine Uwimana',    'master@aspej.edu',   'master1',     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788100001'),
((SELECT id FROM roles WHERE name='librarian'),           'Pacifique Nzeyimana', 'library@aspej.edu',  'librarian1',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788100002'),
((SELECT id FROM roles WHERE name='director_studies'),    'Innocent Habimana',   'dos@aspej.edu',      'dos1',        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788100003'),
((SELECT id FROM roles WHERE name='director_discipline'), 'Beatrice Mukamana',   'dod@aspej.edu',      'dod1',        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788100004'),
((SELECT id FROM roles WHERE name='accountant'),          'Emmanuel Nkurunziza', 'accounts@aspej.edu', 'accountant1', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+250788100005');

-- ============================================================
-- 2. CLASSES (12 A-Level classes)
-- ============================================================
CREATE TABLE IF NOT EXISTS classes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(50) NOT NULL UNIQUE,
    combination  VARCHAR(10) NOT NULL,   -- NIT | TOU | BDC | ACC
    level        TINYINT     NOT NULL,   -- 3,4,5 (NIT/TOU/BDC) or 4,5,6 (ACC)
    display_name VARCHAR(20) NOT NULL,   -- "Level 3" or "Senior 4"
    is_active    TINYINT(1)  DEFAULT 1
);

INSERT IGNORE INTO classes (name, combination, level, display_name) VALUES
('Level 3 NIT', 'NIT', 3, 'Level 3'), ('Level 4 NIT', 'NIT', 4, 'Level 4'), ('Level 5 NIT', 'NIT', 5, 'Level 5'),
('Level 3 TOU', 'TOU', 3, 'Level 3'), ('Level 4 TOU', 'TOU', 4, 'Level 4'), ('Level 5 TOU', 'TOU', 5, 'Level 5'),
('Level 3 BDC', 'BDC', 3, 'Level 3'), ('Level 4 BDC', 'BDC', 4, 'Level 4'), ('Level 5 BDC', 'BDC', 5, 'Level 5'),
('Senior 4 ACC', 'ACC', 4, 'Senior 4'), ('Senior 5 ACC', 'ACC', 5, 'Senior 5'), ('Senior 6 ACC', 'ACC', 6, 'Senior 6');

-- ============================================================
-- 3. SUBJECTS (combination-specific + shared)
-- combination = NULL means shared across all
-- ============================================================
CREATE TABLE IF NOT EXISTS subjects (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  NOT NULL UNIQUE,
    combination VARCHAR(10)  NULL,
    is_active   TINYINT(1)   DEFAULT 1
);

-- Shared (all combinations)
INSERT IGNORE INTO subjects (name, code, combination) VALUES
('English Language',          'ENG',    NULL),
('Kinyarwanda',               'KIN',    NULL),
('Entrepreneurship',          'ENT',    NULL),
('Physical Education',        'PE',     NULL),
('History & Moral Education', 'HME',    NULL);

-- NIT
INSERT IGNORE INTO subjects (name, code, combination) VALUES
('Computer Networking',       'NIT-CN', 'NIT'),
('Web Technologies',          'NIT-WT', 'NIT'),
('Database Management',       'NIT-DB', 'NIT'),
('Operating Systems',         'NIT-OS', 'NIT'),
('ICT Project',               'NIT-PR', 'NIT');

-- TOU
INSERT IGNORE INTO subjects (name, code, combination) VALUES
('Tourism Principles',        'TOU-TP', 'TOU'),
('Hospitality Management',    'TOU-HM', 'TOU'),
('Food & Beverage',           'TOU-FB', 'TOU'),
('Tour Guiding',              'TOU-TG', 'TOU'),
('French for Tourism',        'TOU-FR', 'TOU');

-- BDC
INSERT IGNORE INTO subjects (name, code, combination) VALUES
('Building Technology',       'BDC-BT', 'BDC'),
('Construction Materials',    'BDC-CM', 'BDC'),
('Technical Drawing',         'BDC-TD', 'BDC'),
('Surveying',                 'BDC-SV', 'BDC'),
('Masonry & Carpentry',       'BDC-MC', 'BDC');

-- ACC
INSERT IGNORE INTO subjects (name, code, combination) VALUES
('Financial Accounting',      'ACC-FA', 'ACC'),
('Business Management',       'ACC-BM', 'ACC'),
('Economics',                 'ACC-EC', 'ACC'),
('Mathematics for Finance',   'ACC-MF', 'ACC'),
('Computer Applications',     'ACC-CA', 'ACC');

-- ============================================================
-- 4. TEACHER ASSIGNMENTS (assigned by Director of Studies)
-- ============================================================
CREATE TABLE IF NOT EXISTS teacher_assignments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id  INT     NOT NULL,
    class_id    INT     NOT NULL,
    subject_id  INT     NOT NULL,
    term        TINYINT NOT NULL DEFAULT 1,
    year        YEAR    NOT NULL,
    assigned_by INT     NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_assign (teacher_id, class_id, subject_id, term, year),
    FOREIGN KEY (teacher_id)  REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (class_id)    REFERENCES classes(id)  ON DELETE CASCADE,
    FOREIGN KEY (subject_id)  REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- ============================================================
-- 5. MARKS
-- test_type: test1 | test2 | exam (each out of 100)
-- Term total = test1*0.25 + test2*0.25 + exam*0.50
-- 1st period report = test1 only
-- 2nd period report = test2 only
-- Full term report  = weighted total
-- ============================================================
CREATE TABLE IF NOT EXISTS marks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT          NOT NULL,
    class_id    INT          NOT NULL,
    subject_id  INT          NOT NULL,
    term        TINYINT      NOT NULL,
    year        YEAR         NOT NULL,
    test_type   ENUM('test1','test2','exam') NOT NULL,
    mark_value  DECIMAL(5,2) NOT NULL DEFAULT 0,
    entered_by  INT          NOT NULL,
    entered_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mark (student_id, subject_id, term, year, test_type),
    FOREIGN KEY (student_id) REFERENCES students(id)  ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(id)   ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)  ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES users(id)
);

-- ============================================================
-- 6. CONDUCT MARKS
-- Each student starts at 40/40 per term.
-- Director of Discipline deducts points per incident.
-- Conduct shown on report but NOT included in academic total.
-- ============================================================
CREATE TABLE IF NOT EXISTS conduct_marks (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT          NOT NULL,
    term            TINYINT      NOT NULL,
    year            YEAR         NOT NULL,
    score           DECIMAL(4,1) NOT NULL DEFAULT 40.0,
    deductions      JSON         NULL,  -- [{reason,points,date,by}]
    last_updated_by INT          NULL,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_conduct (student_id, term, year),
    FOREIGN KEY (student_id)      REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (last_updated_by) REFERENCES users(id)    ON DELETE SET NULL
);

-- ============================================================
-- 7. SCHOOL ATTENDANCE (managed by librarian)
-- ============================================================
CREATE TABLE IF NOT EXISTS school_attendance (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT  NOT NULL,
    date       DATE NOT NULL,
    status     ENUM('present','absent','late','excused') DEFAULT 'present',
    marked_by  INT  NOT NULL,
    note       VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_att (student_id, date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by)  REFERENCES users(id)
);

-- ============================================================
-- 8. FEE STRUCTURE (set by accountant/admin, one per term)
-- ============================================================
CREATE TABLE IF NOT EXISTS fee_structure (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_id    INT           NULL,   -- NULL = all classes
    term        TINYINT       NOT NULL,
    year        YEAR          NOT NULL,
    amount      DECIMAL(10,2) NOT NULL DEFAULT 50000,
    description VARCHAR(255)  NOT NULL DEFAULT 'Tuition Fee',
    created_by  INT           NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fee_struct (class_id, term, year),
    FOREIGN KEY (class_id)  REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)  ON DELETE SET NULL
);

INSERT IGNORE INTO fee_structure (class_id, term, year, amount) VALUES
(NULL, 1, YEAR(CURDATE()), 50000),
(NULL, 2, YEAR(CURDATE()), 50000),
(NULL, 3, YEAR(CURDATE()), 50000);

-- ============================================================
-- 9. FEE PAYMENTS (recorded by accountant)
-- ============================================================
CREATE TABLE IF NOT EXISTS student_fees (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT           NOT NULL,
    term           TINYINT       NOT NULL,
    year           YEAR          NOT NULL,
    amount_due     DECIMAL(10,2) NOT NULL DEFAULT 50000,
    amount_paid    DECIMAL(10,2) NOT NULL DEFAULT 0,
    status         ENUM('unpaid','partial','paid') DEFAULT 'unpaid',
    payment_date   DATE          NULL,
    payment_method ENUM('cash','bank_transfer','mobile_money') DEFAULT 'cash',
    reference      VARCHAR(100)  DEFAULT NULL,
    notes          TEXT          NULL,
    recorded_by    INT           NULL,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_fee (student_id, term, year),
    FOREIGN KEY (student_id)  REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)    ON DELETE SET NULL
);

-- ============================================================
-- 10. PROMOTION LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS promotion_log (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT         NOT NULL,
    from_class    VARCHAR(50) NOT NULL,
    to_class      VARCHAR(50) NOT NULL,
    academic_year YEAR        NOT NULL,
    promoted_by   INT         NOT NULL,
    note          VARCHAR(255) NULL,
    promoted_at   TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id)  REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (promoted_by) REFERENCES users(id)
);

-- ============================================================
-- 11. UPDATE students table: add class_id FK
-- ============================================================
ALTER TABLE students
    ADD COLUMN IF NOT EXISTS class_id INT NULL,
    ADD FOREIGN KEY IF NOT EXISTS fk_student_class (class_id) REFERENCES classes(id) ON DELETE SET NULL;

-- ============================================================
-- GRADE LETTER FUNCTION
-- A=80-100, B=70-79, C=60-69, D=50-59, F=0-49
-- ============================================================
DROP FUNCTION IF EXISTS get_grade_letter;
DELIMITER $$
CREATE FUNCTION get_grade_letter(score DECIMAL(5,2))
RETURNS CHAR(1) DETERMINISTIC
BEGIN
    IF score >= 80 THEN RETURN 'A';
    ELSEIF score >= 70 THEN RETURN 'B';
    ELSEIF score >= 60 THEN RETURN 'C';
    ELSEIF score >= 50 THEN RETURN 'D';
    ELSE RETURN 'F';
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- END DATABASE v3
-- New demo logins (all password: Admin@1234):
--   master1 / librarian1 / dos1 / dod1 / accountant1
-- ============================================================
