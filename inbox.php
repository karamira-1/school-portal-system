<?php
// ============================================================
// includes/inbox.php  –  Universal Inbox (all roles)
// Usage: include this file in any portal for the inbox panel
// Required: $current_user array from auth_user()
// ============================================================
if (!isset($current_user)) return;
$pdo    = get_db();
$in_sec = $_GET['inbox'] ?? '';
$uid    = $current_user['id'];

// Get all users this person can message (everyone except themselves)
$all_users = $pdo->query("
    SELECT u.id, u.full_name, r.label AS role_label, r.name AS role_name
    FROM users u JOIN roles r ON r.id=u.role_id
    WHERE u.is_active=1 AND u.id != $uid
    ORDER BY r.name, u.full_name
")->fetchAll();

// Inbox messages (received)
$inbox = $pdo->prepare("
    SELECT m.*, u.full_name AS sender_name, r.name AS sender_role
    FROM inbox_messages m
    JOIN users u ON u.id=m.sender_id
    JOIN roles r ON r.id=u.role_id
    WHERE m.receiver_id=? AND m.parent_id IS NULL
    ORDER BY m.sent_at DESC
    LIMIT 50
");
$inbox->execute([$uid]);
$inbox_msgs = $inbox->fetchAll();

// Unread count
$unread = array_filter($inbox_msgs, fn($m)=>!$m['is_read']);

// Active message thread
$active_msg = null;
$thread = [];
if ($in_sec && is_numeric($in_sec)) {
    $am = $pdo->prepare("SELECT m.*, u.full_name AS sender_name FROM inbox_messages m JOIN users u ON u.id=m.sender_id WHERE m.id=? AND (m.receiver_id=? OR m.sender_id=?)");
    $am->execute([(int)$in_sec, $uid, $uid]);
    $active_msg = $am->fetch();
    if ($active_msg) {
        // Mark as read
        if ($active_msg['receiver_id'] == $uid && !$active_msg['is_read']) {
            $pdo->prepare('UPDATE inbox_messages SET is_read=1, read_at=NOW() WHERE id=?')->execute([$active_msg['id']]);
        }
        // Load replies
        $tr = $pdo->prepare("SELECT m.*, u.full_name AS sender_name FROM inbox_messages m JOIN users u ON u.id=m.sender_id WHERE m.parent_id=? ORDER BY m.sent_at ASC");
        $tr->execute([$active_msg['id']]);
        $thread = $tr->fetchAll();
    }
}

// Handle send
$inbox_msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['inbox_send'])) {
    $to       = (int)($_POST['to_user_id'] ?? 0);
    $subject  = trim($_POST['msg_subject'] ?? '');
    $body     = trim($_POST['msg_body']    ?? '');
    $parent_id= (int)($_POST['parent_id'] ?? 0) ?: null;
    if ($to && $body) {
        $pdo->prepare('INSERT INTO inbox_messages (sender_id,receiver_id,subject,body,parent_id) VALUES (?,?,?,?,?)')
            ->execute([$uid,$to,$subject?:'(no subject)',$body,$parent_id]);
        $inbox_msg = 'sent';
        // Reload
        header("Location: ?".http_build_query(array_merge($_GET,['inbox'=>$in_sec??'sent'])));
        exit;
    }
}
?>
<!-- ============================================================
     INBOX PANEL (embed in portal page body)
     Add this to any section === 'inbox' block
============================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 h-full" style="min-height:70vh">

    <!-- Left: message list -->
    <div class="lg:col-span-1 admin-card !p-0 flex flex-col overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-aspej-navy dark:text-white flex items-center gap-2">
                <i class="fas fa-inbox"></i> Inbox
                <?php if (count($unread)): ?>
                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($unread) ?></span>
                <?php endif; ?>
            </h3>
            <button onclick="openModal('composeModal')" class="btn-gold text-xs px-3 py-1.5 flex items-center gap-1">
                <i class="fas fa-pencil-alt"></i> Compose
            </button>
        </div>
        <div class="overflow-y-auto flex-1">
        <?php if (empty($inbox_msgs)): ?>
        <div class="text-center text-gray-400 py-12 text-sm"><i class="fas fa-inbox text-4xl opacity-30 block mb-3"></i>No messages yet.</div>
        <?php endif; ?>
        <?php foreach ($inbox_msgs as $m): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['inbox'=>$m['id']])) ?>"
           class="flex items-start gap-3 px-4 py-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition
                  <?= ($active_msg && $active_msg['id']==$m['id'])? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-500':'' ?>
                  <?= !$m['is_read']?'bg-yellow-50/60 dark:bg-yellow-900/10':'' ?>
                  no-underline text-gray-700 dark:text-gray-300">
            <div class="w-9 h-9 rounded-full bg-aspej-navy text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                <?= strtoupper(substr($m['sender_name'],0,1)) ?>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-sm truncate <?= !$m['is_read']?'text-aspej-navy dark:text-white':'' ?>"><?= htmlspecialchars($m['sender_name']) ?></p>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2"><?= date('M j', strtotime($m['sent_at'])) ?></span>
                </div>
                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($m['subject']) ?></p>
                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars(mb_substr(strip_tags($m['body']),0,60)) ?>…</p>
            </div>
            <?php if (!$m['is_read']): ?><div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div><?php endif; ?>
        </a>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: thread or empty -->
    <div class="lg:col-span-2 admin-card flex flex-col" style="min-height:500px">
    <?php if ($active_msg): ?>
        <div class="border-b dark:border-gray-700 pb-4 mb-4">
            <h2 class="font-bold text-lg text-aspej-navy dark:text-white"><?= htmlspecialchars($active_msg['subject']) ?></h2>
            <p class="text-sm text-gray-500">From: <strong><?= htmlspecialchars($active_msg['sender_name']) ?></strong> · <?= date('M j, Y g:i A', strtotime($active_msg['sent_at'])) ?></p>
        </div>

        <!-- Messages in thread -->
        <div class="flex-1 overflow-y-auto space-y-4 mb-5">
            <?php
            $all_thread = array_merge([$active_msg], $thread);
            foreach ($all_thread as $tm):
                $is_mine = ($tm['sender_id'] == $uid);
            ?>
            <div class="flex <?= $is_mine?'justify-end':'' ?> gap-3">
                <?php if (!$is_mine): ?>
                <div class="w-8 h-8 rounded-full bg-aspej-navy text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                    <?= strtoupper(substr($tm['sender_name'],0,1)) ?>
                </div>
                <?php endif; ?>
                <div class="max-w-sm">
                    <div class="<?= $is_mine?'bg-aspej-navy text-white':'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100' ?> rounded-2xl px-4 py-3 text-sm leading-relaxed">
                        <?= nl2br(htmlspecialchars($tm['body'])) ?>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 <?= $is_mine?'text-right':'' ?>"><?= date('M j g:i A', strtotime($tm['sent_at'])) ?></p>
                </div>
                <?php if ($is_mine): ?>
                <div class="w-8 h-8 rounded-full bg-aspej-gold text-aspej-navy flex items-center justify-center text-sm font-bold flex-shrink-0">
                    <?= strtoupper(substr($current_user['full_name'],0,1)) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply form -->
        <form method="POST" class="border-t dark:border-gray-700 pt-4">
            <input type="hidden" name="inbox_send" value="1">
            <input type="hidden" name="to_user_id" value="<?= $active_msg['sender_id'] == $uid ? $active_msg['receiver_id'] : $active_msg['sender_id'] ?>">
            <input type="hidden" name="parent_id" value="<?= $active_msg['id'] ?>">
            <div class="flex gap-3">
                <textarea name="msg_body" rows="2" placeholder="Type a reply…" required
                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm resize-none dark:bg-gray-800 dark:text-white focus:outline-none focus:border-aspej-navy"></textarea>
                <button type="submit" class="btn-gold px-5 self-end">Send</button>
            </div>
        </form>

    <?php else: ?>
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <i class="fas fa-comments text-6xl opacity-20 mb-4"></i>
            <p class="font-medium">Select a message to read</p>
            <p class="text-sm mt-1 mb-5">or compose a new message</p>
            <button onclick="openModal('composeModal')" class="btn-gold px-6"><i class="fas fa-pencil-alt mr-2"></i>New Message</button>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Compose Modal -->
<div id="composeModal" class="modal-overlay hidden">
    <div class="modal-box max-w-lg">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">New Message</h3>
            <button onclick="closeModal('composeModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" class="space-y-4 mt-4">
            <input type="hidden" name="inbox_send" value="1">
            <div>
                <label class="form-label">To</label>
                <select name="to_user_id" required class="form-control">
                    <option value="">— Select recipient —</option>
                    <?php
                    $grouped = [];
                    foreach ($all_users as $u) $grouped[$u['role_label']][] = $u;
                    foreach ($grouped as $rl => $users):
                    ?>
                    <optgroup label="<?= htmlspecialchars($rl) ?>">
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Subject</label>
                <input type="text" name="msg_subject" class="form-control" placeholder="What's this about?">
            </div>
            <div>
                <label class="form-label">Message <span class="text-red-500">*</span></label>
                <textarea name="msg_body" rows="5" required class="form-control" placeholder="Type your message…"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('composeModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold"><i class="fas fa-paper-plane mr-2"></i>Send Message</button>
            </div>
        </form>
    </div>
</div>
