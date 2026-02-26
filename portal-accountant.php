<?php
// ============================================================
// portal-accountant.php  –  Accountant Portal
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('accountant', '/login.php?role=accountant');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$classes = get_all_classes();

$filter_class  = (int)($_GET['class_id'] ?? 0);
$filter_term   = (int)($_GET['term']     ?? 1);
$filter_year   = (int)($_GET['year']     ?? date('Y'));
$filter_status = $_GET['status']         ?? '';

// Load fee records
$fees_sql = "
    SELECT sf.*, s.full_name, s.student_number, c.name AS class_name
    FROM student_fees sf
    JOIN students s ON s.id=sf.student_id
    LEFT JOIN classes c ON c.id=s.class_id
    WHERE sf.term=? AND sf.year=?
";
$fee_params = [$filter_term, $filter_year];
if ($filter_class) { $fees_sql .= ' AND s.class_id=?'; $fee_params[] = $filter_class; }
if ($filter_status) { $fees_sql .= ' AND sf.status=?'; $fee_params[] = $filter_status; }
$fees_sql .= ' ORDER BY sf.status, s.full_name';
$fee_stmt = $pdo->prepare($fees_sql);
$fee_stmt->execute($fee_params);
$fees_list = $fee_stmt->fetchAll();

// Summary totals
$summary = $pdo->prepare("
    SELECT
        COUNT(*) AS total_records,
        SUM(CASE WHEN status='paid'    THEN 1 ELSE 0 END) AS paid_count,
        SUM(CASE WHEN status='partial' THEN 1 ELSE 0 END) AS partial_count,
        SUM(CASE WHEN status='unpaid'  THEN 1 ELSE 0 END) AS unpaid_count,
        SUM(amount_paid) AS total_collected,
        SUM(amount_due - amount_paid) AS total_remaining
    FROM student_fees WHERE term=? AND year=?
");
$summary->execute([$filter_term, $filter_year]);
$summary = $summary->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – Accountant Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen">
<div class="flex h-screen overflow-hidden">

<aside class="w-60 bg-yellow-900 flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-yellow-300 text-xs">Accountant Portal</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="?section=dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='dashboard'?'bg-white text-yellow-900 font-bold':'text-yellow-100 hover:bg-white/10' ?>"><i class="fas fa-tachometer-alt w-4"></i> Dashboard</a>
        <a href="?section=fees"      class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='fees'?'bg-white text-yellow-900 font-bold':'text-yellow-100 hover:bg-white/10' ?>"><i class="fas fa-money-bill-wave w-4"></i> Fee Management</a>
        <a href="?section=generate"  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition <?= $section==='generate'?'bg-white text-yellow-900 font-bold':'text-yellow-100 hover:bg-white/10' ?>"><i class="fas fa-magic w-4"></i> Generate Fee Records</a>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-yellow-300 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['dashboard'=>'Fee Dashboard','fees'=>'Fee Management','generate'=>'Generate Fee Records'][$section] ?? '' ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'dashboard'): ?>
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <div class="admin-card text-center"><i class="fas fa-check-circle text-3xl text-green-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $summary['paid_count'] ?></p><p class="text-sm text-gray-500">Fully Paid</p></div>
    <div class="admin-card text-center"><i class="fas fa-adjust text-3xl text-yellow-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $summary['partial_count'] ?></p><p class="text-sm text-gray-500">Partial Payment</p></div>
    <div class="admin-card text-center"><i class="fas fa-times-circle text-3xl text-red-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= $summary['unpaid_count'] ?></p><p class="text-sm text-gray-500">Unpaid</p></div>
    <div class="admin-card text-center col-span-2 md:col-span-1"><i class="fas fa-coins text-3xl text-aspej-gold mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= number_format($summary['total_collected'] ?? 0) ?> RWF</p><p class="text-sm text-gray-500">Total Collected (Term <?= $filter_term ?>)</p></div>
    <div class="admin-card text-center col-span-2 md:col-span-2"><i class="fas fa-exclamation-triangle text-3xl text-orange-500 mb-2"></i><p class="text-2xl font-bold dark:text-white"><?= number_format($summary['total_remaining'] ?? 0) ?> RWF</p><p class="text-sm text-gray-500">Outstanding Balance</p></div>
</div>
<div class="flex gap-3">
    <a href="?section=fees&term=<?= $filter_term ?>&year=<?= $filter_year ?>&status=unpaid" class="btn-danger text-sm"><i class="fas fa-eye mr-1"></i>View Unpaid</a>
    <a href="?section=fees&term=<?= $filter_term ?>&year=<?= $filter_year ?>" class="btn-primary text-sm"><i class="fas fa-list mr-1"></i>All Records</a>
</div>

<?php elseif ($section === 'fees'): ?>
<!-- Filter bar -->
<div class="admin-card mb-4">
    <form class="flex flex-wrap gap-3 items-end" method="GET">
        <input type="hidden" name="section" value="fees">
        <div>
            <label class="form-label">Class</label>
            <select name="class_id" class="form-control">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $filter_class==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Term</label>
            <select name="term" class="form-control">
                <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>" <?= $filter_term==$t?'selected':'' ?>>Term <?= $t ?></option><?php endfor; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><input type="number" name="year" value="<?= $filter_year ?>" class="form-control w-24"></div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="unpaid"  <?= $filter_status==='unpaid'?'selected':'' ?>>Unpaid</option>
                <option value="partial" <?= $filter_status==='partial'?'selected':'' ?>>Partial</option>
                <option value="paid"    <?= $filter_status==='paid'?'selected':'' ?>>Paid</option>
            </select>
        </div>
        <button class="btn-primary">Filter</button>
    </form>
</div>

<div class="admin-card">
    <div class="flex items-center justify-between mb-4">
        <p class="text-gray-500 text-sm"><?= count($fees_list) ?> records</p>
        <a href="/api/export_fees_csv.php?term=<?= $filter_term ?>&year=<?= $filter_year ?>&class_id=<?= $filter_class ?>" class="btn-primary text-sm"><i class="fas fa-file-csv mr-1"></i>Export CSV</a>
    </div>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Student</th><th>Class</th><th>Amount Due</th><th>Amount Paid</th><th>Remaining</th><th>Status</th><th>Payment Date</th><th class="text-right">Action</th></tr></thead>
        <tbody>
        <?php foreach ($fees_list as $f): ?>
        <tr>
            <td class="font-medium text-sm"><?= htmlspecialchars($f['full_name']) ?> <span class="text-xs text-gray-400">#<?= $f['student_number'] ?></span></td>
            <td><span class="badge badge-blue text-xs"><?= htmlspecialchars($f['class_name'] ?? '—') ?></span></td>
            <td class="text-sm"><?= number_format($f['amount_due'],0) ?> RWF</td>
            <td class="text-sm font-semibold text-green-600"><?= number_format($f['amount_paid'],0) ?> RWF</td>
            <td class="text-sm font-semibold <?= ($f['amount_due']-$f['amount_paid'])>0?'text-red-500':'text-green-600' ?>"><?= number_format($f['amount_due']-$f['amount_paid'],0) ?> RWF</td>
            <td><span class="badge <?= $f['status']==='paid'?'badge-green':($f['status']==='partial'?'badge-yellow':'badge-red') ?>"><?= ucfirst($f['status']) ?></span></td>
            <td class="text-xs text-gray-400"><?= $f['payment_date'] ? date('M j, Y', strtotime($f['payment_date'])) : '—' ?></td>
            <td class="text-right">
                <?php if ($f['status'] !== 'paid'): ?>
                <button onclick="openPayModal(<?= htmlspecialchars(json_encode($f)) ?>)" class="btn-gold text-xs px-3 py-1">
                    <i class="fas fa-plus mr-1"></i>Record Payment
                </button>
                <?php else: ?>
                <span class="text-green-500 text-xs"><i class="fas fa-check-circle mr-1"></i>Settled</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($fees_list)): ?><tr><td colspan="8" class="text-center text-gray-400 py-8">No records found. Try "Generate Fee Records" first.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php elseif ($section === 'generate'): ?>
<!-- Generate fee records for all students -->
<div class="admin-card max-w-lg">
    <h3 class="section-heading">Generate Fee Records</h3>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">Creates a fee record for every active student for the selected term. Students already having a record are skipped.</p>
    <form id="generateFeesForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="form-label">Term</label>
                <select name="term" class="form-control">
                    <?php for ($t=1;$t<=3;$t++): ?><option value="<?= $t ?>"><?= $t ?></option><?php endfor; ?>
                </select>
            </div>
            <div><label class="form-label">Year</label><input type="number" name="year" value="<?= date('Y') ?>" class="form-control"></div>
            <div class="col-span-2"><label class="form-label">Amount Due (RWF)</label><input type="number" name="amount_due" value="50000" required class="form-control"></div>
        </div>
        <div id="generateMsg" class="text-sm hidden"></div>
        <button type="submit" class="btn-gold w-full" id="generateBtn"><i class="fas fa-magic mr-2"></i>Generate Records for All Students</button>
    </form>
</div>
<?php endif; ?>

</main>
</div>
</div>

<!-- Payment Modal -->
<div id="payModal" class="modal-overlay hidden">
    <div class="modal-box max-w-md">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Record Payment</h3>
            <button onclick="closeModal('payModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
            <p>Student: <strong id="payStudentName" class="dark:text-white"></strong></p>
            <p>Amount Due: <strong id="payAmountDue" class="text-red-500"></strong></p>
            <p>Remaining: <strong id="payRemaining" class="text-orange-500"></strong></p>
        </div>
        <form id="payForm" class="space-y-4">
            <input type="hidden" name="fee_id"     id="payFeeId">
            <input type="hidden" name="student_id" id="payStudentId">
            <div><label class="form-label">Amount Paid (RWF)</label><input type="number" name="amount_paid" id="payAmountInput" required min="0" class="form-control"></div>
            <div>
                <label class="form-label">Payment Method</label>
                <select name="method" class="form-control">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                </select>
            </div>
            <div><label class="form-label">Reference (optional)</label><input type="text" name="reference" class="form-control" placeholder="Transaction ref…"></div>
            <div><label class="form-label">Notes</label><textarea name="notes" rows="2" class="form-control" placeholder="Optional notes…"></textarea></div>
            <div id="payMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('payModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Payment</button>
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

function openPayModal(f) {
    document.getElementById('payFeeId').value       = f.id;
    document.getElementById('payStudentId').value   = f.student_id;
    document.getElementById('payStudentName').textContent = f.full_name + ' #' + f.student_number;
    document.getElementById('payAmountDue').textContent   = Number(f.amount_due).toLocaleString() + ' RWF';
    const remaining = f.amount_due - f.amount_paid;
    document.getElementById('payRemaining').textContent   = Number(remaining).toLocaleString() + ' RWF';
    document.getElementById('payAmountInput').value = remaining;
    document.getElementById('payAmountInput').max   = remaining;
    openModal('payModal');
}

document.getElementById('payForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('payMsg');
    const fd  = new FormData(e.target); fd.append('action','record_payment');
    const r   = await fetch('/api/accountant_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
});

document.getElementById('generateFeesForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('generateBtn');
    const msg = document.getElementById('generateMsg');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i>Generating…';
    const fd = new FormData(e.target); fd.append('action','generate');
    const r  = await fetch('/api/accountant_manager.php',{method:'POST',body:fd});
    const d  = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic mr-2"></i>Generate Records for All Students';
    if (d.success) setTimeout(()=>window.location='?section=fees',1500);
});
</script>
</body>
</html>
