<?php
// ============================================================
// includes/auth.php  –  Session auth + RBAC (v3)
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

function auth_login(string $username, string $password): array|false {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.username=? AND u.is_active=1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) return false;
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['role']      = $user['role_name'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $pdo->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
    return $user;
}

function auth_logout(): void { session_unset(); session_destroy(); }

function auth_user(): array|null {
    if (empty($_SESSION['user_id'])) return null;
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_role(string|array $roles, string $redirect = '/login.php'): array {
    $user = auth_user();
    if (!$user) { header("Location: $redirect"); exit; }
    $roles = (array)$roles;
    if (!in_array($user['role_name'], $roles)) { header("Location: $redirect?error=forbidden"); exit; }
    return $user;
}

function redirect_if_logged_in(): void {
    $user = auth_user();
    if (!$user) return;
    $map = [
        'admin'               => '/portal-admin.php',
        'master'              => '/portal-master.php',
        'teacher'             => '/portal-teacher.php',
        'librarian'           => '/portal-librarian.php',
        'director_studies'    => '/portal-dos.php',
        'director_discipline' => '/portal-dod.php',
        'accountant'          => '/portal-accountant.php',
        'parent'              => '/portals.php',
        'student'             => '/portal-student.php',
    ];
    header('Location: ' . ($map[$user['role_name']] ?? '/index.php'));
    exit;
}

// ── RBAC permission map ───────────────────────────────────
function can(string $permission, array $user): bool {
    $perms = [
        'admin' => [
            'manage_users','view_analytics','manage_news','manage_fees',
            'view_all_students','send_broadcast','mark_attendance','manage_grades',
            'manage_staff','assign_lessons','view_marks','generate_reports',
            'manage_conduct','manage_students','promote_students',
        ],
        'master' => [
            'manage_staff','view_all','send_broadcast','view_analytics',
        ],
        'librarian' => [
            'manage_students','promote_students','mark_attendance','view_students',
        ],
        'director_studies' => [
            'assign_lessons','view_marks','generate_reports','view_all_students',
            'view_analytics',
        ],
        'director_discipline' => [
            'manage_conduct','view_all_students',
        ],
        'accountant' => [
            'manage_fees','view_students',
        ],
        'teacher' => [
            'enter_marks','view_own_assignments','send_direct_message',
        ],
        'parent'  => ['view_child_data','send_direct_message'],
        'student' => ['view_own_data','send_support_message'],
    ];
    return in_array($permission, $perms[$user['role_name']] ?? []);
}

function unread_notifications(int $user_id): int {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

function unread_messages(int $user_id): int {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM chat_messages WHERE receiver_id=? AND is_read=0');
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

// ── Grade letter helper ───────────────────────────────────
function grade_letter(float $score): string {
    if ($score >= 80) return 'A';
    if ($score >= 70) return 'B';
    if ($score >= 60) return 'C';
    if ($score >= 50) return 'D';
    return 'F';
}

function is_pass(float $score): bool { return $score >= 50; }

// ── Calculate term total from marks array ─────────────────
// Returns ['total'=>float, 'test1'=>float, 'test2'=>float, 'exam'=>float]
function calc_term_marks(array $marks_by_type): array {
    $t1   = (float)($marks_by_type['test1'] ?? 0);
    $t2   = (float)($marks_by_type['test2'] ?? 0);
    $exam = (float)($marks_by_type['exam']  ?? 0);
    $total = round($t1 * 0.25 + $t2 * 0.25 + $exam * 0.50, 2);
    return ['test1'=>$t1,'test2'=>$t2,'exam'=>$exam,'total'=>$total];
}

// ── Get subjects for a combination (own + shared) ─────────
function get_subjects_for_combination(string $combination): array {
    $pdo  = get_db();
    $stmt = $pdo->prepare('SELECT * FROM subjects WHERE (combination=? OR combination IS NULL) AND is_active=1 ORDER BY combination IS NULL DESC, name');
    $stmt->execute([$combination]);
    return $stmt->fetchAll();
}

// ── Get all classes ───────────────────────────────────────
function get_all_classes(): array {
    return get_db()->query('SELECT * FROM classes WHERE is_active=1 ORDER BY combination, level')->fetchAll();
}
