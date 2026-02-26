<?php
// ============================================================
// portal-dos.php  –  Director of Studies Portal
// Sections: dashboard | assign | marks | reports | stats
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('director_studies', '/login.php?role=director_studies');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$classes  = get_all_classes();

// ── Assignments section ───────────────────────────────────
if ($section === 'assign') {
    $teachers = $pdo->query("SELECT u.id, u.full_name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='teacher' AND u.is_active=1 ORDER BY u.full_name")->fetchAll();
    $subjects = $pdo->query('SELECT * FROM subjects WHERE is_active=1 ORDER BY combination IS NULL DESC, name')->fetchAll();
    $current_year = (int)date('Y');

    // Existing assignments
    $assigns = $pdo->query("
        SELECT ta.*, u.full_name AS teacher_name, c.name AS class_name, s.name AS subject_name
        FROM teacher_assignments ta
        JOIN users    u ON u.id=ta.teacher_id
        JOIN classes  c ON c.id=ta.class_id
        JOIN subjects s ON s.id=ta.subject_id
        ORDER BY c.name, s.name
    ")->fetchAll();
}

// ── Marks view ────────────────────────────────────────────
if ($section === 'marks') {
    $view_class   = (int)($_GET['class_id'] ?? 0);
    $view_subject = (int)($_GET['subject_id'] ?? 0);
    $view_term    = (int)($_GET['term'] ?? 1);
    $view_year    = (int)($_GET['year'] ?? date('Y'));
    $marks_data   = [];
    $subjects_for_class = [];

    if ($view_class) {
        $cls = $pdo->prepare('SELECT combination FROM classes WHERE id=?');
        $cls->execute([$view_class]);
        $combo = $cls->fetchColumn();
        $subjects_for_class = get_subjects_for_combination($combo);
    }

    if ($view_class && $view_subject && $view_term) {
        $marks_data = $pdo->prepare("
            SELECT s.id, s.full_name, s.student_number,
                MAX(CASE WHEN m.test_type='test1' THEN m.mark_value END) AS test1,
                MAX(CASE WHEN m.test_type='test2' THEN m.mark_value END) AS test2,
                MAX(CASE WHEN m.test_type='exam'  THEN m.mark_value END) AS exam
            FROM students s
            LEFT JOIN marks m ON m.student_id=s.id AND m.subject_id=? AND m.term=? AND m.year=?
            WHERE s.class_id=? AND s.is_active=1
            GROUP BY s.id ORDER BY s.full_name
        ");
        $marks_data->execute([$view_subject, $view_term, $view_year, $view_class]);
        $marks_data = $marks_data->fetchAll();
    }
}

// ── Reports section ───────────────────────────────────────
if ($section === 'reports') {
    $rpt_class = (int)($_GET['class_id'] ?? 0);
    $rpt_term  = (int)($_GET['term']     ?? 1);
    $rpt_year  = (int)($_GET['year']     ?? date('Y'));
    $rpt_type  = $_GET['rpt_type']       ?? 'full'; // full | period1 | period2
    $report_data = [];

    if ($rpt_class && $rpt_term) {
        // Get class info
        $cls_info = $pdo->prepare('SELECT * FROM classes WHERE id=?');
        $cls_info->execute([$rpt_class]);
        $cls_info = $cls_info->fetch();

        if ($cls_info) {
            $subjects_list = get_subjects_for_combination($cls_info['combination']);

            // Get all students in class
            $students_in_class = $pdo->prepare('SELECT * FROM students WHERE class_id=? AND is_active=1 ORDER BY full_name');
            $students_in_class->execute([$rpt_class]);
            $students_in_class = $students_in_class->fetchAll();

            foreach ($students_in_class as &$st) {
                $st['subjects'] = [];
                $st['total']    = 0;
                $st['count']    = 0;

                foreach ($subjects_list as $subj) {
                    $m = $pdo->prepare("
                        SELECT test_type, mark_value FROM marks
                        WHERE student_id=? AND subject_id=? AND term=? AND year=?
                    ");
                    $m->execute([$st['id'], $subj['id'], $rpt_term, $rpt_year]);
                    $raw = $m->fetchAll();
                    $by_type = [];
                    foreach ($raw as $row) $by_type[$row['test_type']] = (float)$row['mark_value'];

                    $score = match($rpt_type) {
                        'period1' => $by_type['test1'] ?? null,
                        'period2' => $by_type['test2'] ?? null,
                        default   => (!empty($by_type)) ? calc_term_marks($by_type)['total'] : null,
                    };

                    if ($score !== null) {
                        $st['subjects'][$subj['id']] = [
                            'name'   => $subj['name'],
                            'score'  => round($score, 1),
                            'grade'  => grade_letter($score),
                            'pass'   => is_pass($score),
                            'test1'  => $by_type['test1'] ?? '—',
                            'test2'  => $by_type['test2'] ?? '—',
                            'exam'   => $by_type['exam']  ?? '—',
                        ];
                        $st['total'] += $score;
                        $st['count']++;
                    }
                }
                $st['average'] = $st['count'] > 0 ? round($st['total'] / $st['count'], 1) : 0;

                // Conduct
                $cond = $pdo->prepare('SELECT score FROM conduct_marks WHERE student_id=? AND term=? AND year=?');
                $cond->execute([$st['id'], $rpt_term, $rpt_year]);
                $st['conduct'] = $cond->fetchColumn() ?? 40;
            }
            unset($st);

            // Rank by average descending
            usort($students_in_class, fn($a,$b) => $b['average'] <=> $a['average']);
            foreach ($students_in_class as $rank => &$st) $st['rank'] = $rank + 1;
            unset($st);

            // Class average
            $class_avg = count($students_in_class) > 0
                ? round(array_sum(array_column($students_in_class,'average')) / count($students_in_class), 1)
                : 0;

            $report_data = [
                'class'    => $cls_info,
                'students' => $students_in_class,
                'subjects' => $subjects_list,
                'class_avg'=> $class_avg,
                'term'     => $rpt_term,
                'year'     => $rpt_year,
                'type'     => $rpt_type,
            ];
        }
    }
}

// ── Statistics ────────────────────────────────────────────
if ($section === 'stats') {
    $stats_class = (int)($_GET['class_id'] ?? 0);
    $stats_term  = (int)($_GET['term']     ?? 1);
    $stats_year  = (int)($_GET['year']     ?? date('Y'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – Director of Studies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
    <style>
        @media print { .no-print{display:none!important} }
        .report-card { font-family: 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen">
<div class="flex h-screen overflow-hidden">

<aside class="w-60 bg-indigo-900 flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-indigo-300 text-xs">Director of Studies</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <?php foreach ([
            'dashboard'=>['fa-tachometer-alt','Dashboard'],
            'assign'   =>['fa-tasks',         'Assign Lessons'],
            'marks'    =>['fa-clipboard-list', 'View Marks'],
            'reports'  =>['fa-file-alt',       'Generate Reports'],
            'stats'    =>['fa-chart-bar',      'Statistics'],
        ] as $k=>[$ic,$lb]): ?>
        <a href="?section=<?= $k ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section===$k?'bg-white text-indigo-900 font-bold':'text-indigo-100 hover:bg-white/10' ?>">
            <i class="fas <?= $ic ?> w-4"></i> <?= $lb ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-indigo-300 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between no-print">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['dashboard'=>'Dashboard','assign'=>'Assign Lessons to Teachers','marks'=>'View Entered Marks','reports'=>'Generate Reports','stats'=>'Performance Statistics'][$section] ?? '' ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'dashboard'): ?>
<!-- Dashboard -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $total_teachers   = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='teacher'")->fetchColumn();
    $total_assignments= $pdo->query('SELECT COUNT(*) FROM teacher_assignments WHERE year=YEAR(CURDATE())')->fetchColumn();
    $total_marks      = $pdo->query('SELECT COUNT(*) FROM marks WHERE year=YEAR(CURDATE())')->fetchColumn();
    ?>
    <div class="admin-card text-center"><i class="fas fa-chalkboard-teacher text-3xl text-blue-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $total_teachers ?></p><p class="text-sm text-gray-500">Teachers</p></div>
    <div class="admin-card text-center"><i class="fas fa-tasks text-3xl text-indigo-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $total_assignments ?></p><p class="text-sm text-gray-500">Assignments This Year</p></div>
    <div class="admin-card text-center"><i class="fas fa-pencil-alt text-3xl text-green-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $total_marks ?></p><p class="text-sm text-gray-500">Marks Entered</p></div>
    <div class="admin-card text-center"><i class="fas fa-school text-3xl text-orange-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= count($classes) ?></p><p class="text-sm text-gray-500">Classes</p></div>
</div>
<div class="admin-card">
    <h3 class="section-heading">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="?section=assign" class="quick-action-btn"><i class="fas fa-tasks mr-2"></i>Assign Lessons</a>
        <a href="?section=marks"  class="quick-action-btn"><i class="fas fa-eye mr-2"></i>View Marks</a>
        <a href="?section=reports"class="quick-action-btn"><i class="fas fa-file-pdf mr-2"></i>Generate Report</a>
        <a href="?section=stats"  class="quick-action-btn"><i class="fas fa-chart-line mr-2"></i>Statistics</a>
    </div>
</div>

<?php elseif ($section === 'assign'): ?>
<!-- Assign Lessons -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Assignment form -->
    <div class="admin-card">
        <h3 class="section-heading">New Assignment</h3>
        <form id="assignForm" class="space-y-4">
            <div>
                <label class="form-label">Teacher</label>
                <select name="teacher_id" required class="form-control">
                    <option value="">— Select Teacher —</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Class</label>
                <select name="class_id" id="assignClassSelect" required class="form-control">
                    <option value="">— Select Class —</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" data-combo="<?= htmlspecialchars($c['combination']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Subject</label>
                <select name="subject_id" id="assignSubjectSelect" required class="form-control">
                    <option value="">— Select class first —</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Term</label>
                    <select name="term" class="form-control">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Year</label>
                    <input type="number" name="year" value="<?= date('Y') ?>" class="form-control">
                </div>
            </div>
            <div id="assignMsg" class="text-sm hidden"></div>
            <button type="submit" class="btn-gold w-full"><i class="fas fa-plus mr-2"></i>Assign Lesson</button>
        </form>
    </div>

    <!-- Existing assignments -->
    <div class="admin-card">
        <h3 class="section-heading">Current Assignments</h3>
        <div class="overflow-y-auto max-h-96">
        <table class="admin-table text-sm">
            <thead><tr><th>Teacher</th><th>Class</th><th>Subject</th><th>Term</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($assigns as $a): ?>
            <tr>
                <td class="font-medium text-xs"><?= htmlspecialchars($a['teacher_name']) ?></td>
                <td><span class="badge badge-blue text-xs"><?= htmlspecialchars($a['class_name']) ?></span></td>
                <td class="text-xs text-gray-600 dark:text-gray-300"><?= htmlspecialchars($a['subject_name']) ?></td>
                <td class="text-xs text-gray-400">T<?= $a['term'] ?>/<?= $a['year'] ?></td>
                <td><button onclick="removeAssign(<?= $a['id'] ?>)" class="text-red-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($assigns)): ?><tr><td colspan="5" class="text-center text-gray-400 py-6 text-sm">No assignments yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php elseif ($section === 'marks'): ?>
<!-- View Marks -->
<div class="admin-card mb-4">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="marks">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" id="marksClassSel" class="form-control" onchange="this.form.submit()">
                <option value="">— Class —</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" data-combo="<?= $c['combination'] ?>" <?= $view_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($view_class && !empty($subjects_for_class)): ?>
        <div>
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-control">
                <option value="">— Subject —</option>
                <?php foreach ($subjects_for_class as $subj): ?>
                <option value="<?= $subj['id'] ?>" <?= $view_subject==$subj['id']?'selected':'' ?>><?= htmlspecialchars($subj['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-control">
                <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>" <?= $view_term==$t?'selected':'' ?>>Term <?= $t ?></option><?php endfor; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><input type="number" name="year" value="<?= $view_year ?>" class="form-control w-24"></div>
        <button class="btn-primary">View</button>
    </form>
</div>

<?php if (!empty($marks_data)): ?>
<div class="admin-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="section-heading !mb-0">Marks — <?= htmlspecialchars($subjects_for_class[array_search($view_subject, array_column($subjects_for_class,'id'))]['name'] ?? '') ?></h3>
        <a href="/api/export_marks_csv.php?class_id=<?= $view_class ?>&subject_id=<?= $view_subject ?>&term=<?= $view_term ?>&year=<?= $view_year ?>" class="btn-primary text-sm"><i class="fas fa-download mr-1"></i> CSV</a>
    </div>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Student</th><th>Test 1 /100</th><th>Test 2 /100</th><th>Exam /100</th><th>Term Total /100</th><th>Grade</th><th>Status</th></tr></thead>
        <tbody>
        <?php
        $class_total = 0; $class_count = 0;
        foreach ($marks_data as $row):
            $m = calc_term_marks(['test1'=>$row['test1']??0,'test2'=>$row['test2']??0,'exam'=>$row['exam']??0]);
            $has_marks = ($row['test1']!==null || $row['test2']!==null || $row['exam']!==null);
            if ($has_marks) { $class_total += $m['total']; $class_count++; }
        ?>
        <tr>
            <td class="font-medium"><?= htmlspecialchars($row['full_name']) ?> <span class="text-xs text-gray-400">#<?= $row['student_number'] ?></span></td>
            <td><?= $row['test1'] !== null ? number_format($row['test1'],1) : '<span class="text-gray-400">—</span>' ?></td>
            <td><?= $row['test2'] !== null ? number_format($row['test2'],1) : '<span class="text-gray-400">—</span>' ?></td>
            <td><?= $row['exam']  !== null ? number_format($row['exam'],1)  : '<span class="text-gray-400">—</span>' ?></td>
            <td class="font-bold <?= !$has_marks?'text-gray-400':($m['total']>=50?'text-green-600':'text-red-500') ?>">
                <?= $has_marks ? number_format($m['total'],1) : '—' ?>
            </td>
            <td><?= $has_marks ? '<span class="badge badge-blue">'.grade_letter($m['total']).'</span>' : '—' ?></td>
            <td><?php if ($has_marks): ?><span class="badge <?= is_pass($m['total'])?'badge-green':'badge-red' ?>"><?= is_pass($m['total'])?'Pass':'Fail' ?></span><?php else: ?><span class="badge badge-gray">Pending</span><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if ($class_count > 0): ?>
        <tfoot><tr class="bg-gray-50 dark:bg-gray-700 font-bold">
            <td colspan="4" class="px-4 py-2 text-right text-sm">Class Average:</td>
            <td class="px-4 py-2"><?= number_format($class_total/$class_count,1) ?></td>
            <td colspan="2"></td>
        </tr></tfoot>
        <?php endif; ?>
    </table>
    </div>
</div>
<?php endif; ?>

<?php elseif ($section === 'reports'): ?>
<!-- Reports -->
<div class="admin-card mb-4 no-print">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="reports">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" required class="form-control">
                <option value="">— Class —</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $rpt_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-control">
                <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>" <?= $rpt_term==$t?'selected':'' ?>>Term <?= $t ?></option><?php endfor; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><input type="number" name="year" value="<?= $rpt_year ?>" class="form-control w-24"></div>
        <div>
            <label class="form-label">Report Type</label>
            <select name="rpt_type" class="form-control">
                <option value="full"    <?= $rpt_type==='full'?   'selected':'' ?>>Full Term (T1+T2+Exam)</option>
                <option value="period1" <?= $rpt_type==='period1'?'selected':'' ?>>1st Period (Test 1 only)</option>
                <option value="period2" <?= $rpt_type==='period2'?'selected':'' ?>>2nd Period (Test 2 only)</option>
            </select>
        </div>
        <button class="btn-primary">Generate</button>
    </form>
</div>

<?php if (!empty($report_data)): ?>
<div class="no-print flex gap-3 mb-4">
    <button onclick="downloadPDF()" class="btn-gold"><i class="fas fa-file-pdf mr-2"></i>Download PDF</button>
    <a href="/api/export_marks_csv.php?class_id=<?= $rpt_class ?>&term=<?= $rpt_term ?>&year=<?= $rpt_year ?>&rpt_type=<?= $rpt_type ?>&report=1" class="btn-primary"><i class="fas fa-file-csv mr-2"></i>Download CSV</a>
</div>

<div id="reportContainer">
<?php foreach ($report_data['students'] as $st): ?>
<div class="report-card bg-white text-gray-900 rounded-xl shadow-lg p-8 mb-6 page-break" style="max-width:900px">
    <!-- Header -->
    <div class="flex items-start justify-between border-b-2 border-aspej-navy pb-4 mb-6">
        <div class="flex items-center gap-4">
            <img src="/assets/images/logo.png" class="h-16 w-auto" alt="ASPEJ" onerror="this.style.display='none'">
            <div>
                <h2 class="text-xl font-extrabold text-aspej-navy uppercase">ASPEJ School</h2>
                <p class="text-sm text-gray-500">Academic Report Card</p>
                <p class="text-sm font-semibold"><?= htmlspecialchars($report_data['class']['name']) ?> &bull; Term <?= $report_data['term'] ?> &bull; <?= $report_data['year'] ?></p>
                <p class="text-xs text-gray-400"><?= ['full'=>'Full Term Report','period1'=>'1st Period Report','period2'=>'2nd Period Report'][$report_data['type']] ?></p>
            </div>
        </div>
        <div class="text-right text-sm">
            <p class="font-bold text-lg text-aspej-navy"><?= htmlspecialchars($st['full_name']) ?></p>
            <p class="text-gray-500">ID: <?= htmlspecialchars($st['student_number']) ?></p>
            <p class="text-gray-500">Class: <?= htmlspecialchars($report_data['class']['name']) ?></p>
            <p class="font-bold text-indigo-600">Rank: <?= $st['rank'] ?> / <?= count($report_data['students']) ?></p>
        </div>
    </div>

    <!-- Marks table -->
    <table class="w-full text-sm mb-6 border-collapse">
        <thead>
            <tr class="bg-aspej-navy text-white">
                <th class="px-3 py-2 text-left">Subject</th>
                <?php if ($rpt_type === 'full'): ?>
                <th class="px-3 py-2 text-center">Test 1</th>
                <th class="px-3 py-2 text-center">Test 2</th>
                <th class="px-3 py-2 text-center">Exam</th>
                <?php endif; ?>
                <th class="px-3 py-2 text-center">Score /100</th>
                <th class="px-3 py-2 text-center">Grade</th>
                <th class="px-3 py-2 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($st['subjects'] as $subj): ?>
        <tr class="border-b border-gray-200 hover:bg-gray-50">
            <td class="px-3 py-2 font-medium"><?= htmlspecialchars($subj['name']) ?></td>
            <?php if ($rpt_type === 'full'): ?>
            <td class="px-3 py-2 text-center text-gray-600"><?= is_numeric($subj['test1']) ? number_format($subj['test1'],1) : '—' ?></td>
            <td class="px-3 py-2 text-center text-gray-600"><?= is_numeric($subj['test2']) ? number_format($subj['test2'],1) : '—' ?></td>
            <td class="px-3 py-2 text-center text-gray-600"><?= is_numeric($subj['exam'])  ? number_format($subj['exam'],1)  : '—' ?></td>
            <?php endif; ?>
            <td class="px-3 py-2 text-center font-bold <?= $subj['pass']?'text-green-700':'text-red-600' ?>"><?= number_format($subj['score'],1) ?></td>
            <td class="px-3 py-2 text-center font-bold"><?= $subj['grade'] ?></td>
            <td class="px-3 py-2 text-center text-xs font-semibold <?= $subj['pass']?'text-green-600':'text-red-500' ?>"><?= $subj['pass']?'Pass':'Fail' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="bg-gray-100 font-bold">
                <td colspan="<?= $rpt_type==='full'?4:1 ?>" class="px-3 py-2 text-right">Total / Average:</td>
                <td class="px-3 py-2 text-center text-aspej-navy font-extrabold"><?= number_format($st['average'],1) ?></td>
                <td class="px-3 py-2 text-center"><?= grade_letter($st['average']) ?></td>
                <td class="px-3 py-2 text-center text-xs font-semibold <?= $st['average']>=50?'text-green-600':'text-red-500' ?>"><?= $st['average']>=50?'Pass':'Fail' ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Conduct + Class avg -->
    <div class="flex items-start justify-between gap-6">
        <div class="flex-1">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-3 inline-block">
                <p class="text-xs text-yellow-700 uppercase font-semibold mb-1">Conduct (not included in total)</p>
                <p class="text-2xl font-extrabold text-yellow-800"><?= number_format($st['conduct'],1) ?> <span class="text-sm font-normal text-yellow-600">/ 40</span></p>
            </div>
        </div>
        <div class="text-right text-sm text-gray-500">
            <p>Class Average: <strong class="text-gray-800"><?= $report_data['class_avg'] ?>/100</strong></p>
            <p>Position: <strong class="text-indigo-700"><?= $st['rank'] ?> of <?= count($report_data['students']) ?></strong></p>
            <p>Average: <strong class="<?= $st['average']>=50?'text-green-700':'text-red-600' ?>"><?= number_format($st['average'],1) ?>/100</strong></p>
        </div>
    </div>

    <!-- Signature section -->
    <div class="mt-6 pt-4 border-t border-gray-200 grid grid-cols-3 gap-4 text-center text-xs text-gray-500">
        <div><div class="border-b border-gray-400 mb-1 h-8"></div><p>Class Teacher</p></div>
        <div><div class="border-b border-gray-400 mb-1 h-8"></div><p>Director of Studies</p></div>
        <div><div class="border-b border-gray-400 mb-1 h-8"></div><p>School Stamp</p></div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php elseif ($section === 'stats'): ?>
<!-- Statistics -->
<div class="admin-card mb-4 no-print">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="stats">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-control" onchange="this.form.submit()">
                <option value="">— All Classes —</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $stats_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-control">
                <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>" <?= $stats_term==$t?'selected':'' ?>>Term <?= $t ?></option><?php endfor; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><input type="number" name="year" value="<?= $stats_year ?>" class="form-control w-24"></div>
        <button class="btn-primary">Load Stats</button>
    </form>
</div>

<?php
// Class performance stats
$perf_query = $pdo->prepare("
    SELECT c.name AS class_name,
           COUNT(DISTINCT s.id) AS total_students,
           ROUND(AVG(s.gpa * 25), 1) AS avg_mark,
           SUM(CASE WHEN s.gpa >= 2.0 THEN 1 ELSE 0 END) AS passing
    FROM classes c
    LEFT JOIN students s ON s.class_id=c.id AND s.is_active=1
    " . ($stats_class ? "WHERE c.id=$stats_class" : "") . "
    GROUP BY c.id ORDER BY c.combination, c.level
");
$perf_query->execute();
$perf_stats = $perf_query->fetchAll();

// Teacher marks entry stats
$teacher_stats = $pdo->query("
    SELECT u.full_name, COUNT(m.id) AS marks_entered,
           COUNT(DISTINCT CONCAT(m.class_id,'-',m.subject_id)) AS subjects_covered
    FROM users u
    JOIN roles r ON r.id=u.role_id
    LEFT JOIN marks m ON m.entered_by=u.id AND m.year=YEAR(CURDATE())
    WHERE r.name='teacher'
    GROUP BY u.id ORDER BY marks_entered DESC
")->fetchAll();
?>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="admin-card">
        <h3 class="section-heading">Class Performance Overview</h3>
        <canvas id="classChart" height="250"></canvas>
        <div class="overflow-x-auto mt-4">
        <table class="admin-table text-sm">
            <thead><tr><th>Class</th><th>Students</th><th>Avg Mark</th><th>Passing</th></tr></thead>
            <tbody>
            <?php foreach ($perf_stats as $ps): ?>
            <tr>
                <td><span class="badge badge-blue"><?= htmlspecialchars($ps['class_name']) ?></span></td>
                <td><?= $ps['total_students'] ?></td>
                <td class="font-bold <?= $ps['avg_mark']>=50?'text-green-600':'text-red-500' ?>"><?= $ps['avg_mark'] ?></td>
                <td><?= $ps['passing'] ?> / <?= $ps['total_students'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <div class="admin-card">
        <h3 class="section-heading">Teacher Activity (This Year)</h3>
        <div class="overflow-x-auto">
        <table class="admin-table text-sm">
            <thead><tr><th>Teacher</th><th>Marks Entered</th><th>Subjects Covered</th></tr></thead>
            <tbody>
            <?php foreach ($teacher_stats as $ts): ?>
            <tr>
                <td class="font-medium"><?= htmlspecialchars($ts['full_name']) ?></td>
                <td><span class="font-bold text-indigo-600"><?= $ts['marks_entered'] ?></span></td>
                <td class="text-gray-500"><?= $ts['subjects_covered'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($perf_stats,'class_name')) ?>,
        datasets: [{
            label: 'Avg Mark',
            data:  <?= json_encode(array_column($perf_stats,'avg_mark')) ?>,
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { min:0, max:100, grid: { color:'rgba(0,0,0,0.05)' } } }
    }
});
</script>
<?php endif; ?>

</main>
</div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
// Dark mode
document.getElementById('theme-toggle')?.addEventListener('click',()=>{
    document.documentElement.classList.toggle('dark');
    localStorage.theme = document.documentElement.classList.contains('dark')?'dark':'light';
});

// Subject filter by combination on assign page
const subjectsByCombo = <?php echo json_encode(
    array_reduce($pdo->query('SELECT id,name,code,combination FROM subjects WHERE is_active=1')->fetchAll(),
    function($acc,$s){
        $key = $s['combination'] ?? 'shared';
        $acc[$key][] = $s;
        return $acc;
    }, [])
) ?>;

document.getElementById('assignClassSelect')?.addEventListener('change', function() {
    const combo = this.options[this.selectedIndex]?.dataset?.combo;
    const sel   = document.getElementById('assignSubjectSelect');
    sel.innerHTML = '<option value="">— Select Subject —</option>';
    if (!combo) return;
    const shared = subjectsByCombo['shared'] || [];
    const specific = subjectsByCombo[combo] || [];
    [...shared,...specific].forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id; opt.textContent = s.name;
        sel.appendChild(opt);
    });
});

// Assign form
document.getElementById('assignForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('assignMsg');
    const fd  = new FormData(e.target); fd.append('action','assign');
    const r   = await fetch('/api/assignment_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
});

async function removeAssign(id) {
    if (!confirm('Remove this assignment?')) return;
    const fd = new FormData(); fd.append('action','remove'); fd.append('id',id);
    const r  = await fetch('/api/assignment_manager.php',{method:'POST',body:fd});
    const d  = await r.json();
    if (d.success) location.reload(); else alert(d.message);
}

// PDF download
function downloadPDF() {
    const el  = document.getElementById('reportContainer');
    const opt = {
        margin: 0.5,
        filename: 'ASPEJ_Reports_Term<?= $rpt_term ?>_<?= $rpt_year ?>.pdf',
        image: { type:'jpeg', quality:0.98 },
        html2canvas: { scale:2 },
        jsPDF: { unit:'in', format:'a4', orientation:'portrait' }
    };
    html2pdf().set(opt).from(el).save();
}
</script>
</body>
</html>
