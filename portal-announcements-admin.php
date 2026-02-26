<?php
// ============================================================
// Section to embed in any staff portal: "Announcements"
// Embeds in portal-admin.php, portal-master.php, portal-dos.php
// ============================================================
require_once __DIR__ . '/includes/auth.php';
$current_user = require_role(['admin','master','director_studies','librarian']);
$pdo = get_db();

$anns = $pdo->query("
    SELECT a.*, u.full_name AS author_name
    FROM announcements a JOIN users u ON u.id=a.posted_by
    ORDER BY a.is_pinned DESC, a.published_at DESC
")->fetchAll();
?>
<!-- Include TinyMCE for rich text body -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#announcementBody,#editAnnBody',
    plugins: 'lists link image',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat',
    height: 260,
    menubar: false,
    skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
    content_css: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
});
</script>

<div class="admin-card mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="section-heading !mb-0">Announcements</h3>
        <button onclick="openModal('addAnnouncementModal')" class="btn-gold text-sm px-4">
            <i class="fas fa-plus mr-2"></i>Post Announcement
        </button>
    </div>
    <div class="overflow-x-auto">
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Audience</th><th>By</th><th>Date</th><th>Status</th><th>File</th><th class="text-right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($anns as $a): ?>
        <tr>
            <td class="font-medium flex items-center gap-2">
                <?php if ($a['is_pinned']): ?><span class="text-yellow-500 text-xs" title="Pinned"><i class="fas fa-thumbtack"></i></span><?php endif; ?>
                <?= htmlspecialchars(mb_strimwidth($a['title'],0,60,'…')) ?>
            </td>
            <td><span class="badge badge-blue text-xs"><?= ucfirst($a['audience']) ?></span></td>
            <td class="text-sm text-gray-500"><?= htmlspecialchars($a['author_name']) ?></td>
            <td class="text-xs text-gray-400"><?= date('M j, Y', strtotime($a['published_at'])) ?></td>
            <td><span class="badge <?= $a['is_published']?'badge-green':'badge-gray' ?>"><?= $a['is_published']?'Published':'Draft' ?></span></td>
            <td class="text-xs"><?= $a['file_path']?'<span class="text-blue-500"><i class="fas fa-paperclip"></i></span>':'—' ?></td>
            <td class="text-right space-x-1">
                <button onclick="editAnn(<?= htmlspecialchars(json_encode($a)) ?>)" class="text-blue-500 hover:text-blue-700 text-sm"><i class="fas fa-edit"></i></button>
                <button onclick="togglePublish(<?= $a['id'] ?>)" title="<?= $a['is_published']?'Unpublish':'Publish' ?>" class="text-<?= $a['is_published']?'orange':'green' ?>-500 text-sm"><i class="fas fa-<?= $a['is_published']?'eye-slash':'eye' ?>"></i></button>
                <button onclick="pinAnn(<?= $a['id'] ?>)" title="<?= $a['is_pinned']?'Unpin':'Pin' ?>" class="text-yellow-500 text-sm"><i class="fas fa-thumbtack"></i></button>
                <button onclick="deleteAnn(<?= $a['id'] ?>)" class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($anns)): ?><tr><td colspan="7" class="text-center text-gray-400 py-8">No announcements yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="modal-overlay hidden">
    <div class="modal-box max-w-2xl">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Post New Announcement</h3>
            <button onclick="closeModal('addAnnouncementModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="addAnnForm" enctype="multipart/form-data" class="space-y-4 mt-4">
            <input type="hidden" name="action" value="create">
            <div><label class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="form-control" placeholder="Announcement title"></div>
            <div><label class="form-label">Body <span class="text-red-500">*</span></label>
                <textarea id="announcementBody" name="body" rows="5" class="form-control"></textarea></div>
            <div><label class="form-label">Short Excerpt (optional)</label>
                <input type="text" name="excerpt" class="form-control" placeholder="Brief preview shown in the card"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Audience</label>
                    <select name="audience" class="form-control">
                        <option value="all">Everyone</option>
                        <option value="students">Students Only</option>
                        <option value="staff">Staff Only</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_pinned" value="1" id="addPinned" class="w-4 h-4">
                    <label for="addPinned" class="form-label !mb-0 cursor-pointer">Pin to top</label>
                </div>
            </div>
            <div><label class="form-label">Cover Image (optional)</label>
                <input type="file" name="cover_image" accept="image/*" class="text-sm text-gray-600 dark:text-gray-400"></div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                <label class="form-label text-blue-700 dark:text-blue-300"><i class="fas fa-paperclip mr-1"></i>Downloadable Attachment (optional)</label>
                <input type="file" name="attach_file" class="text-sm text-gray-600 dark:text-gray-400 block mt-1">
                <p class="text-xs text-blue-500 mt-1">PDF, Word, Excel, ZIP etc. Max 20MB.</p>
            </div>
            <div id="addAnnMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('addAnnouncementModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold"><i class="fas fa-bullhorn mr-2"></i>Publish</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="modal-overlay hidden">
    <div class="modal-box max-w-2xl">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-aspej-navy dark:text-white">Edit Announcement</h3>
            <button onclick="closeModal('editAnnouncementModal')" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
        </div>
        <form id="editAnnForm" enctype="multipart/form-data" class="space-y-4 mt-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editAnnId">
            <div><label class="form-label">Title</label><input type="text" name="title" id="editAnnTitle" required class="form-control"></div>
            <div><label class="form-label">Body</label><textarea id="editAnnBody" name="body" rows="5" class="form-control"></textarea></div>
            <div><label class="form-label">Excerpt</label><input type="text" name="excerpt" id="editAnnExcerpt" class="form-control"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">Audience</label>
                    <select name="audience" id="editAnnAudience" class="form-control">
                        <option value="all">Everyone</option><option value="students">Students Only</option><option value="staff">Staff Only</option>
                    </select></div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_pinned" value="1" id="editAnnPinned" class="w-4 h-4">
                    <label for="editAnnPinned" class="form-label !mb-0 cursor-pointer">Pin to top</label>
                </div>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                <label class="form-label text-blue-700 dark:text-blue-300"><i class="fas fa-paperclip mr-1"></i>Replace Attachment (optional)</label>
                <input type="file" name="attach_file" class="text-sm text-gray-600 dark:text-gray-400 block mt-1">
            </div>
            <div id="editAnnMsg" class="text-sm hidden"></div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('editAnnouncementModal')" class="btn-gray">Cancel</button>
                <button type="submit" class="btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editAnn(a) {
    document.getElementById('editAnnId').value       = a.id;
    document.getElementById('editAnnTitle').value    = a.title;
    document.getElementById('editAnnExcerpt').value  = a.excerpt||'';
    document.getElementById('editAnnAudience').value = a.audience;
    document.getElementById('editAnnPinned').checked = !!a.is_pinned;
    if (tinymce.get('editAnnBody')) tinymce.get('editAnnBody').setContent(a.body||'');
    openModal('editAnnouncementModal');
}

async function submitAnnForm(formId, msgId, modalId) {
    const form = document.getElementById(formId);
    if (tinymce.get('announcementBody'))
        document.querySelector('#addAnnForm [name=body]').value = tinymce.get('announcementBody').getContent();
    if (tinymce.get('editAnnBody'))
        document.querySelector('#editAnnForm [name=body]').value = tinymce.get('editAnnBody').getContent();
    const msg  = document.getElementById(msgId);
    const fd   = new FormData(form);
    const r    = await fetch('/api/announcements_manager.php',{method:'POST',body:fd});
    const d    = await r.json();
    msg.classList.remove('hidden','text-red-500','text-green-600');
    msg.classList.add(d.success?'text-green-600':'text-red-500');
    msg.textContent = d.message;
    if (d.success) setTimeout(()=>location.reload(),1200);
}
document.getElementById('addAnnForm')?.addEventListener('submit', e=>{ e.preventDefault(); submitAnnForm('addAnnForm','addAnnMsg','addAnnouncementModal'); });
document.getElementById('editAnnForm')?.addEventListener('submit', e=>{ e.preventDefault(); submitAnnForm('editAnnForm','editAnnMsg','editAnnouncementModal'); });

async function togglePublish(id) {
    const fd=new FormData(); fd.append('action','toggle_publish'); fd.append('id',id);
    await fetch('/api/announcements_manager.php',{method:'POST',body:fd});
    location.reload();
}
async function pinAnn(id) {
    const fd=new FormData(); fd.append('action','pin'); fd.append('id',id);
    await fetch('/api/announcements_manager.php',{method:'POST',body:fd});
    location.reload();
}
async function deleteAnn(id) {
    if (!confirm('Delete this announcement?')) return;
    const fd=new FormData(); fd.append('action','delete'); fd.append('id',id);
    await fetch('/api/announcements_manager.php',{method:'POST',body:fd});
    location.reload();
}
</script>
