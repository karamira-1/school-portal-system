<?php
// ============================================================
// api/student_accounts.php
// Activates or rejects student portal registration requests
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !in_array($user['role_name'],['admin','master','librarian'])) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'activate') {
    $id             = (int)($_POST['id'] ?? 0);
    $student_number = trim($_POST['student_number'] ?? '');
    $username       = trim($_POST['username']       ?? '');

    if (!$id || !$student_number || !$username) {
        echo json_encode(['success'=>false,'message'=>'Missing required data.']); exit;
    }

    // Load registration
    $reg = $pdo->prepare('SELECT * FROM student_registrations WHERE id=? AND status=\'pending\'');
    $reg->execute([$id]);
    $reg = $reg->fetch();
    if (!$reg) { echo json_encode(['success'=>false,'message'=>'Request not found or already processed.']); exit; }

    // Verify student still exists
    $st = $pdo->prepare('SELECT id FROM students WHERE student_number=? AND LOWER(TRIM(full_name))=LOWER(TRIM(?)) AND is_active=1');
    $st->execute([$student_number, $reg['full_name']]);
    $student = $st->fetch();
    if (!$student) { echo json_encode(['success'=>false,'message'=>'Student record not found. Cannot activate.']); exit; }

    // Check username not taken
    $chk = $pdo->prepare('SELECT id FROM students WHERE portal_username=? AND id!=?');
    $chk->execute([$username, $student['id']]);
    if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'Username already in use.']); exit; }

    try {
        $pdo->beginTransaction();

        // Create user row for student
        $role_id = $pdo->query("SELECT id FROM roles WHERE name='student'")->fetchColumn();
        $pdo->prepare('INSERT INTO users (role_id,full_name,email,username,password_hash,is_active) VALUES (?,?,?,?,?,1)')
            ->execute([$role_id, $reg['full_name'], $reg['email']??null, $username, $reg['password_hash']]);
        $new_user_id = $pdo->lastInsertId();

        // Link to students table
        $pdo->prepare('UPDATE students SET portal_username=?, portal_password=?, account_status=\'active\', registered_at=NOW() WHERE id=?')
            ->execute([$username, $reg['password_hash'], $student['id']]);

        // Mark request approved
        $pdo->prepare('UPDATE student_registrations SET status=\'approved\', reviewed_by=?, reviewed_at=NOW() WHERE id=?')
            ->execute([$user['id'], $id]);

        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>"Account activated for {$reg['full_name']}."]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
    }

} elseif ($action === 'reject') {
    $id     = (int)($_POST['id']     ?? 0);
    $reason = trim($_POST['reason']  ?? '');
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    $pdo->prepare("UPDATE student_registrations SET status='rejected', rejection_reason=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?")
        ->execute([$reason?:null, $user['id'], $id]);
    echo json_encode(['success'=>true,'message'=>'Request rejected.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
