# ASPEJ School – Full Stack Web System

A complete PHP + MySQL school management system with public website, student portal, teacher dashboard, and admin panel.

---

## 📁 Project Structure

```
aspej/
├── database.sql                ← Run this ONCE to create all tables + seed data
│
├── index.php                   ← Homepage  (news pulled live from DB)
├── about.php                   ← About the school
├── academics.php               ← Programs and extracurriculars
├── news.php                    ← All news items from DB
├── admissions.php              ← Multi-step admission form (AJAX + file upload)
├── contact.php                 ← AJAX contact form
├── portals.php                 ← Portal selection page
│
├── login.php                   ← Staff login (role-based redirect)
├── portal-student.php          ← Student dashboard (session auth)
├── portal-teacher.php          ← Teacher dashboard (RBAC-scoped)
├── portal-admin.php            ← Admin control panel (9 sections)
│
├── includes/
│   ├── db.php                  ← PDO connection  ← EDIT DB CREDENTIALS HERE
│   ├── auth.php                ← Session auth, role checks, RBAC helpers
│   ├── header.php              ← Public site shared nav
│   ├── footer.php              ← Public site shared footer
│   ├── admin_header.php        ← Admin/Teacher portal sidebar shell
│   └── admin_footer.php        ← Closes admin layout, loads admin.js
│
├── api/
│   ├── submit_application.php  ← Admissions form: validates, uploads cert, inserts to DB
│   ├── submit_contact.php      ← Contact form: validates email, inserts to DB
│   ├── get_grades.php          ← Returns grades JSON per term (student auth-gated)
│   ├── logout.php              ← Universal logout → /login.php
│   ├── user_manager.php        ← Admin: create / update / toggle users
│   ├── news_manager.php        ← Admin: CMS create / edit / delete news
│   ├── attendance_manager.php  ← Teacher: submit daily attendance + auto-update stats
│   ├── grade_manager.php       ← Teacher: save grades + auto-recalculate GPA
│   ├── chat_manager.php        ← Send / poll messages + admin broadcast
│   ├── fee_manager.php         ← Admin: mark fees paid / overdue / waived
│   └── alert_manager.php       ← Resolve alerts + full performance scan
│
└── assets/
    ├── css/
    │   ├── style.css           ← Public site custom CSS (no Tailwind build needed)
    │   └── admin.css           ← Admin + Teacher portal styles
    ├── js/
    │   ├── script.js           ← Theme toggle, mobile menu, back-to-top, smooth scroll
    │   ├── admissions.js       ← 3-step stepper, province→district cascade, AJAX submit
    │   ├── contact.js          ← Contact form AJAX + banners
    │   ├── portal.js           ← Student dashboard: chart, grade loader, PDF download
    │   ├── admin.js            ← Admin portal: modals, AJAX, chat polling, broadcast
    │   └── teacher.js          ← Attendance form AJAX, grade form AJAX
    └── images/                 ← Add your images here (see list below)
```

---

## 🚀 Setup

### 1. Import the Database
```bash
mysql -u root -p < database.sql
```
Or open `database.sql` in phpMyAdmin and run it.

### 2. Configure the Connection
Edit **`includes/db.php`**:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aspej_school');
define('DB_USER', 'your_username');   // ← change this
define('DB_PASS', 'your_password');   // ← change this
```

### 3. Set Upload Permissions
```bash
mkdir -p uploads/certificates
chmod 755 uploads/certificates
```

### 4. Point Your Web Server
Set the document root to the `aspej/` folder.

- **Apache**: works out of the box with `mod_rewrite`
- **Nginx**: ensure `index.php` is the default index
- **Local**: use XAMPP / Laragon / MAMP → place folder in `htdocs/`

---

## 🔐 Demo Credentials

| Role    | Username  | Password     | Portal                        |
|---------|-----------|--------------|-------------------------------|
| Admin   | `admin`   | `Admin@1234` | `/login.php?role=admin`       |
| Teacher | `teacher1`| `Admin@1234` | `/login.php?role=teacher`     |
| Parent  | `parent1` | `Admin@1234` | `/login.php?role=parent`      |
| Student | `1045`    | `student123` | `/portals.php` (student tab)  |

---

## 🖼️ Required Images

Place these in `assets/images/`:

| File                  | Used on              |
|-----------------------|----------------------|
| `logo.png`            | All pages (nav)      |
| `school-hero.jpg`     | Homepage hero        |
| `about-hero.jpg`      | About page           |
| `academics-hero.jpg`  | Academics page       |
| `news-hero.jpg`       | News page            |
| `contact-hero.jpg`    | Contact page         |
| `admissions-info.jpg` | Admissions sidebar   |
| `entrance.jpg`        | Homepage section     |
| `classroom.jpg`       | Homepage section     |
| `robotics.jpg`        | News card            |
| `sports.jpg`          | News card            |
| `staff-training.jpg`  | News card            |
| `debate.jpg`          | News card            |
| `meeting.jpg`         | News card            |
| `student-profile.jpg` | Student portal       |
| `default-avatar.png`  | Staff portals        |
| `leader-1..4.jpg`     | About page team      |

---

## ⚙️ Features by Portal

### 🌐 Public Site
- Homepage with live news from DB
- Multi-step admissions form with province→district cascade (Rwanda)
- O-Level / A-Level trade selection, PDF cert upload (2MB max)
- AJAX contact form
- Dark mode (localStorage + system preference)

### 🎓 Student Portal (`/portals.php`)
- Login by student number + password
- Dashboard: GPA, attendance %, quick access
- Grades: term selector → fetches JSON via API
- Attendance: monthly bar chart (Chart.js)
- Reports: PDF download via html2pdf.js

### 👨‍🏫 Teacher Portal (`/login.php?role=teacher`)
- RBAC-scoped: only sees assigned classes & subjects
- Daily attendance marking (Present / Absent / Tardy per student)
- Grade entry per subject per term → auto-recalculates GPA
- Chat with parents and admins (PHP polling, 4s interval)
- Performance alerts for their classes

### 🛡️ Admin Portal (`/login.php?role=admin`)
- **Dashboard**: KPI cards + GPA distribution chart + recent applications
- **User Management**: create / edit / deactivate accounts for any role
- **Students**: full registry with GPA + attendance bars
- **News CMS**: add / edit / delete news cards without touching code
- **Attendance**: view all submitted records by date + class
- **Fee Payments**: mark paid / overdue / waived, track 5,000 RWF application fees
- **Messages**: direct chat + broadcast to all teachers / parents / students
- **Alerts**: resolve performance flags + trigger full school-wide scan

---

## 🛠️ Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | PHP 8+ (no framework)               |
| Database   | MySQL / MariaDB (PDO)               |
| Frontend   | Tailwind CSS (CDN) + Custom CSS     |
| Icons      | Font Awesome 6                      |
| Charts     | Chart.js 4                          |
| Animations | AOS (Animate On Scroll)             |
| PDF export | html2pdf.js                         |
| Auth       | PHP sessions + bcrypt               |
| Realtime   | PHP polling (4s interval)           |
