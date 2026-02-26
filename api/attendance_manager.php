<?php
// ============================================================
// api/attendance_manager.php  –  Teacher submits daily attendance
// POST { class_name, date, students: [{id, status, note}...] }
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !can('mark_attendance', $user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo        = get_db();
$class_name = trim($_POST['class_name'] ?? '');
$date       = trim($_POST['date']       ?? '');
$students   = $_POST['students']        ?? [];

if (!$class_name || !$date || empty($students)) {
    echo json_encode(['success'=>false,'message'=>'Missing required fields.']); exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success'=>false,'message'=>'Invalid date format.']); exit;
}

// Prevent future dates
if ($date > date('Y-m-d')) {
    echo json_encode(['success'=>false,'message'=>'Cannot mark attendance for future dates.']); exit;
}

// Teacher RBAC: verify this teacher is assigned to this class
if ($user['role_name'] === 'teacher') {
    $check = $pdo->prepare('SELECT id FROM teacher_classes WHERE user_id=? AND class_name=?');
    $check->execute([$user['id'], $class_name]);
    if (!$check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'You are not assigned to this class.']); exit;
    }
}

// Check if already submitted
$already = $pdo->prepare('SELECT COUNT(*) FROM daily_attendance WHERE class_name=? AND date=? AND marked_by=?');
$already->execute([$class_name, $date, $user['id']]);
if ((int)$already->fetchColumn() > 0) {
    echo json_encode(['success'=>false,'message'=>'Attendance already submitted for this date.']); exit;
}

$allowed_statuses = ['present','absent','tardy'];
$inserted = 0;

try {
    $pdo->beginTransaction();

    $insert = $pdo->prepare('
        INSERT INTO daily_attendance (student_id, class_name, date, status, marked_by, note)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE status=VALUES(status), note=VALUES(note), marked_by=VALUES(marked_by)
    ');

    foreach ($students as $student) {
        $student_id = (int)($student['id']     ?? 0);
        $status     = trim($student['status']  ?? 'present');
        $note       = trim($student['note']    ?? '');

        if (!$student_id) continue;
        if (!in_array($status, $allowed_statuses)) $status = 'present';

        $insert->execute([$student_id, $class_name, $date, $status, $user['id'], $note ?: null]);
        $inserted++;
    }

    // ── Update attendance summary for each student ────────
    // Recalculate total / absent from daily_attendance table
    $update_summary = $pdo->prepare('
        UPDATE students
        SET total_days    = (SELECT COUNT(*)   FROM daily_attendance WHERE student_id = students.id),
            absent_days   = (SELECT COUNT(*)   FROM daily_attendance WHERE student_id = students.id AND status = "absent"),
            attendance_pct= ROUND(
                (SELECT COUNT(*) FROM daily_attendance WHERE student_id=students.id AND status="present") * 100.0 /
                NULLIF((SELECT COUNT(*) FROM daily_attendance WHERE student_id=students.id),0)
            , 2)
        WHERE class = ?
    ');
    $update_summary->execute([$class_name]);

    // ── Run performance alert scan ─────────────────────────
    // Low GPA alert
    $low_gpa_students = $pdo->prepare("
        SELECT id, full_name, gpa FROM students WHERE class=? AND gpa < 2.0
    ");
    $low_gpa_students->execute([$class_name]);
    $alert_insert = $pdo->prepare("
        INSERT IGNORE INTO performance_alerts (student_id, type, message)
        SELECT ?, 'low_gpa', CONCAT('GPA of ', ?, ' is below 2.0 threshold.')
        WHERE NOT EXISTS (
            SELECT 1 FROM performance_alerts
            WHERE student_id=? AND type='low_gpa' AND is_resolved=0
        )
    ");
    foreach ($low_gpa_students->fetchAll() as $sg) {
        $alert_insert->execute([$sg['id'], number_format($sg['gpa'],1), $sg['id']]);
    }

    // Low attendance alert
    $low_att_students = $pdo->prepare("
        SELECT id, full_name, attendance_pct FROM students WHERE class=? AND attendance_pct < 80
    ");
    $low_att_students->execute([$class_name]);
    $att_alert_insert = $pdo->prepare("
        INSERT IGNORE INTO performance_alerts (student_id, type, message)
        SELECT ?, 'low_attendance', CONCAT('Attendance of ', ?, '% is below the 80% threshold.')
        WHERE NOT EXISTS (
            SELECT 1 FROM performance_alerts
            WHERE student_id=? AND type='low_attendance' AND is_resolved=0
        )
    ");
    foreach ($low_att_students->fetchAll() as $sg) {
        $att_alert_insert->execute([$sg['id'], number_format($sg['attendance_pct'],1), $sg['id']]);
    }

    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>"Attendance saved for $inserted students.",'inserted'=>$inserted]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
