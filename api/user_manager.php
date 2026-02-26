<?php
// ============================================================
// api/user_manager.php  –  Admin: Create / Update / Toggle users
// POST { action: 'create'|'update'|'toggle', ...fields }
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !can('manage_users', $user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']);
    exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

// ── Helper: get role_id from name ─────────────────────────
function role_id_from_name(PDO $pdo, string $name): int|false {
    $s = $pdo->prepare('SELECT id FROM roles WHERE name=?');
    $s->execute([$name]);
    return $s->fetchColumn();
}

if ($action === 'create') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username']  ?? '');
    $email      = trim($_POST['email']     ?? '');
    $phone      = trim($_POST['phone']     ?? '');
    $role_name  = trim($_POST['role_name'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (!$full_name || !$username || !$email || !$role_name || !$password) {
        echo json_encode(['success'=>false,'message'=>'All required fields must be filled.']); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success'=>false,'message'=>'Invalid email address.']); exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters.']); exit;
    }

    $role_id = role_id_from_name($pdo, $role_name);
    if (!$role_id) { echo json_encode(['success'=>false,'message'=>'Invalid role.']); exit; }

    // Check unique username/email
    $check = $pdo->prepare('SELECT id FROM users WHERE username=? OR email=?');
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Username or email already exists.']); exit;
    }

    try {
        $pdo->prepare('INSERT INTO users (role_id,full_name,email,username,password_hash,phone) VALUES (?,?,?,?,?,?)')
            ->execute([$role_id, $full_name, $email, $username, password_hash($password, PASSWORD_BCRYPT), $phone]);
        echo json_encode(['success'=>true,'message'=>'User created successfully.','id'=>$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
    }

} elseif ($action === 'update') {
    $user_id    = (int)($_POST['user_id']  ?? 0);
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email']     ?? '');
    $phone      = trim($_POST['phone']     ?? '');
    $role_name  = trim($_POST['role_name'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (!$user_id || !$full_name || !$email || !$role_name) {
        echo json_encode(['success'=>false,'message'=>'Required fields missing.']); exit;
    }
    $role_id = role_id_from_name($pdo, $role_name);
    if (!$role_id) { echo json_encode(['success'=>false,'message'=>'Invalid role.']); exit; }

    // Check email uniqueness (excluding self)
    $check = $pdo->prepare('SELECT id FROM users WHERE email=? AND id!=?');
    $check->execute([$email, $user_id]);
    if ($check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Email already in use.']); exit;
    }

    try {
        if ($password) {
            if (strlen($password) < 8) { echo json_encode(['success'=>false,'message'=>'Password too short.']); exit; }
            $pdo->prepare('UPDATE users SET role_id=?,full_name=?,email=?,phone=?,password_hash=? WHERE id=?')
                ->execute([$role_id,$full_name,$email,$phone,password_hash($password,PASSWORD_BCRYPT),$user_id]);
        } else {
            $pdo->prepare('UPDATE users SET role_id=?,full_name=?,email=?,phone=? WHERE id=?')
                ->execute([$role_id,$full_name,$email,$phone,$user_id]);
        }
        echo json_encode(['success'=>true,'message'=>'User updated.']);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Database error.']);
    }

} elseif ($action === 'toggle') {
    $user_id    = (int)($_POST['user_id']    ?? 0);
    $is_active  = (int)($_POST['is_active']  ?? 0);
    // Prevent admin from deactivating themselves
    if ($user_id === $user['id']) {
        echo json_encode(['success'=>false,'message'=>'Cannot deactivate your own account.']); exit;
    }
    $pdo->prepare('UPDATE users SET is_active=? WHERE id=?')->execute([$is_active ? 0 : 1, $user_id]);
    echo json_encode(['success'=>true,'message'=>'User status updated.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
