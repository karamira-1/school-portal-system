<?php
// ============================================================
// portal-teacher.php  –  Teacher Dashboard
// Sections: dashboard | attendance | grades | chat | alerts
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('teacher', '/login.php?role=teacher');

$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$msg_count   = unread_messages($current_user['id']);
$alert_count = unread_notifications($current_user['id']);

// Fetch this teacher's assigned classes + subjects (RBAC scope)
$tc_stmt = $pdo->prepare('SELECT DISTINCT class_name FROM teacher_classes WHERE user_id = ?');
$tc_stmt->execute([$current_user['id']]);
$my_classes = $tc_stmt->fetchAll(PDO::FETCH_COLUMN);

$sub_stmt = $pdo->prepare('SELECT DISTINCT subject FROM teacher_classes WHERE user_id = ?');
$sub_stmt->execute([$current_user['id']]);
$my_subjects = $sub_stmt->fetchAll(PDO::FETCH_COLUMN);

// ── Shared: student list for teacher's classes ────────────
$class_filter = $my_classes[0] ?? '';   // default to first class
if (!empty($_GET['class']) && in_array($_GET['class'], $my_classes)) {
    $class_filter = $_GET['class'];
}

$students_in_class = [];
if ($class_filter) {
    $st_stmt = $pdo->prepare('SELECT * FROM students WHERE class = ? ORDER BY full_name');
    $st_stmt->execute([$class_filter]);
    $students_in_class = $st_stmt->fetchAll();
}

// ── Section: Attendance Marking ───────────────────────────
if ($section === 'attendance') {
    $att_date = $_GET['date'] ?? date('Y-m-d');

    // Already marked today?
    $marked_stmt = $pdo->prepare('
        SELECT student_id, status, note
        FROM   daily_attendance
        WHERE  class_name = ? AND date = ? AND marked_by = ?
    ');
    $marked_stmt->execute([$class_filter, $att_date, $current_user['id']]);
    $already_marked = [];
    foreach ($marked_stmt->fetchAll() as $r) {
        $already_marked[$r['student_id']] = $r;
    }
    $is_submitted = count($already_marked) > 0;
}

// ── Section: Grades ───────────────────────────────────────
if ($section === 'grades') {
    $grade_term = $_GET['term'] ?? 'Term 2, 2025';
    $grade_subject = $_GET['subject'] ?? ($my_subjects[0] ?? '');

    // Load grades already entered by this teacher for the selected term/subject
    $existing_grades = [];
    if ($class_filter && $grade_subject && $grade_term) {
        $g_stmt = $pdo->prepare('
            SELECT g.*, s.full_name, s.student_number
            FROM   grades g
            JOIN   students s ON s.id = g.student_id
            WHERE  g.term = ? AND g.subject = ? AND s.class = ?
            ORDER  BY s.full_name
        ');
        $g_stmt->execute([$grade_term, $grade_subject, $class_filter]);
        $existing_grades = $g_stmt->fetchAll();
    }
}

// ── Section: Chat (teacher side) ─────────────────────────
if ($section === 'chat') {
    $chat_with = (int)($_GET['with'] ?? 0);
    // Teachers can message parents and admins
    $contacts = $pdo->query("
        SELECT u.id, u.full_name, u.profile_image, r.name AS role_name,
               (SELECT COUNT(*) FROM chat_messages WHERE sender_id=u.id AND receiver_id={$current_user['id']} AND is_read=0) AS unread
        FROM   users u JOIN roles r ON r.id=u.role_id
        WHERE  u.id != {$current_user['id']} AND u.is_active=1
               AND r.name IN ('admin','parent')
        ORDER  BY r.name, u.full_name
    ")->fetchAll();

    $messages = [];
    if ($chat_with) {
        $m_stmt = $pdo->prepare("
            SELECT m.*, u.full_name AS sender_name, u.profile_image AS sender_img
            FROM   chat_messages m JOIN users u ON u.id=m.sender_id
            WHERE  (m.sender_id=? AND m.receiver_id=?)
                OR (m.sender_id=? AND m.receiver_id=?)
            ORDER  BY m.created_at ASC LIMIT 100
        ");
        $m_stmt->execute([$current_user['id'],$chat_with,$chat_with,$current_user['id']]);
        $messages = $m_stmt->fetchAll();
        $pdo->prepare("UPDATE chat_messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
            ->execute([$chat_with,$current_user['id']]);
    }
}

// ── Section: Alerts ───────────────────────────────────────
if ($section === 'alerts') {
    $alerts = $pdo->prepare("
        SELECT pa.*, s.full_name, s.class, s.gpa, s.attendance_pct
        FROM   performance_alerts pa JOIN students s ON s.id=pa.student_id
        WHERE  s.class IN (" . implode(',', array_fill(0, count($my_classes), '?')) . ")
               AND pa.is_resolved = 0
        ORDER  BY pa.created_at DESC
    ");
    $alerts->execute($my_classes);
    $alerts_list = $alerts->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ Teacher Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { 'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C' } } }
        };
        if (localStorage.theme==='dark') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 transition-colors duration-300">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-60 bg-blue-900 flex flex-col flex-shrink-0">
        <div class="px-5 py-5 border-b border-white/10">
            <a href="/index.php" class="flex items-center gap-2">
                <img src="/assets/images/logo.png" alt="ASPEJ" class="h-8">
                <div>
                    <p class="text-white font-bold text-sm">ASPEJ School</p>
                    <p class="text-blue-300 text-xs">Teacher Portal</p>
                </div>
            </a>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            <?php
            $nav = [
                'dashboard'  => ['fa-tachometer-alt',  'Dashboard'],
                'attendance' => ['fa-calendar-check',  'Mark Attendance'],
                'grades'     => ['fa-clipboard-list',  'Grade Entry'],
                'chat'       => ['fa-comments',        'Messages'],
                'alerts'     => ['fa-exclamation-triangle','Alerts'],
            ];
            foreach ($nav as $key => [$icon, $label]):
            ?>
            <a href="?section=<?= $key ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      <?= $section===$key ? 'bg-white text-blue-900 font-bold shadow' : 'text-blue-100 hover:bg-white/10' ?>">
                <i class="fas <?= $icon ?> w-4 text-center"></i> <?= $label ?>
                <?php if ($key==='chat' && $msg_count>0): ?>
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5"><?= $msg_count ?></span>
                <?php endif; ?>
                <?php if ($key==='alerts' && $alert_count>0): ?>
                <span class="ml-auto bg-yellow-400 text-blue-900 text-xs rounded-full px-1.5"><?= $alert_count ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <!-- Class filter in sidebar -->
        <?php if (count($my_classes) > 1): ?>
        <div class="px-4 py-3 border-t border-white/10">
            <p class="text-blue-300 text-xs uppercase tracking-wide mb-2">Switch Class</p>
            <?php foreach ($my_classes as $cls): ?>
            <a href="?section=<?= $section ?>&class=<?= urlencode($cls) ?>"
               class="block px-3 py-1.5 rounded text-sm mb-1 <?= $class_filter===$cls?'bg-aspej-gold text-blue-900 font-bold':'text-blue-200 hover:bg-white/10' ?>">
                <?= htmlspecialchars($cls) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="px-4 py-3 border-t border-white/10">
            <div class="flex items-center gap-2 mb-2">
                <img src="<?= htmlspecialchars($current_user['profile_image']) ?>" class="w-8 h-8 rounded-full object-cover border-2 border-aspej-gold">
                <div class="min-w-0">
                    <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
                    <p class="text-blue-300 text-xs">Teacher</p>
                </div>
            </div>
            <a href="/api/logout.php" class="flex items-center text-blue-300 hover:text-white text-xs transition gap-1">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
                    <?php
                    $titles=['dashboard'=>'My Dashboard','attendance'=>'Attendance Marking',
                             'grades'=>'Grade Entry','chat'=>'Messages','alerts'=>'Performance Alerts'];
                    echo $titles[$section] ?? 'Teacher Portal';
                    ?>
                </h1>
                <?php if ($class_filter): ?>
                <span class="badge badge-blue">Class <?= htmlspecialchars($class_filter) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="text-gray-500 dark:text-gray-400 hover:text-aspej-navy dark:hover:text-white">
                    <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">

        <?php if ($section === 'dashboard'): ?>
        <!-- ── Teacher Dashboard ── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="admin-card text-center">
                <i class="fas fa-users text-3xl text-blue-500 mb-2"></i>
                <p class="text-2xl font-bold dark:text-white"><?= count($students_in_class) ?></p>
                <p class="text-sm text-gray-500">Students in <?= htmlspecialchars($class_filter) ?></p>
            </div>
            <div class="admin-card text-center">
                <i class="fas fa-book text-3xl text-indigo-500 mb-2"></i>
                <p class="text-2xl font-bold dark:text-white"><?= count($my_subjects) ?></p>
                <p class="text-sm text-gray-500">Assigned Subjects</p>
            </div>
            <div class="admin-card text-center">
                <?php $today_att = $pdo->prepare('SELECT COUNT(*) FROM daily_attendance WHERE class_name=? AND date=? AND marked_by=?');
                      $today_att->execute([$class_filter, date('Y-m-d'), $current_user['id']]);
                      $att_done = $today_att->fetchColumn(); ?>
                <i class="fas fa-calendar-check text-3xl <?= $att_done?'text-green-500':'text-red-500' ?> mb-2"></i>
                <p class="text-2xl font-bold dark:text-white"><?= $att_done ? 'Done' : 'Pending' ?></p>
                <p class="text-sm text-gray-500">Today's Attendance</p>
            </div>
            <div class="admin-card text-center">
                <?php $low_att = $pdo->prepare('SELECT COUNT(*) FROM students WHERE class=? AND attendance_pct < 80');
                      $low_att->execute([$class_filter]);
                      $low_att_count = $low_att->fetchColumn(); ?>
                <i class="fas fa-exclamation-triangle text-3xl text-orange-500 mb-2"></i>
                <p class="text-2xl font-bold dark:text-white"><?= $low_att_count ?></p>
                <p class="text-sm text-gray-500">Below 80% Attendance</p>
            </div>
        </div>

        <!-- My class students summary -->
        <div class="admin-card mb-6">
            <h3 class="section-heading">My Class – <?= htmlspecialchars($class_filter) ?></h3>
            <div class="overflow-x-auto">
            <table class="admin-table">
                <thead><tr><th>Student</th><th>GPA</th><th>Attendance</th><th>Alert</th></tr></thead>
                <tbody>
                <?php foreach ($students_in_class as $s): ?>
                <tr>
                    <td class="font-medium"><?= htmlspecialchars($s['full_name']) ?> <span class="text-xs text-gray-400">#<?= $s['student_number'] ?></span></td>
                    <td class="font-bold <?= $s['gpa']<2.0?'text-red-500':($s['gpa']>=3.5?'text-green-600':'text-gray-700 dark:text-gray-200') ?>">
                        <?= number_format($s['gpa'],1) ?>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="<?= $s['attendance_pct']<80?'bg-red-500':'bg-green-500' ?> h-1.5 rounded-full"
                                     style="width:<?= min(100,$s['attendance_pct']) ?>%"></div>
                            </div>
                            <span class="text-xs"><?= number_format($s['attendance_pct'],0) ?>%</span>
                        </div>
                    </td>
                    <td>
                        <?php if ($s['gpa']<2.0 || $s['attendance_pct']<80): ?>
                        <span class="badge badge-red text-xs">⚠ At Risk</span>
                        <?php else: ?>
                        <span class="text-green-500 text-xs"><i class="fas fa-check-circle"></i></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- My Subjects -->
        <div class="admin-card">
            <h3 class="section-heading">My Assigned Classes & Subjects</h3>
            <?php
            $tc_full = $pdo->prepare('SELECT * FROM teacher_classes WHERE user_id=? ORDER BY class_name, subject');
            $tc_full->execute([$current_user['id']]);
            $tc_all = $tc_full->fetchAll();
            ?>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php foreach ($tc_all as $tc): ?>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <i class="fas fa-book text-blue-500"></i>
                <div>
                    <p class="font-semibold text-sm dark:text-white"><?= htmlspecialchars($tc['subject']) ?></p>
                    <p class="text-xs text-gray-400">Class <?= htmlspecialchars($tc['class_name']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php elseif ($section === 'attendance'): ?>
        <!-- ── Attendance Marking ── -->
        <div class="admin-card mb-4">
            <form class="flex flex-wrap gap-3 items-end mb-2" method="GET">
                <input type="hidden" name="section" value="attendance">
                <input type="hidden" name="class"   value="<?= htmlspecialchars($class_filter) ?>">
                <div>
                    <label class="form-label">Date</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($att_date) ?>"
                           max="<?= date('Y-m-d') ?>" class="form-control">
                </div>
                <button class="btn-primary">Load</button>
            </form>
        </div>

        <?php if (empty($students_in_class)): ?>
        <div class="admin-card text-center text-gray-400 py-12">
            <i class="fas fa-users text-4xl mb-3"></i>
            <p>No students found for class <?= htmlspecialchars($class_filter) ?>.</p>
        </div>
        <?php else: ?>
        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="section-heading !mb-0">
                    <?= $is_submitted ? '<i class="fas fa-check-circle text-green-500 mr-2"></i>Attendance Submitted' : 'Mark Attendance' ?>
                </h3>
                <?php if (!$is_submitted): ?>
                <div class="flex gap-2">
                    <button type="button" onclick="markAll('present')" class="btn-gold text-xs px-3">All Present</button>
                    <button type="button" onclick="markAll('absent')"  class="btn-danger text-xs px-3">All Absent</button>
                </div>
                <?php endif; ?>
            </div>

            <form id="attendanceForm" class="space-y-2">
            <input type="hidden" name="class_name" value="<?= htmlspecialchars($class_filter) ?>">
            <input type="hidden" name="date"       value="<?= htmlspecialchars($att_date) ?>">

            <?php foreach ($students_in_class as $s):
                $existing = $already_marked[$s['id']] ?? null;
                $status = $existing['status'] ?? 'present';
            ?>
            <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700">
                <input type="hidden" name="students[<?= $s['id'] ?>][id]" value="<?= $s['id'] ?>">
                <div class="flex-1 min-w-0">
                    <p class="font-medium dark:text-white truncate"><?= htmlspecialchars($s['full_name']) ?></p>
                    <p class="text-xs text-gray-400">#<?= htmlspecialchars($s['student_number']) ?></p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <?php foreach (['present'=>['bg-green-500','fa-check','Present'],
                                    'absent' =>['bg-red-500',  'fa-times','Absent'],
                                    'tardy'  =>['bg-yellow-500','fa-clock','Tardy']] as $st=>[$col,$ico,$lbl]):
                    ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="students[<?= $s['id'] ?>][status]"
                               value="<?= $st ?>"
                               <?= $status===$st?'checked':'' ?>
                               <?= $is_submitted?'disabled':'' ?>
                               class="sr-only peer" id="att_<?= $s['id'] ?>_<?= $st ?>">
                        <span class="peer-checked:<?= $col ?> peer-checked:text-white border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500
                                     peer-checked:border-transparent px-3 py-1.5 rounded-full text-xs font-semibold
                                     flex items-center gap-1 transition select-none">
                            <i class="fas <?= $ico ?>"></i> <?= $lbl ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="students[<?= $s['id'] ?>][note]"
                       placeholder="Note…" value="<?= htmlspecialchars($existing['note'] ?? '') ?>"
                       <?= $is_submitted?'disabled readonly':'' ?>
                       class="w-32 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 dark:bg-gray-800 dark:text-white focus:outline-none focus:border-aspej-gold">
            </div>
            <?php endforeach; ?>

            <?php if (!$is_submitted): ?>
            <div class="pt-4 flex justify-end gap-3">
                <p id="attFormMsg" class="text-sm text-gray-400 self-center"></p>
                <button type="submit" id="submitAttBtn" class="btn-gold px-8">
                    <i class="fas fa-save mr-2"></i> Submit Attendance
                </button>
            </div>
            <?php else: ?>
            <div class="pt-4 text-center text-green-600 dark:text-green-400 font-semibold">
                <i class="fas fa-check-circle mr-2"></i>
                Attendance for <?= htmlspecialchars($att_date) ?> has already been submitted.
            </div>
            <?php endif; ?>
            </form>
        </div>
        <?php endif; ?>

        <?php elseif ($section === 'grades'): ?>
        <!-- ── Grade Entry ── -->
        <div class="admin-card mb-4">
            <form class="flex flex-wrap gap-3 items-end" method="GET">
                <input type="hidden" name="section" value="grades">
                <input type="hidden" name="class"   value="<?= htmlspecialchars($class_filter) ?>">
                <div>
                    <label class="form-label">Term</label>
                    <select name="term" class="form-control">
                        <?php foreach (['Term 2, 2025','Term 1, 2025','Term 3, 2024'] as $t): ?>
                        <option value="<?= $t ?>" <?= $grade_term===$t?'selected':'' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Subject</label>
                    <select name="subject" class="form-control">
                        <?php foreach ($my_subjects as $subj): ?>
                        <option value="<?= htmlspecialchars($subj) ?>" <?= $grade_subject===$subj?'selected':'' ?>><?= htmlspecialchars($subj) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn-primary">Load</button>
            </form>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="section-heading !mb-0">
                    <?= htmlspecialchars($grade_subject) ?> – <?= htmlspecialchars($grade_term) ?>
                </h3>
            </div>

            <form id="gradesForm" class="space-y-3">
            <input type="hidden" name="term"    value="<?= htmlspecialchars($grade_term) ?>">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($grade_subject) ?>">

            <!-- Table header -->
            <div class="grid grid-cols-12 gap-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <div class="col-span-4">Student</div>
                <div class="col-span-2">Grade</div>
                <div class="col-span-6">Remarks</div>
            </div>

            <?php
            // Map existing grades by student_id
            $grades_map = [];
            foreach ($existing_grades as $g) $grades_map[$g['student_id']] = $g;
            foreach ($students_in_class as $s):
                $existing = $grades_map[$s['id']] ?? null;
            ?>
            <div class="grid grid-cols-12 gap-2 items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <input type="hidden" name="grades[<?= $s['id'] ?>][student_id]" value="<?= $s['id'] ?>">
                <div class="col-span-4">
                    <p class="font-medium text-sm dark:text-white"><?= htmlspecialchars($s['full_name']) ?></p>
                    <p class="text-xs text-gray-400">#<?= htmlspecialchars($s['student_number']) ?></p>
                </div>
                <div class="col-span-2">
                    <select name="grades[<?= $s['id'] ?>][grade]" class="form-control text-sm py-1.5 font-bold">
                        <option value="">—</option>
                        <?php foreach (['A','A-','B+','B','B-','C+','C','C-','D','F'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($existing['grade']??'')===$g?'selected':'' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-6">
                    <input type="text" name="grades[<?= $s['id'] ?>][remarks]"
                           value="<?= htmlspecialchars($existing['remarks'] ?? '') ?>"
                           placeholder="Teacher remarks…"
                           class="form-control text-sm py-1.5">
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Term-wide comments -->
            <div class="pt-3 border-t dark:border-gray-600">
                <label class="form-label">Overall Teacher Comments for Term</label>
                <?php
                $tc_comments = $pdo->prepare('SELECT teacher_comments FROM term_summaries WHERE student_id=? AND term=?');
                // Show first student's as example (or could be class-wide)
                $first_student_id = !empty($students_in_class) ? $students_in_class[0]['id'] : 0;
                $tc_comments->execute([$first_student_id, $grade_term]);
                $existing_comments = $tc_comments->fetchColumn() ?? '';
                ?>
                <textarea name="teacher_comments" rows="3" class="form-control"
                          placeholder="General comment for this term…"><?= htmlspecialchars($existing_comments) ?></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <p id="gradeFormMsg" class="text-sm text-gray-400 self-center"></p>
                <button type="submit" id="submitGradeBtn" class="btn-gold px-8">
                    <i class="fas fa-save mr-2"></i> Save Grades
                </button>
            </div>
            </form>
        </div>

        <?php elseif ($section === 'chat'): ?>
        <!-- ── Teacher Chat ── -->
        <div class="flex h-[calc(100vh-12rem)] gap-5">
            <!-- Contacts -->
            <div class="w-64 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl shadow overflow-y-auto">
                <div class="p-4 border-b dark:border-gray-700">
                    <h4 class="font-semibold text-aspej-navy dark:text-white text-sm">Conversations</h4>
                    <p class="text-xs text-gray-400">Parents & Admins</p>
                </div>
                <?php foreach ($contacts as $c): ?>
                <a href="?section=chat&with=<?= $c['id'] ?>"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b dark:border-gray-700 transition <?= $chat_with===$c['id']?'bg-blue-50 dark:bg-blue-900/20':'' ?>">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($c['profile_image']) ?>" class="w-9 h-9 rounded-full object-cover">
                        <?php if ($c['unread']>0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $c['unread'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium dark:text-white truncate"><?= htmlspecialchars($c['full_name']) ?></p>
                        <p class="text-xs text-gray-400 capitalize"><?= $c['role_name'] ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Chat window -->
            <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <?php if ($chat_with): ?>
                <?php $partner = array_values(array_filter($contacts, fn($c)=>$c['id']===$chat_with))[0] ?? null; ?>
                <?php if ($partner): ?>
                <div class="px-5 py-3 border-b dark:border-gray-700 flex items-center gap-3">
                    <img src="<?= htmlspecialchars($partner['profile_image']) ?>" class="w-9 h-9 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-sm dark:text-white"><?= htmlspecialchars($partner['full_name']) ?></p>
                        <p class="text-xs text-gray-400 capitalize"><?= $partner['role_name'] ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-4">
                    <?php foreach ($messages as $m):
                    $mine = $m['sender_id'] == $current_user['id']; ?>
                    <div class="flex <?= $mine?'justify-end':'justify-start' ?>">
                        <?php if (!$mine): ?>
                        <img src="<?= htmlspecialchars($m['sender_img']) ?>" class="w-8 h-8 rounded-full mr-2 mt-1 flex-shrink-0 object-cover">
                        <?php endif; ?>
                        <div class="max-w-[70%]">
                            <div class="<?= $mine?'bg-blue-700 text-white':'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white' ?> rounded-2xl px-4 py-2.5 text-sm">
                                <?= htmlspecialchars($m['message']) ?>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 <?= $mine?'text-right':'' ?>"><?= date('H:i', strtotime($m['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                    <p class="text-center text-gray-400 mt-10 text-sm">No messages yet. Start the conversation!</p>
                    <?php endif; ?>
                </div>
                <form id="sendMsgForm" data-to="<?= $chat_with ?>" class="p-4 border-t dark:border-gray-700 flex gap-3">
                    <input type="text" id="msgInput" placeholder="Type a message…" required
                           class="flex-1 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-2 text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:border-blue-500">
                    <button class="bg-blue-700 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-blue-800 flex-shrink-0">
                        <i class="fas fa-paper-plane text-sm"></i>
                    </button>
                </form>
                <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <i class="fas fa-comments text-5xl mb-4 opacity-30"></i>
                        <p>Select a contact to start messaging</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($section === 'alerts'): ?>
        <!-- ── Teacher Alerts ── -->
        <div class="admin-card">
            <div class="space-y-3">
            <?php if (empty($alerts_list)): ?>
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-smile text-5xl text-green-400 mb-3"></i>
                <p>No performance alerts for your classes!</p>
            </div>
            <?php endif; ?>
            <?php foreach ($alerts_list as $a): ?>
            <div class="flex items-start gap-4 p-4 rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20">
                <i class="fas <?= $a['type']==='low_gpa'?'fa-star text-yellow-500':'fa-user-times text-red-500' ?> text-xl mt-1"></i>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-bold text-sm dark:text-white"><?= htmlspecialchars($a['full_name']) ?></p>
                        <span class="badge badge-blue"><?= htmlspecialchars($a['class']) ?></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300"><?= htmlspecialchars($a['message']) ?></p>
                    <p class="text-xs text-gray-400 mt-1">
                        GPA: <strong><?= number_format($a['gpa'],1) ?></strong> &bull;
                        Attendance: <strong><?= number_format($a['attendance_pct'],1) ?>%</strong>
                    </p>
                </div>
                <a href="?section=chat" class="btn-primary text-xs px-3">Message Parent</a>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php endif; // end teacher section switch ?>

        </main>
    </div><!-- /main -->
</div><!-- /flex -->

<script src="/assets/js/admin.js"></script>
<script src="/assets/js/teacher.js"></script>
</body>
</html>
