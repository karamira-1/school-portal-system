<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('mark_attendance', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo      = get_db();
$date     = trim($_POST['date']     ?? '');
$class_id = (int)($_POST['class_id'] ?? 0);
$students = $_POST['students']       ?? [];

if (!$date||!$class_id||empty($students)) { echo json_encode(['success'=>false,'message'=>'Date, class and students required.']); exit; }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||$date>date('Y-m-d')) {
    echo json_encode(['success'=>false,'message'=>'Invalid or future date.']); exit;
}

// Check already submitted
$chk = $pdo->prepare('SELECT COUNT(*) FROM school_attendance WHERE date=? AND student_id IN (SELECT id FROM students WHERE class_id=?)');
$chk->execute([$date,$class_id]);
if ((int)$chk->fetchColumn() > 0) {
    echo json_encode(['success'=>false,'message'=>'Attendance for this class and date has already been submitted.']); exit;
}

$allowed = ['present','absent','late','excused'];
$inserted = 0;
$ins = $pdo->prepare('INSERT INTO school_attendance (student_id,date,status,marked_by,note) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),note=VALUES(note),marked_by=VALUES(marked_by)');

try {
    $pdo->beginTransaction();
    foreach ($students as $s) {
        $sid    = (int)($s['id']     ?? 0);
        $status = trim($s['status']  ?? 'present');
        $note   = trim($s['note']    ?? '');
        if (!$sid) continue;
        if (!in_array($status,$allowed)) $status='present';
        $ins->execute([$sid,$date,$status,$user['id'],$note?:null]);
        $inserted++;
    }
    // Update daily_attendance in old table for compatibility
    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>"Attendance saved for $inserted students.",'inserted'=>$inserted]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
