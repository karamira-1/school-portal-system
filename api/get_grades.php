<?php
// ============================================================
// api/get_grades.php  –  Returns grades+summary for a term
// GET params: student_id (internal), term
// Called by portal.js via fetch()
// ============================================================
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
$pdo = get_db();

$student_id = (int) $_SESSION['student_id'];
$term       = trim($_GET['term'] ?? '');

if (!$term) {
    echo json_encode(['success' => false, 'message' => 'Term is required.']);
    exit;
}

// Grades
$stmt = $pdo->prepare('SELECT subject, grade, remarks FROM grades WHERE student_id = ? AND term = ?');
$stmt->execute([$student_id, $term]);
$grades = $stmt->fetchAll();

// Summary
$stmt = $pdo->prepare('SELECT overall_gpa, teacher_comments, year FROM term_summaries WHERE student_id = ? AND term = ?');
$stmt->execute([$student_id, $term]);
$summary = $stmt->fetch();

echo json_encode([
    'success' => true,
    'grades'  => $grades,
    'summary' => $summary ?: null,
]);
