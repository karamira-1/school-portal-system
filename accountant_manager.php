<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('manage_fees', $user)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'record_payment') {
    $fee_id     = (int)($_POST['fee_id']      ?? 0);
    $student_id = (int)($_POST['student_id']  ?? 0);
    $paid       = (float)($_POST['amount_paid'] ?? 0);
    $method     = trim($_POST['method']        ?? 'cash');
    $reference  = trim($_POST['reference']     ?? '');
    $notes      = trim($_POST['notes']         ?? '');

    if (!$fee_id || $paid <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid payment data.']); exit; }
    if (!in_array($method,['cash','bank_transfer','mobile_money'])) $method='cash';

    // Get current record
    $rec = $pdo->prepare('SELECT * FROM student_fees WHERE id=?');
    $rec->execute([$fee_id]);
    $fee = $rec->fetch();
    if (!$fee) { echo json_encode(['success'=>false,'message'=>'Fee record not found.']); exit; }

    $new_paid   = min($fee['amount_due'], $fee['amount_paid'] + $paid);
    $new_status = $new_paid >= $fee['amount_due'] ? 'paid' : ($new_paid > 0 ? 'partial' : 'unpaid');

    $pdo->prepare('UPDATE student_fees SET amount_paid=?, status=?, payment_date=CURDATE(), payment_method=?, reference=?, notes=?, recorded_by=? WHERE id=?')
        ->execute([$new_paid, $new_status, $method, $reference ?: null, $notes ?: null, $user['id'], $fee_id]);

    echo json_encode(['success'=>true,'message'=>"Payment of ".number_format($paid,0)." RWF recorded. Status: ".ucfirst($new_status)."."]);

} elseif ($action === 'generate') {
    $term       = (int)($_POST['term']       ?? 0);
    $year       = (int)($_POST['year']       ?? 0);
    $amount_due = (float)($_POST['amount_due'] ?? 50000);

    if (!$term||!$year||$amount_due<=0) { echo json_encode(['success'=>false,'message'=>'Term, year and amount required.']); exit; }

    // Get all active students
    $students = $pdo->query('SELECT id FROM students WHERE is_active=1')->fetchAll(PDO::FETCH_COLUMN);
    $created  = 0;

    $insert = $pdo->prepare('INSERT IGNORE INTO student_fees (student_id,term,year,amount_due,status) VALUES (?,?,?,?,\'unpaid\')');
    foreach ($students as $sid) {
        $insert->execute([$sid,$term,$year,$amount_due]);
        $created += $insert->rowCount();
    }
    echo json_encode(['success'=>true,'message'=>"Generated $created new fee record(s) for Term $term/$year.",'created'=>$created]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
