<?php
// ============================================================
// api/fee_manager.php  –  Admin marks fee payments
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !can('manage_fees', $user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'mark_paid') {
    $fee_id = (int)($_POST['fee_id'] ?? 0);
    $method = trim($_POST['method'] ?? 'cash');
    $ref    = trim($_POST['reference'] ?? '');
    $notes  = trim($_POST['notes']  ?? '');

    if (!$fee_id) { echo json_encode(['success'=>false,'message'=>'Fee ID required.']); exit; }

    $allowed_methods = ['cash','bank_transfer','mobile_money','card'];
    if (!in_array($method, $allowed_methods)) $method = 'cash';

    $pdo->prepare("
        UPDATE fee_payments
        SET status='paid', paid_date=CURDATE(), marked_by=?, payment_method=?, reference=?, notes=?
        WHERE id=? AND status != 'paid'
    ")->execute([$user['id'], $method, $ref ?: null, $notes ?: null, $fee_id]);

    echo json_encode(['success'=>true,'message'=>'Payment marked as paid.']);

} elseif ($action === 'mark_overdue') {
    // Auto-flag all past-due payments
    $count = $pdo->exec("
        UPDATE fee_payments
        SET status='overdue'
        WHERE status='pending' AND due_date < CURDATE()
    ");
    echo json_encode(['success'=>true,'message'=>"$count payments flagged as overdue."]);

} elseif ($action === 'waive') {
    $fee_id = (int)($_POST['fee_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    if (!$fee_id) { echo json_encode(['success'=>false,'message'=>'Fee ID required.']); exit; }
    $pdo->prepare("UPDATE fee_payments SET status='waived', notes=?, marked_by=? WHERE id=?")
        ->execute([$reason, $user['id'], $fee_id]);
    echo json_encode(['success'=>true,'message'=>'Payment waived.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
