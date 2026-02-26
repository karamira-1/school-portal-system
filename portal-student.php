<?php
// ============================================================
// portal-student.php  –  Student Portal (session-based auth)
// ============================================================
session_start();
require_once __DIR__ . '/includes/db.php';

$pdo = get_db();

// ── Simple login handler ────────────────────────────────────
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password       = trim($_POST['password'] ?? '');

    if ($student_number && $password) {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE student_number = ?');
        $stmt->execute([$student_number]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password_hash'])) {
            $_SESSION['student_id']     = $student['id'];
            $_SESSION['student_number'] = $student['student_number'];
        } else {
            $login_error = 'Invalid student number or password.';
        }
    } else {
        $login_error = 'Please enter both fields.';
    }
}

// ── Logout ──────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /portal-student.php');
    exit;
}

// ── Fetch student data if logged in ─────────────────────────
$student = null;
$terms   = [];
$attendance = [];

if (!empty($_SESSION['student_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$_SESSION['student_id']]);
    $student = $stmt->fetch();

    // All unique terms
    $stmt = $pdo->prepare('SELECT DISTINCT term, year FROM grades WHERE student_id = ? ORDER BY year DESC, term DESC');
    $stmt->execute([$student['id']]);
    $terms = $stmt->fetchAll();

    // Attendance log
    $stmt = $pdo->prepare('SELECT * FROM attendance_log WHERE student_id = ? ORDER BY year, month_label');
    $stmt->execute([$student['id']]);
    $attendance = $stmt->fetchAll();
}

// Determine page render
$is_logged_in = !empty($student);
$page_title   = $is_logged_in ? 'Student Portal – ' . $student['full_name'] : 'Student Portal – Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – <?= htmlspecialchars($page_title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { 'aspej-navy': '#1D2A4D', 'aspej-gold': '#FFC72C' } } }
        };
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-500">

<?php if (!$is_logged_in): ?>
<!-- ============================================================
     LOGIN SCREEN
============================================================ -->
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-10">
        <div class="text-center mb-8">
            <a href="/index.php">
                <img src="/assets/images/logo.png" alt="ASPEJ" class="h-14 mx-auto mb-3">
            </a>
            <h1 class="text-2xl font-bold text-aspej-navy dark:text-white">Student Portal Login</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your credentials to access your portal</p>
        </div>

        <?php if ($login_error): ?>
        <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($login_error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="login">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student Number</label>
                <input type="text" name="student_number" required placeholder="e.g. 1045"
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 dark:bg-gray-700 dark:text-white focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 dark:bg-gray-700 dark:text-white focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 outline-none">
            </div>
            <button type="submit"
                    class="w-full bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-3 rounded-lg text-lg shadow-lg transition-all duration-300">
                Log In <i class="fas fa-sign-in-alt ml-2"></i>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">
            <a href="/portals.php" class="hover:text-aspej-gold transition"><i class="fas fa-arrow-left mr-1"></i> Back to Portals</a>
        </p>
        <p class="text-center text-xs text-gray-400 mt-2">Demo: student# <strong>1045</strong> / password <strong>student123</strong></p>
    </div>
</div>

<?php else: ?>
<!-- ============================================================
     PORTAL DASHBOARD
============================================================ -->

<?php
// Preload first term data for PHP-rendered defaults
$default_term = !empty($terms) ? $terms[0]['term'] : '';
$stmt = $pdo->prepare('SELECT * FROM grades WHERE student_id = ? AND term = ?');
$stmt->execute([$student['id'], $default_term]);
$default_grades = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM term_summaries WHERE student_id = ? AND term = ?');
$stmt->execute([$student['id'], $default_term]);
$default_summary = $stmt->fetch();

// Build JSON for JS
$terms_json      = json_encode($terms);
$attendance_json = json_encode($attendance);
$student_json    = json_encode([
    'id'             => $student['student_number'],
    'name'           => $student['full_name'],
    'class'          => $student['class'],
    'gpa'            => $student['gpa'],
    'attendance_pct' => $student['attendance_pct'],
    'total_days'     => $student['total_days'],
    'absent_days'    => $student['absent_days'],
]);
?>

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-aspej-navy text-white flex-shrink-0 hidden md:flex flex-col">
        <div class="p-6">
            <a href="/index.php" class="flex items-center space-x-2 mb-8">
                <img src="/assets/images/logo.png" alt="ASPEJ" class="h-8 w-auto">
                <span class="text-lg font-bold text-aspej-gold">Student Portal</span>
            </a>
        </div>
        <nav class="flex-1 space-y-1 px-4">
            <a href="#" class="sidebar-link active" data-section="dashboard">
                <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i> Dashboard
            </a>
            <a href="#" class="sidebar-link" data-section="grades">
                <i class="fas fa-graduation-cap mr-3 w-5 text-center"></i> Grades
            </a>
            <a href="#" class="sidebar-link" data-section="attendance">
                <i class="fas fa-user-check mr-3 w-5 text-center"></i> Attendance
            </a>
            <a href="#" class="sidebar-link" data-section="reports">
                <i class="fas fa-file-alt mr-3 w-5 text-center"></i> Reports
            </a>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="?logout=1" class="flex items-center text-gray-400 hover:text-white transition duration-200">
                <i class="fas fa-sign-out-alt mr-3"></i> Log Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-auto">

        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center flex-shrink-0">
            <h2 id="page-title" class="text-2xl font-semibold text-aspej-navy dark:text-white">Dashboard</h2>
            <div class="flex items-center space-x-4">
                <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Toggle dark mode">
                    <i class="fas fa-sun text-aspej-gold dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline-block text-white"></i>
                </button>
                <div class="flex items-center space-x-2">
                    <span class="text-gray-700 dark:text-white font-medium"><?= htmlspecialchars($student['full_name']) ?></span>
                    <img class="h-10 w-10 rounded-full object-cover border-2 border-aspej-gold"
                         src="/assets/images/student-profile.jpg" alt="Profile">
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">

            <!-- ── Dashboard ── -->
            <section id="dashboard" class="content-section" data-section-name="Dashboard">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="dashboard-card bg-aspej-navy text-white">
                        <i class="fas fa-user-graduate text-3xl mb-2 text-aspej-gold"></i>
                        <p class="text-sm uppercase tracking-wide opacity-80">Student</p>
                        <p class="text-2xl font-bold"><?= htmlspecialchars($student['full_name']) ?></p>
                        <p class="text-sm opacity-70">Class <?= htmlspecialchars($student['class']) ?> &bull; #<?= htmlspecialchars($student['student_number']) ?></p>
                    </div>
                    <div class="dashboard-card bg-white dark:bg-gray-700 shadow-xl">
                        <i class="fas fa-star text-3xl mb-2 text-aspej-navy dark:text-aspej-gold"></i>
                        <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Overall GPA</p>
                        <p class="text-4xl font-bold text-aspej-navy dark:text-white"><?= number_format($student['gpa'], 1) ?></p>
                    </div>
                    <div class="dashboard-card bg-white dark:bg-gray-700 shadow-xl">
                        <i class="fas fa-chart-line text-3xl mb-2 text-aspej-navy dark:text-aspej-gold"></i>
                        <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Attendance Rate</p>
                        <p class="text-4xl font-bold text-aspej-navy dark:text-white"><?= number_format($student['attendance_pct'], 1) ?>%</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <h3 class="text-xl font-bold mb-4 text-aspej-navy dark:text-white">Quick Access</h3>
                    <p class="mb-4 text-gray-600 dark:text-gray-300">
                        Welcome back, <?= htmlspecialchars(explode(' ', $student['full_name'])[0]) ?>! Check your latest report card or attendance summary below.
                    </p>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <button class="quick-access-btn" data-target="reports"><i class="fas fa-file-alt mr-2"></i> View Report Card</button>
                        <button class="quick-access-btn" data-target="grades"><i class="fas fa-clipboard-list mr-2"></i> Check Grades</button>
                        <button class="quick-access-btn" data-target="attendance"><i class="fas fa-calendar-check mr-2"></i> Attendance Log</button>
                        <a href="/news.php" class="quick-access-btn"><i class="fas fa-bullhorn mr-2"></i> School News</a>
                    </div>
                </div>
            </section>

            <!-- ── Grades ── -->
            <section id="grades" class="content-section hidden" data-section-name="Grades">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <h3 class="text-xl font-bold text-aspej-navy dark:text-white">Current Term Grades</h3>
                        <div class="flex items-center gap-2">
                            <label for="term-select-grades" class="text-sm text-gray-600 dark:text-gray-300">Term:</label>
                            <select id="term-select-grades" class="border border-gray-300 dark:border-gray-600 rounded-md p-2 dark:bg-gray-700 dark:text-white text-sm">
                                <?php foreach ($terms as $t): ?>
                                <option value="<?= htmlspecialchars($t['term']) ?>"><?= htmlspecialchars($t['term']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase hidden sm:table-cell">Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="grades-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($default_grades as $g): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($g['subject']) ?></td>
                                    <td class="px-6 py-4 text-lg font-semibold text-aspej-navy dark:text-aspej-gold"><?= htmlspecialchars($g['grade']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell"><?= htmlspecialchars($g['remarks']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-6 text-right text-lg font-bold text-aspej-navy dark:text-aspej-gold">
                        Term GPA: <span id="grades-gpa"><?= $default_summary ? number_format($default_summary['overall_gpa'], 1) : '–' ?></span>
                    </p>
                </div>
            </section>

            <!-- ── Attendance ── -->
            <section id="attendance" class="content-section hidden" data-section-name="Attendance">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <h3 class="text-xl font-bold mb-6 text-aspej-navy dark:text-white">Monthly Attendance Summary</h3>
                    <div class="grid grid-cols-3 gap-4 mb-8 text-center">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="text-3xl font-bold text-aspej-navy dark:text-white"><?= $student['total_days'] ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Days</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg">
                            <p class="text-3xl font-bold text-green-600"><?= $student['total_days'] - $student['absent_days'] ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Days Present</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-lg">
                            <p class="text-3xl font-bold text-red-500"><?= $student['absent_days'] ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Days Absent</p>
                        </div>
                    </div>
                    <div class="w-full md:w-3/4 lg:w-2/3 mx-auto">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- ── Reports ── -->
            <section id="reports" class="content-section hidden" data-section-name="Reports">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-xl font-bold text-aspej-navy dark:text-white">Report Card</h3>
                        <div class="flex space-x-3 items-center">
                            <select id="term-select" class="border border-gray-300 dark:border-gray-600 rounded-md p-2 dark:bg-gray-700 dark:text-white text-sm">
                                <?php foreach ($terms as $t): ?>
                                <option value="<?= htmlspecialchars($t['term']) ?>"><?= htmlspecialchars($t['term']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button id="download-report-btn" class="bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-semibold py-2 px-4 rounded-md transition duration-300 text-sm">
                                <i class="fas fa-download mr-1"></i> Download PDF
                            </button>
                        </div>
                    </div>

                    <div id="report-card-content" class="p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="text-center mb-6 border-b pb-4 dark:border-gray-600">
                            <img src="/assets/images/logo.png" alt="ASPEJ" class="h-12 mx-auto mb-2">
                            <h4 class="text-2xl font-extrabold text-aspej-navy dark:text-white">Academic Report Card</h4>
                            <p id="report-term-year" class="text-lg text-aspej-gold font-semibold mt-1"><?= htmlspecialchars($default_term) ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-gray-700 dark:text-gray-300 mb-6 border-b pb-4 dark:border-gray-600 text-sm">
                            <div><span class="font-semibold">Student:</span> <span id="report-student-name"><?= htmlspecialchars($student['full_name']) ?></span></div>
                            <div><span class="font-semibold">ID:</span> <span id="report-student-id"><?= htmlspecialchars($student['student_number']) ?></span></div>
                            <div><span class="font-semibold">Class:</span> <span id="report-student-class"><?= htmlspecialchars($student['class']) ?></span></div>
                            <div><span class="font-semibold">Year:</span> <span id="report-student-year"><?= date('Y') ?></span></div>
                        </div>
                        <h5 class="text-lg font-bold mb-3 text-aspej-navy dark:text-white">Subject Performance</h5>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <thead class="bg-aspej-navy">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-white uppercase">Subject</th>
                                        <th class="py-3 px-4 text-center text-sm font-semibold text-white uppercase w-20">Grade</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-white uppercase hidden sm:table-cell">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="report-card-table-body" class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                                    <?php foreach ($default_grades as $g): ?>
                                    <tr>
                                        <td class="py-3 px-4"><?= htmlspecialchars($g['subject']) ?></td>
                                        <td class="py-3 px-4 text-center font-semibold"><?= htmlspecialchars($g['grade']) ?></td>
                                        <td class="py-3 px-4 hidden sm:table-cell text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($g['remarks']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-between items-center mb-6">
                            <p class="text-xl font-bold text-aspej-navy dark:text-white">
                                Overall GPA: <span id="report-gpa" class="text-aspej-gold"><?= $default_summary ? number_format($default_summary['overall_gpa'], 1) : '–' ?></span>
                            </p>
                        </div>
                        <div class="border-t pt-4 dark:border-gray-600">
                            <h5 class="text-lg font-bold mb-2 text-aspej-navy dark:text-white">Teacher Comments</h5>
                            <p id="report-teacher-comments" class="italic text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 p-4 rounded-md shadow-inner">
                                <?= $default_summary ? htmlspecialchars($default_summary['teacher_comments']) : '' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div><!-- /main content -->
</div><!-- /flex wrapper -->

<!-- Pass server data to JS -->
<script>
    const PORTAL_STUDENT = <?= $student_json ?>;
    const PORTAL_ATTENDANCE = <?= $attendance_json ?>;
    const PORTAL_TERMS = <?= $terms_json ?>;
</script>
<script src="/assets/js/portal.js"></script>
<script src="/assets/js/script.js"></script>

<?php endif; // end is_logged_in ?>
</body>
</html>
