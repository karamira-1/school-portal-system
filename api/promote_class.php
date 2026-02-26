<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('promote_students', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo           = get_db();
$from_class_id = (int)($_POST['from_class_id'] ?? 0);
$year          = (int)($_POST['year']          ?? date('Y'));
$note          = trim($_POST['note']           ?? '');

if (!$from_class_id||!$year) { echo json_encode(['success'=>false,'message'=>'Class and year required.']); exit; }

// Get source class
$src = $pdo->prepare('SELECT * FROM classes WHERE id=?');
$src->execute([$from_class_id]);
$from_class = $src->fetch();
if (!$from_class) { echo json_encode(['success'=>false,'message'=>'Source class not found.']); exit; }

// Promotion map
$promo_map = [
    'Level 3 NIT' => 'Level 4 NIT', 'Level 4 NIT' => 'Level 5 NIT',
    'Level 3 TOU' => 'Level 4 TOU', 'Level 4 TOU' => 'Level 5 TOU',
    'Level 3 BDC' => 'Level 4 BDC', 'Level 4 BDC' => 'Level 5 BDC',
    'Senior 4 ACC'=> 'Senior 5 ACC','Senior 5 ACC' => 'Senior 6 ACC',
];

$to_class_name = $promo_map[$from_class['name']] ?? null;
if (!$to_class_name) { echo json_encode(['success'=>false,'message'=>'This class has no next level to promote to (Level 5 / Senior 6 are final year).']); exit; }

// Get destination class
$dst = $pdo->prepare('SELECT * FROM classes WHERE name=?');
$dst->execute([$to_class_name]);
$to_class = $dst->fetch();
if (!$to_class) { echo json_encode(['success'=>false,'message'=>"Destination class '$to_class_name' not found in database."]); exit; }

// Get all active students in source class
$students = $pdo->prepare('SELECT id FROM students WHERE class_id=? AND is_active=1');
$students->execute([$from_class_id]);
$students = $students->fetchAll(PDO::FETCH_COLUMN);

if (empty($students)) { echo json_encode(['success'=>false,'message'=>'No active students in this class.']); exit; }

try {
    $pdo->beginTransaction();

    $update  = $pdo->prepare('UPDATE students SET class_id=?, class=? WHERE id=?');
    $log_ins = $pdo->prepare('INSERT INTO promotion_log (student_id,from_class,to_class,academic_year,promoted_by,note) VALUES (?,?,?,?,?,?)');

    foreach ($students as $sid) {
        $update->execute([$to_class['id'], $to_class_name, $sid]);
        $log_ins->execute([$sid, $from_class['name'], $to_class_name, $year, $user['id'], $note?:null]);
    }

    $pdo->commit();
    $count = count($students);
    echo json_encode(['success'=>true,'message'=>"$count student(s) promoted from '{$from_class['name']}' to '$to_class_name'.",'promoted'=>$count]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
}
