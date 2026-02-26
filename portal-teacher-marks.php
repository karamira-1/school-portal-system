<?php
// ============================================================
// Merge into portal-teacher.php  –  Marks Entry section
// This file shows the marks entry UI for inclusion
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('teacher', '/login.php?role=teacher');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';

// Get this teacher's assignments
$my_assignments = $pdo->prepare("
    SELECT ta.*, c.name AS class_name, c.combination,
           s.name AS subject_name, s.id AS subject_id
    FROM   teacher_assignments ta
    JOIN   classes  c ON c.id=ta.class_id
    JOIN   subjects s ON s.id=ta.subject_id
    WHERE  ta.teacher_id=?
    ORDER  BY c.name, s.name
");
$my_assignments->execute([$current_user['id']]);
$my_assignments = $my_assignments->fetchAll();

// Active assignment for marks entry
$active_assign_id = (int)($_GET['assign_id'] ?? ($my_assignments[0]['id'] ?? 0));
$active_assign = null;
foreach ($my_assignments as $a) if ($a['id'] == $active_assign_id) { $active_assign = $a; break; }

$view_term    = $active_assign['term']     ?? 1;
$view_year    = $active_assign['year']     ?? date('Y');
$test_type    = $_GET['test_type']         ?? 'test1';

// Students for this class
$students_for_marks = [];
$existing_marks     = [];
if ($active_assign) {
    $st = $pdo->prepare('SELECT * FROM students WHERE class_id=? AND is_active=1 ORDER BY full_name');
    $st->execute([$active_assign['class_id']]);
    $students_for_marks = $st->fetchAll();

    // Load existing marks
    $em = $pdo->prepare("
        SELECT student_id, mark_value FROM marks
        WHERE subject_id=? AND term=? AND year=? AND test_type=?
    ");
    $em->execute([$active_assign['subject_id'], $view_term, $view_year, $test_type]);
    foreach ($em->fetchAll() as $m) $existing_marks[$m['student_id']] = $m['mark_value'];
}
?>
<!-- This template is included in portal-teacher.php marks section -->
<!-- MARKS ENTRY UI -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

    <!-- Assignment selector -->
    <div class="admin-card">
        <h3 class="section-heading">My Assignments</h3>
        <div class="space-y-1">
        <?php if (empty($my_assignments)): ?>
        <p class="text-gray-400 text-sm">No lessons assigned yet. Contact the Director of Studies.</p>
        <?php endif; ?>
        <?php foreach ($my_assignments as $a): ?>
        <a href="?section=marks&assign_id=<?= $a['id'] ?>"
           class="block px-3 py-2 rounded-lg text-sm transition <?= $active_assign_id==$a['id']?'bg-blue-700 text-white font-semibold':'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
            <p class="font-medium"><?= htmlspecialchars($a['subject_name']) ?></p>
            <p class="text-xs opacity-70"><?= htmlspecialchars($a['class_name']) ?> &bull; T<?= $a['term'] ?>/<?= $a['year'] ?></p>
        </a>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Marks entry -->
    <div class="lg:col-span-3">
    <?php if ($active_assign): ?>
    <div class="admin-card">
        <!-- Header & test type selector -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="font-bold text-lg text-aspej-navy dark:text-white"><?= htmlspecialchars($active_assign['subject_name']) ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($active_assign['class_name']) ?> &bull; Term <?= $view_term ?>, <?= $view_year ?></p>
            </div>
            <div class="flex gap-2">
                <?php foreach (['test1'=>'Test 1 (25%)','test2'=>'Test 2 (25%)','exam'=>'Exam (50%)'] as $tt=>$lbl): ?>
                <a href="?section=marks&assign_id=<?= $active_assign_id ?>&test_type=<?= $tt ?>"
                   class="text-xs font-semibold px-3 py-1.5 rounded-full border transition
                          <?= $test_type===$tt?'bg-aspej-navy text-white border-aspej-navy':'border-gray-300 text-gray-600 dark:text-gray-300 hover:border-aspej-navy dark:hover:border-aspej-gold' ?>">
                    <?= $lbl ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <form id="marksEntryForm">
            <input type="hidden" name="assign_id"  value="<?= $active_assign_id ?>">
            <input type="hidden" name="subject_id" value="<?= $active_assign['subject_id'] ?>">
            <input type="hidden" name="class_id"   value="<?= $active_assign['class_id'] ?>">
            <input type="hidden" name="term"       value="<?= $view_term ?>">
            <input type="hidden" name="year"       value="<?= $view_year ?>">
            <input type="hidden" name="test_type"  value="<?= $test_type ?>">

            <!-- Column headers -->
            <div class="grid grid-cols-12 gap-2 px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <div class="col-span-5">Student</div>
                <div class="col-span-3">Mark /100</div>
                <div class="col-span-2">Grade</div>
                <div class="col-span-2">Status</div>
            </div>

            <div class="space-y-2 mb-5">
            <?php foreach ($students_for_marks as $s):
                $mark = $existing_marks[$s['id']] ?? '';
                $grade = is_numeric($mark) ? grade_letter((float)$mark) : '—';
                $pass  = is_numeric($mark) ? (((float)$mark) >= 50 ? 'Pass' : 'Fail') : '—';
            ?>
            <div class="grid grid-cols-12 gap-2 items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <input type="hidden" name="marks[<?= $s['id'] ?>][student_id]" value="<?= $s['id'] ?>">
                <div class="col-span-5">
                    <p class="font-medium text-sm dark:text-white"><?= htmlspecialchars($s['full_name']) ?></p>
                    <p class="text-xs text-gray-400">#<?= htmlspecialchars($s['student_number']) ?></p>
                </div>
                <div class="col-span-3">
                    <input type="number" name="marks[<?= $s['id'] ?>][mark]"
                           value="<?= htmlspecialchars((string)$mark) ?>"
                           min="0" max="100" step="0.5"
                           class="form-control text-sm py-1.5 font-bold text-center mark-input"
                           data-student-id="<?= $s['id'] ?>"
                           placeholder="0–100">
                </div>
                <div class="col-span-2 text-center">
                    <span class="badge badge-blue font-bold grade-display-<?= $s['id'] ?>"><?= $grade ?></span>
                </div>
                <div class="col-span-2 text-center">
                    <span class="text-xs font-semibold pass-display-<?= $s['id'] ?> <?= $pass==='Pass'?'text-green-600':($pass==='Fail'?'text-red-500':'text-gray-400') ?>"><?= $pass ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <div class="flex justify-end gap-3">
                <span id="marksFormMsg" class="text-sm text-gray-400 self-center"></span>
                <button type="submit" id="saveMarksBtn" class="btn-gold px-8">
                    <i class="fas fa-save mr-2"></i>Save Marks
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="admin-card text-center text-gray-400 py-12">
        <i class="fas fa-tasks text-5xl mb-3 opacity-30"></i>
        <p>Select an assignment from the left to enter marks.</p>
    </div>
    <?php endif; ?>
    </div>
</div>

<script>
// Live grade preview as teacher types
document.querySelectorAll('.mark-input').forEach(input => {
    input.addEventListener('input', function() {
        const sid   = this.dataset.studentId;
        const val   = parseFloat(this.value);
        const gradeEl= document.querySelector('.grade-display-'+sid);
        const passEl = document.querySelector('.pass-display-'+sid);
        if (isNaN(val) || this.value === '') {
            gradeEl.textContent = '—'; passEl.textContent = '—';
            passEl.className = 'text-xs font-semibold pass-display-'+sid+' text-gray-400';
            return;
        }
        const grade = val>=80?'A':val>=70?'B':val>=60?'C':val>=50?'D':'F';
        const pass  = val>=50;
        gradeEl.textContent = grade;
        passEl.textContent  = pass?'Pass':'Fail';
        passEl.className    = 'text-xs font-semibold pass-display-'+sid+' '+(pass?'text-green-600':'text-red-500');
    });
});

document.getElementById('marksEntryForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('saveMarksBtn');
    const msg = document.getElementById('marksFormMsg');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i>Saving…';
    const fd = new FormData(e.target); fd.append('action','save_marks');
    const r  = await fetch('/api/marks_manager.php',{method:'POST',body:fd});
    const d  = await r.json();
    msg.className   = 'text-sm self-center '+(d.success?'text-green-600 dark:text-green-400':'text-red-500');
    msg.textContent = d.success ? '✓ '+d.message : '⚠ '+d.message;
    btn.disabled=false; btn.innerHTML='<i class="fas fa-save mr-2"></i>Save Marks';
});
</script>
