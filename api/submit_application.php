<?php
// ============================================================
// api/submit_application.php  –  Handles admission form POST
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

// ── Sanitise helpers ─────────────────────────────────────────
function str_field(string $key, int $max = 255): string {
    return mb_substr(trim($_POST[$key] ?? ''), 0, $max);
}

// ── Required fields ──────────────────────────────────────────
$first_name   = str_field('firstName', 100);
$last_name    = str_field('lastName',  100);
$dob          = str_field('dob',        10);
$gender       = str_field('gender',     10);
$parent_name  = str_field('parentName',150);
$parent_phone = str_field('parentPhone', 20);
$parent_id    = str_field('parentID',   16);
$province     = str_field('province',   50);
$district     = str_field('district',   50);
$level        = str_field('level',      10);
$trade        = str_field('trade',     100);

// Basic validation
$required = [$first_name, $last_name, $dob, $gender, $parent_name, $parent_phone, $parent_id, $province, $district, $level];
foreach ($required as $val) {
    if ($val === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        exit;
    }
}

// Validate parent ID (16 digits)
if (!preg_match('/^\d{16}$/', $parent_id)) {
    echo json_encode(['success' => false, 'message' => 'Parent ID must be exactly 16 digits.']);
    exit;
}

// Validate date
if (!DateTime::createFromFormat('Y-m-d', $dob)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date of birth.']);
    exit;
}

// ── Handle optional O-Level certificate upload ───────────────
$cert_filename = null;
if (!empty($_FILES['oLevelUpload']['tmp_name'])) {
    $file     = $_FILES['oLevelUpload'];
    $max_size = 2 * 1024 * 1024; // 2 MB

    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Certificate file must be under 2 MB.']);
        exit;
    }
    // Check MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'Certificate must be a PDF file.']);
        exit;
    }

    $upload_dir = __DIR__ . '/../uploads/certificates/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $cert_filename = uniqid('cert_', true) . '.pdf';
    move_uploaded_file($file['tmp_name'], $upload_dir . $cert_filename);
}

// ── Insert into DB ────────────────────────────────────────────
try {
    $pdo  = get_db();
    $stmt = $pdo->prepare('
        INSERT INTO applications
            (first_name, last_name, dob, gender,
             parent_name, parent_phone, parent_id, province, district,
             level, trade, olevel_cert)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $first_name, $last_name, $dob, $gender,
        $parent_name, $parent_phone, $parent_id, $province, $district,
        $level, $trade ?: null, $cert_filename,
    ]);

    echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}
