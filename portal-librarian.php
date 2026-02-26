<?php
// ============================================================
// portal-librarian.php  –  Librarian Portal
// Sections: dashboard | students | attendance | promote
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('librarian', '/login.php?role=librarian');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$classes = get_all_classes();

// ── Attendance section ────────────────────────────────────
if ($section === 'attendance') {
    $att_date  = $_GET['date']     ?? date('Y-m-d');
    $att_class = (int)($_GET['class_id'] ?? 0);
    $students_for_att = [];
    if ($att_class) {
        $st = $pdo->prepare('SELECT * FROM students WHERE class_id=? AND is_active=1 ORDER BY full_name');
        $st->execute([$att_class]);
        $students_for_att = $st->fetchAll();
    }
    // Existing records for today
    $existing_att = [];
    if ($att_class && !empty($students_for_att)) {
        $ids = array_column($students_for_att,'id');
        $ph  = implode(',', array_fill(0,count($ids),'?'));
        $ea  = $pdo->prepare("SELECT student_id,status,note FROM school_attendance WHERE date=? AND student_id IN ($ph)");
        $ea->execute(array_merge([$att_date],$ids));
        foreach ($ea->fetchAll() as $r) $existing_att[$r['student_id']] = $r;
    }
    $att_submitted = !empty($existing_att);
}

// ── Students section ──────────────────────────────────────
if ($section === 'students') {
    $search   = trim($_GET['q'] ?? '');
    $filter_class = (int)($_GET['class_id'] ?? 0);
    $sql = 'SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON c.id=s.class_id WHERE 1=1';
    $params = [];
    if ($search) { $sql.=' AND (s.full_name LIKE ? OR s.student_number LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; }
    if ($filter_class) { $sql.=' AND s.class_id=?'; $params[]=$filter_class; }
    $sql .= ' ORDER BY c.name, s.full_name LIMIT 300';
    $st = $pdo->prepare($sql); $st->execute($params);
    $students_list = $st->fetchAll();
}

// ── Promote section ───────────────────────────────────────
if ($section === 'promote') {
    // Build promotion map: which class promotes to which
    $promo_map = [
        'Level 3 NIT' => 'Level 4 NIT', 'Level 4 NIT' => 'Level 5 NIT',
        'Level 3 TOU' => 'Level 4 TOU', 'Level 4 TOU' => 'Level 5 TOU',
        'Level 3 BDC' => 'Level 4 BDC', 'Level 4 BDC' => 'Level 5 BDC',
        'Senior 4 ACC' => 'Senior 5 ACC', 'Senior 5 ACC' => 'Senior 6 ACC',
    ];
}

// ── Stats ─────────────────────────────────────────────────
$total_students = $pdo->query('SELECT COUNT(*) FROM students WHERE is_active=1')->fetchColumn();
$today_att      = $pdo->prepare('SELECT COUNT(*) FROM school_attendance WHERE date=CURDATE()');
$today_att->execute(); $today_att_count = $today_att->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – Librarian Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen">
<div class="flex h-screen overflow-hidden">

<!-- Sidebar -->
<aside class="w-60 bg-green-900 flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-green-300 text-xs">Librarian Portal</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <?php foreach (['dashboard'=>['fa-tachometer-alt','Dashboard'],'students'=>['fa-user-graduate','Students'],'attendance'=>['fa-calendar-check','Attendance'],'promote'=>['fa-level-up-alt','Promote Class']] as $k=>[$ic,$lb]): ?>
        <a href="?section=<?= $k ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section===$k?'bg-white text-green-900 font-bold':'text-green-100 hover:bg-white/10' ?>">
            <i class="fas <?= $ic ?> w-4"></i> <?= $lb ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-green-300 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['dashboard'=>'Dashboard','students'=>'Student Management','attendance'=>'School Attendance','promote'=>'Class Promotion'][$section] ?? '' ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'dashboard'): ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="admin-card text-center"><i class="fas fa-users text-3xl text-green-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $total_students ?></p><p class="text-sm text-gray-500">Total Students</p></div>
    <div class="admin-card text-center"><i class="fas fa-calendar-check text-3xl text-blue-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $today_att_count ?></p><p class="text-sm text-gray-500">Attendance Today</p></div>
    <div class="admin-card text-center"><i class="fas fa-school text-3xl text-indigo-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= count($classes) ?></p><p class="text-sm text-gray-500">Active Classes</p></div>
    <div class="admin-card text-center"><i class="fas fa-level-up-alt text-3xl text-orange-500 mb-2"></i>
        <?php $promos = $pdo->query('SELECT COUNT(*) FROM promotion_log WHERE YEAR(promoted_at)=YEAR(CURDATE())')->fetchColumn(); ?>
        <p class="text-2xl font-bold dark:text-white"><?= $promos ?></p><p class="text-sm text-gray-500">Promotions This Year</p>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="admin-card">
        <h3 class="section-heading">Quick Actions</h3>
        <div class="space-y-3">
            <a href="?section=students&action=add" class="quick-action-btn w-full"><i class="fas fa-user-plus mr-2"></i> Add New Student</a>
            <a href="?section=attendance" class="quick-action-btn w-full"><i class="fas fa-calendar-check mr-2"></i> Mark Today's Attendance</a>
            <a href="?section=promote" class="quick-action-btn w-full"><i class="fas fa-level-up-alt mr-2"></i> Promote a Class</a>
        </div>
    </div>
    <div class="admin-card">
        <h3 class="section-heading">Students per Class</h3>
        <div class="space-y-2">
        <?php
        $by_class = $pdo->query("SELECT c.name, COUNT(s.id) as cnt FROM classes c LEFT JOIN students s ON s.class_id=c.id AND s.is_active=1 GROUP BY c.id ORDER BY c.combination, c.level")->fetchAll();
        foreach ($by_class as $bc):
        ?>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($bc['name']) ?></span>
            <span class="badge badge-blue"><?= $bc['cnt'] ?> students</span>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<?php elseif ($section === 'students'): ?>
<div class="admin-card">
    <div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
        <form class="flex gap-2" method="GET">
            <input type="hidden" name="section" value="students">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search name or ID…" class="form-control text-sm">
            <select name="class_id" class="form-control text-sm">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filter_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary text-sm px-3">Filter</button>
        </form>
        <button onclick="openModal('addStudentModal')" class="btn-gold text-sm px-4"><i class="fas fa-user-plus mr-1"></i> Add Student</button>
    </div>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>#</th><th>Name</th><th>Class</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($students_list as $s): ?>
        <tr>
            <td class="text-gray-400 text-xs"><?= htmlspecialchars($s['student_number']) ?></td>
            <td class="font-medium"><?= htmlspecialchars($s['full_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($s['class_name'] ?? 'Unassigned') ?></span></td>
            <td><span class="badge <?= ($s['is_active']??1)?'badge-green':'badge-red' ?>"><?= ($s['is_active']??1)?'Active':'Inactive' ?></span></td>
            <td class="text-right">
                <button onclick="openEditStudent(<?= htmlspecialchars(json_encode($s)) ?>)" class="text-blue-500 hover:text-blue-700 text-sm"><i class="fas fa-edit"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($students_list)): ?><tr><td colspan="5" class="text-center text-gray-400 py-8">No students found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php elseif ($section === 'attendance'): ?>
<div class="admin-card mb-4">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="attendance">
        <div><label class="form-label">Date</label><input type="date" name="date" value="<?= htmlspecialchars($att_date) ?>" max="<?= date('Y-m-d') ?>" class="form-control"></div>
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-control">
                <option value="">— Select Class —</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $att_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn-primary">Load</button>
    </form>
</div>

<?php if ($att_class && !empty($students_for_att)): ?>
<div class="admin-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="section-heading !mb-0"><?= $att_submitted ? '<i class="fas fa-check-circle text-green-500 mr-2"></i>Already Submitted' : 'Mark Attendance – '.htmlspecialchars($att_date) ?></h3>
        <?php if (!$att_submitted): ?>
        <div class="flex gap-2">
            <button onclick="setAll('present')" class="btn-gold text-xs px-3">All Present</button>
            <button onclick="setAll('absent')" class="btn-danger text-xs px-3">All Absent</button>
        </div>
        <?php endif; ?>
    </div>
    <form id="attForm">
        <input type="hidden" name="date"     value="<?= htmlspecialchars($att_date) ?>">
        <input type="hidden" name="class_id" value="<?= $att_class ?>">
        <div class="space-y-2">
        <?php foreach ($students_for_att as $s):
            $existing = $existing_att[$s['id']] ?? null;
            $status   = $existing['status'] ?? 'present';
        ?>
        <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <input type="hidden" name="students[<?= $s['id'] ?>][id]" value="<?= $s['id'] ?>">
            <div class="flex-1 min-w-0">
                <p class="font-medium dark:text-white text-sm"><?= htmlspecialchars($s['full_name']) ?></p>
                <p class="text-xs text-gray-400">#<?= htmlspecialchars($s['student_number']) ?></p>
            </div>
            <div class="flex gap-2">
                <?php foreach (['present'=>['bg-green-500','fa-check'],'absent'=>['bg-red-500','fa-times'],'late'=>['bg-yellow-500','fa-clock'],'excused'=>['bg-blue-500','fa-file-alt']] as $st=>[$col,$ico]): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="students[<?= $s['id'] ?>][status]" value="<?= $st ?>" <?= $status===$st?'checked':'' ?> <?= $att_submitted?'disabled':'' ?> class="sr-only peer">
                    <span class="peer-checked:<?= $col ?> peer-checked:text-white border border-gray-300 dark:border-gray-600 text-gray-400 peer-checked:border-transparent px-3 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1 transition select-none">
                        <i class="fas <?= $ico ?>"></i> <?= ucfirst($st) ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
            <input type="text" name="students[<?= $s['id'] ?>][note]" value="<?= htmlspecialchars($existing['note']??'') ?>" placeholder="Note…" <?= $att_submitted?'disabled readonly':'' ?> class="w-28 text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 dark:bg-gray-800 dark:text-white focus:outline-none focus:border-aspej-gold">
        </div>
        <?php endforeach; ?>
        </div>
        <?php if (!$att_submitted): ?>
        <div class="flex justify-end gap-3 mt-4">
            <span id="attMsg" class="text-sm text-gray-400 self-center"></span>
            <button type="submit" class="btn-gold px-8"><i class="fas fa-save mr-2"></i>Submit Attendance</button>
        </div>
        <?php endif; ?>
    </form>
</div>
<?php elseif ($att_class): ?>
<div class="admin-card text-center text-gray-400 py-10"><i class="fas fa-users text-4xl mb-3"></i><p>No students in this class.</p></div>
<?php else: ?>
<div class="admin-card text-center text-gray-400 py-10"><i class="fas fa-hand-point-up text-4xl mb-3"></i><p>Select a class and date above to begin.</p></div>
<?php endif; ?>

<?php elseif ($section === 'promote'): ?>
<div class="admin-card max-w-2xl">
    <h3 class="section-heading">Promote Entire Class</h3>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">All active students in the selected class will be moved to the next level. A promotion log entry is created for each student.</p>

    <?php
    // Show promotable classes only
    $promotable = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM students s WHERE s.class_id=c.id AND s.is_active=1) as student_count FROM classes c WHERE c.name IN ('Level 3 NIT','Level 4 NIT','Level 3 TOU','Level 4 TOU','Level 3 BDC','Level 4 BDC','Senior 4 ACC','Senior 5 ACC') ORDER BY c.combination, c.level");
    $promotable->execute();
    $promotable_list = $promotable->fetchAll();
    ?>

    <form id="promoteForm" class="space-y-5">
        <div>
            <label class="form-label">Source Class (to promote FROM)</label>
            <select name="from_class_id" id="fromClassSelect" required class="form-control">
                <option value="">— Select class to promote —</option>
                <?php foreach ($promotable_list as $pc): ?>
                <option value="<?= $pc['id'] ?>" data-name="<?= htmlspecialchars($pc['name']) ?>" data-count="<?= $pc['student_count'] ?>">
                    <?= htmlspecialchars($pc['name']) ?> (<?= $pc['student_count'] ?> students)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="promotePreview" class="hidden p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
            <p class="font-semibold text-blue-800 dark:text-blue-300"><i class="fas fa-info-circle mr-2"></i><span id="previewText"></span></p>
        </div>
        <div>
            <label class="form-label">Academic Year</label>
            <input type="number" name="year" value="<?= date('Y') ?>" min="2020" max="2035" class="form-control w-32">
        </div>
        <div><label class="form-label">Note (optional)</label><input type="text" name="note" placeholder="e.g. End of year promotion 2025" class="form-control"></div>
        <div id="promoteMsg" class="text-sm hidden"></div>
        <button type="submit" class="btn-gold px-8" id="promoteBtn"><i class="fas fa-level-up-alt mr-2"></i>Promote Class</button>
    </form>

    <!-- Promotion History -->
    <div class="mt-8 border-t dark:border-gray-600 pt-5">
        <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Promotions</h4>
        <?php
        $promo_hist = $pdo->query("SELECT pl.*, s.full_name, s.student_number, u.full_name AS by_name FROM promotion_log pl JOIN students s ON s.id=pl.student_id JOIN users u ON u.id=pl.promoted_by ORDER BY pl.promoted_at DESC LIMIT 20")->fetchAll();
        ?>
        <?php if (empty($promo_hist)): ?>
        <p class="text-gray-400 text-sm">No promotions recorded yet.</p>
        <?php else: ?>
        <div class="overflow-x-auto"><table class="admin-table text-sm">
            <thead><tr><th>Student</th><th>From</th><th>To</th><th>Year</th><th>By</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($promo_hist as $ph): ?>
            <tr>
                <td><?= htmlspecialchars($ph['full_name']) ?> <span class="text-xs text-gray-400">#<?= $ph['student_number'] ?></span></td>
                <td><span class="badge badge-red text-xs"><?= htmlspecialchars($ph['from_class']) ?></span></td>
                <td><span class="badge badge-green text-xs"><?= htmlspecialchars($ph['to_class']) ?></span></td>
                <td class="text-gray-500"><?= $ph['academic_year'] ?></td>
                <td class="text-gray-500"><?= htmlspecialchars($ph['by_name']) ?></td>
                <td class="text-xs text-gray-400"><?= date('M j, Y', strtotime($ph['promoted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

</main>
</div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal-overlay hidden">
    <div class="modal-box max-w-xl">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Enroll New Student</h3>
            <button onclick="closeModal('addStudentModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="addStudentForm" class="space-y-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Full Name</label><input type="text" name="full_name" required class="form-control"></div>
                <div><label class="form-label">Student Number</label><input type="text" name="student_number" required class="form-control" placeholder="e.g. 2025001"></div>
                <div>
                    <label class="form-label">Class</label>
                    <select name="class_id" required class="form-control">
                        <option value="">— Select —</option>
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Email (optional)</label><input type="email" name="email" class="form-control"></div>
                <div><label class="form-label">Password</label><input type="password" name="password" required minlength="6" class="form-control" placeholder="Min 6 chars"></div>
            </div>
            <div id="addStudentMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('addStudentModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Enroll Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal-overlay hidden">
    <div class="modal-box max-w-lg">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Edit Student</h3>
            <button onclick="closeModal('editStudentModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="editStudentForm" class="space-y-4 mt-4">
            <input type="hidden" name="student_id" id="editStudentId">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Full Name</label><input type="text" name="full_name" id="editStudentName" required class="form-control"></div>
                <div><label class="form-label">Student Number</label><input type="text" name="student_number" id="editStudentNumber" required class="form-control"></div>
                <div>
                    <label class="form-label">Class</label>
                    <select name="class_id" id="editStudentClass" class="form-control">
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Email</label><input type="email" name="email" id="editStudentEmail" class="form-control"></div>
                <div class="col-span-2"><label class="form-label">New Password <span class="text-gray-400 font-normal">(leave blank)</span></label><input type="password" name="password" class="form-control"></div>
            </div>
            <div id="editStudentMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('editStudentModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
// Attendance
document.getElementById('attForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('attMsg');
    const fd  = new FormData(e.target);
    const r   = await fetch('/api/school_attendance.php', {method:'POST',body:fd});
    const d   = await r.json();
    msg.textContent = d.message;
    msg.className   = 'text-sm self-center ' + (d.success?'text-green-600':'text-red-500');
    if (d.success) setTimeout(()=>location.reload(), 1500);
});
function setAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(r=>r.checked=true);
}

// Promote form
const fromSelect = document.getElementById('fromClassSelect');
const promo_map  = {
    'Level 3 NIT':'Level 4 NIT','Level 4 NIT':'Level 5 NIT',
    'Level 3 TOU':'Level 4 TOU','Level 4 TOU':'Level 5 TOU',
    'Level 3 BDC':'Level 4 BDC','Level 4 BDC':'Level 5 BDC',
    'Senior 4 ACC':'Senior 5 ACC','Senior 5 ACC':'Senior 6 ACC'
};
fromSelect?.addEventListener('change', function() {
    const opt     = this.options[this.selectedIndex];
    const name    = opt.dataset.name;
    const count   = opt.dataset.count;
    const preview = document.getElementById('promotePreview');
    const text    = document.getElementById('previewText');
    if (name && promo_map[name]) {
        text.textContent = `${count} students will be moved from "${name}" → "${promo_map[name]}"`;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});
document.getElementById('promoteForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('promoteBtn');
    const msg = document.getElementById('promoteMsg');
    if (!confirm('Promote this entire class? This cannot be undone.')) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Promoting…';
    const fd = new FormData(e.target);
    const r  = await fetch('/api/promote_class.php', {method:'POST',body:fd});
    const d  = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-level-up-alt mr-2"></i>Promote Class';
    if (d.success) setTimeout(()=>location.reload(), 2000);
});

// Student forms
function openEditStudent(s) {
    document.getElementById('editStudentId').value     = s.id;
    document.getElementById('editStudentName').value   = s.full_name;
    document.getElementById('editStudentNumber').value = s.student_number;
    document.getElementById('editStudentClass').value  = s.class_id || '';
    document.getElementById('editStudentEmail').value  = s.email || '';
    openModal('editStudentModal');
}
document.getElementById('addStudentForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('addStudentMsg');
    const fd  = new FormData(e.target); fd.append('action','create');
    const r   = await fetch('/api/student_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
});
document.getElementById('editStudentForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('editStudentMsg');
    const fd  = new FormData(e.target); fd.append('action','update');
    const r   = await fetch('/api/student_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
});
</script>
</body>
</html>
