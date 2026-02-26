<?php
// ============================================================
// api/marks_manager.php  –  Teacher saves subject marks
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('enter_marks', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'save_marks') {
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $class_id   = (int)($_POST['class_id']   ?? 0);
    $term       = (int)($_POST['term']       ?? 0);
    $year       = (int)($_POST['year']       ?? 0);
    $test_type  = trim($_POST['test_type']   ?? '');
    $marks      = $_POST['marks']            ?? [];

    if (!$subject_id || !$class_id || !$term || !$year || !in_array($test_type,['test1','test2','exam'])) {
        echo json_encode(['success'=>false,'message'=>'Missing required fields.']); exit;
    }

    // RBAC: verify this teacher is assigned to this class + subject
    $check = $pdo->prepare('SELECT id FROM teacher_assignments WHERE teacher_id=? AND class_id=? AND subject_id=? AND term=? AND year=?');
    $check->execute([$user['id'],$class_id,$subject_id,$term,$year]);
    if (!$check->fetch()) { echo json_encode(['success'=>false,'message'=>'You are not assigned to this class/subject.']); exit; }

    $saved = 0;
    $upsert = $pdo->prepare('
        INSERT INTO marks (student_id, class_id, subject_id, term, year, test_type, mark_value, entered_by)
        VALUES (?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE mark_value=VALUES(mark_value), entered_by=VALUES(entered_by), updated_at=NOW()
    ');

    try {
        $pdo->beginTransaction();
        foreach ($marks as $m) {
            $student_id = (int)($m['student_id'] ?? 0);
            $mark_value = trim($m['mark'] ?? '');
            if (!$student_id || $mark_value === '') continue;
            $mark_value = (float)$mark_value;
            if ($mark_value < 0 || $mark_value > 100) continue;
            $upsert->execute([$student_id,$class_id,$subject_id,$term,$year,$test_type,$mark_value,$user['id']]);
            $saved++;
        }
        $pdo->commit();
        echo json_encode(['success'=>true,'message'=>"$saved mark(s) saved successfully.",'saved'=>$saved]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
