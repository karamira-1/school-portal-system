<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Contact Secretary – ASPEJ School</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --navy:#0F1F3D;--gold:#E6AC2C;--bg:#F5F4F0;--border:#E8E4DC;--muted:#7A7A7A; }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'DM Sans',sans-serif;background:var(--bg);color:#1a1a1a;min-height:100vh}
        nav{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:0 40px;height:64px;position:sticky;top:0;z-index:50}
        .nav-brand{font-family:'Playfair Display',serif;font-size:20px;font-weight:900;color:white;text-decoration:none}
        .nav-brand span{color:var(--gold)}
        .nav-links a{color:rgba(255,255,255,.65);text-decoration:none;font-size:14px;font-weight:500;margin-left:24px;transition:color .15s}
        .nav-links a:hover{color:white}
        .nav-btn{background:var(--gold);color:var(--navy);padding:8px 20px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none}

        .page-hero{background:linear-gradient(135deg,var(--navy),#2a3f6b);padding:56px 40px 44px;text-align:center;position:relative;overflow:hidden}
        .page-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 50% 60% at 30% 50%,rgba(230,172,44,.12) 0%,transparent 60%)}
        .hero-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(230,172,44,.15);border:1px solid rgba(230,172,44,.3);color:var(--gold);padding:6px 16px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;position:relative}
        .page-hero h1{font-family:'Playfair Display',serif;font-size:38px;font-weight:900;color:white;margin-bottom:10px;position:relative}
        .page-hero p{color:rgba(255,255,255,.6);font-size:15px;position:relative}

        .content{max-width:960px;margin:0 auto;padding:48px 24px;display:grid;grid-template-columns:1fr 380px;gap:40px;align-items:start}
        @media(max-width:768px){.content{grid-template-columns:1fr}.side-info{order:-1}}

        /* Form card */
        .form-card{background:white;border-radius:20px;padding:40px;box-shadow:0 4px 30px rgba(0,0,0,.07);border:1px solid var(--border)}
        .form-title{font-family:'Playfair Display',serif;font-size:24px;font-weight:700;color:var(--navy);margin-bottom:6px}
        .form-subtitle{color:var(--muted);font-size:14px;margin-bottom:28px}
        .field-group{margin-bottom:18px}
        .field-label{display:block;font-size:12px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px}
        .field-input{width:100%;padding:12px 14px;border:2px solid var(--border);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;color:#1a1a1a;outline:none;transition:border-color .15s;background:white}
        .field-input:focus{border-color:var(--navy)}
        .field-input::placeholder{color:#bbb}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .submit-btn{width:100%;padding:14px;background:linear-gradient(135deg,var(--navy),#2a3f6b);color:white;border:none;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:10px;margin-top:8px}
        .submit-btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(15,31,61,.25)}

        .success-msg{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:16px;border-radius:12px;font-size:14px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px}
        .error-msg{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:12px;font-size:14px;margin-bottom:20px}

        /* Side info */
        .side-info{display:flex;flex-direction:column;gap:16px}
        .info-card{background:white;border-radius:16px;padding:24px;border:1px solid var(--border)}
        .info-card h3{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--navy);margin-bottom:14px}
        .info-row{display:flex;align-items:flex-start;gap:12px;margin-bottom:12px;font-size:13px}
        .info-row:last-child{margin-bottom:0}
        .info-icon{width:32px;height:32px;border-radius:8px;background:#f0f4ff;color:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px}
        .info-row p{color:var(--muted);font-size:11px}
        .info-row strong{color:#1a1a1a;display:block;margin-bottom:2px}
        .note-box{background:#fffdf0;border:1px solid #fde68a;border-radius:12px;padding:16px;font-size:13px;color:#92400e}
        .note-box strong{display:block;margin-bottom:4px}
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/includes/db.php';
$pdo     = get_db();
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['sender_name']  ?? '');
    $email   = trim($_POST['sender_email'] ?? '');
    $phone   = trim($_POST['sender_phone'] ?? '');
    $subject = trim($_POST['subject']      ?? '');
    $message = trim($_POST['message']      ?? '');

    if (!$name || !$subject || !$message) {
        $error = 'Please fill in your name, subject, and message.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo->prepare('INSERT INTO secretary_messages (sender_name,sender_email,sender_phone,subject,message) VALUES (?,?,?,?,?)')
            ->execute([$name, $email?:null, $phone?:null, $subject, $message]);
        $success = true;
    }
}
?>
<nav>
    <a href="/index.php" class="nav-brand">🏫 ASPEJ <span>School</span></a>
    <div class="nav-links">
        <a href="/index.php">Home</a>
        <a href="/announcements.php">Announcements</a>
        <a href="/login.php" class="nav-btn">Portal Login</a>
    </div>
</nav>

<div class="page-hero">
    <div class="hero-badge"><i class="fas fa-envelope"></i> Contact Us</div>
    <h1>Message the School Secretary</h1>
    <p>No account required. Send your message and we'll get back to you.</p>
</div>

<div class="content">
    <div class="form-card">
        <h2 class="form-title">Send a Message</h2>
        <p class="form-subtitle">Fill in the form below. This goes directly to the school secretary.</p>

        <?php if ($success): ?>
        <div class="success-msg"><i class="fas fa-check-circle fa-lg mt-0.5"></i><div><strong>Message sent successfully!</strong><br>The school secretary will review your message and respond as soon as possible.</div></div>
        <?php elseif ($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <div class="grid-2">
                <div class="field-group">
                    <label class="field-label">Your Name <span style="color:red">*</span></label>
                    <input type="text" name="sender_name" class="field-input" placeholder="Full name" value="<?= htmlspecialchars($_POST['sender_name']??'') ?>" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="tel" name="sender_phone" class="field-input" placeholder="+250 7xx xxx xxx" value="<?= htmlspecialchars($_POST['sender_phone']??'') ?>">
                </div>
            </div>
            <div class="field-group">
                <label class="field-label">Email Address</label>
                <input type="email" name="sender_email" class="field-input" placeholder="your@email.com (optional)" value="<?= htmlspecialchars($_POST['sender_email']??'') ?>">
            </div>
            <div class="field-group">
                <label class="field-label">Subject <span style="color:red">*</span></label>
                <input type="text" name="subject" class="field-input" placeholder="e.g. Question about admission, fee payment…" value="<?= htmlspecialchars($_POST['subject']??'') ?>" required>
            </div>
            <div class="field-group">
                <label class="field-label">Your Message <span style="color:red">*</span></label>
                <textarea name="message" class="field-input" rows="5" placeholder="Write your message here…" required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Send Message</button>
        </form>
        <?php endif; ?>
    </div>

    <div class="side-info">
        <div class="info-card">
            <h3>School Contact</h3>
            <?php
            $tpl = $pdo->query('SELECT * FROM report_template WHERE id=1')->fetch();
            ?>
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-school"></i></div>
                <div><strong><?= htmlspecialchars($tpl['school_name']??'ASPEJ School') ?></strong></div>
            </div>
            <?php if ($tpl['school_address']??null): ?>
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div><strong><?= htmlspecialchars($tpl['school_address']) ?></strong><p>School Address</p></div>
            </div>
            <?php endif; ?>
            <?php if ($tpl['school_phone']??null): ?>
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div><strong><?= htmlspecialchars($tpl['school_phone']) ?></strong><p>Main Office</p></div>
            </div>
            <?php endif; ?>
            <?php if ($tpl['school_email']??null): ?>
            <div class="info-row">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div><strong><?= htmlspecialchars($tpl['school_email']) ?></strong><p>Email Address</p></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="note-box">
            <strong><i class="fas fa-info-circle mr-1"></i> No Account Needed</strong>
            This form is open to everyone — parents, guardians, and visitors. You do not need to log in or create an account to contact us.
        </div>
        <div class="info-card" style="text-align:center">
            <p style="font-size:13px;color:var(--muted);margin-bottom:12px">Are you a student looking to access your reports?</p>
            <a href="/login.php?role=student" style="display:inline-flex;align-items:center;gap:8px;background:var(--navy);color:white;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none"><i class="fas fa-user-graduate"></i> Student Portal Login</a>
        </div>
    </div>
</div>
</body>
</html>
