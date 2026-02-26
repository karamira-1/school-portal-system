<?php
// ============================================================
// portal-master.php  –  Master / Mistress Portal
// Can create / edit / deactivate: teacher, accountant,
// director_studies, director_discipline, librarian accounts
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('master', '/login.php?role=master');
$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';

// Staff roles this portal manages
$staff_roles = ['teacher','librarian','director_studies','director_discipline','accountant'];

// ── Load staff list ───────────────────────────────────────
$role_filter = $_GET['role_filter'] ?? '';
$search      = trim($_GET['q'] ?? '');
$sql = "SELECT u.*, r.name AS role_name, r.label AS role_label
        FROM users u JOIN roles r ON r.id=u.role_id
        WHERE r.name IN ('teacher','librarian','director_studies','director_discipline','accountant')";
$params = [];
if ($role_filter && in_array($role_filter, $staff_roles)) {
    $sql .= ' AND r.name = ?'; $params[] = $role_filter;
}
if ($search) {
    $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= ' ORDER BY r.name, u.full_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$staff_list = $stmt->fetchAll();

// ── Stats ─────────────────────────────────────────────────
$stats = [];
foreach ($staff_roles as $r) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name=? AND u.is_active=1");
    $s->execute([$r]);
    $stats[$r] = (int)$s->fetchColumn();
}
$roles_list = $pdo->query("SELECT * FROM roles WHERE name IN ('teacher','librarian','director_studies','director_discipline','accountant')")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ – Master Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C'}}}};
    if(localStorage.theme==='dark')document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
<!-- Sidebar -->
<aside class="w-60 bg-aspej-navy flex flex-col flex-shrink-0">
    <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-bold">ASPEJ School</p>
        <p class="text-aspej-gold text-xs">Master / Mistress</p>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1">
        <a href="?section=dashboard" class="admin-nav-link <?= $section==='dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt w-5 mr-3"></i>Dashboard</a>
        <a href="?section=staff"     class="admin-nav-link <?= $section==='staff'?'active':'' ?>"><i class="fas fa-users-cog w-5 mr-3"></i>Manage Staff</a>
        <a href="?section=add"       class="admin-nav-link <?= $section==='add'?'active':'' ?>"><i class="fas fa-user-plus w-5 mr-3"></i>Add Staff</a>
    </nav>
    <div class="px-4 py-3 border-t border-white/10">
        <p class="text-white text-xs font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
        <a href="/api/logout.php" class="text-gray-400 hover:text-white text-xs flex items-center gap-1 mt-1"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
</aside>

<div class="flex-1 flex flex-col overflow-hidden">
<header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-aspej-navy dark:text-white">
        <?= ['dashboard'=>'Dashboard','staff'=>'Staff Management','add'=>'Add Staff Account'][$section] ?? 'Portal' ?>
    </h1>
    <button id="theme-toggle" class="text-gray-500 hover:text-aspej-navy dark:text-gray-400 dark:hover:text-white">
        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline-block"></i>
    </button>
</header>

<main class="flex-1 overflow-y-auto p-6">

<?php if ($section === 'dashboard'): ?>
<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <?php
    $role_icons = ['teacher'=>['fa-chalkboard-teacher','bg-blue-500'],'librarian'=>['fa-book','bg-green-500'],
                   'director_studies'=>['fa-graduation-cap','bg-indigo-500'],
                   'director_discipline'=>['fa-shield-alt','bg-red-500'],'accountant'=>['fa-calculator','bg-yellow-500']];
    foreach ($stats as $role => $count):
        [$icon,$color] = $role_icons[$role];
        $label = match($role){'director_studies'=>'Dir. Studies','director_discipline'=>'Dir. Discipline',default=>ucfirst($role)};
    ?>
    <div class="admin-card text-center">
        <div class="<?= $color ?> w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
            <i class="fas <?= $icon ?> text-white"></i>
        </div>
        <p class="text-2xl font-bold dark:text-white"><?= $count ?></p>
        <p class="text-xs text-gray-500"><?= $label ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <h3 class="section-heading">Recent Staff Accounts</h3>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Role</th><th>Username</th><th>Status</th><th>Last Login</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($staff_list, 0, 10) as $u): ?>
        <tr>
            <td class="font-medium"><?= htmlspecialchars($u['full_name']) ?></td>
            <td><span class="badge badge-blue text-xs"><?= htmlspecialchars($u['role_label']) ?></span></td>
            <td class="text-gray-400">@<?= htmlspecialchars($u['username']) ?></td>
            <td><span class="badge <?= $u['is_active']?'badge-green':'badge-red' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
            <td class="text-xs text-gray-400"><?= $u['last_login'] ? date('M j, Y', strtotime($u['last_login'])) : 'Never' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php elseif ($section === 'staff'): ?>
<!-- Staff Management -->
<div class="admin-card">
    <div class="flex flex-col sm:flex-row gap-3 mb-5 justify-between">
        <!-- Role filter tabs -->
        <div class="flex flex-wrap gap-2">
            <a href="?section=staff" class="badge <?= !$role_filter?'badge-navy':'badge-gray' ?>">All Staff</a>
            <?php foreach ($staff_roles as $r): ?>
            <?php $lbl = match($r){'director_studies'=>'DoS','director_discipline'=>'DoD','teacher'=>'Teacher','librarian'=>'Librarian','accountant'=>'Accountant'}; ?>
            <a href="?section=staff&role_filter=<?= $r ?>" class="badge <?= $role_filter===$r?'badge-navy':'badge-gray' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
        </div>
        <!-- Search -->
        <form class="flex gap-2" method="GET">
            <input type="hidden" name="section" value="staff">
            <?php if ($role_filter): ?><input type="hidden" name="role_filter" value="<?= htmlspecialchars($role_filter) ?>"><?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search…" class="form-control text-sm w-48">
            <button class="btn-primary text-sm px-3">Search</button>
        </form>
    </div>

    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Role</th><th>Username</th><th>Email</th><th>Phone</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($staff_list as $u): ?>
        <tr>
            <td class="flex items-center gap-2">
                <img src="<?= htmlspecialchars($u['profile_image'] ?? '/assets/images/default-avatar.png') ?>" class="w-8 h-8 rounded-full object-cover">
                <span class="font-medium"><?= htmlspecialchars($u['full_name']) ?></span>
            </td>
            <td><span class="badge badge-blue text-xs"><?= htmlspecialchars($u['role_label']) ?></span></td>
            <td class="text-gray-400 text-sm">@<?= htmlspecialchars($u['username']) ?></td>
            <td class="text-gray-500 text-sm"><?= htmlspecialchars($u['email']) ?></td>
            <td class="text-gray-500 text-sm"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
            <td><span class="badge <?= $u['is_active']?'badge-green':'badge-red' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
            <td class="text-right space-x-2">
                <button onclick="openEditStaff(<?= htmlspecialchars(json_encode($u)) ?>)" class="text-blue-500 text-sm hover:text-blue-700"><i class="fas fa-edit"></i></button>
                <button onclick="toggleStaff(<?= $u['id'] ?>,<?= $u['is_active'] ?>)" class="text-<?= $u['is_active']?'red':'green' ?>-500 text-sm"><i class="fas fa-<?= $u['is_active']?'ban':'check-circle' ?>"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($staff_list)): ?><tr><td colspan="7" class="text-center text-gray-400 py-8">No staff found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php elseif ($section === 'add'): ?>
<!-- Add Staff Form -->
<div class="max-w-2xl">
<div class="admin-card">
    <h3 class="section-heading">Create New Staff Account</h3>
    <form id="addStaffForm" class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div><label class="form-label">Full Name</label><input type="text" name="full_name" required class="form-control"></div>
            <div><label class="form-label">Username</label><input type="text" name="username" required class="form-control"></div>
            <div><label class="form-label">Email</label><input type="email" name="email" required class="form-control"></div>
            <div><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
            <div>
                <label class="form-label">Role</label>
                <select name="role_name" required class="form-control">
                    <option value="">— Select Role —</option>
                    <?php foreach ($roles_list as $r): ?>
                    <option value="<?= $r['name'] ?>"><?= $r['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label">Password</label><input type="password" name="password" required minlength="8" class="form-control" placeholder="Min 8 characters"></div>
        </div>
        <div id="addStaffMsg" class="text-sm hidden"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn-gold px-8"><i class="fas fa-user-plus mr-2"></i>Create Account</button>
            <a href="?section=staff" class="btn-gray">Cancel</a>
        </div>
    </form>
</div>
</div>
<?php endif; ?>

</main>
</div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="modal-overlay hidden">
    <div class="modal-box max-w-lg">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Edit Staff Account</h3>
            <button onclick="closeModal('editStaffModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="editStaffForm" class="space-y-4 mt-4">
            <input type="hidden" name="user_id" id="editStaffId">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Full Name</label><input type="text" name="full_name" id="editStaffName" required class="form-control"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" id="editStaffEmail" required class="form-control"></div>
                <div><label class="form-label">Phone</label><input type="tel" name="phone" id="editStaffPhone" class="form-control"></div>
                <div>
                    <label class="form-label">Role</label>
                    <select name="role_name" id="editStaffRole" class="form-control">
                        <?php foreach ($roles_list as $r): ?>
                        <option value="<?= $r['name'] ?>"><?= $r['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-2"><label class="form-label">New Password <span class="text-gray-400 font-normal">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="form-control"></div>
            </div>
            <div id="editStaffMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('editStaffModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/admin.js"></script>
<script>
function openEditStaff(u) {
    document.getElementById('editStaffId').value    = u.id;
    document.getElementById('editStaffName').value  = u.full_name;
    document.getElementById('editStaffEmail').value = u.email;
    document.getElementById('editStaffPhone').value = u.phone || '';
    document.getElementById('editStaffRole').value  = u.role_name;
    openModal('editStaffModal');
}
async function toggleStaff(id, active) {
    if (!confirm((active?'Deactivate':'Activate')+' this account?')) return;
    const fd = new FormData();
    fd.append('action','toggle'); fd.append('user_id',id); fd.append('is_active',active);
    const r = await fetch('/api/user_manager.php',{method:'POST',body:fd});
    const d = await r.json();
    if (d.success) location.reload(); else alert(d.message);
}
document.getElementById('addStaffForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('addStaffMsg');
    const fd  = new FormData(e.target); fd.append('action','create');
    const r   = await fetch('/api/user_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) e.target.reset();
});
document.getElementById('editStaffForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('editStaffMsg');
    const fd  = new FormData(e.target); fd.append('action','update');
    const r   = await fetch('/api/user_manager.php',{method:'POST',body:fd});
    const d   = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1000);
});
</script>
</body>
</html>
