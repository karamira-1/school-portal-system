<?php
// ============================================================
// api/alert_manager.php  –  Resolve / scan performance alerts
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !in_array($user['role_name'], ['admin','teacher'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'resolve') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'Alert ID required.']); exit; }
    $pdo->prepare('UPDATE performance_alerts SET is_resolved=1 WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Alert resolved.']);

} elseif ($action === 'scan') {
    // Full scan: GPA < 2.0 and attendance < 80%
    $students = $pdo->query('SELECT id, full_name, gpa, attendance_pct FROM students')->fetchAll();
    $created = 0;

    $gpa_insert = $pdo->prepare("
        INSERT IGNORE INTO performance_alerts (student_id, type, message)
        SELECT ?, 'low_gpa', CONCAT('GPA of ', ?, ' is below the 2.0 minimum threshold.')
        WHERE NOT EXISTS (SELECT 1 FROM performance_alerts WHERE student_id=? AND type='low_gpa' AND is_resolved=0)
    ");
    $att_insert = $pdo->prepare("
        INSERT IGNORE INTO performance_alerts (student_id, type, message)
        SELECT ?, 'low_attendance', CONCAT('Attendance rate of ', ?, '% is below the 80% required threshold.')
        WHERE NOT EXISTS (SELECT 1 FROM performance_alerts WHERE student_id=? AND type='low_attendance' AND is_resolved=0)
    ");

    foreach ($students as $s) {
        if ($s['gpa'] < 2.0) {
            $gpa_insert->execute([$s['id'], number_format($s['gpa'],1), $s['id']]);
            $created += $gpa_insert->rowCount();
        }
        if ($s['attendance_pct'] < 80) {
            $att_insert->execute([$s['id'], number_format($s['attendance_pct'],1), $s['id']]);
            $created += $att_insert->rowCount();
        }
    }

    echo json_encode(['success'=>true,'message'=>"Scan complete. $created new alert(s) generated.",'created'=>$created]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
