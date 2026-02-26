<?php
// ============================================================
// portal-admin.php  –  Admin Dashboard
// Sections: dashboard | users | students | analytics |
//           news | attendance | fees | chat | alerts
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role('admin', '/login.php?role=admin');

$pdo     = get_db();
$section = $_GET['section'] ?? 'dashboard';
$alert_count = unread_notifications($current_user['id']);
$msg_count   = unread_messages($current_user['id']);

// ── Section title map ─────────────────────────────────────
$titles = [
    'dashboard'  => 'Dashboard Overview',
    'users'      => 'User Management',
    'students'   => 'Student Records',
    'analytics'  => 'School Analytics',
    'news'       => 'News & Events CMS',
    'attendance' => 'Attendance Overview',
    'fees'       => 'Fee Payments',
    'chat'       => 'Messages & Broadcasts',
    'alerts'     => 'Performance Alerts',
];
$admin_section = $section;
$admin_title   = $titles[$section] ?? 'Admin';

// ============================================================
// SECTION DATA LOADERS
// ============================================================

// ── Dashboard KPIs ────────────────────────────────────────
if ($section === 'dashboard' || $section === 'analytics') {
    $kpi = [];
    $kpi['total_students']  = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    $kpi['total_teachers']  = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id=(SELECT id FROM roles WHERE name='teacher')")->fetchColumn();
    $kpi['total_parents']   = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id=(SELECT id FROM roles WHERE name='parent')")->fetchColumn();
    $kpi['avg_gpa']         = $pdo->query('SELECT ROUND(AVG(gpa),2) FROM students')->fetchColumn();
    $kpi['avg_attendance']  = $pdo->query('SELECT ROUND(AVG(attendance_pct),1) FROM students')->fetchColumn();
    $kpi['pending_fees']    = $pdo->query("SELECT COUNT(*) FROM fee_payments WHERE status='pending'")->fetchColumn();
    $kpi['open_apps']       = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='pending'")->fetchColumn();
    $kpi['unresolved_alerts']= $pdo->query("SELECT COUNT(*) FROM performance_alerts WHERE is_resolved=0")->fetchColumn();

    // GPA distribution
    $gpa_dist = $pdo->query("
        SELECT
            SUM(gpa >= 3.5) AS a_range,
            SUM(gpa >= 3.0 AND gpa < 3.5) AS b_range,
            SUM(gpa >= 2.0 AND gpa < 3.0) AS c_range,
            SUM(gpa < 2.0) AS d_range
        FROM students
    ")->fetch();

    // Monthly enrollment (last 6 months)
    $monthly_enroll = $pdo->query("
        SELECT DATE_FORMAT(created_at,'%b') AS month, COUNT(*) AS cnt
        FROM students
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at,'%Y-%m')
        ORDER BY created_at
    ")->fetchAll();
}

// ── User Management ───────────────────────────────────────
if ($section === 'users') {
    $role_filter = $_GET['role_filter'] ?? '';
    $search      = trim($_GET['q'] ?? '');
    $sql = 'SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE 1=1';
    $params = [];
    if ($role_filter) { $sql .= ' AND r.name = ?'; $params[] = $role_filter; }
    if ($search)      { $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
                        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= ' ORDER BY u.created_at DESC LIMIT 100';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users_list = $stmt->fetchAll();
    $roles_list = $pdo->query('SELECT * FROM roles')->fetchAll();
}

// ── Students ──────────────────────────────────────────────
if ($section === 'students') {
    $search = trim($_GET['q'] ?? '');
    $sql = 'SELECT * FROM students WHERE 1=1';
    $params = [];
    if ($search) { $sql .= ' AND (full_name LIKE ? OR student_number LIKE ? OR class LIKE ?)';
                   $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $sql .= ' ORDER BY class, full_name LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students_list = $stmt->fetchAll();
}

// ── News CMS ──────────────────────────────────────────────
if ($section === 'news') {
    $news_list = $pdo->query('SELECT * FROM news_events ORDER BY published_at DESC')->fetchAll();
}

// ── Attendance Overview ───────────────────────────────────
if ($section === 'attendance') {
    $att_date = $_GET['date'] ?? date('Y-m-d');
    $att_class = $_GET['class'] ?? '';
    $sql = '
        SELECT da.*, s.full_name, s.class, s.student_number,
               u.full_name AS marked_by_name
        FROM   daily_attendance da
        JOIN   students s ON s.id = da.student_id
        JOIN   users    u ON u.id = da.marked_by
        WHERE  da.date = ?
    ';
    $params = [$att_date];
    if ($att_class) { $sql .= ' AND s.class = ?'; $params[] = $att_class; }
    $stmt = $pdo->prepare($sql . ' ORDER BY s.class, s.full_name');
    $stmt->execute($params);
    $att_records = $stmt->fetchAll();
    $classes = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class")->fetchAll(PDO::FETCH_COLUMN);

    // Summary counts
    $att_summary = ['present'=>0,'absent'=>0,'tardy'=>0,'total'=>count($att_records)];
    foreach ($att_records as $r) $att_summary[$r['status']]++;
}

// ── Fee Payments ──────────────────────────────────────────
if ($section === 'fees') {
    $fee_status = $_GET['status'] ?? '';
    $sql = '
        SELECT fp.*, a.first_name, a.last_name, a.level,
               u.full_name AS marked_by_name
        FROM   fee_payments fp
        LEFT JOIN applications a ON a.id = fp.application_id
        LEFT JOIN users        u ON u.id = fp.marked_by
        WHERE  1=1
    ';
    $params = [];
    if ($fee_status) { $sql .= ' AND fp.status = ?'; $params[] = $fee_status; }
    $sql .= ' ORDER BY fp.created_at DESC LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $fees_list = $stmt->fetchAll();

    $fee_totals = $pdo->query("
        SELECT status, COUNT(*) AS cnt, SUM(amount) AS total
        FROM fee_payments GROUP BY status
    ")->fetchAll();
}

// ── Chat ──────────────────────────────────────────────────
if ($section === 'chat') {
    $chat_with = (int)($_GET['with'] ?? 0);
    $contacts  = $pdo->query("
        SELECT DISTINCT u.id, u.full_name, u.profile_image, r.name AS role_name,
               (SELECT COUNT(*) FROM chat_messages WHERE sender_id=u.id AND receiver_id={$current_user['id']} AND is_read=0) AS unread
        FROM   users u JOIN roles r ON r.id=u.role_id
        WHERE  u.id != {$current_user['id']} AND u.is_active=1
        ORDER  BY r.name, u.full_name
    ")->fetchAll();

    $messages = [];
    if ($chat_with) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.full_name AS sender_name, u.profile_image AS sender_img
            FROM   chat_messages m JOIN users u ON u.id=m.sender_id
            WHERE  (m.sender_id=? AND m.receiver_id=?)
                OR (m.sender_id=? AND m.receiver_id=?)
            ORDER  BY m.created_at ASC LIMIT 100
        ");
        $stmt->execute([$current_user['id'],$chat_with,$chat_with,$current_user['id']]);
        $messages = $stmt->fetchAll();
        // Mark read
        $pdo->prepare("UPDATE chat_messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
            ->execute([$chat_with,$current_user['id']]);
    }
}

// ── Alerts ────────────────────────────────────────────────
if ($section === 'alerts') {
    $alerts_list = $pdo->query("
        SELECT pa.*, s.full_name, s.class, s.gpa, s.attendance_pct
        FROM   performance_alerts pa
        JOIN   students s ON s.id=pa.student_id
        ORDER  BY pa.is_resolved ASC, pa.created_at DESC
        LIMIT  100
    ")->fetchAll();
    // Mark notifications read
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$current_user['id']]);
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- ============================================================
     SECTION: DASHBOARD
============================================================ -->
<?php if ($section === 'dashboard'): ?>

<!-- KPI Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
    <?php
    $kpi_cards = [
        ['label'=>'Total Students',   'value'=>$kpi['total_students'],  'icon'=>'fa-user-graduate',     'color'=>'bg-blue-500'],
        ['label'=>'Total Teachers',   'value'=>$kpi['total_teachers'],  'icon'=>'fa-chalkboard-teacher','color'=>'bg-indigo-500'],
        ['label'=>'Average GPA',      'value'=>$kpi['avg_gpa'],         'icon'=>'fa-star',              'color'=>'bg-aspej-gold text-aspej-navy'],
        ['label'=>'Avg Attendance',   'value'=>$kpi['avg_attendance'].'%','icon'=>'fa-chart-line',      'color'=>'bg-green-500'],
        ['label'=>'Pending Fees',     'value'=>$kpi['pending_fees'],    'icon'=>'fa-money-bill-wave',   'color'=>'bg-red-500'],
        ['label'=>'Open Applications','value'=>$kpi['open_apps'],       'icon'=>'fa-file-signature',    'color'=>'bg-orange-500'],
        ['label'=>'Total Parents',    'value'=>$kpi['total_parents'],   'icon'=>'fa-user-friends',      'color'=>'bg-teal-500'],
        ['label'=>'Active Alerts',    'value'=>$kpi['unresolved_alerts'],'icon'=>'fa-bell',             'color'=>'bg-pink-500'],
    ];
    foreach ($kpi_cards as $c):
    ?>
    <div class="admin-card flex items-center space-x-4">
        <div class="<?= $c['color'] ?> w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas <?= $c['icon'] ?> text-white text-2xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $c['value'] ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-400"><?= $c['label'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="admin-card">
        <h3 class="section-heading">GPA Distribution</h3>
        <canvas id="gpaChart" height="220"></canvas>
    </div>
    <div class="admin-card">
        <h3 class="section-heading">Recent Applications</h3>
        <?php
        $recent_apps = $pdo->query("
            SELECT CONCAT(first_name,' ',last_name) AS name, level, status, created_at
            FROM applications ORDER BY created_at DESC LIMIT 8
        ")->fetchAll();
        ?>
        <div class="overflow-x-auto">
        <table class="admin-table">
            <thead><tr><th>Applicant</th><th>Level</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recent_apps as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($a['level']) ?></span></td>
                <td><span class="badge <?= $a['status']==='pending'?'badge-yellow':($a['status']==='approved'?'badge-green':'badge-red') ?>">
                    <?= ucfirst($a['status']) ?></span></td>
                <td class="text-gray-400 text-xs"><?= date('M j', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="admin-card">
    <h3 class="section-heading">Quick Actions</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="?section=users&action=new" class="quick-action-btn"><i class="fas fa-user-plus mr-2"></i> Add User</a>
        <a href="?section=news&action=new"  class="quick-action-btn"><i class="fas fa-plus mr-2"></i> Add News</a>
        <a href="?section=fees"             class="quick-action-btn"><i class="fas fa-money-check mr-2"></i> Review Fees</a>
        <a href="?section=alerts"           class="quick-action-btn"><i class="fas fa-bell mr-2"></i> View Alerts</a>
    </div>
</div>

<script>
// GPA Doughnut
new Chart(document.getElementById('gpaChart'), {
    type: 'doughnut',
    data: {
        labels: ['A (3.5+)', 'B (3.0–3.5)', 'C (2.0–3.0)', 'D (<2.0)'],
        datasets: [{ data: [<?= (int)$gpa_dist['a_range'] ?>,<?= (int)$gpa_dist['b_range'] ?>,<?= (int)$gpa_dist['c_range'] ?>,<?= (int)$gpa_dist['d_range'] ?>],
            backgroundColor: ['#22c55e','#3b82f6','#f59e0b','#ef4444'],
            borderWidth: 2, borderColor: 'rgba(255,255,255,0.1)' }]
    },
    options: { responsive:true, cutout:'70%', plugins:{ legend:{ position:'bottom' } } }
});
</script>

<!-- ============================================================
     SECTION: USER MANAGEMENT
============================================================ -->
<?php elseif ($section === 'users'): ?>

<div class="admin-card mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex gap-3 flex-wrap">
            <?php foreach ([''=>'All Roles','admin'=>'Admin','teacher'=>'Teacher','parent'=>'Parent','student'=>'Student'] as $r=>$lbl): ?>
            <a href="?section=users&role_filter=<?= $r ?>"
               class="badge <?= ($role_filter??'')===$r ? 'badge-navy' : 'badge-gray' ?>">
                <?= $lbl ?>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="flex gap-2">
            <form class="flex gap-2" method="GET">
                <input type="hidden" name="section" value="users">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search name, email…"
                       class="form-control text-sm">
                <button class="btn-primary text-sm px-3">Search</button>
            </form>
            <button onclick="openModal('addUserModal')" class="btn-gold text-sm px-4">
                <i class="fas fa-user-plus mr-1"></i> Add User
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users_list as $u): ?>
        <tr>
            <td class="flex items-center gap-2">
                <img src="<?= htmlspecialchars($u['profile_image']) ?>" class="w-8 h-8 rounded-full object-cover" alt="">
                <span class="font-medium"><?= htmlspecialchars($u['full_name']) ?></span>
            </td>
            <td class="text-gray-500">@<?= htmlspecialchars($u['username']) ?></td>
            <td class="text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-blue capitalize"><?= $u['role_name'] ?></span></td>
            <td><span class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-red' ?>">
                <?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="text-gray-400 text-xs"><?= $u['last_login'] ? date('M j, Y H:i', strtotime($u['last_login'])) : 'Never' ?></td>
            <td class="text-right">
                <button onclick="openEditUser(<?= htmlspecialchars(json_encode($u)) ?>)"
                        class="text-blue-500 hover:text-blue-700 mr-2 text-sm"><i class="fas fa-edit"></i></button>
                <button onclick="toggleUser(<?= $u['id'] ?>, <?= $u['is_active'] ?>)"
                        class="text-<?= $u['is_active'] ? 'red' : 'green' ?>-500 hover:opacity-80 text-sm">
                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check-circle' ?>"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Add New User</h3>
            <button onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="addUserForm" class="space-y-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Full Name</label><input type="text" name="full_name" required class="form-control"></div>
                <div><label class="form-label">Username</label><input type="text" name="username" required class="form-control"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" required class="form-control"></div>
                <div><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
                <div><label class="form-label">Role</label>
                    <select name="role_name" required class="form-control">
                        <?php foreach ($roles_list as $r): ?>
                        <option value="<?= $r['name'] ?>"><?= $r['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Password</label><input type="password" name="password" required class="form-control" placeholder="Min 8 chars"></div>
            </div>
            <div id="addUserError" class="text-red-500 text-sm hidden"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('addUserModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay hidden">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Edit User</h3>
            <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="editUserForm" class="space-y-4 mt-4">
            <input type="hidden" name="user_id" id="editUserId">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Full Name</label><input type="text" name="full_name" id="editFullName" required class="form-control"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" id="editEmail" required class="form-control"></div>
                <div><label class="form-label">Phone</label><input type="tel" name="phone" id="editPhone" class="form-control"></div>
                <div><label class="form-label">Role</label>
                    <select name="role_name" id="editRole" required class="form-control">
                        <?php foreach ($roles_list as $r): ?>
                        <option value="<?= $r['name'] ?>"><?= $r['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div><label class="form-label">New Password <span class="text-gray-400">(leave blank to keep)</span></label>
                <input type="password" name="password" class="form-control"></div>
            <div id="editUserError" class="text-red-500 text-sm hidden"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('editUserModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     SECTION: STUDENTS
============================================================ -->
<?php elseif ($section === 'students'): ?>

<div class="admin-card">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <form class="flex gap-2" method="GET">
            <input type="hidden" name="section" value="students">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search student name, number, class…"
                   class="form-control text-sm">
            <button class="btn-primary text-sm px-3">Search</button>
        </form>
        <button onclick="openModal('addStudentModal')" class="btn-gold text-sm px-4">
            <i class="fas fa-user-plus mr-1"></i> Enroll Student
        </button>
    </div>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>#</th><th>Name</th><th>Class</th><th>GPA</th><th>Attendance</th><th>Absent Days</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($students_list as $i => $s): ?>
        <tr>
            <td class="text-gray-400 text-xs"><?= htmlspecialchars($s['student_number']) ?></td>
            <td class="font-medium"><?= htmlspecialchars($s['full_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($s['class']) ?></span></td>
            <td class="font-bold <?= $s['gpa'] < 2.0 ? 'text-red-500' : 'text-green-600' ?>">
                <?= number_format($s['gpa'],1) ?>
            </td>
            <td>
                <div class="flex items-center gap-2">
                    <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width:<?= min(100,$s['attendance_pct']) ?>%"></div>
                    </div>
                    <span class="text-xs text-gray-500"><?= number_format($s['attendance_pct'],1) ?>%</span>
                </div>
            </td>
            <td class="text-red-400"><?= $s['absent_days'] ?></td>
            <td class="text-right">
                <a href="?section=students&view=<?= $s['id'] ?>" class="text-blue-500 hover:text-blue-700 text-sm mr-2"><i class="fas fa-eye"></i></a>
                <button onclick="alert('Edit student #<?= $s['id'] ?>')" class="text-yellow-500 hover:text-yellow-700 text-sm"><i class="fas fa-edit"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($students_list)): ?>
        <tr><td colspan="7" class="text-center text-gray-400 py-8">No students found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ============================================================
     SECTION: NEWS CMS
============================================================ -->
<?php elseif ($section === 'news'): ?>

<div class="flex justify-between items-center mb-6">
    <p class="text-gray-500 dark:text-gray-400"><?= count($news_list) ?> items in the database</p>
    <button onclick="openModal('addNewsModal')" class="btn-gold">
        <i class="fas fa-plus mr-2"></i> Add News Item
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    <?php foreach ($news_list as $n): ?>
    <div class="admin-card !p-0 overflow-hidden flex flex-col">
        <div class="h-40 bg-gray-200 dark:bg-gray-700 relative">
            <img src="<?= htmlspecialchars($n['image']) ?>"
                 onerror="this.src='/assets/images/default-news.jpg'"
                 class="w-full h-full object-cover" alt="">
            <span class="absolute top-2 left-2 badge badge-gold"><?= htmlspecialchars($n['type']) ?></span>
        </div>
        <div class="p-4 flex-1 flex flex-col">
            <p class="text-xs text-gray-400 mb-1"><?= date('M j, Y', strtotime($n['published_at'])) ?></p>
            <h4 class="font-bold text-gray-800 dark:text-white mb-2"><?= htmlspecialchars($n['title']) ?></h4>
            <p class="text-gray-500 dark:text-gray-400 text-sm flex-1 line-clamp-2"><?= htmlspecialchars($n['summary']) ?></p>
            <div class="flex gap-2 mt-3">
                <button onclick="openEditNews(<?= htmlspecialchars(json_encode($n)) ?>)"
                        class="btn-primary text-xs flex-1">
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button onclick="deleteNews(<?= $n['id'] ?>)"
                        class="btn-danger text-xs px-3">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add News Modal -->
<div id="addNewsModal" class="modal-overlay hidden">
    <div class="modal-box max-w-xl">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Add News / Event</h3>
            <button onclick="closeModal('addNewsModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="addNewsForm" class="space-y-4 mt-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="form-label">Title</label><input type="text" name="title" required class="form-control"></div>
                <div><label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="News">News</option>
                        <option value="Event">Event</option>
                        <option value="Announcement">Announcement</option>
                    </select>
                </div>
                <div><label class="form-label">Published Date</label>
                    <input type="date" name="published_at" value="<?= date('Y-m-d') ?>" required class="form-control">
                </div>
                <div class="col-span-2"><label class="form-label">Summary</label>
                    <textarea name="summary" required rows="3" class="form-control"></textarea>
                </div>
                <div><label class="form-label">Image Path</label>
                    <input type="text" name="image" placeholder="assets/images/news.jpg" class="form-control">
                </div>
                <div><label class="form-label">Link</label>
                    <input type="text" name="link" placeholder="news.php#article-1" class="form-control">
                </div>
            </div>
            <div id="addNewsError" class="text-red-500 text-sm hidden"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('addNewsModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Publish</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit News Modal -->
<div id="editNewsModal" class="modal-overlay hidden">
    <div class="modal-box max-w-xl">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Edit News Item</h3>
            <button onclick="closeModal('editNewsModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="editNewsForm" class="space-y-4 mt-4">
            <input type="hidden" name="id" id="editNewsId">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="form-label">Title</label><input type="text" name="title" id="editNewsTitle" required class="form-control"></div>
                <div><label class="form-label">Type</label>
                    <select name="type" id="editNewsType" class="form-control">
                        <option value="News">News</option><option value="Event">Event</option><option value="Announcement">Announcement</option>
                    </select>
                </div>
                <div><label class="form-label">Date</label><input type="date" name="published_at" id="editNewsDate" required class="form-control"></div>
                <div class="col-span-2"><label class="form-label">Summary</label><textarea name="summary" id="editNewsSummary" rows="3" class="form-control"></textarea></div>
                <div><label class="form-label">Image Path</label><input type="text" name="image" id="editNewsImage" class="form-control"></div>
                <div><label class="form-label">Link</label><input type="text" name="link" id="editNewsLink" class="form-control"></div>
            </div>
            <div id="editNewsError" class="text-red-500 text-sm hidden"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal('editNewsModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     SECTION: ATTENDANCE OVERVIEW
============================================================ -->
<?php elseif ($section === 'attendance'): ?>

<div class="admin-card mb-6">
    <form class="flex flex-wrap gap-3 items-end mb-6" method="GET">
        <input type="hidden" name="section" value="attendance">
        <div>
            <label class="form-label">Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($att_date) ?>" class="form-control">
        </div>
        <div>
            <label class="form-label">Class</label>
            <select name="class" class="form-control">
                <option value="">All Classes</option>
                <?php foreach ($classes as $cls): ?>
                <option value="<?= htmlspecialchars($cls) ?>" <?= $att_class===$cls?'selected':'' ?>><?= htmlspecialchars($cls) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn-primary">Filter</button>
    </form>

    <!-- Summary pills -->
    <div class="flex gap-4 mb-6">
        <div class="flex items-center gap-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-4 py-2 rounded-full text-sm font-semibold">
            <i class="fas fa-check-circle"></i> Present: <?= $att_summary['present'] ?>
        </div>
        <div class="flex items-center gap-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-4 py-2 rounded-full text-sm font-semibold">
            <i class="fas fa-times-circle"></i> Absent: <?= $att_summary['absent'] ?>
        </div>
        <div class="flex items-center gap-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 px-4 py-2 rounded-full text-sm font-semibold">
            <i class="fas fa-clock"></i> Tardy: <?= $att_summary['tardy'] ?>
        </div>
        <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-4 py-2 rounded-full text-sm font-semibold">
            Total Records: <?= $att_summary['total'] ?>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Student</th><th>Class</th><th>Status</th><th>Marked By</th><th>Note</th></tr></thead>
        <tbody>
        <?php if (empty($att_records)): ?>
        <tr><td colspan="5" class="text-center text-gray-400 py-10">
            No attendance records for <?= htmlspecialchars($att_date) ?>.
            <a href="/portal-teacher.php" class="text-aspej-gold hover:underline ml-2">Teachers mark attendance here →</a>
        </td></tr>
        <?php endif; ?>
        <?php foreach ($att_records as $r): ?>
        <tr>
            <td class="font-medium"><?= htmlspecialchars($r['full_name']) ?> <span class="text-xs text-gray-400">#<?= htmlspecialchars($r['student_number']) ?></span></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($r['class']) ?></span></td>
            <td><span class="badge <?= $r['status']==='present'?'badge-green':($r['status']==='absent'?'badge-red':'badge-yellow') ?>">
                <?= ucfirst($r['status']) ?></span></td>
            <td class="text-gray-500 text-sm"><?= htmlspecialchars($r['marked_by_name']) ?></td>
            <td class="text-gray-400 text-sm"><?= htmlspecialchars($r['note'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ============================================================
     SECTION: FEE PAYMENTS
============================================================ -->
<?php elseif ($section === 'fees'): ?>

<!-- Summary cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $fee_map = ['pending'=>['badge-yellow','fa-clock','Pending'],
                'paid'   =>['badge-green', 'fa-check','Paid'],
                'overdue'=>['badge-red',   'fa-exclamation','Overdue'],
                'waived' =>['badge-gray',  'fa-ban','Waived']];
    foreach ($fee_map as $st => [$badge, $icon, $label]):
        $row = array_filter($fee_totals, fn($r) => $r['status']===$st);
        $row = $row ? array_values($row)[0] : ['cnt'=>0,'total'=>0];
    ?>
    <div class="admin-card text-center">
        <i class="fas <?= $icon ?> text-2xl mb-2 <?= $st==='pending'?'text-yellow-500':($st==='paid'?'text-green-500':($st==='overdue'?'text-red-500':'text-gray-400')) ?>"></i>
        <p class="text-2xl font-bold dark:text-white"><?= $row['cnt'] ?></p>
        <p class="text-gray-500 text-sm"><?= $label ?></p>
        <p class="text-xs text-gray-400"><?= number_format($row['total'],0) ?> RWF</p>
    </div>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <div class="flex flex-wrap gap-3 items-center justify-between mb-4">
        <div class="flex gap-2 flex-wrap">
            <?php foreach ([''=>'All','pending'=>'Pending','paid'=>'Paid','overdue'=>'Overdue','waived'=>'Waived'] as $st=>$lbl): ?>
            <a href="?section=fees&status=<?= $st ?>"
               class="badge <?= ($fee_status??'')===$st?'badge-navy':'badge-gray' ?>">
               <?= $lbl ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>ID</th><th>Payer / Applicant</th><th>Purpose</th><th>Amount</th><th>Method</th><th>Status</th><th>Due Date</th><th class="text-right">Action</th></tr></thead>
        <tbody>
        <?php foreach ($fees_list as $f): ?>
        <tr>
            <td class="text-gray-400 text-xs">#<?= $f['id'] ?></td>
            <td>
                <p class="font-medium text-sm"><?= htmlspecialchars($f['payer_name']) ?></p>
                <?php if ($f['first_name']): ?>
                <p class="text-xs text-gray-400">Applicant: <?= htmlspecialchars($f['first_name'].' '.$f['last_name']) ?></p>
                <?php endif; ?>
            </td>
            <td class="text-sm"><?= htmlspecialchars($f['purpose']) ?></td>
            <td class="font-bold text-aspej-navy dark:text-aspej-gold"><?= number_format($f['amount'],0) ?> RWF</td>
            <td class="text-sm capitalize text-gray-500"><?= str_replace('_',' ',$f['payment_method']) ?></td>
            <td><span class="badge <?= $f['status']==='paid'?'badge-green':($f['status']==='pending'?'badge-yellow':($f['status']==='overdue'?'badge-red':'badge-gray')) ?>">
                <?= ucfirst($f['status']) ?></span></td>
            <td class="text-xs text-gray-400"><?= $f['due_date'] ? date('M j, Y', strtotime($f['due_date'])) : '—' ?></td>
            <td class="text-right">
                <?php if ($f['status'] !== 'paid'): ?>
                <button onclick="markFeePaid(<?= $f['id'] ?>)" class="btn-gold text-xs px-3 py-1">
                    <i class="fas fa-check mr-1"></i> Mark Paid
                </button>
                <?php else: ?>
                <span class="text-xs text-gray-400">Paid <?= $f['paid_date'] ? date('M j', strtotime($f['paid_date'])) : '' ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($fees_list)): ?>
        <tr><td colspan="8" class="text-center text-gray-400 py-8">No fee records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ============================================================
     SECTION: CHAT / MESSAGES
============================================================ -->
<?php elseif ($section === 'chat'): ?>

<div class="flex h-[calc(100vh-12rem)] gap-5">

    <!-- Contact list -->
    <div class="w-72 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl shadow overflow-y-auto">
        <div class="p-4 border-b dark:border-gray-700">
            <h4 class="font-semibold text-aspej-navy dark:text-white">Conversations</h4>
        </div>
        <!-- Broadcast button -->
        <button onclick="openModal('broadcastModal')"
                class="w-full flex items-center gap-3 px-4 py-3 bg-aspej-gold/10 hover:bg-aspej-gold/20 text-aspej-navy dark:text-aspej-gold font-semibold text-sm transition">
            <i class="fas fa-broadcast-tower"></i> Send Broadcast
        </button>
        <?php foreach ($contacts as $c): ?>
        <a href="?section=chat&with=<?= $c['id'] ?>"
           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition border-b dark:border-gray-700 <?= $chat_with===$c['id']?'bg-blue-50 dark:bg-gray-700':'' ?>">
            <div class="relative">
                <img src="<?= htmlspecialchars($c['profile_image']) ?>" class="w-10 h-10 rounded-full object-cover" alt="">
                <?php if ($c['unread'] > 0): ?>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $c['unread'] ?></span>
                <?php endif; ?>
            </div>
            <div class="min-w-0">
                <p class="font-medium text-sm text-gray-800 dark:text-white truncate"><?= htmlspecialchars($c['full_name']) ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= $c['role_name'] ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Chat window -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <?php if ($chat_with && !empty($messages)): ?>
        <?php $chat_partner = array_values(array_filter($contacts, fn($c)=>$c['id']===$chat_with))[0] ?? null; ?>
        <div class="px-5 py-3 border-b dark:border-gray-700 flex items-center gap-3">
            <?php if ($chat_partner): ?>
            <img src="<?= htmlspecialchars($chat_partner['profile_image']) ?>" class="w-9 h-9 rounded-full object-cover">
            <div>
                <p class="font-semibold text-aspej-navy dark:text-white"><?= htmlspecialchars($chat_partner['full_name']) ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= $chat_partner['role_name'] ?></p>
            </div>
            <?php endif; ?>
        </div>
        <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-4">
            <?php foreach ($messages as $m): ?>
            <?php $mine = $m['sender_id'] == $current_user['id']; ?>
            <div class="flex <?= $mine?'justify-end':'justify-start' ?>">
                <?php if (!$mine): ?>
                <img src="<?= htmlspecialchars($m['sender_img']) ?>" class="w-8 h-8 rounded-full mr-2 mt-1 flex-shrink-0 object-cover">
                <?php endif; ?>
                <div class="max-w-[70%]">
                    <div class="<?= $mine?'bg-aspej-navy text-white':'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white' ?> rounded-2xl px-4 py-3 text-sm">
                        <?= htmlspecialchars($m['message']) ?>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 <?= $mine?'text-right':'' ?>">
                        <?= date('H:i', strtotime($m['created_at'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Send box -->
        <form id="sendMsgForm" data-to="<?= $chat_with ?>" class="p-4 border-t dark:border-gray-700 flex gap-3">
            <input type="text" id="msgInput" placeholder="Type a message…" required
                   class="flex-1 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-2 text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:border-aspej-gold">
            <button class="bg-aspej-navy text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-90 transition flex-shrink-0">
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </form>

        <?php elseif ($chat_with): ?>
        <div id="chatMessages" class="flex-1 overflow-y-auto p-5 space-y-4">
            <p class="text-center text-gray-400 mt-10">No messages yet. Start the conversation!</p>
        </div>
        <?php $chat_partner = array_values(array_filter($contacts, fn($c)=>$c['id']===$chat_with))[0] ?? null; ?>
        <div class="px-5 py-3 border-b dark:border-gray-700 flex items-center gap-3 order-first">
            <?php if ($chat_partner): ?>
            <img src="<?= htmlspecialchars($chat_partner['profile_image']) ?>" class="w-9 h-9 rounded-full object-cover">
            <div>
                <p class="font-semibold text-aspej-navy dark:text-white"><?= htmlspecialchars($chat_partner['full_name']) ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= $chat_partner['role_name'] ?></p>
            </div>
            <?php endif; ?>
        </div>
        <form id="sendMsgForm" data-to="<?= $chat_with ?>" class="p-4 border-t dark:border-gray-700 flex gap-3">
            <input type="text" id="msgInput" placeholder="Type a message…" required
                   class="flex-1 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-2 text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:border-aspej-gold">
            <button class="bg-aspej-navy text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-90 flex-shrink-0">
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </form>

        <?php else: ?>
        <div class="flex-1 flex items-center justify-center text-gray-400">
            <div class="text-center">
                <i class="fas fa-comments text-5xl mb-4 opacity-30"></i>
                <p>Select a conversation from the left to start messaging</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Broadcast Modal -->
<div id="broadcastModal" class="modal-overlay hidden">
    <div class="modal-box max-w-md">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Send Broadcast</h3>
            <button onclick="closeModal('broadcastModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="broadcastForm" class="space-y-4 mt-4">
            <div>
                <label class="form-label">Audience</label>
                <select name="group" class="form-control">
                    <option value="all_teachers">All Teachers</option>
                    <option value="all_parents">All Parents</option>
                    <option value="all_students">All Students</option>
                    <option value="everyone">Everyone</option>
                </select>
            </div>
            <div>
                <label class="form-label">Message</label>
                <textarea name="message" rows="4" required class="form-control" placeholder="Type your announcement…"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('broadcastModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold"><i class="fas fa-broadcast-tower mr-2"></i> Send</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     SECTION: ALERTS
============================================================ -->
<?php elseif ($section === 'alerts'): ?>

<div class="admin-card">
    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-500 dark:text-gray-400"><?= count($alerts_list) ?> alerts</p>
        <button onclick="runAlertScan()" class="btn-primary text-sm">
            <i class="fas fa-sync-alt mr-1"></i> Re-scan Now
        </button>
    </div>

    <div class="space-y-3">
    <?php if (empty($alerts_list)): ?>
    <div class="text-center py-12 text-gray-400">
        <i class="fas fa-check-circle text-5xl text-green-400 mb-3"></i>
        <p class="text-lg">All students are performing well — no active alerts!</p>
    </div>
    <?php endif; ?>
    <?php foreach ($alerts_list as $a): ?>
    <div class="flex items-start gap-4 p-4 rounded-xl border <?= $a['is_resolved']?'border-gray-200 dark:border-gray-700 opacity-60':'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20' ?>">
        <div class="flex-shrink-0 mt-1">
            <i class="fas <?= $a['type']==='low_gpa'?'fa-star text-yellow-500':($a['type']==='low_attendance'?'fa-user-times text-red-500':'fa-exclamation-triangle text-orange-500') ?> text-xl"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <p class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($a['full_name']) ?></p>
                <span class="badge badge-blue"><?= htmlspecialchars($a['class']) ?></span>
                <span class="badge <?= $a['type']==='low_gpa'?'badge-yellow':($a['type']==='low_attendance'?'badge-red':'badge-orange') ?> capitalize">
                    <?= str_replace('_',' ',$a['type']) ?>
                </span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300"><?= htmlspecialchars($a['message']) ?></p>
            <p class="text-xs text-gray-400 mt-1">
                GPA: <strong><?= number_format($a['gpa'],1) ?></strong> &bull;
                Attendance: <strong><?= number_format($a['attendance_pct'],1) ?>%</strong> &bull;
                <?= date('M j, Y', strtotime($a['created_at'])) ?>
            </p>
        </div>
        <?php if (!$a['is_resolved']): ?>
        <button onclick="resolveAlert(<?= $a['id'] ?>)"
                class="flex-shrink-0 btn-primary text-xs px-3 py-1">
            <i class="fas fa-check mr-1"></i> Resolve
        </button>
        <?php else: ?>
        <span class="flex-shrink-0 badge badge-green text-xs">Resolved</span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<?php endif; // end section switch ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
