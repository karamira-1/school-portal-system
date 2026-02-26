<?php
// ============================================================
// portal-master.php  –  Section: Report Template Designer
// This section is ADDED to portal-master.php
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role(['master','admin'], '/login.php');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';

// Load current template
$tpl = $pdo->query('SELECT * FROM report_template WHERE id=1')->fetch();

// Handle save
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_template'])) {
    $fields = [
        'school_name','school_motto','school_address','school_phone',
        'school_email','school_po_box','principal_name','principal_title',
        'dos_name','dod_name','class_teacher_label','footer_note',
        'stamp_note','academic_year',
    ];
    // Handle logo upload
    $logo_path = $tpl['school_logo_path'] ?? null;
    if (!empty($_FILES['school_logo']['name'])) {
        $ext      = strtolower(pathinfo($_FILES['school_logo']['name'], PATHINFO_EXTENSION));
        $allowed  = ['png','jpg','jpeg','gif','svg'];
        if (in_array($ext,$allowed) && $_FILES['school_logo']['size'] < 2*1024*1024) {
            $dir   = __DIR__.'/uploads/logos/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'school_logo.' . $ext;
            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $dir.$fname)) {
                $logo_path = '/uploads/logos/'.$fname;
            }
        }
    }
    $set = implode(',', array_map(fn($f)=>"$f=?", $fields));
    $vals= array_map(fn($f)=>trim($_POST[$f]??''), $fields);
    $vals[] = $logo_path;
    $vals[] = $current_user['id'];
    $vals[] = 1;
    $pdo->prepare("UPDATE report_template SET $set, school_logo_path=?, last_updated_by=? WHERE id=?")
        ->execute($vals);
    $tpl = $pdo->query('SELECT * FROM report_template WHERE id=1')->fetch();
    $saved_ok = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ASPEJ – Report Template Designer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 min-h-screen text-gray-800 dark:text-gray-100">
<div class="flex h-screen overflow-hidden">

<!-- Sidebar -->
<aside class="w-60 bg-aspej-navy flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-aspej-gold text-xs">Master / Mistress</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="?section=dashboard"      class="admin-nav-link <?= $section==='dashboard'?'active':''?>"><i class="fas fa-tachometer-alt w-5 mr-3"></i>Dashboard</a>
        <a href="?section=staff"          class="admin-nav-link <?= $section==='staff'?'active':''?>"><i class="fas fa-users-cog w-5 mr-3"></i>Manage Staff</a>
        <a href="?section=add"            class="admin-nav-link <?= $section==='add'?'active':''?>"><i class="fas fa-user-plus w-5 mr-3"></i>Add Staff</a>
        <a href="?section=report_template"class="admin-nav-link <?= $section==='report_template'?'active':''?>"><i class="fas fa-file-signature w-5 mr-3"></i>Report Template</a>
        <a href="?section=registrations"  class="admin-nav-link <?= $section==='registrations'?'active':''?>"><i class="fas fa-user-clock w-5 mr-3"></i>Student Requests</a>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-gray-400 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['report_template'=>'Report Card Template Designer','registrations'=>'Student Account Requests','dashboard'=>'Dashboard','staff'=>'Staff Management','add'=>'Add Staff'][$section]??$section ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'report_template'): ?>

<?php if (!empty($saved_ok)): ?>
<div class="admin-card bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 mb-5 flex items-center gap-3 text-green-700 dark:text-green-300">
    <i class="fas fa-check-circle text-xl"></i>
    <span>Report template saved successfully. All new reports will use this layout.</span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
<!-- Form -->
<div class="admin-card">
    <h3 class="section-heading">School Information & Signatories</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">This information appears on every report card printed or downloaded.</p>

    <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="save_template" value="1">

        <!-- School logo -->
        <div class="flex items-center gap-5">
            <div id="logoPreview" class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700">
                <?php if ($tpl['school_logo_path']): ?>
                <img src="<?= htmlspecialchars($tpl['school_logo_path']) ?>" class="w-full h-full object-contain">
                <?php else: ?><i class="fas fa-image text-2xl text-gray-400"></i><?php endif; ?>
            </div>
            <div>
                <label class="form-label">School Logo</label>
                <input type="file" name="school_logo" id="logoFile" accept="image/*" class="text-sm text-gray-600 dark:text-gray-400 file:btn-primary file:text-xs file:mr-2">
                <p class="text-xs text-gray-400 mt-1">PNG, JPG or SVG. Max 2MB.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2"><label class="form-label">School Name <span class="text-red-500">*</span></label>
                <input type="text" name="school_name" value="<?= htmlspecialchars($tpl['school_name']??'') ?>" required class="form-control"></div>
            <div class="col-span-2"><label class="form-label">School Motto</label>
                <input type="text" name="school_motto" value="<?= htmlspecialchars($tpl['school_motto']??'') ?>" class="form-control" placeholder="e.g. Excellence, Discipline, Innovation"></div>
            <div><label class="form-label">Address</label>
                <input type="text" name="school_address" value="<?= htmlspecialchars($tpl['school_address']??'') ?>" class="form-control" placeholder="Kigali, Rwanda"></div>
            <div><label class="form-label">P.O. Box</label>
                <input type="text" name="school_po_box" value="<?= htmlspecialchars($tpl['school_po_box']??'') ?>" class="form-control" placeholder="P.O. Box 123"></div>
            <div><label class="form-label">Phone</label>
                <input type="text" name="school_phone" value="<?= htmlspecialchars($tpl['school_phone']??'') ?>" class="form-control"></div>
            <div><label class="form-label">Email</label>
                <input type="email" name="school_email" value="<?= htmlspecialchars($tpl['school_email']??'') ?>" class="form-control"></div>
            <div><label class="form-label">Academic Year</label>
                <input type="text" name="academic_year" value="<?= htmlspecialchars($tpl['academic_year']??'') ?>" class="form-control" placeholder="2024 – 2025"></div>
        </div>

        <div class="border-t dark:border-gray-600 pt-5">
            <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                <i class="fas fa-signature text-aspej-gold"></i> Signature Section
            </h4>
            <p class="text-xs text-gray-400 mb-4">These names appear in the signature blocks at the bottom of every report.</p>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Principal Name</label>
                    <input type="text" name="principal_name" value="<?= htmlspecialchars($tpl['principal_name']??'') ?>" class="form-control" placeholder="Full name"></div>
                <div><label class="form-label">Principal Title</label>
                    <input type="text" name="principal_title" value="<?= htmlspecialchars($tpl['principal_title']??'Principal') ?>" class="form-control"></div>
                <div><label class="form-label">Director of Studies Name</label>
                    <input type="text" name="dos_name" value="<?= htmlspecialchars($tpl['dos_name']??'') ?>" class="form-control" placeholder="Full name"></div>
                <div><label class="form-label">Class Teacher Label</label>
                    <input type="text" name="class_teacher_label" value="<?= htmlspecialchars($tpl['class_teacher_label']??'Class Teacher') ?>" class="form-control"></div>
                <div><label class="form-label">Dir. of Discipline Name</label>
                    <input type="text" name="dod_name" value="<?= htmlspecialchars($tpl['dod_name']??'') ?>" class="form-control" placeholder="Full name"></div>
                <div><label class="form-label">Stamp Label</label>
                    <input type="text" name="stamp_note" value="<?= htmlspecialchars($tpl['stamp_note']??'School Stamp') ?>" class="form-control"></div>
            </div>
        </div>

        <div><label class="form-label">Report Footer Note</label>
            <textarea name="footer_note" rows="2" class="form-control" placeholder="e.g. This report is official only with school stamp and signatures."><?= htmlspecialchars($tpl['footer_note']??'') ?></textarea></div>

        <div class="flex gap-3">
            <button type="submit" class="btn-gold px-8"><i class="fas fa-save mr-2"></i>Save Template</button>
            <button type="button" onclick="showPreview()" class="btn-primary px-6"><i class="fas fa-eye mr-2"></i>Preview Report</button>
        </div>
    </form>
</div>

<!-- Live Preview -->
<div>
    <h3 class="section-heading mb-3">Report Card Preview</h3>
    <div id="reportPreviewBox" class="bg-white border border-gray-200 rounded-xl shadow-inner p-6 text-gray-900" style="font-family:'DM Sans',sans-serif;min-height:400px">
        <?php include __DIR__ . '/includes/report_preview_sample.php'; ?>
    </div>
</div>
</div>

<?php elseif ($section === 'registrations'): ?>
<!-- Student account activation requests -->
<?php
$regs = $pdo->query("SELECT sr.*, s.class_id, c.name AS class_name FROM student_registrations sr JOIN students s ON LOWER(TRIM(s.full_name))=LOWER(TRIM(sr.full_name)) AND s.student_number=sr.student_number LEFT JOIN classes c ON c.id=s.class_id ORDER BY sr.created_at DESC")->fetchAll();
?>
<div class="admin-card">
    <h3 class="section-heading">Pending Activations</h3>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Reg. Number</th><th>Full Name</th><th>Username</th><th>Class</th><th>Submitted</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($regs as $r): ?>
        <tr>
            <td class="font-mono text-sm"><?= htmlspecialchars($r['student_number']) ?></td>
            <td class="font-medium"><?= htmlspecialchars($r['full_name']) ?></td>
            <td class="text-gray-400">@<?= htmlspecialchars($r['username']) ?></td>
            <td><span class="badge badge-blue text-xs"><?= htmlspecialchars($r['class_name']??'—') ?></span></td>
            <td class="text-xs text-gray-400"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
            <td>
                <span class="badge <?= $r['status']==='approved'?'badge-green':($r['status']==='rejected'?'badge-red':'badge-yellow') ?>">
                    <?= ucfirst($r['status']) ?>
                </span>
            </td>
            <td class="text-right space-x-2">
                <?php if ($r['status']==='pending'): ?>
                <button onclick="activateReg(<?= $r['id'] ?>, '<?= htmlspecialchars($r['student_number'],ENT_QUOTES) ?>', '<?= htmlspecialchars($r['username'],ENT_QUOTES) ?>')" class="btn-gold text-xs px-3 py-1"><i class="fas fa-check mr-1"></i>Activate</button>
                <button onclick="rejectReg(<?= $r['id'] ?>)" class="btn-danger text-xs px-3 py-1"><i class="fas fa-times mr-1"></i>Reject</button>
                <?php else: ?>
                <span class="text-gray-400 text-xs">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($regs)): ?><tr><td colspan="7" class="text-center text-gray-400 py-8">No registration requests yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

</main>
</div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
// Logo preview
document.getElementById('logoFile')?.addEventListener('change', function() {
    if (!this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logoPreview').innerHTML =
            `<img src="${e.target.result}" class="w-full h-full object-contain">`;
    };
    reader.readAsDataURL(this.files[0]);
});

async function activateReg(id, student_number, username) {
    if (!confirm(`Activate account for ${username}?`)) return;
    const fd = new FormData();
    fd.append('action','activate'); fd.append('id',id);
    fd.append('student_number',student_number); fd.append('username',username);
    const r = await fetch('/api/student_accounts.php',{method:'POST',body:fd});
    const d = await r.json();
    if (d.success) location.reload();
    else alert(d.message);
}
async function rejectReg(id) {
    const reason = prompt('Reason for rejection (optional):');
    if (reason === null) return;
    const fd = new FormData();
    fd.append('action','reject'); fd.append('id',id); fd.append('reason',reason);
    const r = await fetch('/api/student_accounts.php',{method:'POST',body:fd});
    const d = await r.json();
    if (d.success) location.reload();
    else alert(d.message);
}

document.getElementById('theme-toggle')?.addEventListener('click',()=>{
    document.documentElement.classList.toggle('dark');
    localStorage.theme = document.documentElement.classList.contains('dark')?'dark':'light';
});
</script>
</body>
</html>
