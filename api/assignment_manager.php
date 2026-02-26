<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('assign_lessons', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'assign') {
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $class_id   = (int)($_POST['class_id']   ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $term       = (int)($_POST['term']       ?? 1);
    $year       = (int)($_POST['year']       ?? date('Y'));

    if (!$teacher_id||!$class_id||!$subject_id||!$term||!$year) {
        echo json_encode(['success'=>false,'message'=>'All fields required.']); exit;
    }
    // Verify teacher role
    $tr = $pdo->prepare("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND r.name='teacher'");
    $tr->execute([$teacher_id]);
    if (!$tr->fetch()) { echo json_encode(['success'=>false,'message'=>'User is not a teacher.']); exit; }

    try {
        $pdo->prepare('INSERT IGNORE INTO teacher_assignments (teacher_id,class_id,subject_id,term,year,assigned_by) VALUES (?,?,?,?,?,?)')
            ->execute([$teacher_id,$class_id,$subject_id,$term,$year,$user['id']]);
        echo json_encode(['success'=>true,'message'=>'Assignment created.']);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Already assigned or DB error.']);
    }

} elseif ($action === 'remove') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    $pdo->prepare('DELETE FROM teacher_assignments WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Assignment removed.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
