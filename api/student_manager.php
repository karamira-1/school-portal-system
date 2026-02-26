<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('manage_students', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $full_name     = trim($_POST['full_name']      ?? '');
    $student_number= trim($_POST['student_number'] ?? '');
    $class_id      = (int)($_POST['class_id']      ?? 0);
    $email         = trim($_POST['email']          ?? '');
    $password      = $_POST['password']            ?? '';

    if (!$full_name||!$student_number||!$class_id||!$password) {
        echo json_encode(['success'=>false,'message'=>'Full name, student number, class and password are required.']); exit;
    }
    if (strlen($password) < 6) { echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters.']); exit; }

    // Check unique student number
    $chk = $pdo->prepare('SELECT id FROM students WHERE student_number=?');
    $chk->execute([$student_number]);
    if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'Student number already exists.']); exit; }

    $pdo->prepare('INSERT INTO students (student_number,full_name,class_id,email,password_hash,is_active) VALUES (?,?,?,?,?,1)')
        ->execute([$student_number,$full_name,$class_id,$email?:null,password_hash($password,PASSWORD_BCRYPT)]);

    // Also set class column for backward compat
    $cls = $pdo->prepare('SELECT name FROM classes WHERE id=?'); $cls->execute([$class_id]);
    $cls_name = $cls->fetchColumn();
    $pdo->prepare("UPDATE students SET class=? WHERE student_number=?")->execute([$cls_name,$student_number]);

    echo json_encode(['success'=>true,'message'=>'Student enrolled successfully.','id'=>$pdo->lastInsertId()]);

} elseif ($action === 'update') {
    $student_id    = (int)($_POST['student_id']    ?? 0);
    $full_name     = trim($_POST['full_name']      ?? '');
    $student_number= trim($_POST['student_number'] ?? '');
    $class_id      = (int)($_POST['class_id']      ?? 0);
    $email         = trim($_POST['email']          ?? '');
    $password      = $_POST['password']            ?? '';

    if (!$student_id||!$full_name||!$student_number||!$class_id) {
        echo json_encode(['success'=>false,'message'=>'Required fields missing.']); exit;
    }

    // Sync class name
    $cls = $pdo->prepare('SELECT name FROM classes WHERE id=?'); $cls->execute([$class_id]);
    $cls_name = $cls->fetchColumn();

    if ($password) {
        if (strlen($password)<6) { echo json_encode(['success'=>false,'message'=>'Password too short.']); exit; }
        $pdo->prepare('UPDATE students SET full_name=?,student_number=?,class_id=?,class=?,email=?,password_hash=? WHERE id=?')
            ->execute([$full_name,$student_number,$class_id,$cls_name,$email?:null,password_hash($password,PASSWORD_BCRYPT),$student_id]);
    } else {
        $pdo->prepare('UPDATE students SET full_name=?,student_number=?,class_id=?,class=?,email=? WHERE id=?')
            ->execute([$full_name,$student_number,$class_id,$cls_name,$email?:null,$student_id]);
    }
    echo json_encode(['success'=>true,'message'=>'Student updated.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
