<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ School – Portal Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --navy:   #0F1F3D;
            --gold:   #E6AC2C;
            --gold2:  #F5CC6A;
            --white:  #FAFAF8;
            --text:   #2C2C2C;
            --muted:  #7A7A7A;
            --panel:  rgba(255,255,255,0.97);
            --border: rgba(0,0,0,0.08);
            --shadow: 0 25px 60px rgba(0,0,0,0.18);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── Background ── */
        .bg-canvas {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #0A1628 0%, #1D2A4D 50%, #0F1F3D 100%);
            overflow: hidden;
        }
        .bg-canvas::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                radial-gradient(ellipse 80% 50% at 20% 20%, rgba(230,172,44,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(230,172,44,0.07) 0%, transparent 60%);
        }
        .grid-lines {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .orb {
            position: absolute; border-radius: 50%;
            background: radial-gradient(circle, rgba(230,172,44,0.2), transparent 70%);
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 500px; height: 500px; top: -100px; left: -100px; }
        .orb-2 { width: 400px; height: 400px; bottom: -80px; right: -80px; animation-delay: -4s; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-30px)} }

        /* ── Layout ── */
        .login-wrapper {
            position: relative; z-index: 1;
            display: flex;
            width: 100%; min-height: 100vh;
        }

        /* ── Left hero panel ── */
        .hero-panel {
            flex: 1;
            display: flex; flex-direction: column;
            justify-content: center; align-items: flex-start;
            padding: 60px 70px;
            max-width: 540px;
        }
        .school-crest {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, var(--gold), var(--gold2));
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; margin-bottom: 32px;
            box-shadow: 0 8px 32px rgba(230,172,44,0.35);
        }
        .school-name {
            font-family: 'Playfair Display', serif;
            font-size: 48px; font-weight: 900;
            color: white; line-height: 1.1;
            margin-bottom: 12px;
        }
        .school-name span { color: var(--gold); }
        .school-tagline {
            font-size: 15px; color: rgba(255,255,255,0.55);
            letter-spacing: 0.08em; text-transform: uppercase;
            margin-bottom: 48px;
        }
        .portal-chips {
            display: flex; flex-wrap: wrap; gap: 8px;
        }
        .chip {
            padding: 6px 14px; border-radius: 100px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
            font-size: 12px; font-weight: 500;
            transition: all .2s;
        }
        .chip:hover { background: rgba(230,172,44,0.15); color: var(--gold); border-color: rgba(230,172,44,0.3); }

        /* ── Right form panel ── */
        .form-panel {
            width: 460px; flex-shrink: 0;
            background: var(--white);
            display: flex; flex-direction: column;
            justify-content: center;
            padding: 48px 52px;
            box-shadow: -30px 0 80px rgba(0,0,0,0.25);
            min-height: 100vh;
        }
        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px; font-weight: 700;
            color: var(--navy); margin-bottom: 6px;
        }
        .form-sub {
            color: var(--muted); font-size: 14px; margin-bottom: 32px;
        }

        /* Role selector */
        .role-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 8px; margin-bottom: 28px;
        }
        .role-btn {
            display: flex; align-items: center; gap-x: 10px; gap: 8px;
            padding: 10px 12px; border-radius: 10px;
            border: 2px solid var(--border);
            background: white; cursor: pointer;
            font-size: 12px; font-weight: 500; color: var(--text);
            transition: all .15s; text-align: left;
        }
        .role-btn i { font-size: 14px; width: 18px; }
        .role-btn:hover { border-color: var(--navy); background: #f7f9ff; }
        .role-btn.active {
            border-color: var(--navy);
            background: var(--navy); color: white;
        }
        .role-btn.active i { color: var(--gold); }

        /* Form fields */
        .field-group { margin-bottom: 16px; }
        .field-label {
            display: block; font-size: 12px; font-weight: 600;
            color: var(--navy); margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .field-input {
            width: 100%; padding: 12px 14px;
            border: 2px solid var(--border); border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 14px;
            color: var(--text); background: white;
            transition: border-color .15s;
            outline: none;
        }
        .field-input:focus { border-color: var(--navy); }
        .field-input::placeholder { color: #BBBBB8; }
        .input-wrap { position: relative; }
        .input-wrap .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--muted); cursor: pointer; border: none;
            background: none; font-size: 14px;
        }

        .login-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--navy), #2a3f6b);
            color: white; border: none; border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: all .2s; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(15,31,61,0.3); }
        .login-btn:active { transform: translateY(0); }

        /* Register link */
        .register-link {
            text-align: center; margin-top: 20px;
            font-size: 13px; color: var(--muted);
        }
        .register-link a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }

        /* Error / message */
        .alert {
            padding: 10px 14px; border-radius: 10px;
            font-size: 13px; font-weight: 500; margin-bottom: 16px;
        }
        .alert-error { background: #FFF0F0; color: #C0392B; border: 1px solid #FFD0D0; }
        .alert-info  { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }

        /* Divider */
        .divider {
            text-align: center; position: relative;
            margin: 20px 0; color: var(--muted); font-size: 12px;
        }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%;
            width: 38%; height: 1px; background: var(--border);
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        /* Secretary link */
        .secretary-link {
            display: flex; align-items: center; gap: 8px;
            padding: 11px 14px; border-radius: 10px;
            border: 2px dashed var(--border);
            color: var(--muted); font-size: 13px;
            text-decoration: none; transition: all .15s;
        }
        .secretary-link:hover { border-color: var(--gold); color: var(--navy); background: #fffdf5; }

        /* Register modal */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 100;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity .2s;
        }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-box {
            background: white; border-radius: 20px;
            padding: 40px; width: 480px; max-width: 95vw;
            box-shadow: 0 30px 80px rgba(0,0,0,0.2);
            transform: translateY(20px); transition: transform .2s;
        }
        .modal-overlay.show .modal-box { transform: translateY(0); }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 700;
            color: var(--navy); margin-bottom: 6px;
        }
        .modal-sub { font-size: 13px; color: var(--muted); margin-bottom: 24px; }

        @media (max-width: 900px) {
            .hero-panel { display: none; }
            .form-panel { width: 100%; box-shadow: none; padding: 40px 24px; }
        }
        @media (max-width: 480px) {
            .role-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/includes/auth.php';
redirect_if_logged_in();

$error   = '';
$success = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $user = auth_login($username, $password);
        if ($user) {
            $map = [
                'admin'               => '/portal-admin.php',
                'master'              => '/portal-master.php',
                'teacher'             => '/portal-teacher.php',
                'librarian'           => '/portal-librarian.php',
                'director_studies'    => '/portal-dos.php',
                'director_discipline' => '/portal-dod.php',
                'accountant'          => '/portal-accountant.php',
                'student'             => '/portal-student.php',
            ];
            header('Location: ' . ($map[$user['role_name']] ?? '/index.php'));
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}

// Handle student registration
$reg_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $pdo = get_db();
    $reg_number = trim($_POST['reg_number'] ?? '');
    $reg_name   = trim($_POST['reg_name']   ?? '');
    $reg_user   = trim($_POST['reg_username']  ?? '');
    $reg_email  = trim($_POST['reg_email']  ?? '');
    $reg_phone  = trim($_POST['reg_phone']  ?? '');
    $reg_pass   = $_POST['reg_password']    ?? '';
    $reg_pass2  = $_POST['reg_password2']   ?? '';

    if (!$reg_number||!$reg_name||!$reg_user||!$reg_pass) {
        $reg_msg = 'error:All required fields must be filled.';
    } elseif (strlen($reg_pass) < 6) {
        $reg_msg = 'error:Password must be at least 6 characters.';
    } elseif ($reg_pass !== $reg_pass2) {
        $reg_msg = 'error:Passwords do not match.';
    } else {
        // Verify student number + name exact match (case-insensitive)
        $chk = $pdo->prepare("SELECT id FROM students WHERE student_number=? AND LOWER(TRIM(full_name))=LOWER(TRIM(?)) AND is_active=1");
        $chk->execute([$reg_number, $reg_name]);
        if (!$chk->fetch()) {
            $reg_msg = 'error:No student found with that registration number and name. Please check for exact spelling.';
        } else {
            // Check not already registered
            $dup = $pdo->prepare("SELECT id FROM student_registrations WHERE student_number=?");
            $dup->execute([$reg_number]);
            if ($dup->fetch()) {
                $reg_msg = 'error:A registration request for this student number already exists. Wait for activation or contact the school.';
            } else {
                $dupUser = $pdo->prepare("SELECT id FROM student_registrations WHERE username=?");
                $dupUser->execute([$reg_user]);
                if ($dupUser->fetch()) {
                    $reg_msg = 'error:That username is already taken. Choose a different one.';
                } else {
                    $pdo->prepare("INSERT INTO student_registrations (student_number,full_name,username,password_hash,email,phone) VALUES (?,?,?,?,?,?)")
                        ->execute([$reg_number,$reg_name,$reg_user,password_hash($reg_pass,PASSWORD_BCRYPT),$reg_email?:null,$reg_phone?:null]);
                    $reg_msg = 'success:Registration submitted! Your account will be activated by the school administration. Please wait for confirmation.';
                }
            }
        }
    }
}

$error = htmlspecialchars($error);
$reg_type = strstr($reg_msg,':', true);
$reg_text = substr($reg_msg, strpos($reg_msg,':')+1);
?>

<div class="bg-canvas">
    <div class="grid-lines"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="login-wrapper">
    <!-- Hero -->
    <div class="hero-panel">
        <div class="school-crest">🏫</div>
        <h1 class="school-name">ASPEJ<br><span>School</span></h1>
        <p class="school-tagline">Excellence · Discipline · Innovation</p>
        <div class="portal-chips">
            <span class="chip"><i class="fas fa-shield-halved"></i> Administration</span>
            <span class="chip"><i class="fas fa-graduation-cap"></i> Director of Studies</span>
            <span class="chip"><i class="fas fa-chalkboard-teacher"></i> Teachers</span>
            <span class="chip"><i class="fas fa-book"></i> Librarian</span>
            <span class="chip"><i class="fas fa-shield-alt"></i> Discipline</span>
            <span class="chip"><i class="fas fa-calculator"></i> Accounts</span>
            <span class="chip"><i class="fas fa-user-graduate"></i> Students</span>
            <span class="chip"><i class="fas fa-user-tie"></i> Master/Mistress</span>
        </div>
    </div>

    <!-- Login Form -->
    <div class="form-panel">
        <p class="form-title">Welcome back</p>
        <p class="form-sub">Sign in to your portal</p>

        <!-- Role selector -->
        <div class="role-grid">
            <?php
            $roles = [
                'admin'               => ['fa-shield-halved',      'Admin'],
                'master'              => ['fa-user-tie',            'Master/Mistress'],
                'director_studies'    => ['fa-graduation-cap',      'Dir. Studies'],
                'director_discipline' => ['fa-shield-alt',          'Dir. Discipline'],
                'teacher'             => ['fa-chalkboard-teacher',  'Teacher'],
                'librarian'           => ['fa-book',                'Librarian'],
                'accountant'          => ['fa-calculator',          'Accountant'],
                'student'             => ['fa-user-graduate',       'Student'],
            ];
            $selected = $_GET['role'] ?? ($_POST['selected_role'] ?? 'student');
            foreach ($roles as $key=>[$icon,$label]):
            ?>
            <button type="button" class="role-btn <?= $selected===$key?'active':'' ?>" onclick="selectRole('<?= $key ?>')">
                <i class="fas <?= $icon ?>"></i> <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation mr-2"></i><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="login" value="1">
            <input type="hidden" name="selected_role" id="selectedRoleInput" value="<?= htmlspecialchars($selected) ?>">
            <div class="field-group">
                <label class="field-label">Username</label>
                <input type="text" name="username" class="field-input" placeholder="Enter your username" autocomplete="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="field-group">
                <label class="field-label">Password</label>
                <div class="input-wrap">
                    <input type="password" name="password" id="pwField" class="field-input" placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()"><i class="fas fa-eye" id="pwIcon"></i></button>
                </div>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <!-- Student register -->
        <div id="studentRegArea" style="<?= $selected==='student'?'':'display:none' ?>">
            <div class="divider">New student?</div>
            <button type="button" onclick="openRegModal()" class="login-btn" style="background: linear-gradient(135deg,#1a6b3a,#2d9e5a)">
                <i class="fas fa-user-plus"></i> Create Student Account
            </button>
        </div>

        <div class="divider">need help?</div>
        <a href="/contact-secretary.php" class="secretary-link">
            <i class="fas fa-envelope"></i>
            <span>Send a message to the School Secretary <span style="font-size:11px;color:#bbb">(no account required)</span></span>
        </a>
    </div>
</div>

<!-- Student Registration Modal -->
<div class="modal-overlay" id="regModal">
    <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
            <h2 class="modal-title">Student Registration</h2>
            <button onclick="closeRegModal()" style="border:none;background:none;font-size:20px;color:#999;cursor:pointer;line-height:1">×</button>
        </div>
        <p class="modal-sub">Your registration number and <strong>full name must exactly match</strong> the school records. Your account will be reviewed and activated by the school.</p>

        <?php if ($reg_type === 'error'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($reg_text) ?></div>
        <?php elseif ($reg_type === 'success'): ?>
        <div class="alert alert-info" style="background:#f0fdf4;color:#166534;border-color:#bbf7d0"><?= htmlspecialchars($reg_text) ?></div>
        <?php endif; ?>

        <form method="POST" id="regForm">
            <input type="hidden" name="register" value="1">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field-group" style="grid-column:span 2">
                    <label class="field-label">Registration Number <span style="color:red">*</span></label>
                    <input type="text" name="reg_number" class="field-input" placeholder="e.g. 2025001" required value="<?= htmlspecialchars($_POST['reg_number']??'') ?>">
                </div>
                <div class="field-group" style="grid-column:span 2">
                    <label class="field-label">Full Name (exact as registered) <span style="color:red">*</span></label>
                    <input type="text" name="reg_name" class="field-input" placeholder="As it appears in school records" required value="<?= htmlspecialchars($_POST['reg_name']??'') ?>">
                </div>
                <div class="field-group">
                    <label class="field-label">Username <span style="color:red">*</span></label>
                    <input type="text" name="reg_username" class="field-input" placeholder="Choose a username" required value="<?= htmlspecialchars($_POST['reg_username']??'') ?>">
                </div>
                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input type="email" name="reg_email" class="field-input" placeholder="Optional" value="<?= htmlspecialchars($_POST['reg_email']??'') ?>">
                </div>
                <div class="field-group">
                    <label class="field-label">Password <span style="color:red">*</span></label>
                    <input type="password" name="reg_password" class="field-input" placeholder="Min 6 chars" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Confirm Password <span style="color:red">*</span></label>
                    <input type="password" name="reg_password2" class="field-input" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" class="login-btn" style="margin-top:8px">
                <i class="fas fa-paper-plane"></i> Submit Registration Request
            </button>
        </form>
    </div>
</div>

<script>
const roles = <?= json_encode(array_keys($roles)) ?>;
function selectRole(role) {
    document.querySelectorAll('.role-btn').forEach((b,i) => b.classList.toggle('active', roles[i]===role));
    document.getElementById('selectedRoleInput').value = role;
    document.getElementById('studentRegArea').style.display = role==='student' ? 'block' : 'none';
}
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('pwIcon');
    if (f.type==='password') { f.type='text'; i.className='fas fa-eye-slash'; }
    else { f.type='password'; i.className='fas fa-eye'; }
}
function openRegModal()  { document.getElementById('regModal').classList.add('show'); }
function closeRegModal() { document.getElementById('regModal').classList.remove('show'); }
document.getElementById('regModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeRegModal();
});
// Auto-open modal if reg was attempted
<?php if ($reg_type): ?>
openRegModal();
<?php endif; ?>
</script>
</body>
</html>
