<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('manage_conduct', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'deduct') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $term       = (int)($_POST['term']       ?? 0);
    $year       = (int)($_POST['year']       ?? 0);
    $points     = (float)($_POST['points']   ?? 0);
    $reason     = trim($_POST['reason']      ?? '');

    if (!$student_id||!$term||!$year||$points<=0||!$reason) {
        echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
    }
    if ($points > 40) { echo json_encode(['success'=>false,'message'=>'Cannot deduct more than 40 points.']); exit; }

    // Get or create conduct record
    $existing = $pdo->prepare('SELECT * FROM conduct_marks WHERE student_id=? AND term=? AND year=?');
    $existing->execute([$student_id,$term,$year]);
    $record = $existing->fetch();

    if (!$record) {
        $pdo->prepare('INSERT INTO conduct_marks (student_id,term,year,score,deductions,last_updated_by) VALUES (?,?,?,40,?,?)')
            ->execute([$student_id,$term,$year, json_encode([]), $user['id']]);
        $existing->execute([$student_id,$term,$year]);
        $record = $existing->fetch();
    }

    $current_score = (float)$record['score'];
    $new_score     = max(0, $current_score - $points);
    $deductions    = json_decode($record['deductions'] ?? '[]', true) ?: [];
    $deductions[]  = [
        'points' => $points,
        'reason' => $reason,
        'date'   => date('Y-m-d'),
        'by'     => $user['full_name'],
    ];

    $pdo->prepare('UPDATE conduct_marks SET score=?, deductions=?, last_updated_by=? WHERE student_id=? AND term=? AND year=?')
        ->execute([$new_score, json_encode($deductions), $user['id'], $student_id, $term, $year]);

    echo json_encode(['success'=>true,'message'=>"Deducted $points points. New score: $new_score/40.",'new_score'=>$new_score]);

} elseif ($action === 'restore') {
    // Restore points (remove a deduction entry by index)
    $student_id = (int)($_POST['student_id'] ?? 0);
    $term       = (int)($_POST['term']       ?? 0);
    $year       = (int)($_POST['year']       ?? 0);
    $index      = (int)($_POST['index']      ?? -1);

    $rec = $pdo->prepare('SELECT * FROM conduct_marks WHERE student_id=? AND term=? AND year=?');
    $rec->execute([$student_id,$term,$year]);
    $record = $rec->fetch();
    if (!$record) { echo json_encode(['success'=>false,'message'=>'Record not found.']); exit; }

    $deds = json_decode($record['deductions'] ?? '[]', true) ?: [];
    if (!isset($deds[$index])) { echo json_encode(['success'=>false,'message'=>'Deduction index not found.']); exit; }

    $restored_points = $deds[$index]['points'];
    array_splice($deds, $index, 1);
    $new_score = min(40, (float)$record['score'] + $restored_points);

    $pdo->prepare('UPDATE conduct_marks SET score=?, deductions=?, last_updated_by=? WHERE student_id=? AND term=? AND year=?')
        ->execute([$new_score, json_encode($deds), $user['id'], $student_id, $term, $year]);

    echo json_encode(['success'=>true,'message'=>"Restored $restored_points points. New score: $new_score/40."]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
