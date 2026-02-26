<?php
// ============================================================
// api/announcements_manager.php
// POST: create | update | delete | toggle_publish | pin
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
// Only admin, master, director_studies can manage announcements
if (!$user || !in_array($user['role_name'],['admin','master','director_studies','librarian'])) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

function make_slug(string $title): string {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug . '-' . time();
}

function handle_upload(array $file, string $dir, int $max_mb = 20): array|false {
    $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','png','jpg','jpeg','gif','zip','mp4'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > $max_mb * 1024 * 1024) return false;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = uniqid('ann_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) return false;
    return ['path' => '/uploads/announcements/' . $fname, 'name' => $file['name'], 'size' => $file['size']];
}

if ($action === 'create') {
    $title    = trim($_POST['title']    ?? '');
    $body     = trim($_POST['body']     ?? '');
    $excerpt  = trim($_POST['excerpt']  ?? '');
    $audience = $_POST['audience']      ?? 'all';
    $pinned   = (int)($_POST['is_pinned'] ?? 0);

    if (!$title || !$body) { echo json_encode(['success'=>false,'message'=>'Title and body are required.']); exit; }

    // Handle cover image
    $cover_path = null;
    if (!empty($_FILES['cover_image']['name'])) {
        $ci = handle_upload($_FILES['cover_image'], __DIR__.'/../uploads/announcements/covers/', 5);
        if ($ci) $cover_path = $ci['path'];
    }

    // Handle downloadable file
    $file_path = $file_name = $file_size = null;
    if (!empty($_FILES['attach_file']['name'])) {
        $af = handle_upload($_FILES['attach_file'], __DIR__.'/../uploads/announcements/files/', 20);
        if ($af) { $file_path=$af['path']; $file_name=$af['name']; $file_size=$af['size']; }
    }

    $slug = make_slug($title);
    $exc  = $excerpt ?: mb_strimwidth(strip_tags($body), 0, 200, '…');

    $pdo->prepare('INSERT INTO announcements (title,slug,body,excerpt,cover_image,file_path,file_name,file_size,audience,is_pinned,posted_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$title,$slug,$body,$exc,$cover_path,$file_path,$file_name,$file_size,$audience,$pinned,$user['id']]);

    echo json_encode(['success'=>true,'message'=>'Announcement published.','id'=>$pdo->lastInsertId()]);

} elseif ($action === 'update') {
    $id       = (int)($_POST['id']      ?? 0);
    $title    = trim($_POST['title']    ?? '');
    $body     = trim($_POST['body']     ?? '');
    $excerpt  = trim($_POST['excerpt']  ?? '');
    $audience = $_POST['audience']      ?? 'all';
    $pinned   = (int)($_POST['is_pinned'] ?? 0);

    if (!$id || !$title || !$body) { echo json_encode(['success'=>false,'message'=>'Required fields missing.']); exit; }

    $exc = $excerpt ?: mb_strimwidth(strip_tags($body), 0, 200, '…');

    // Handle file updates
    $file_updates = '';
    $extra_params = [];
    if (!empty($_FILES['attach_file']['name'])) {
        $af = handle_upload($_FILES['attach_file'], __DIR__.'/../uploads/announcements/files/', 20);
        if ($af) {
            $file_updates = ', file_path=?, file_name=?, file_size=?';
            $extra_params = [$af['path'], $af['name'], $af['size']];
        }
    }

    $params = array_merge([$title,$body,$exc,$audience,$pinned], $extra_params, [$id]);
    $pdo->prepare("UPDATE announcements SET title=?,body=?,excerpt=?,audience=?,is_pinned=? $file_updates WHERE id=?")
        ->execute($params);

    echo json_encode(['success'=>true,'message'=>'Announcement updated.']);

} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    $pdo->prepare('DELETE FROM announcements WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Deleted.']);

} elseif ($action === 'toggle_publish') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE announcements SET is_published=NOT is_published WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Status toggled.']);

} elseif ($action === 'pin') {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE announcements SET is_pinned=NOT is_pinned WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Pin status toggled.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
