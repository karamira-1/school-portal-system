<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Announcements – ASPEJ School</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --navy:    #0F1F3D;
            --navy2:   #1D2A4D;
            --gold:    #E6AC2C;
            --gold2:   #FFC72C;
            --white:   #FAFAF8;
            --muted:   #7A7A7A;
            --border:  #E8E4DC;
            --bg:      #F5F4F0;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: #1a1a1a; }

        /* ── NAV ── */
        nav {
            background: var(--navy);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 40px; height: 64px;
            position: sticky; top: 0; z-index: 50;
        }
        .nav-brand {
            font-family: 'Playfair Display', serif;
            font-size: 20px; font-weight: 900; color: white;
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .nav-brand span { color: var(--gold); }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-links a { color: rgba(255,255,255,0.65); text-decoration: none; font-size: 14px; font-weight: 500; transition: color .15s; }
        .nav-links a:hover, .nav-links a.active { color: white; }
        .nav-btn {
            background: var(--gold); color: var(--navy);
            padding: 8px 20px; border-radius: 8px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; transition: all .15s;
        }
        .nav-btn:hover { background: var(--gold2); transform: translateY(-1px); }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, #2a3f6b 100%);
            padding: 64px 40px 48px;
            text-align: center;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 20% 50%, rgba(230,172,44,0.15) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 80% 50%, rgba(230,172,44,0.08) 0%, transparent 60%);
        }
        .hero-label {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(230,172,44,0.15); border: 1px solid rgba(230,172,44,0.3);
            color: var(--gold); padding: 6px 16px; border-radius: 100px;
            font-size: 12px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; margin-bottom: 18px; position: relative;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 44px; font-weight: 900; color: white;
            position: relative; line-height: 1.1; margin-bottom: 14px;
        }
        .hero h1 em { color: var(--gold); font-style: italic; }
        .hero p { color: rgba(255,255,255,0.6); font-size: 16px; position: relative; }

        /* ── Content ── */
        .content { max-width: 1100px; margin: 0 auto; padding: 48px 24px; }

        /* Filter bar */
        .filter-bar {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 32px; flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 18px; border-radius: 100px;
            border: 2px solid var(--border);
            background: white; cursor: pointer;
            font-size: 13px; font-weight: 600; color: var(--muted);
            transition: all .15s; font-family: 'DM Sans', sans-serif;
        }
        .filter-btn:hover { border-color: var(--navy); color: var(--navy); }
        .filter-btn.active { border-color: var(--navy); background: var(--navy); color: white; }

        /* ── Announcement Grid ── */
        .announce-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px,1fr));
            gap: 20px;
        }

        .announce-card {
            background: white; border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden; cursor: pointer;
            transition: all .2s;
            display: flex; flex-direction: column;
        }
        .announce-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.1);
            border-color: var(--navy);
        }
        .announce-card.pinned { border-color: var(--gold); border-width: 2px; }
        .card-cover {
            height: 160px;
            background: linear-gradient(135deg, var(--navy), #2a3f6b);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .card-cover img { width: 100%; height: 100%; object-fit: cover; }
        .card-cover-default {
            font-size: 48px; opacity: 0.3;
        }
        .pin-badge {
            position: absolute; top: 12px; right: 12px;
            background: var(--gold); color: var(--navy);
            padding: 4px 10px; border-radius: 100px;
            font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.08em;
        }
        .card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .card-date {
            font-size: 11px; color: var(--muted); font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 17px; font-weight: 700; color: var(--navy);
            line-height: 1.3; margin-bottom: 10px;
        }
        .card-excerpt { font-size: 13px; color: var(--muted); line-height: 1.6; flex: 1; }
        .card-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-author { font-size: 12px; color: var(--muted); }
        .has-file-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #eff6ff; color: #1d4ed8;
            padding: 4px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 600;
        }
        .read-more {
            font-size: 12px; color: var(--navy); font-weight: 700;
            display: flex; align-items: center; gap: 4px;
        }

        /* ── Popup Modal ── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: rgba(10,20,40,0.7); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            opacity: 0; pointer-events: none;
            transition: opacity .25s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal-box {
            background: white; border-radius: 20px;
            width: 100%; max-width: 700px; max-height: 90vh;
            overflow: hidden; display: flex; flex-direction: column;
            box-shadow: 0 40px 100px rgba(0,0,0,0.3);
            transform: translateY(30px) scale(0.97);
            transition: transform .25s;
        }
        .modal-overlay.open .modal-box { transform: translateY(0) scale(1); }
        .modal-header-img {
            height: 200px; background: linear-gradient(135deg, var(--navy), #2a3f6b);
            flex-shrink: 0; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-header-img img { width: 100%; height: 100%; object-fit: cover; }
        .modal-close-btn {
            position: absolute; top: 14px; right: 14px;
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(0,0,0,0.3); color: white;
            border: none; cursor: pointer; font-size: 16px;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .modal-close-btn:hover { background: rgba(0,0,0,0.6); }
        .modal-body { padding: 32px; overflow-y: auto; flex: 1; }
        .modal-date { font-size: 12px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px; }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 26px; font-weight: 700; color: var(--navy);
            line-height: 1.2; margin-bottom: 16px;
        }
        .modal-author { font-size: 13px; color: var(--muted); margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
        .modal-content {
            font-size: 15px; line-height: 1.8; color: #333;
        }
        .modal-content p { margin-bottom: 14px; }
        .modal-content strong { color: var(--navy); }
        .modal-content ul, .modal-content ol { margin: 10px 0 14px 20px; }
        .modal-content li { margin-bottom: 6px; }
        .modal-download {
            margin-top: 24px; padding: 16px 20px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
        }
        .modal-download .file-info { display: flex; align-items: center; gap: 12px; }
        .modal-download .file-icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: #1d4ed8; color: white;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
            flex-shrink: 0;
        }
        .modal-download .file-name { font-weight: 600; color: var(--navy); font-size: 14px; }
        .modal-download .file-size { font-size: 12px; color: var(--muted); }
        .download-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #1d4ed8; color: white;
            padding: 10px 20px; border-radius: 8px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; transition: all .15s;
            white-space: nowrap; flex-shrink: 0;
        }
        .download-btn:hover { background: #1e40af; transform: translateY(-1px); }

        /* ── Contact Secretary box ── */
        .secretary-cta {
            background: linear-gradient(135deg, var(--navy), #2a3f6b);
            border-radius: 20px; padding: 40px;
            text-align: center; margin-top: 48px;
            color: white;
        }
        .secretary-cta h3 {
            font-family: 'Playfair Display', serif;
            font-size: 26px; font-weight: 700; margin-bottom: 10px;
        }
        .secretary-cta p { color: rgba(255,255,255,0.65); font-size: 15px; margin-bottom: 24px; }
        .secretary-cta a {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--gold); color: var(--navy);
            padding: 13px 28px; border-radius: 10px;
            font-weight: 700; font-size: 15px; text-decoration: none;
            transition: all .15s;
        }
        .secretary-cta a:hover { background: var(--gold2); transform: translateY(-2px); }

        @media (max-width: 600px) {
            nav { padding: 0 20px; }
            .hero { padding: 40px 20px 32px; }
            .hero h1 { font-size: 30px; }
            .content { padding: 32px 16px; }
            .announce-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();

// Load all published announcements
$audience_filter = '';
$params = [];
$audience_filter = "is_published=1";
$anns = $pdo->query("
    SELECT a.*, u.full_name AS author_name
    FROM announcements a
    JOIN users u ON u.id=a.posted_by
    WHERE a.is_published=1
    ORDER BY a.is_pinned DESC, a.published_at DESC
")->fetchAll();
?>

<!-- Nav -->
<nav>
    <a href="/index.php" class="nav-brand">🏫 ASPEJ <span>School</span></a>
    <div class="nav-links">
        <a href="/index.php">Home</a>
        <a href="/about.php">About</a>
        <a href="/announcements.php" class="active">Announcements</a>
        <a href="/contact-secretary.php">Contact</a>
        <a href="/login.php" class="nav-btn">Portal Login</a>
    </div>
</nav>

<!-- Hero -->
<div class="hero">
    <div class="hero-label"><i class="fas fa-bullhorn"></i> School Announcements</div>
    <h1>Latest <em>News</em> &amp; Updates</h1>
    <p>Stay informed about school events, schedules, and important notices.</p>
</div>

<!-- Content -->
<div class="content">
    <!-- Filter buttons -->
    <div class="filter-bar">
        <button class="filter-btn active" onclick="filterAnn('all',this)">All</button>
        <button class="filter-btn" onclick="filterAnn('pinned',this)"><i class="fas fa-thumbtack mr-1"></i>Pinned</button>
        <button class="filter-btn" onclick="filterAnn('files',this)"><i class="fas fa-paperclip mr-1"></i>With Attachments</button>
    </div>

    <!-- Grid -->
    <div class="announce-grid" id="announceGrid">
    <?php foreach ($anns as $ann): ?>
    <div class="announce-card <?= $ann['is_pinned']?'pinned':'' ?>"
         data-pinned="<?= $ann['is_pinned']?'1':'0' ?>"
         data-hasfile="<?= $ann['file_path']?'1':'0' ?>"
         onclick="openAnnouncement(<?= $ann['id'] ?>)">
        <div class="card-cover">
            <?php if ($ann['cover_image']): ?>
            <img src="<?= htmlspecialchars($ann['cover_image']) ?>" alt="">
            <?php else: ?>
            <div class="card-cover-default">📢</div>
            <?php endif; ?>
            <?php if ($ann['is_pinned']): ?><span class="pin-badge"><i class="fas fa-thumbtack"></i> Pinned</span><?php endif; ?>
        </div>
        <div class="card-body">
            <div class="card-date"><?= date('F j, Y', strtotime($ann['published_at'])) ?></div>
            <h2 class="card-title"><?= htmlspecialchars($ann['title']) ?></h2>
            <p class="card-excerpt"><?= htmlspecialchars(mb_strimwidth($ann['excerpt'] ?: strip_tags($ann['body']), 0, 120, '…')) ?></p>
        </div>
        <div class="card-footer">
            <div>
                <div class="card-author">By <?= htmlspecialchars($ann['author_name']) ?></div>
                <?php if ($ann['file_path']): ?>
                <div class="has-file-badge mt-1"><i class="fas fa-paperclip"></i> Attachment</div>
                <?php endif; ?>
            </div>
            <span class="read-more">Read more <i class="fas fa-chevron-right"></i></span>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($anns)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--muted)">
        <i class="fas fa-bell-slash" style="font-size:48px;opacity:.3;margin-bottom:16px;display:block"></i>
        <p>No announcements yet. Check back soon.</p>
    </div>
    <?php endif; ?>
    </div>

    <!-- Secretary CTA -->
    <div class="secretary-cta">
        <h3>Have a question?</h3>
        <p>Send a message directly to the school secretary. No account required.</p>
        <a href="/contact-secretary.php"><i class="fas fa-envelope"></i> Message the Secretary</a>
    </div>
</div>

<!-- ── Announcement Modal ── -->
<div class="modal-overlay" id="annModal" onclick="handleModalClick(event)">
    <div class="modal-box" id="annModalBox">
        <div class="modal-header-img" id="modalCover">
            <div style="font-size:64px;opacity:.25">📢</div>
            <button class="modal-close-btn" onclick="closeAnnouncement()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-date" id="modalDate"></div>
            <h2 class="modal-title" id="modalTitle"></h2>
            <div class="modal-author" id="modalAuthor"></div>
            <div class="modal-content" id="modalContent"></div>
            <div class="modal-download" id="modalDownload" style="display:none">
                <div class="file-info">
                    <div class="file-icon"><i class="fas fa-file-download"></i></div>
                    <div>
                        <div class="file-name" id="downloadFileName"></div>
                        <div class="file-size" id="downloadFileSize"></div>
                    </div>
                </div>
                <a href="#" id="downloadLink" class="download-btn" download>
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Announcement data (embedded JSON)
const announcements = <?= json_encode(array_map(fn($a) => [
    'id'          => $a['id'],
    'title'       => $a['title'],
    'body'        => $a['body'],
    'cover_image' => $a['cover_image'],
    'file_path'   => $a['file_path'],
    'file_name'   => $a['file_name'],
    'file_size'   => $a['file_size'],
    'author'      => $a['author_name'],
    'date'        => date('F j, Y', strtotime($a['published_at'])),
    'pinned'      => (bool)$a['is_pinned'],
], $anns), JSON_HEX_TAG) ?>;

function openAnnouncement(id) {
    const ann = announcements.find(a => a.id === id);
    if (!ann) return;

    // Cover
    const cover = document.getElementById('modalCover');
    if (ann.cover_image) {
        cover.innerHTML = `<img src="${ann.cover_image}" alt=""><button class="modal-close-btn" onclick="closeAnnouncement()"><i class="fas fa-times"></i></button>`;
    } else {
        cover.innerHTML = `<div style="font-size:64px;opacity:.25">📢</div><button class="modal-close-btn" onclick="closeAnnouncement()"><i class="fas fa-times"></i></button>`;
    }

    document.getElementById('modalDate').textContent = ann.date;
    document.getElementById('modalTitle').textContent = ann.title;
    document.getElementById('modalAuthor').textContent = `Posted by ${ann.author}`;
    document.getElementById('modalContent').innerHTML = ann.body;

    // Download
    const dlBox  = document.getElementById('modalDownload');
    const dlLink = document.getElementById('downloadLink');
    if (ann.file_path) {
        document.getElementById('downloadFileName').textContent = ann.file_name || 'Download Attachment';
        const size = ann.file_size ? formatBytes(ann.file_size) : '';
        document.getElementById('downloadFileSize').textContent = size;
        dlLink.href = ann.file_path;
        dlLink.download = ann.file_name || 'file';
        dlBox.style.display = 'flex';
    } else {
        dlBox.style.display = 'none';
    }

    document.getElementById('annModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeAnnouncement() {
    document.getElementById('annModal').classList.remove('open');
    document.body.style.overflow = '';
}
function handleModalClick(e) {
    if (e.target === e.currentTarget) closeAnnouncement();
}
document.addEventListener('keydown', e => { if (e.key==='Escape') closeAnnouncement(); });

function formatBytes(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

function filterAnn(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.announce-card').forEach(card => {
        if (type === 'all')    card.style.display = '';
        else if (type === 'pinned')  card.style.display = card.dataset.pinned === '1' ? '' : 'none';
        else if (type === 'files')   card.style.display = card.dataset.hasfile === '1' ? '' : 'none';
    });
}
</script>
</body>
</html>
