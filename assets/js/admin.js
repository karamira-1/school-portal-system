// ============================================================
// assets/js/admin.js  –  Admin & Teacher Portal JavaScript
// ============================================================

// ── Dark mode ─────────────────────────────────────────────
(function() {
    if (localStorage.theme === 'dark') document.documentElement.classList.add('dark');
    document.getElementById('theme-toggle')?.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    });
})();

// ── Sidebar mobile toggle ─────────────────────────────────
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
});

// ============================================================
// MODAL HELPERS
// ============================================================
function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.add('hidden');
    }
});

// ============================================================
// USER MANAGEMENT
// ============================================================
function openEditUser(user) {
    document.getElementById('editUserId').value   = user.id;
    document.getElementById('editFullName').value = user.full_name;
    document.getElementById('editEmail').value    = user.email;
    document.getElementById('editPhone').value    = user.phone || '';
    document.getElementById('editRole').value     = user.role_name;
    openModal('editUserModal');
}

async function toggleUser(userId, currentStatus) {
    const action = currentStatus ? 'Deactivate' : 'Activate';
    if (!confirm(`${action} this user?`)) return;
    const fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('user_id', userId);
    fd.append('is_active', currentStatus);
    const res  = await fetch('/api/user_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}

document.getElementById('addUserForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('addUserError');
    errEl.classList.add('hidden');
    const fd = new FormData(e.target);
    fd.append('action','create');
    const res  = await fetch('/api/user_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) { location.reload(); }
    else { errEl.textContent = data.message; errEl.classList.remove('hidden'); }
});

document.getElementById('editUserForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('editUserError');
    errEl.classList.add('hidden');
    const fd = new FormData(e.target);
    fd.append('action','update');
    const res  = await fetch('/api/user_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) { location.reload(); }
    else { errEl.textContent = data.message; errEl.classList.remove('hidden'); }
});

// ============================================================
// NEWS CMS
// ============================================================
function openEditNews(item) {
    document.getElementById('editNewsId').value      = item.id;
    document.getElementById('editNewsTitle').value   = item.title;
    document.getElementById('editNewsType').value    = item.type;
    document.getElementById('editNewsDate').value    = item.published_at;
    document.getElementById('editNewsSummary').value = item.summary;
    document.getElementById('editNewsImage').value   = item.image || '';
    document.getElementById('editNewsLink').value    = item.link || '';
    openModal('editNewsModal');
}

async function deleteNews(id) {
    if (!confirm('Delete this news item? This cannot be undone.')) return;
    const fd = new FormData();
    fd.append('action','delete');
    fd.append('id', id);
    const res  = await fetch('/api/news_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}

document.getElementById('addNewsForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('addNewsError');
    errEl.classList.add('hidden');
    const fd = new FormData(e.target);
    fd.append('action','create');
    const res  = await fetch('/api/news_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) { closeModal('addNewsModal'); location.reload(); }
    else { errEl.textContent = data.message; errEl.classList.remove('hidden'); }
});

document.getElementById('editNewsForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errEl = document.getElementById('editNewsError');
    errEl.classList.add('hidden');
    const fd = new FormData(e.target);
    fd.append('action','update');
    const res  = await fetch('/api/news_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) { closeModal('editNewsModal'); location.reload(); }
    else { errEl.textContent = data.message; errEl.classList.remove('hidden'); }
});

// ============================================================
// FEE PAYMENTS
// ============================================================
async function markFeePaid(feeId) {
    const method = prompt('Payment method? (cash / bank_transfer / mobile_money / card)', 'cash');
    if (!method) return;
    const ref    = prompt('Payment reference number (optional):') || '';
    const fd = new FormData();
    fd.append('action','mark_paid');
    fd.append('fee_id', feeId);
    fd.append('method', method.trim());
    fd.append('reference', ref.trim());
    const res  = await fetch('/api/fee_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}

// ============================================================
// ALERT MANAGEMENT
// ============================================================
async function resolveAlert(alertId) {
    const fd = new FormData();
    fd.append('action','resolve');
    fd.append('id', alertId);
    const res  = await fetch('/api/alert_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}

async function runAlertScan() {
    const btn = document.querySelector('[onclick="runAlertScan()"]');
    if (btn) { btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i> Scanning…'; }
    const fd = new FormData();
    fd.append('action','scan');
    const res  = await fetch('/api/alert_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    alert(data.message);
    location.reload();
}

// ============================================================
// BROADCAST FORM
// ============================================================
document.getElementById('broadcastForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action','broadcast');
    const res  = await fetch('/api/chat_manager.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) { closeModal('broadcastModal'); alert(data.message); }
    else alert(data.message);
});

// ============================================================
// CHAT – Send message + PHP Polling
// ============================================================
const sendForm    = document.getElementById('sendMsgForm');
const chatWindow  = document.getElementById('chatMessages');
let   lastMsgId   = 0;
let   pollInterval = null;

// Get last rendered message id from DOM
if (chatWindow) {
    const msgs = chatWindow.querySelectorAll('[data-msg-id]');
    if (msgs.length) lastMsgId = parseInt(msgs[msgs.length-1].dataset.msgId) || 0;
}

if (sendForm) {
    sendForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input    = document.getElementById('msgInput');
        const message  = input.value.trim();
        const receiver = sendForm.dataset.to;
        if (!message || !receiver) return;

        input.value = '';
        const fd = new FormData();
        fd.append('action','send');
        fd.append('receiver_id', receiver);
        fd.append('message', message);

        const res  = await fetch('/api/chat_manager.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success) {
            appendMessage({ message, sender_id: 'me', created_at: data.sent_at, id: data.id });
            lastMsgId = data.id;
        }
    });

    // Start polling every 4 seconds
    const peerId = sendForm.dataset.to;
    if (peerId) {
        pollInterval = setInterval(() => pollMessages(peerId), 4000);
        // Scroll to bottom on load
        scrollChatDown();
    }
}

async function pollMessages(peerId) {
    try {
        const res  = await fetch(`/api/chat_manager.php?action=poll&peer_id=${peerId}&last_id=${lastMsgId}`);
        const data = await res.json();
        if (data.success && data.messages.length > 0) {
            data.messages.forEach(m => {
                if (m.sender_id != data.my_id) {
                    appendMessage(m);
                    lastMsgId = m.id;
                }
            });
        }
    } catch(e) { /* network error, silent */ }
}

function appendMessage(m) {
    if (!chatWindow) return;
    const mine = m.sender_id === 'me';
    const time = m.created_at?.length <= 5 ? m.created_at : new Date(m.created_at).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    const div  = document.createElement('div');
    div.setAttribute('data-msg-id', m.id || '');
    div.className = `flex ${mine ? 'justify-end' : 'justify-start'}`;
    div.innerHTML = `
        <div class="max-w-[70%]">
            <div class="${mine ? 'bg-aspej-navy text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'} rounded-2xl px-4 py-3 text-sm">
                ${escHtml(m.message)}
            </div>
            <p class="text-xs text-gray-400 mt-1 ${mine ? 'text-right' : ''}">${time}</p>
        </div>
    `;
    chatWindow.appendChild(div);
    scrollChatDown();
}

function scrollChatDown() {
    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Cleanup interval on page unload
window.addEventListener('beforeunload', () => { if (pollInterval) clearInterval(pollInterval); });

// ============================================================
// SCROLL CHAT DOWN ON LOAD
// ============================================================
document.addEventListener('DOMContentLoaded', scrollChatDown);
