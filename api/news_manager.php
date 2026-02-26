<?php
// ============================================================
// api/news_manager.php  –  Admin: Add / Edit / Delete news
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user || !can('manage_news', $user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
}

$pdo    = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $title   = trim($_POST['title']        ?? '');
    $summary = trim($_POST['summary']      ?? '');
    $type    = trim($_POST['type']         ?? 'News');
    $image   = trim($_POST['image']        ?? 'assets/images/default-news.jpg');
    $link    = trim($_POST['link']         ?? '#');
    $date    = trim($_POST['published_at'] ?? date('Y-m-d'));

    if (!$title || !$summary) { echo json_encode(['success'=>false,'message'=>'Title and summary required.']); exit; }
    if (!in_array($type,['News','Event','Announcement'])) $type='News';

    $pdo->prepare('INSERT INTO news_events (type,title,summary,image,link,published_at) VALUES (?,?,?,?,?,?)')
        ->execute([$type,$title,$summary,$image,$link,$date]);
    echo json_encode(['success'=>true,'message'=>'News item published.','id'=>$pdo->lastInsertId()]);

} elseif ($action === 'update') {
    $id      = (int)($_POST['id']          ?? 0);
    $title   = trim($_POST['title']        ?? '');
    $summary = trim($_POST['summary']      ?? '');
    $type    = trim($_POST['type']         ?? 'News');
    $image   = trim($_POST['image']        ?? '');
    $link    = trim($_POST['link']         ?? '#');
    $date    = trim($_POST['published_at'] ?? '');

    if (!$id || !$title || !$summary || !$date) { echo json_encode(['success'=>false,'message'=>'Missing fields.']); exit; }

    $pdo->prepare('UPDATE news_events SET type=?,title=?,summary=?,image=?,link=?,published_at=? WHERE id=?')
        ->execute([$type,$title,$summary,$image,$link,$date,$id]);
    echo json_encode(['success'=>true,'message'=>'News item updated.']);

} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required.']); exit; }
    $pdo->prepare('DELETE FROM news_events WHERE id=?')->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Item deleted.']);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
