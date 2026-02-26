<?php
// ============================================================
// portal-dod.php  –  Director of Discipline Portal
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('director_discipline', '/login.php?role=director_discipline');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$classes = get_all_classes();

$view_class = (int)($_GET['class_id'] ?? 0);
$view_term  = (int)($_GET['term']     ?? 1);
$view_year  = (int)($_GET['year']     ?? date('Y'));

$students_conduct = [];
if ($view_class && $view_term && $view_year) {
    $sc = $pdo->prepare("
        SELECT s.id, s.full_name, s.student_number,
               COALESCE(cm.score, 40) AS score,
               cm.deductions, cm.id AS conduct_id
        FROM   students s
        LEFT JOIN conduct_marks cm ON cm.student_id=s.id AND cm.term=? AND cm.year=?
        WHERE  s.class_id=? AND s.is_active=1
        ORDER  BY s.full_name
    ");
    $sc->execute([$view_term, $view_year, $view_class]);
    $students_conduct = $sc->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – Director of Discipline</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen">
<div class="flex h-screen overflow-hidden">

<aside class="w-60 bg-red-900 flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-red-300 text-xs">Director of Discipline</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="?section=dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='dashboard'?'bg-white text-red-900 font-bold':'text-red-100 hover:bg-white/10' ?>"><i class="fas fa-tachometer-alt w-4"></i> Dashboard</a>
        <a href="?section=conduct"   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='conduct'?'bg-white text-red-900 font-bold':'text-red-100 hover:bg-white/10' ?>"><i class="fas fa-shield-alt w-4"></i> Conduct Marks</a>
        <a href="?section=incidents" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='incidents'?'bg-white text-red-900 font-bold':'text-red-100 hover:bg-white/10' ?>"><i class="fas fa-exclamation-circle w-4"></i> Incident Log</a>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-red-300 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['dashboard'=>'Dashboard','conduct'=>'Conduct Marks','incidents'=>'Incident Log'][$section] ?? '' ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'dashboard'): ?>
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <?php
    $perfect_conduct = $pdo->prepare('SELECT COUNT(*) FROM conduct_marks WHERE score=40 AND term=? AND year=?');
    $perfect_conduct->execute([$view_term ?: 1, $view_year ?: date('Y')]);
    $deducted = $pdo->prepare('SELECT COUNT(*) FROM conduct_marks WHERE score<40 AND term=? AND year=?');
    $deducted->execute([$view_term ?: 1, $view_year ?: date('Y')]);
    ?>
    <div class="admin-card text-center"><i class="fas fa-star text-3xl text-green-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $perfect_conduct->fetchColumn() ?></p><p class="text-sm text-gray-500">Perfect Conduct (40/40)</p></div>
    <div class="admin-card text-center"><i class="fas fa-minus-circle text-3xl text-red-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $deducted->fetchColumn() ?></p><p class="text-sm text-gray-500">Students with Deductions</p></div>
    <div class="admin-card text-center"><i class="fas fa-users text-3xl text-blue-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $pdo->query('SELECT COUNT(*) FROM students WHERE is_active=1')->fetchColumn() ?></p><p class="text-sm text-gray-500">Total Students</p></div>
</div>
<div class="admin-card">
    <h3 class="section-heading">Students with Low Conduct</h3>
    <?php
    $low = $pdo->query("SELECT s.full_name, s.student_number, c.name AS class_name, cm.score, cm.deductions FROM conduct_marks cm JOIN students s ON s.id=cm.student_id JOIN classes c ON c.id=s.class_id WHERE cm.score < 30 ORDER BY cm.score ASC LIMIT 20")->fetchAll();
    ?>
    <?php if (empty($low)): ?><p class="text-gray-400 text-sm">No students with conduct below 30/40.</p>
    <?php else: ?>
    <div class="overflow-x-auto"><table class="admin-table text-sm">
        <thead><tr><th>Student</th><th>Class</th><th>Conduct Score</th></tr></thead>
        <tbody>
        <?php foreach ($low as $l): ?>
        <tr>
            <td class="font-medium"><?= htmlspecialchars($l['full_name']) ?> <span class="text-xs text-gray-400">#<?= $l['student_number'] ?></span></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($l['class_name']) ?></span></td>
            <td><span class="font-bold text-red-600"><?= number_format($l['score'],1) ?> / 40</span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<?php elseif ($section === 'conduct'): ?>
<div class="admin-card mb-4">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="conduct">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" required class="form-control">
                <option value="">— Class —</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $view_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-control">
                <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>" <?= $view_term==$t?'selected':'' ?>>Term <?= $t ?></option><?php endfor; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><input type="number" name="year" value="<?= $view_year ?>" class="form-control w-24"></div>
        <button class="btn-primary">Load</button>
    </form>
</div>

<?php if (!empty($students_conduct)): ?>
<div class="admin-card">
    <h3 class="section-heading">Conduct Marks — Each student starts at 40/40</h3>
    <div class="space-y-3">
    <?php foreach ($students_conduct as $s):
        $deductions = json_decode($s['deductions'] ?? '[]', true) ?: [];
    ?>
    <div class="border border-gray-200 dark:border-gray-600 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <p class="font-semibold dark:text-white"><?= htmlspecialchars($s['full_name']) ?> <span class="text-xs text-gray-400">#<?= $s['student_number'] ?></span></p>
                <div class="flex items-center gap-2 mt-1">
                    <div class="w-32 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div class="<?= $s['score']>=30?'bg-green-500':($s['score']>=20?'bg-yellow-500':'bg-red-500') ?> h-2 rounded-full transition-all"
                             style="width:<?= ($s['score']/40)*100 ?>%"></div>
                    </div>
                    <span class="font-bold text-sm <?= $s['score']>=30?'text-green-600':($s['score']>=20?'text-yellow-600':'text-red-600') ?>"><?= number_format($s['score'],1) ?> / 40</span>
                </div>
            </div>
            <button onclick="openConductModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['full_name'],ENT_QUOTES) ?>', <?= number_format($s['score'],1) ?>)"
                    class="btn-danger text-xs px-4">
                <i class="fas fa-minus mr-1"></i> Deduct Points
            </button>
        </div>
        <?php if (!empty($deductions)): ?>
        <div class="mt-2 space-y-1">
            <?php foreach ($deductions as $d): ?>
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <span class="badge badge-red text-xs">-<?= $d['points'] ?></span>
                <span><?= htmlspecialchars($d['reason']) ?></span>
                <span class="text-gray-400"><?= $d['date'] ?? '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php elseif ($view_class): ?>
<div class="admin-card text-center text-gray-400 py-10"><p>No students found for this class.</p></div>
<?php else: ?>
<div class="admin-card text-center text-gray-400 py-10"><p>Select a class and term to manage conduct marks.</p></div>
<?php endif; ?>

<?php elseif ($section === 'incidents'): ?>
<!-- Full incident log -->
<div class="admin-card">
    <h3 class="section-heading">All Conduct Deductions</h3>
    <?php
    $all_conduct = $pdo->query("
        SELECT s.full_name, s.student_number, c.name AS class_name,
               cm.score, cm.deductions, cm.term, cm.year
        FROM conduct_marks cm
        JOIN students s ON s.id=cm.student_id
        JOIN classes  c ON c.id=s.class_id
        WHERE cm.deductions IS NOT NULL
        ORDER BY cm.year DESC, cm.term DESC, s.full_name
    ")->fetchAll();
    ?>
    <div class="overflow-x-auto">
    <table class="admin-table text-sm">
        <thead><tr><th>Student</th><th>Class</th><th>Term/Year</th><th>Score</th><th>Deductions</th></tr></thead>
        <tbody>
        <?php foreach ($all_conduct as $ac):
            $deds = json_decode($ac['deductions'] ?? '[]', true) ?: [];
            if (empty($deds)) continue;
        ?>
        <tr>
            <td class="font-medium"><?= htmlspecialchars($ac['full_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($ac['class_name']) ?></span></td>
            <td class="text-gray-500">T<?= $ac['term'] ?>/<?= $ac['year'] ?></td>
            <td class="font-bold <?= $ac['score']<30?'text-red-500':'text-orange-500' ?>"><?= $ac['score'] ?>/40</td>
            <td>
                <?php foreach ($deds as $d): ?>
                <span class="badge badge-red mr-1 mb-1">-<?= $d['points'] ?> <?= htmlspecialchars($d['reason']) ?></span>
                <?php endforeach; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

</main>
</div>
</div>

<!-- Conduct Deduction Modal -->
<div id="conductModal" class="modal-overlay hidden">
    <div class="modal-box max-w-md">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-red-700 dark:text-red-400">Deduct Conduct Points</h3>
            <button onclick="closeModal('conductModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <div class="mt-2 mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">Student: <strong id="conductStudentName"></strong></p>
            <p class="text-sm text-gray-600 dark:text-gray-300">Current score: <strong id="conductCurrentScore"></strong>/40</p>
        </div>
        <form id="conductForm" class="space-y-4">
            <input type="hidden" name="student_id"  id="conductStudentId">
            <input type="hidden" name="term"         value="<?= $view_term  ?: 1 ?>">
            <input type="hidden" name="year"         value="<?= $view_year  ?: date('Y') ?>">
            <div>
                <label class="form-label">Points to Deduct (1–40)</label>
                <input type="number" name="points" required min="1" max="40" class="form-control" placeholder="e.g. 5">
            </div>
            <div>
                <label class="form-label">Reason for Deduction</label>
                <input type="text" name="reason" required class="form-control" placeholder="e.g. Late to school 3 times">
            </div>
            <div id="conductMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('conductModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-danger">Deduct Points</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
document.getElementById('theme-toggle')?.addEventListener('click',()=>{
    document.documentElement.classList.toggle('dark');
    localStorage.theme=document.documentElement.classList.contains('dark')?'dark':'light';
});
function openConductModal(id, name, score) {
    document.getElementById('conductStudentId').value     = id;
    document.getElementById('conductStudentName').textContent = name;
    document.getElementById('conductCurrentScore').textContent = score;
    openModal('conductModal');
}
document.getElementById('conductForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('conductMsg');
    const fd  = new FormData(e.target); fd.append('action','deduct');
    const r   = await fetch('/api/conduct_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
});
</script>
</body>
</html>
