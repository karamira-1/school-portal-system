<?php
// ============================================================
// api/chat_manager.php  –  Send message / broadcast / poll
// action: 'send' | 'poll' | 'broadcast'
// PHP long-polling: poll returns new messages since last_id
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

$user = auth_user();
if (!$user) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorised.']); exit; }

$pdo    = get_db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $message     = trim($_POST['message']      ?? '');
    if (!$message || !$receiver_id) {
        echo json_encode(['success'=>false,'message'=>'Message and receiver required.']); exit;
    }
    $message = mb_substr($message, 0, 2000);

    // Verify receiver exists
    $recv = $pdo->prepare('SELECT id FROM users WHERE id=? AND is_active=1');
    $recv->execute([$receiver_id]);
    if (!$recv->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Receiver not found.']); exit;
    }

    $pdo->prepare('INSERT INTO chat_messages (sender_id, receiver_id, channel, message) VALUES (?,?,?,?)')
        ->execute([$user['id'], $receiver_id, 'direct', $message]);

    $new_id = $pdo->lastInsertId();
    echo json_encode([
        'success'  => true,
        'message'  => $message,
        'id'       => $new_id,
        'sent_at'  => date('H:i'),
        'sender'   => $user['full_name'],
    ]);

} elseif ($action === 'poll') {
    // PHP polling: client sends last_id, we return messages newer than that
    $peer_id  = (int)($_GET['peer_id']  ?? 0);
    $last_id  = (int)($_GET['last_id']  ?? 0);
    if (!$peer_id) { echo json_encode(['success'=>false,'messages'=>[]]); exit; }

    $stmt = $pdo->prepare("
        SELECT m.id, m.message, m.sender_id, m.created_at,
               u.full_name AS sender_name, u.profile_image AS sender_img
        FROM   chat_messages m JOIN users u ON u.id=m.sender_id
        WHERE  m.id > ?
               AND ((m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?))
        ORDER  BY m.id ASC LIMIT 50
    ");
    $stmt->execute([$last_id, $user['id'],$peer_id, $peer_id,$user['id']]);
    $msgs = $stmt->fetchAll();

    // Mark as read
    $pdo->prepare('UPDATE chat_messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0')
        ->execute([$peer_id, $user['id']]);

    echo json_encode(['success'=>true,'messages'=>$msgs,'my_id'=>$user['id']]);

} elseif ($action === 'broadcast') {
    if (!can('send_broadcast', $user)) {
        http_response_code(403); echo json_encode(['success'=>false,'message'=>'Forbidden.']); exit;
    }
    $group   = trim($_POST['group']   ?? 'all_teachers');
    $message = trim($_POST['message'] ?? '');
    if (!$message) { echo json_encode(['success'=>false,'message'=>'Message required.']); exit; }

    $role_map = [
        'all_teachers' => ['teacher'],
        'all_parents'  => ['parent'],
        'all_students' => ['student'],
        'everyone'     => ['teacher','parent','student'],
    ];
    $roles = $role_map[$group] ?? ['teacher'];

    // Get recipients
    $placeholders = implode(',', array_fill(0, count($roles), '?'));
    $recipients = $pdo->prepare("
        SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id
        WHERE r.name IN ($placeholders) AND u.is_active=1
    ");
    $recipients->execute($roles);

    $insert = $pdo->prepare('INSERT INTO chat_messages (sender_id, receiver_id, channel, message, broadcast_group) VALUES (?,?,?,?,?)');
    $notif  = $pdo->prepare('INSERT INTO notifications (user_id, title, body, type, link) VALUES (?,?,?,?,?)');
    $count  = 0;

    foreach ($recipients->fetchAll(PDO::FETCH_COLUMN) as $rid) {
        $insert->execute([$user['id'], $rid, 'broadcast', $message, $group]);
        $notif->execute([$rid, 'Broadcast Message from Admin', mb_substr($message,0,100), 'info', '/portal-'.($user['role_name']==='admin'?'student':'teacher').'.php?section=chat']);
        $count++;
    }

    echo json_encode(['success'=>true,'message'=>"Broadcast sent to $count recipients.",'count'=>$count]);

} else {
    echo json_encode(['success'=>false,'message'=>'Unknown action.']);
}
