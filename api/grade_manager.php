<?php
// ============================================================
// api/grade_manager.php  –  Teacher saves / updates grades
// POST { term, subject, teacher_comments, grades:[{student_id, grade, remarks}] }
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !can('manage_grades', $user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo              = get_db();
$term             = trim($_POST['term']             ?? '');
$subject          = trim($_POST['subject']          ?? '');
$teacher_comments = trim($_POST['teacher_comments'] ?? '');
$grades           = $_POST['grades'] ?? [];

if (!$term || !$subject || empty($grades)) {
    echo json_encode(['success'=>false,'message'=>'Missing required fields.']); exit;
}

// RBAC: verify teacher is assigned this subject
if ($user['role_name'] === 'teacher') {
    $check = $pdo->prepare('SELECT id FROM teacher_classes WHERE user_id=? AND subject=?');
    $check->execute([$user['id'], $subject]);
    if (!$check->fetch()) {
        echo json_encode(['success'=>false,'message'=>'You are not assigned to teach '.$subject.'.']); exit;
    }
}

$allowed_grades = ['A','A-','B+','B','B-','C+','C','C-','D','F',''];
$saved = 0;

try {
    $pdo->beginTransaction();

    $upsert = $pdo->prepare('
        INSERT INTO grades (student_id, term, year, subject, grade, remarks)
        VALUES (?, ?, YEAR(CURDATE()), ?, ?, ?)
        ON DUPLICATE KEY UPDATE grade=VALUES(grade), remarks=VALUES(remarks)
    ');

    foreach ($grades as $g) {
        $student_id = (int)($g['student_id'] ?? 0);
        $grade      = trim($g['grade']       ?? '');
        $remarks    = trim($g['remarks']      ?? '');

        if (!$student_id || !in_array($grade, $allowed_grades)) continue;
        if ($grade === '') continue; // Skip blank entries

        $upsert->execute([$student_id, $term, $subject, $grade, $remarks]);
        $saved++;
    }

    // ── Recalculate GPA for affected students ─────────────
    // Simple GPA mapping: A=4.0, A-=3.7, B+=3.3, B=3.0, B-=2.7, C+=2.3, C=2.0, C-=1.7, D=1.0, F=0
    $gpa_map = ['A'=>4.0,'A-'=>3.7,'B+'=>3.3,'B'=>3.0,'B-'=>2.7,'C+'=>2.3,'C'=>2.0,'C-'=>1.7,'D'=>1.0,'F'=>0.0];

    // Get all unique student_ids from this batch
    $student_ids = array_unique(array_map(fn($g) => (int)$g['student_id'], $grades));

    foreach ($student_ids as $sid) {
        if (!$sid) continue;
        // Get all latest grades per subject (most recent term only)
        $g_stmt = $pdo->prepare("SELECT grade FROM grades WHERE student_id=? AND term=?");
        $g_stmt->execute([$sid, $term]);
        $all_grades = $g_stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($all_grades)) {
            $points = array_map(fn($gr) => $gpa_map[$gr] ?? 0.0, $all_grades);
            $avg_gpa = round(array_sum($points) / count($points), 1);

            // Update term summary
            $pdo->prepare("
                INSERT INTO term_summaries (student_id, term, year, overall_gpa, teacher_comments)
                VALUES (?, ?, YEAR(CURDATE()), ?, ?)
                ON DUPLICATE KEY UPDATE overall_gpa=VALUES(overall_gpa), teacher_comments=VALUES(teacher_comments)
            ")->execute([$sid, $term, $avg_gpa, $teacher_comments]);

            // Update the students table with overall GPA (average across all terms)
            $pdo->prepare("
                UPDATE students SET gpa=(
                    SELECT ROUND(AVG(overall_gpa),1) FROM term_summaries WHERE student_id=?
                ) WHERE id=?
            ")->execute([$sid, $sid]);
        }

        // ── Check for low GPA alert ───────────────────────
        $new_gpa_stmt = $pdo->prepare('SELECT gpa FROM students WHERE id=?');
        $new_gpa_stmt->execute([$sid]);
        $new_gpa = (float)$new_gpa_stmt->fetchColumn();

        if ($new_gpa < 2.0) {
            $pdo->prepare("
                INSERT IGNORE INTO performance_alerts (student_id, type, message)
                SELECT ?, 'low_gpa', CONCAT('GPA of ', ?, ' has fallen below the 2.0 threshold.')
                WHERE NOT EXISTS (
                    SELECT 1 FROM performance_alerts WHERE student_id=? AND type='low_gpa' AND is_resolved=0
                )
            ")->execute([$sid, number_format($new_gpa,1), $sid]);
        }
    }

    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>"$saved grade(s) saved successfully.",'saved'=>$saved]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
