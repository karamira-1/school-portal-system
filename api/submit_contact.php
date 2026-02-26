<?php
// ============================================================
// api/submit_contact.php  –  Handles contact form POST
// Returns JSON { success: bool, message: string }
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: same-origin');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name    = mb_substr(trim($_POST['name']    ?? ''), 0, 150);
$email   = mb_substr(trim($_POST['email']   ?? ''), 0, 150);
$subject = mb_substr(trim($_POST['subject'] ?? ''), 0, 255);
$message = mb_substr(trim($_POST['message'] ?? ''), 0, 5000);

// Validation
if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $pdo  = get_db();
    $stmt = $pdo->prepare('INSERT INTO contact_messages (full_name, email, subject, message) VALUES (?,?,?,?)');
    $stmt->execute([$name, $email, $subject, $message]);

    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
