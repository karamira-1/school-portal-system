<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ASPEJ School Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════
   GLOBAL RESET & VARIABLES
═══════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:       #0E1B2E;
  --ink-2:     #1D3557;
  --gold:      #C9963A;
  --gold-lt:   #F0C87A;
  --cream:     #FDFAF4;
  --cream-2:   #F5EFE0;
  --stone:     #8B8070;
  --border:    #E2D9CC;
  --white:     #FFFFFF;
  --success:   #2D7D46;
  --danger:    #B91C1C;
  --shadow-sm: 0 2px 8px rgba(14,27,46,.08);
  --shadow-md: 0 8px 32px rgba(14,27,46,.12);
  --shadow-lg: 0 24px 64px rgba(14,27,46,.18);
  --r:         12px;
  --r-lg:      20px;
}

html, body {
  height: 100%;
  font-family: 'Outfit', sans-serif;
  font-size: 14px;
  color: var(--ink);
  background: var(--cream);
  overflow-x: hidden;
}

/* ═══ PAGES ═══ */
.page { display: none; min-height: 100vh; }
.page.active { display: block; }

/* ═══════════════════════════════════════════════════
   LOGIN PAGE
═══════════════════════════════════════════════════ */
#page-login {
  display: none;
  min-height: 100vh;
  background: var(--ink);
  position: relative;
  overflow: hidden;
}
#page-login.active { display: flex; }

.login-bg {
  position: absolute; inset: 0;
  background: 
    radial-gradient(ellipse 70% 80% at -10% 50%, rgba(201,150,58,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 60% at 110% 60%, rgba(29,53,87,.6) 0%, transparent 60%),
    linear-gradient(160deg, #0E1B2E 0%, #1a2d45 50%, #0E1B2E 100%);
}

/* Decorative lines */
.login-bg::before {
  content: '';
  position: absolute; inset: 0;
  background-image: 
    repeating-linear-gradient(0deg, transparent, transparent 59px, rgba(255,255,255,.025) 60px),
    repeating-linear-gradient(90deg, transparent, transparent 59px, rgba(255,255,255,.025) 60px);
}

.login-split {
  position: relative; z-index: 1;
  display: flex; width: 100%; min-height: 100vh;
}

/* Left hero panel */
.login-hero {
  flex: 1;
  display: flex; flex-direction: column;
  justify-content: center; padding: 60px 70px;
  max-width: 520px;
}

.school-emblem {
  width: 72px; height: 72px;
  background: linear-gradient(135deg, var(--gold), var(--gold-lt));
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 32px;
  box-shadow: 0 8px 24px rgba(201,150,58,.4);
  margin-bottom: 32px;
  animation: emblemPulse 3s ease-in-out infinite;
}
@keyframes emblemPulse {
  0%,100% { box-shadow: 0 8px 24px rgba(201,150,58,.4); }
  50% { box-shadow: 0 8px 40px rgba(201,150,58,.7); }
}

.hero-school-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 52px; font-weight: 700; line-height: 1.05;
  color: var(--white);
  margin-bottom: 10px;
}
.hero-school-name span { color: var(--gold-lt); }

.hero-tagline {
  font-size: 13px; letter-spacing: .2em; text-transform: uppercase;
  color: rgba(255,255,255,.4); margin-bottom: 48px;
  font-weight: 500;
}

.hero-portal-list { display: flex; flex-direction: column; gap: 10px; }
.portal-item {
  display: flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 10px; padding: 10px 14px;
  color: rgba(255,255,255,.65); font-size: 13px;
  transition: all .2s;
}
.portal-item:hover { background: rgba(201,150,58,.12); border-color: rgba(201,150,58,.3); color: var(--gold-lt); }
.portal-item i { width: 16px; opacity: .7; }

/* Right form panel */
.login-form-wrap {
  width: 460px; flex-shrink: 0;
  background: var(--white);
  display: flex; flex-direction: column; justify-content: center;
  padding: 56px 48px;
  box-shadow: -40px 0 80px rgba(0,0,0,.3);
  min-height: 100vh;
  animation: slideInRight .5s cubic-bezier(.16,1,.3,1);
}
@keyframes slideInRight { from { transform: translateX(30px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.form-greeting {
  font-family: 'Cormorant Garamond', serif;
  font-size: 32px; font-weight: 700; color: var(--ink);
  margin-bottom: 4px;
}
.form-sub { color: var(--stone); font-size: 13px; margin-bottom: 32px; }

/* Tabs */
.form-tabs {
  display: flex; gap: 4px;
  background: var(--cream-2); border-radius: 10px; padding: 4px;
  margin-bottom: 28px;
}
.form-tab {
  flex: 1; padding: 9px; border-radius: 8px; border: none;
  background: transparent; cursor: pointer;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
  color: var(--stone); transition: all .2s;
}
.form-tab.active { background: var(--white); color: var(--ink); box-shadow: var(--shadow-sm); }

.tab-content { display: none; }
.tab-content.active { display: block; }

/* Fields */
.field { margin-bottom: 16px; }
.field label {
  display: block; font-size: 11px; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase;
  color: var(--ink-2); margin-bottom: 6px;
}
.field input {
  width: 100%; padding: 11px 14px;
  border: 2px solid var(--border); border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--ink);
  background: var(--white); outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.field input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,150,58,.12); }
.field input::placeholder { color: #C5BBAE; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Buttons */
.btn-primary {
  width: 100%; padding: 13px;
  background: linear-gradient(135deg, var(--ink), var(--ink-2));
  color: var(--white); border: none; border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; transition: all .2s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(14,27,46,.3); }

.btn-gold {
  width: 100%; padding: 13px;
  background: linear-gradient(135deg, var(--gold), #b8852e);
  color: var(--white); border: none; border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; transition: all .2s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(201,150,58,.4); }

.forgot-link {
  text-align: right; margin-bottom: 16px;
}
.forgot-link a, .link { color: var(--gold); font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; }
.forgot-link a:hover, .link:hover { text-decoration: underline; }

.divider {
  text-align: center; position: relative;
  font-size: 11px; color: var(--stone); margin: 20px 0;
}
.divider::before, .divider::after {
  content: ''; position: absolute; top: 50%;
  width: 40%; height: 1px; background: var(--border);
}
.divider::before { left: 0; }
.divider::after { right: 0; }

.public-msg-link {
  text-align: center; margin-top: 16px;
  font-size: 12px; color: var(--stone);
}

/* Alert */
.alert {
  padding: 10px 14px; border-radius: 8px; font-size: 12px;
  font-weight: 500; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
}
.alert-info { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
.alert-success { background: #F0FDF4; color: var(--success); border: 1px solid #BBF7D0; }
.alert-danger { background: #FEF2F2; color: var(--danger); border: 1px solid #FECACA; }

/* ═══════════════════════════════════════════════════
   STUDENT DASHBOARD
═══════════════════════════════════════════════════ */
#page-dashboard {
  display: none; min-height: 100vh;
  background: var(--cream);
}
#page-dashboard.active { display: flex; }

/* Sidebar */
.sidebar {
  width: 240px; flex-shrink: 0;
  background: var(--ink);
  display: flex; flex-direction: column;
  position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
}
.sidebar-brand {
  padding: 24px 20px 20px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  display: flex; align-items: center; gap: 10px;
}
.brand-icon {
  width: 36px; height: 36px; border-radius: 8px;
  background: linear-gradient(135deg, var(--gold), var(--gold-lt));
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; flex-shrink: 0;
}
.brand-text { line-height: 1.2; }
.brand-text strong { color: var(--white); font-size: 13px; display: block; }
.brand-text span { color: rgba(255,255,255,.4); font-size: 11px; }

.sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
.nav-section-label {
  font-size: 10px; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
  color: rgba(255,255,255,.25); padding: 0 8px; margin: 16px 0 6px;
}
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: 8px;
  color: rgba(255,255,255,.55); font-size: 13px; font-weight: 500;
  cursor: pointer; transition: all .15s; text-decoration: none;
  position: relative; margin-bottom: 2px;
}
.nav-item:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.9); }
.nav-item.active { background: rgba(201,150,58,.15); color: var(--gold-lt); }
.nav-item i { width: 16px; font-size: 13px; flex-shrink: 0; }
.nav-badge {
  margin-left: auto; background: var(--gold); color: var(--ink);
  font-size: 10px; font-weight: 700; min-width: 18px; height: 18px;
  border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 5px;
}

.sidebar-user {
  padding: 16px; border-top: 1px solid rgba(255,255,255,.08);
  display: flex; align-items: center; gap: 10px;
}
.user-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: linear-gradient(135deg, var(--gold), var(--gold-lt));
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; color: var(--ink); flex-shrink: 0;
}
.user-info { flex: 1; min-width: 0; }
.user-info strong { display: block; font-size: 12px; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-info span { font-size: 11px; color: rgba(255,255,255,.4); }
.logout-btn {
  color: rgba(255,255,255,.3); cursor: pointer; background: none; border: none;
  padding: 4px; border-radius: 6px; transition: color .15s; font-size: 14px;
}
.logout-btn:hover { color: rgba(255,255,255,.7); }

/* Main content */
.main-content {
  flex: 1; margin-left: 240px;
  display: flex; flex-direction: column; min-height: 100vh;
}
.topbar {
  background: var(--white); border-bottom: 1px solid var(--border);
  padding: 16px 32px;
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 50;
}
.page-title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: var(--ink); }
.topbar-actions { display: flex; align-items: center; gap: 12px; }
.topbar-btn {
  width: 36px; height: 36px; border-radius: 8px;
  background: var(--cream-2); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--stone); transition: all .15s; position: relative;
}
.topbar-btn:hover { background: var(--border); color: var(--ink); }
.topbar-badge {
  position: absolute; top: -3px; right: -3px;
  width: 14px; height: 14px; border-radius: 50%;
  background: var(--gold); color: var(--white);
  font-size: 8px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--white);
}

.content-area { flex: 1; padding: 28px 32px; }

/* ── Dashboard section ── */
.welcome-banner {
  background: linear-gradient(135deg, var(--ink), var(--ink-2));
  border-radius: var(--r-lg); padding: 28px 32px; margin-bottom: 24px;
  display: flex; align-items: center; justify-content: space-between;
  position: relative; overflow: hidden;
}
.welcome-banner::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse 60% 80% at 90% 50%, rgba(201,150,58,.2) 0%, transparent 60%);
}
.welcome-text { position: relative; }
.welcome-text h2 { font-family: 'Cormorant Garamond', serif; font-size: 26px; color: var(--white); font-weight: 700; margin-bottom: 4px; }
.welcome-text p { color: rgba(255,255,255,.55); font-size: 13px; }
.welcome-icon { position: relative; font-size: 56px; opacity: .25; }

.stats-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px;
}
.stat-card {
  background: var(--white); border-radius: var(--r); padding: 20px;
  border: 1px solid var(--border); box-shadow: var(--shadow-sm);
  transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-icon {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; margin-bottom: 12px;
}
.stat-value { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.stat-label { font-size: 12px; color: var(--stone); }

/* ── Reports section ── */
.section { display: none; }
.section.active { display: block; }

.reports-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
}
.report-card {
  background: var(--white); border-radius: var(--r-lg);
  border: 1px solid var(--border); overflow: hidden;
  transition: all .2s; cursor: pointer;
}
.report-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.report-card-header {
  padding: 20px; background: linear-gradient(135deg, var(--ink), #1a3a5c);
  position: relative; overflow: hidden;
}
.report-card-header::after {
  content: ''; position: absolute;
  bottom: -30px; right: -20px;
  width: 100px; height: 100px; border-radius: 50%;
  background: rgba(255,255,255,.05);
}
.report-term {
  font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--gold-lt); margin-bottom: 6px;
}
.report-title { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 700; color: var(--white); }
.report-type-badge {
  display: inline-block; margin-top: 8px;
  background: rgba(201,150,58,.25); color: var(--gold-lt);
  padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 600;
}
.report-card-body { padding: 16px 20px; }
.report-stat { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.report-stat-label { font-size: 12px; color: var(--stone); }
.report-stat-value { font-size: 13px; font-weight: 600; color: var(--ink); }
.report-avg { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: var(--ink-2); text-align: center; padding: 8px 0 4px; }
.report-avg-label { text-align: center; font-size: 11px; color: var(--stone); margin-bottom: 12px; }
.grade-pill {
  display: inline-block; padding: 4px 12px; border-radius: 100px;
  font-size: 11px; font-weight: 700; text-align: center;
}
.grade-A { background: #D1FAE5; color: #065F46; }
.grade-B { background: #DBEAFE; color: #1E40AF; }
.grade-C { background: #FEF3C7; color: #92400E; }
.grade-F { background: #FEE2E2; color: #991B1B; }
.view-btn {
  width: 100%; padding: 9px; border-radius: 8px;
  background: var(--cream-2); border: 1px solid var(--border);
  color: var(--ink); font-family: 'Outfit', sans-serif;
  font-size: 12px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 6px;
  transition: all .15s;
}
.view-btn:hover { background: var(--ink); color: var(--white); border-color: var(--ink); }

/* ── Inbox section ── */
.inbox-layout { display: grid; grid-template-columns: 300px 1fr; gap: 0; background: var(--white); border-radius: var(--r-lg); border: 1px solid var(--border); overflow: hidden; min-height: 500px; }
.inbox-sidebar { border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.inbox-sidebar-header { padding: 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.inbox-sidebar-header h3 { font-size: 14px; font-weight: 700; color: var(--ink); }
.compose-btn {
  padding: 6px 12px; background: var(--gold); color: var(--white);
  border: none; border-radius: 8px; font-size: 11px; font-weight: 700;
  cursor: pointer; display: flex; align-items: center; gap: 5px;
  font-family: 'Outfit', sans-serif; transition: all .15s;
}
.compose-btn:hover { background: #b8852e; }
.inbox-list { flex: 1; overflow-y: auto; }
.inbox-item {
  padding: 14px 16px; border-bottom: 1px solid var(--border);
  cursor: pointer; transition: background .12s; position: relative;
}
.inbox-item:hover { background: var(--cream); }
.inbox-item.active { background: #EFF6FF; border-left: 3px solid var(--gold); }
.inbox-item.unread { background: rgba(201,150,58,.04); }
.inbox-sender { font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 3px; display: flex; align-items: center; gap: 6px; }
.unread-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }
.inbox-preview { font-size: 12px; color: var(--stone); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.inbox-time { font-size: 10px; color: var(--stone); position: absolute; top: 14px; right: 14px; }

.inbox-main { display: flex; flex-direction: column; }
.inbox-thread-header { padding: 16px 24px; border-bottom: 1px solid var(--border); }
.thread-subject { font-size: 16px; font-weight: 700; color: var(--ink); margin-bottom: 4px; }
.thread-meta { font-size: 12px; color: var(--stone); }
.inbox-messages { flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
.message-bubble { display: flex; gap: 10px; max-width: 75%; }
.message-bubble.sent { flex-direction: row-reverse; align-self: flex-end; }
.msg-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; flex-shrink: 0; margin-top: 2px;
}
.msg-avatar.teacher { background: var(--ink); color: var(--white); }
.msg-avatar.student { background: var(--gold); color: var(--white); }
.msg-content { display: flex; flex-direction: column; gap: 3px; }
.msg-name { font-size: 11px; font-weight: 600; color: var(--stone); }
.message-bubble.sent .msg-name { text-align: right; }
.msg-bubble {
  padding: 10px 14px; border-radius: 12px; font-size: 13px; line-height: 1.5;
}
.msg-bubble.received { background: var(--cream-2); color: var(--ink); border-radius: 4px 12px 12px 12px; }
.msg-bubble.sent-bubble { background: var(--ink); color: var(--white); border-radius: 12px 4px 12px 12px; }
.msg-time { font-size: 10px; color: var(--stone); }
.message-bubble.sent .msg-time { text-align: right; }

.inbox-reply { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; align-items: flex-end; }
.reply-input {
  flex: 1; padding: 10px 14px;
  border: 2px solid var(--border); border-radius: var(--r);
  resize: none; font-family: 'Outfit', sans-serif;
  font-size: 13px; color: var(--ink); outline: none;
  transition: border-color .15s; min-height: 44px;
}
.reply-input:focus { border-color: var(--gold); }
.send-btn {
  width: 44px; height: 44px; border-radius: var(--r);
  background: var(--gold); color: var(--white); border: none;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 14px; transition: all .15s; flex-shrink: 0;
}
.send-btn:hover { background: #b8852e; }

.inbox-empty {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center; color: var(--stone); gap: 8px;
}
.inbox-empty i { font-size: 48px; opacity: .3; }
.inbox-empty p { font-size: 13px; }

/* ═══════════════════════════════════════════════════
   REPORT CARD (full page)
═══════════════════════════════════════════════════ */
#page-report {
  display: none; min-height: 100vh;
  background: #E8E0D0; padding: 24px;
}
#page-report.active { display: block; }

.report-action-bar {
  max-width: 840px; margin: 0 auto 16px;
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
}
.report-action-bar-inner { display: flex; gap: 10px; }
.rc-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 9px 18px; border-radius: 10px; border: none;
  font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all .15s; text-decoration: none;
}
.rc-btn-back { background: rgba(14,27,46,.08); color: var(--ink); }
.rc-btn-print { background: var(--ink); color: var(--white); }
.rc-btn-pdf { background: #DC2626; color: var(--white); }
.rc-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }

@media print {
  .report-action-bar, #page-login, #page-dashboard, #page-announcements, #page-contact, .sidebar, .topbar { display: none !important; }
  #page-report { background: white; padding: 0; display: block !important; }
  .report-card-doc { box-shadow: none; border-radius: 0; }
}

/* The actual report card */
.report-card-doc {
  max-width: 840px; margin: 0 auto;
  background: var(--white);
  border-radius: 16px; overflow: hidden;
  box-shadow: 0 8px 48px rgba(0,0,0,.18);
  font-family: 'Outfit', sans-serif;
}

/* Header */
.rc-header {
  background: linear-gradient(135deg, var(--ink) 0%, #1D3557 100%);
  padding: 28px 36px; display: flex; align-items: flex-start; gap: 24px;
  position: relative; overflow: hidden;
}
.rc-header::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse 60% 80% at 85% 50%, rgba(201,150,58,.15) 0%, transparent 60%);
}
.rc-logo-box {
  width: 70px; height: 70px; border-radius: 14px;
  background: linear-gradient(135deg, var(--gold), var(--gold-lt));
  display: flex; align-items: center; justify-content: center;
  font-size: 30px; flex-shrink: 0; position: relative;
  box-shadow: 0 4px 16px rgba(201,150,58,.4);
}
.rc-header-text { position: relative; flex: 1; }
.rc-school-name { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: var(--white); margin-bottom: 4px; }
.rc-school-meta { color: rgba(255,255,255,.5); font-size: 12px; line-height: 1.6; }
.rc-report-badge {
  position: relative; text-align: right;
}
.rc-badge-pill {
  display: inline-block; background: var(--gold); color: var(--white);
  padding: 5px 14px; border-radius: 100px;
  font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
  margin-bottom: 8px;
}
.rc-term-info { color: rgba(255,255,255,.5); font-size: 12px; line-height: 1.6; }

/* Student info */
.rc-student { padding: 20px 36px; background: var(--cream); display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-bottom: 1px solid var(--border); }
.rc-info-cell { padding: 8px 16px; }
.rc-info-cell:not(:last-child) { border-right: 1px solid var(--border); }
.rc-info-label { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--stone); margin-bottom: 4px; }
.rc-info-value { font-size: 14px; font-weight: 600; color: var(--ink); }

/* Marks table */
.rc-body { padding: 24px 36px; }
.rc-section-title {
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--stone); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
}
.rc-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

.rc-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.rc-table th {
  background: var(--ink); color: var(--white);
  padding: 10px 14px; text-align: left;
  font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
}
.rc-table th:first-child { border-radius: 8px 0 0 0; }
.rc-table th:last-child { border-radius: 0 8px 0 0; }
.rc-table td { padding: 10px 14px; border-bottom: 1px solid var(--border); font-size: 13px; }
.rc-table tr:last-child td { border-bottom: none; }
.rc-table tr:hover td { background: var(--cream); }
.rc-table tfoot td { background: var(--cream); font-weight: 700; border-top: 2px solid var(--border); }
.grade-cell {
  display: inline-flex; align-items: center; justify-content: center;
  width: 28px; height: 28px; border-radius: 6px;
  font-size: 12px; font-weight: 800;
}
.score-cell { font-family: 'Cormorant Garamond', serif; font-size: 17px; font-weight: 700; }

/* Summary row */
.rc-summary { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px; }
.rc-sum-box {
  background: var(--cream); border-radius: var(--r); padding: 16px;
  border: 1px solid var(--border); text-align: center;
}
.rc-sum-val { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: var(--ink-2); }
.rc-sum-label { font-size: 11px; color: var(--stone); margin-top: 2px; }

/* Comment section */
.rc-comment-box { background: var(--cream); border-radius: var(--r); padding: 16px 20px; border: 1px solid var(--border); margin-bottom: 24px; }
.rc-comment-title { font-size: 12px; font-weight: 700; color: var(--ink); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
.rc-comment-text { font-size: 13px; color: var(--ink); line-height: 1.6; font-style: italic; }

/* Conduct */
.conduct-row { display: flex; align-items: center; gap: 16px; background: var(--cream); border-radius: var(--r); padding: 14px 20px; border: 1px solid var(--border); margin-bottom: 24px; }
.conduct-score { font-family: 'Cormorant Garamond', serif; font-size: 36px; font-weight: 700; color: var(--success); flex-shrink: 0; }
.conduct-bar-wrap { flex: 1; }
.conduct-label { font-size: 12px; font-weight: 600; color: var(--ink); margin-bottom: 6px; }
.conduct-bar { height: 8px; background: var(--border); border-radius: 100px; overflow: hidden; }
.conduct-fill { height: 100%; border-radius: 100px; background: linear-gradient(90deg, #10B981, #059669); }
.conduct-note { font-size: 11px; color: var(--stone); margin-top: 4px; }

/* Signatures */
.rc-signatures { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-top: 1px solid var(--border); padding: 24px 36px; }
.sig-block { padding: 0 12px; }
.sig-block:not(:last-child) { border-right: 1px solid var(--border); }
.sig-line-area { height: 50px; display: flex; align-items: flex-end; padding-bottom: 6px; }
.sig-line { width: 100%; height: 1px; background: var(--ink); margin-top: auto; }
.sig-name { font-size: 12px; font-weight: 700; color: var(--ink); margin-top: 6px; }
.sig-title { font-size: 11px; color: var(--stone); }
.sig-date { font-size: 10px; color: var(--stone); margin-top: 4px; }

/* Footer */
.rc-footer { background: var(--ink); padding: 14px 36px; text-align: center; }
.rc-footer-text { color: rgba(255,255,255,.4); font-size: 11px; font-style: italic; }

/* ═══════════════════════════════════════════════════
   ANNOUNCEMENTS PAGE
═══════════════════════════════════════════════════ */
#page-announcements {
  display: none; min-height: 100vh;
  background: var(--cream);
}
#page-announcements.active { display: block; }

.ann-nav {
  background: var(--white); border-bottom: 1px solid var(--border);
  padding: 0 40px; display: flex; align-items: center; justify-content: space-between;
  height: 64px; position: sticky; top: 0; z-index: 50;
}
.ann-nav-brand { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 700; color: var(--ink); display: flex; align-items: center; gap: 10px; }
.ann-nav-links { display: flex; align-items: center; gap: 24px; }
.ann-nav-links a { color: var(--stone); text-decoration: none; font-size: 13px; font-weight: 500; transition: color .15s; cursor: pointer; }
.ann-nav-links a:hover { color: var(--ink); }
.ann-nav-btn {
  background: var(--ink); color: var(--white); padding: 8px 18px;
  border-radius: 8px; font-size: 13px; font-weight: 600;
  text-decoration: none; cursor: pointer; border: none;
  font-family: 'Outfit', sans-serif; transition: all .15s; display: inline-block;
}
.ann-nav-btn:hover { background: var(--ink-2); }

.ann-hero {
  background: linear-gradient(160deg, var(--ink) 0%, #1a3a5c 100%);
  padding: 56px 40px; text-align: center; position: relative; overflow: hidden;
}
.ann-hero::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse 50% 60% at 25% 50%, rgba(201,150,58,.12) 0%, transparent 60%);
}
.ann-hero-eyebrow {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(201,150,58,.15); border: 1px solid rgba(201,150,58,.3);
  color: var(--gold-lt); padding: 5px 14px; border-radius: 100px;
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  margin-bottom: 16px; position: relative;
}
.ann-hero h1 {
  font-family: 'Cormorant Garamond', serif; font-size: 48px; font-weight: 700;
  color: var(--white); position: relative; margin-bottom: 12px; line-height: 1.1;
}
.ann-hero h1 em { color: var(--gold-lt); font-style: italic; }
.ann-hero-sub { color: rgba(255,255,255,.5); font-size: 15px; position: relative; }

.ann-content { max-width: 1100px; margin: 0 auto; padding: 48px 24px; }

.ann-filter { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; }
.ann-filter-btn {
  padding: 7px 16px; border-radius: 100px;
  border: 2px solid var(--border); background: var(--white);
  color: var(--stone); font-size: 12px; font-weight: 600;
  cursor: pointer; font-family: 'Outfit', sans-serif; transition: all .15s;
}
.ann-filter-btn:hover { border-color: var(--ink); color: var(--ink); }
.ann-filter-btn.active { background: var(--ink); color: var(--white); border-color: var(--ink); }

.ann-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.ann-card {
  background: var(--white); border-radius: var(--r-lg);
  border: 1px solid var(--border); overflow: hidden;
  transition: all .2s; cursor: pointer;
}
.ann-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--gold); }
.ann-card.pinned { border-color: var(--gold); border-width: 2px; }
.ann-img {
  height: 160px; background: linear-gradient(135deg, var(--ink), #1a3a5c);
  display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden;
}
.ann-img-emoji { font-size: 56px; opacity: .25; }
.ann-pin-tag {
  position: absolute; top: 12px; left: 12px;
  background: var(--gold); color: var(--white);
  padding: 3px 10px; border-radius: 100px;
  font-size: 10px; font-weight: 700; text-transform: uppercase;
}
.ann-attach-tag {
  position: absolute; bottom: 12px; right: 12px;
  background: rgba(255,255,255,.15); color: var(--white);
  padding: 3px 8px; border-radius: 6px;
  font-size: 10px; font-weight: 600; display: flex; align-items: center; gap: 4px;
}
.ann-body { padding: 18px 20px; }
.ann-date { font-size: 11px; color: var(--stone); font-weight: 600; margin-bottom: 8px; }
.ann-card-title { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 700; color: var(--ink); line-height: 1.3; margin-bottom: 8px; }
.ann-excerpt { font-size: 12px; color: var(--stone); line-height: 1.6; }
.ann-footer { padding: 12px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.ann-author { font-size: 11px; color: var(--stone); }
.ann-read-more { font-size: 12px; font-weight: 700; color: var(--gold); display: flex; align-items: center; gap: 4px; }

/* ── Announcement Popup ── */
.ann-modal-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(10,18,32,.75); backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center; padding: 24px;
  opacity: 0; pointer-events: none; transition: opacity .3s;
}
.ann-modal-overlay.open { opacity: 1; pointer-events: all; }

.ann-modal {
  background: var(--white); border-radius: 24px;
  width: 100%; max-width: 720px; max-height: 88vh;
  overflow: hidden; display: flex; flex-direction: column;
  box-shadow: 0 40px 100px rgba(0,0,0,.35);
  transform: translateY(24px) scale(.97); transition: transform .3s cubic-bezier(.16,1,.3,1);
}
.ann-modal-overlay.open .ann-modal { transform: translateY(0) scale(1); }
.ann-modal-top {
  height: 220px; background: linear-gradient(135deg, var(--ink), #1a3a5c);
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  position: relative; font-size: 80px; opacity: 1;
}
.ann-modal-top-emoji { opacity: .2; font-size: 80px; }
.ann-modal-close {
  position: absolute; top: 16px; right: 16px;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,.15); color: var(--white);
  border: none; cursor: pointer; font-size: 16px;
  display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
.ann-modal-close:hover { background: rgba(255,255,255,.3); }
.ann-modal-body { flex: 1; overflow-y: auto; padding: 32px; }
.ann-modal-meta { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.ann-modal-date { font-size: 12px; color: var(--stone); }
.ann-modal-badge {
  display: inline-block; background: var(--cream-2); border: 1px solid var(--border);
  color: var(--stone); padding: 2px 10px; border-radius: 100px; font-size: 11px; font-weight: 600;
}
.ann-modal-title { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 700; color: var(--ink); line-height: 1.2; margin-bottom: 16px; }
.ann-modal-content { font-size: 14px; color: #444; line-height: 1.8; }
.ann-modal-content p { margin-bottom: 14px; }
.ann-modal-content strong { color: var(--ink); }

.ann-download-box {
  margin-top: 24px; background: #EFF6FF;
  border: 1px solid #BFDBFE; border-radius: var(--r);
  padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;
}
.ann-file-info { display: flex; align-items: center; gap: 12px; }
.ann-file-icon {
  width: 44px; height: 44px; border-radius: 10px;
  background: #1D4ED8; color: var(--white);
  display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.ann-file-name { font-weight: 700; font-size: 13px; color: var(--ink-2); }
.ann-file-size { font-size: 11px; color: var(--stone); }
.ann-download-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: #1D4ED8; color: var(--white); padding: 9px 18px;
  border-radius: 8px; font-size: 12px; font-weight: 700; border: none;
  cursor: pointer; font-family: 'Outfit', sans-serif; transition: all .15s; flex-shrink: 0;
  text-decoration: none;
}
.ann-download-btn:hover { background: #1E40AF; }

/* ═══════════════════════════════════════════════════
   CONTACT / SECRETARY PAGE
═══════════════════════════════════════════════════ */
#page-contact {
  display: none; min-height: 100vh;
  background: var(--cream);
}
#page-contact.active { display: block; }

.contact-hero {
  background: linear-gradient(160deg, var(--ink), #1a3a5c);
  padding: 56px 40px; text-align: center; position: relative; overflow: hidden;
}
.contact-hero::before {
  content: ''; position: absolute; inset: 0;
  background: radial-gradient(ellipse 50% 70% at 80% 50%, rgba(201,150,58,.15) 0%, transparent 60%);
}
.contact-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; color: var(--white); font-weight: 700; position: relative; margin-bottom: 10px; }
.contact-hero p { color: rgba(255,255,255,.5); font-size: 15px; position: relative; }

.contact-content { max-width: 960px; margin: 0 auto; padding: 48px 24px; display: grid; grid-template-columns: 1fr 360px; gap: 32px; align-items: start; }
.contact-form-card { background: var(--white); border-radius: var(--r-lg); padding: 36px; border: 1px solid var(--border); }
.contact-form-title { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
.contact-form-sub { color: var(--stone); font-size: 13px; margin-bottom: 28px; }
.contact-field-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.contact-field { margin-bottom: 16px; }
.contact-field label { display: block; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ink-2); margin-bottom: 6px; }
.contact-field input, .contact-field textarea, .contact-field select {
  width: 100%; padding: 11px 14px;
  border: 2px solid var(--border); border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--ink);
  outline: none; transition: border-color .15s; background: var(--white); resize: none;
}
.contact-field input:focus, .contact-field textarea:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,150,58,.1); }
.contact-field input::placeholder, .contact-field textarea::placeholder { color: #C5BBAE; }
.contact-submit {
  width: 100%; padding: 13px;
  background: linear-gradient(135deg, var(--ink), var(--ink-2));
  color: var(--white); border: none; border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 700;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all .2s;
}
.contact-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(14,27,46,.25); }

.contact-info-card { background: var(--white); border-radius: var(--r-lg); padding: 28px; border: 1px solid var(--border); }
.contact-info-title { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 700; color: var(--ink); margin-bottom: 20px; }
.contact-info-item { display: flex; gap: 12px; margin-bottom: 16px; }
.contact-info-icon {
  width: 36px; height: 36px; border-radius: 8px;
  background: var(--cream-2); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--gold); font-size: 14px; flex-shrink: 0;
}
.contact-info-text strong { display: block; font-size: 13px; color: var(--ink); margin-bottom: 2px; }
.contact-info-text span { font-size: 12px; color: var(--stone); }
.no-account-note {
  background: #FFFBEB; border: 1px solid #FDE68A; border-radius: var(--r);
  padding: 14px; margin-top: 20px; font-size: 12px; color: #92400E; line-height: 1.5;
}
.no-account-note strong { display: block; margin-bottom: 4px; }

/* ═══════════════════════════════════════════════════
   MODALS (compose, forgot pw)
═══════════════════════════════════════════════════ */
.modal-overlay {
  position: fixed; inset: 0; z-index: 9000;
  background: rgba(10,18,32,.6); backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center; padding: 20px;
  opacity: 0; pointer-events: none; transition: opacity .25s;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal-box {
  background: var(--white); border-radius: 20px;
  padding: 32px; width: 100%; max-width: 480px;
  box-shadow: var(--shadow-lg);
  transform: translateY(16px); transition: transform .3s cubic-bezier(.16,1,.3,1);
}
.modal-overlay.open .modal-box { transform: translateY(0); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.modal-title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: var(--ink); }
.modal-close { width: 32px; height: 32px; border-radius: 50%; background: var(--cream-2); border: none; cursor: pointer; color: var(--stone); font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.modal-close:hover { background: var(--border); color: var(--ink); }
.modal-sub { color: var(--stone); font-size: 13px; margin-bottom: 24px; }

/* Select in modal */
.modal-box select {
  width: 100%; padding: 11px 14px;
  border: 2px solid var(--border); border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--ink);
  background: var(--white); outline: none; margin-bottom: 16px;
  transition: border-color .15s; appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238B8070' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 14px center;
}
.modal-box select:focus { border-color: var(--gold); }
.modal-box textarea {
  width: 100%; padding: 11px 14px;
  border: 2px solid var(--border); border-radius: var(--r);
  font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--ink);
  outline: none; resize: none; transition: border-color .15s; margin-bottom: 16px;
}
.modal-box textarea:focus { border-color: var(--gold); }
.modal-box .field { margin-bottom: 14px; }
.modal-box .field input { border: 2px solid var(--border); border-radius: var(--r); padding: 11px 14px; font-family: 'Outfit', sans-serif; font-size: 13px; width: 100%; outline: none; transition: border-color .15s; }
.modal-box .field input:focus { border-color: var(--gold); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; }
.btn-cancel { padding: 9px 20px; border-radius: 8px; border: 2px solid var(--border); background: transparent; color: var(--stone); font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-cancel:hover { border-color: var(--ink); color: var(--ink); }
.btn-submit { padding: 9px 20px; border-radius: 8px; border: none; background: var(--ink); color: var(--white); font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; transition: all .15s; }
.btn-submit:hover { background: var(--ink-2); }

/* Success state */
.success-screen { text-align: center; padding: 20px 0 8px; }
.success-icon { width: 64px; height: 64px; border-radius: 50%; background: #D1FAE5; color: #065F46; font-size: 26px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.success-screen h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
.success-screen p { font-size: 13px; color: var(--stone); line-height: 1.6; }

/* ═══ UTILITIES ═══ */
.hidden { display: none !important; }
.text-center { text-align: center; }
.mt-12 { margin-top: 12px; }
.mt-16 { margin-top: 16px; }
.flex-center { display: flex; align-items: center; gap: 6px; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 100px; }
::-webkit-scrollbar-thumb:hover { background: var(--stone); }

/* Responsive */
@media (max-width: 900px) {
  .login-hero { display: none; }
  .login-form-wrap { width: 100%; min-height: 100vh; }
  .sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .reports-grid { grid-template-columns: 1fr; }
  .ann-grid { grid-template-columns: 1fr 1fr; }
  .rc-student { grid-template-columns: 1fr 1fr; }
  .rc-signatures { grid-template-columns: 1fr 1fr; gap: 16px; }
  .contact-content { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════
     LOGIN PAGE
═══════════════════════════════════════════════════ -->
<div id="page-login" class="page active">
  <div class="login-bg"></div>
  <div class="login-split">

    <!-- Hero side -->
    <div class="login-hero">
      <div class="school-emblem">🏫</div>
      <h1 class="hero-school-name">ASPEJ<br><span>School</span></h1>
      <p class="hero-tagline">Excellence · Discipline · Innovation</p>
      <div class="hero-portal-list">
        <div class="portal-item"><i class="fas fa-user-graduate"></i> Student Academic Portal</div>
        <div class="portal-item"><i class="fas fa-chalkboard-teacher"></i> Teacher & Staff Access</div>
        <div class="portal-item"><i class="fas fa-graduation-cap"></i> Report Cards & Results</div>
        <div class="portal-item"><i class="fas fa-comments"></i> Inbox & Messaging</div>
        <div class="portal-item"><i class="fas fa-bullhorn"></i> School Announcements</div>
      </div>
    </div>

    <!-- Form side -->
    <div class="login-form-wrap">
      <p class="form-greeting">Welcome back</p>
      <p class="form-sub">Sign in to your school portal</p>

      <!-- Tabs: Login / Register -->
      <div class="form-tabs">
        <button class="form-tab active" onclick="switchTab('login')">Sign In</button>
        <button class="form-tab" onclick="switchTab('register')">Create Account</button>
      </div>

      <!-- LOGIN TAB -->
      <div id="tab-login" class="tab-content active">
        <div id="login-error" class="alert alert-danger hidden">
          <i class="fas fa-circle-exclamation"></i> Invalid credentials. Try: <strong>1045</strong> / <strong>student123</strong>
        </div>
        <div class="field">
          <label>Username / Registration No.</label>
          <input type="text" id="login-user" placeholder="e.g. 1045 or teacher1" value="1045">
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" id="login-pass" placeholder="••••••••" value="student123">
        </div>
        <div class="forgot-link">
          <a onclick="openForgotModal()">Forgot password?</a>
        </div>
        <button class="btn-primary" onclick="doLogin()">
          <i class="fas fa-sign-in-alt"></i> Sign In to Portal
        </button>
        <div class="divider">or</div>
        <div class="text-center" style="font-size:13px;color:var(--stone)">
          Don't have an account? <span class="link" onclick="switchTab('register')">Create one →</span>
        </div>
        <div class="public-msg-link">
          <i class="fas fa-envelope" style="margin-right:4px"></i>
          Parent? <span class="link" onclick="goToContact()">Contact the secretary without an account</span>
        </div>
      </div>

      <!-- REGISTER TAB -->
      <div id="tab-register" class="tab-content">
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Your registration number and <strong>exact full name</strong> must match school records.
        </div>
        <div id="reg-error" class="alert alert-danger hidden"></div>
        <div id="reg-success" class="hidden">
          <div class="success-screen">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h3>Registration Submitted!</h3>
            <p>Your account request has been sent to the school administration. You'll be notified once activated.</p>
          </div>
          <div class="mt-16">
            <button class="btn-primary" onclick="switchTab('login')"><i class="fas fa-sign-in-alt"></i> Back to Sign In</button>
          </div>
        </div>
        <div id="reg-form">
          <div class="field-row">
            <div class="field">
              <label>Registration Number <span style="color:var(--danger)">*</span></label>
              <input type="text" id="reg-number" placeholder="e.g. 2025001">
            </div>
            <div class="field">
              <label>Username <span style="color:var(--danger)">*</span></label>
              <input type="text" id="reg-username" placeholder="Choose username">
            </div>
          </div>
          <div class="field">
            <label>Full Name (exact as registered) <span style="color:var(--danger)">*</span></label>
            <input type="text" id="reg-name" placeholder="Must match school records exactly">
          </div>
          <div class="field-row">
            <div class="field">
              <label>Password <span style="color:var(--danger)">*</span></label>
              <input type="password" id="reg-pass" placeholder="Min 6 chars">
            </div>
            <div class="field">
              <label>Confirm Password <span style="color:var(--danger)">*</span></label>
              <input type="password" id="reg-pass2" placeholder="Repeat password">
            </div>
          </div>
          <div class="field">
            <label>Email (optional)</label>
            <input type="email" id="reg-email" placeholder="your@email.com">
          </div>
          <button class="btn-gold" onclick="doRegister()">
            <i class="fas fa-user-plus"></i> Submit Registration Request
          </button>
        </div>
        <div class="mt-12 text-center" style="font-size:12px;color:var(--stone)">
          Already have an account? <span class="link" onclick="switchTab('login')">Sign in →</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     STUDENT DASHBOARD
═══════════════════════════════════════════════════ -->
<div id="page-dashboard" class="page">
  <div class="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">🏫</div>
      <div class="brand-text">
        <strong>ASPEJ School</strong>
        <span>Student Portal</span>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main</div>
      <div class="nav-item active" onclick="showSection('home')">
        <i class="fas fa-home"></i> Dashboard
      </div>
      <div class="nav-item" onclick="showSection('reports')">
        <i class="fas fa-file-alt"></i> My Reports
      </div>
      <div class="nav-item" onclick="showSection('inbox')">
        <i class="fas fa-inbox"></i> Inbox
        <span class="nav-badge">2</span>
      </div>
      <div class="nav-section-label">School</div>
      <div class="nav-item" onclick="goToAnnouncements()">
        <i class="fas fa-bullhorn"></i> Announcements
      </div>
      <div class="nav-item" onclick="goToContact()">
        <i class="fas fa-envelope"></i> Contact Secretary
      </div>
    </nav>
    <div class="sidebar-user">
      <div class="user-avatar">JB</div>
      <div class="user-info">
        <strong>Jean-Baptiste M.</strong>
        <span>Reg. #1045 · NIT L4</span>
      </div>
      <button class="logout-btn" onclick="doLogout()" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
    </div>
  </div>

  <div class="main-content">
    <div class="topbar">
      <div class="page-title" id="topbar-title">Dashboard</div>
      <div class="topbar-actions">
        <div class="topbar-btn" onclick="showSection('inbox')" title="Inbox">
          <i class="fas fa-bell"></i>
          <span class="topbar-badge">2</span>
        </div>
        <div class="topbar-btn" onclick="goToAnnouncements()" title="Announcements">
          <i class="fas fa-bullhorn"></i>
        </div>
      </div>
    </div>

    <div class="content-area">

      <!-- HOME SECTION -->
      <div id="section-home" class="section active">
        <div class="welcome-banner">
          <div class="welcome-text">
            <h2>Good morning, Jean-Baptiste 👋</h2>
            <p>Academic Year 2024–2025 · Term 2 is currently active</p>
          </div>
          <div class="welcome-icon">🎓</div>
        </div>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background:#EFF6FF;color:#1D4ED8"><i class="fas fa-book-open"></i></div>
            <div class="stat-value">10</div>
            <div class="stat-label">Subjects Enrolled</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#D1FAE5;color:#065F46"><i class="fas fa-star"></i></div>
            <div class="stat-value">76.8</div>
            <div class="stat-label">Current Average</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;color:#92400E"><i class="fas fa-trophy"></i></div>
            <div class="stat-value">3rd</div>
            <div class="stat-label">Class Rank</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:#FCE7F3;color:#9D174D"><i class="fas fa-envelope"></i></div>
            <div class="stat-value">2</div>
            <div class="stat-label">Unread Messages</div>
          </div>
        </div>
        <div style="background:var(--white);border-radius:var(--r-lg);border:1px solid var(--border);padding:24px;">
          <div style="font-size:13px;font-weight:700;color:var(--ink);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-clock" style="color:var(--gold)"></i> Recent Activity
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
              <div style="width:36px;height:36px;border-radius:8px;background:#EFF6FF;color:#1D4ED8;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0"><i class="fas fa-file-alt"></i></div>
              <div><div style="font-size:13px;font-weight:600;color:var(--ink)">Term 1 Report Available</div><div style="font-size:12px;color:var(--stone)">View and download your Term 1 report card</div></div>
              <div style="margin-left:auto;font-size:11px;color:var(--stone)">2 days ago</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
              <div style="width:36px;height:36px;border-radius:8px;background:#FEF3C7;color:#92400E;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0"><i class="fas fa-envelope"></i></div>
              <div><div style="font-size:13px;font-weight:600;color:var(--ink)">Message from Mr. Habimana</div><div style="font-size:12px;color:var(--stone)">Regarding your Database Management assignment</div></div>
              <div style="margin-left:auto;font-size:11px;color:var(--stone)">3 days ago</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;">
              <div style="width:36px;height:36px;border-radius:8px;background:#D1FAE5;color:#065F46;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0"><i class="fas fa-bullhorn"></i></div>
              <div><div style="font-size:13px;font-weight:600;color:var(--ink)">New Announcement</div><div style="font-size:12px;color:var(--stone)">Term 1 Examination Schedule Released</div></div>
              <div style="margin-left:auto;font-size:11px;color:var(--stone)">5 days ago</div>
            </div>
          </div>
        </div>
      </div>

      <!-- REPORTS SECTION -->
      <div id="section-reports" class="section">
        <div class="reports-grid">
          <div class="report-card" onclick="goToReport()">
            <div class="report-card-header">
              <div class="report-term">Academic Year 2024–2025</div>
              <div class="report-title">Term 1 Report</div>
              <span class="report-type-badge">Full Term</span>
            </div>
            <div class="report-card-body">
              <div class="report-stat"><span class="report-stat-label">Class</span><span class="report-stat-value">Level 4 NIT</span></div>
              <div class="report-stat"><span class="report-stat-label">Subjects</span><span class="report-stat-value">10 subjects</span></div>
              <div class="report-avg">76.8</div>
              <div class="report-avg-label">Average Score / 100</div>
              <div style="text-align:center;margin-bottom:12px"><span class="grade-pill grade-B">Grade B — Good</span></div>
              <button class="view-btn"><i class="fas fa-eye"></i> View Report Card</button>
            </div>
          </div>
          <div class="report-card" onclick="alert('2nd Period report will be available after exams.')">
            <div class="report-card-header" style="background:linear-gradient(135deg,#2D7D46,#1a5c30)">
              <div class="report-term">Academic Year 2024–2025</div>
              <div class="report-title">1st Period Report</div>
              <span class="report-type-badge" style="background:rgba(255,255,255,.15)">Test 1 Only</span>
            </div>
            <div class="report-card-body">
              <div class="report-stat"><span class="report-stat-label">Class</span><span class="report-stat-value">Level 4 NIT</span></div>
              <div class="report-stat"><span class="report-stat-label">Subjects</span><span class="report-stat-value">10 subjects</span></div>
              <div class="report-avg">74.2</div>
              <div class="report-avg-label">Test 1 Average / 100</div>
              <div style="text-align:center;margin-bottom:12px"><span class="grade-pill grade-B">Grade B — Good</span></div>
              <button class="view-btn"><i class="fas fa-eye"></i> View Report Card</button>
            </div>
          </div>
          <div class="report-card" style="opacity:.6;pointer-events:none">
            <div class="report-card-header" style="background:linear-gradient(135deg,#6B7280,#4B5563)">
              <div class="report-term">Academic Year 2024–2025</div>
              <div class="report-title">Term 2 Report</div>
              <span class="report-type-badge" style="background:rgba(255,255,255,.1)">Pending</span>
            </div>
            <div class="report-card-body">
              <div style="text-align:center;padding:24px 0;color:var(--stone)">
                <i class="fas fa-lock" style="font-size:32px;opacity:.3;display:block;margin-bottom:12px"></i>
                <p style="font-size:13px">Not yet available</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- INBOX SECTION -->
      <div id="section-inbox" class="section">
        <div class="inbox-layout">
          <div class="inbox-sidebar">
            <div class="inbox-sidebar-header">
              <h3>Inbox <span style="background:#FEF3C7;color:#92400E;padding:2px 8px;border-radius:100px;font-size:10px;font-weight:700;margin-left:4px">2 new</span></h3>
              <button class="compose-btn" onclick="openComposeModal()"><i class="fas fa-pencil-alt"></i> Compose</button>
            </div>
            <div class="inbox-list">
              <div class="inbox-item unread active" onclick="selectThread(0)">
                <div class="inbox-sender"><span class="unread-dot"></span> Mr. Habimana (DoS)</div>
                <div class="inbox-preview">Your DB assignment grade has been...</div>
                <div class="inbox-time">Mar 3</div>
              </div>
              <div class="inbox-item unread" onclick="selectThread(1)">
                <div class="inbox-sender"><span class="unread-dot"></span> Ms. Uwimana (Principal)</div>
                <div class="inbox-preview">Welcome to Term 2! Please ensure...</div>
                <div class="inbox-time">Mar 1</div>
              </div>
              <div class="inbox-item" onclick="selectThread(2)">
                <div class="inbox-sender">Mr. Nkurunziza (Teacher)</div>
                <div class="inbox-preview">Well done on your Networking exam!</div>
                <div class="inbox-time">Feb 28</div>
              </div>
              <div class="inbox-item" onclick="selectThread(3)">
                <div class="inbox-sender">Director of Discipline</div>
                <div class="inbox-preview">Your conduct score has been updated</div>
                <div class="inbox-time">Feb 25</div>
              </div>
            </div>
          </div>
          <div class="inbox-main" id="inbox-thread">
            <div class="inbox-thread-header">
              <div class="thread-subject">RE: Database Management Assignment – Grade Update</div>
              <div class="thread-meta">With Mr. Innocent Habimana (Director of Studies) · 3 messages</div>
            </div>
            <div class="inbox-messages">
              <div class="message-bubble">
                <div class="msg-avatar teacher">IH</div>
                <div class="msg-content">
                  <div class="msg-name">Mr. Innocent Habimana</div>
                  <div class="msg-bubble received">Jean-Baptiste, I've reviewed your Database Management project. You scored 82/100 — excellent work on the normalization section! However, please review the indexing chapter for Term 2.</div>
                  <div class="msg-time">March 3 · 9:14 AM</div>
                </div>
              </div>
              <div class="message-bubble sent">
                <div class="msg-avatar student">JB</div>
                <div class="msg-content">
                  <div class="msg-name">You</div>
                  <div class="msg-bubble sent-bubble">Thank you Mr. Habimana! I'll make sure to review indexing thoroughly before the next assessment. Is there any recommended material?</div>
                  <div class="msg-time">March 3 · 10:22 AM</div>
                </div>
              </div>
              <div class="message-bubble">
                <div class="msg-avatar teacher">IH</div>
                <div class="msg-content">
                  <div class="msg-name">Mr. Innocent Habimana</div>
                  <div class="msg-bubble received">I'd recommend checking the SQL Indexing Guide in the library. Also, Chapter 7 of your textbook covers it well. Keep up the good work!</div>
                  <div class="msg-time">March 3 · 2:48 PM</div>
                </div>
              </div>
            </div>
            <div class="inbox-reply">
              <textarea class="reply-input" id="reply-text" placeholder="Write a reply…" rows="1" onkeydown="handleReplyKey(event)"></textarea>
              <button class="send-btn" onclick="sendReply()"><i class="fas fa-paper-plane"></i></button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /content-area -->
  </div><!-- /main-content -->
</div><!-- /page-dashboard -->

<!-- ═══════════════════════════════════════════════════
     REPORT CARD PAGE
═══════════════════════════════════════════════════ -->
<div id="page-report" class="page">
  <div class="report-action-bar">
    <button class="rc-btn rc-btn-back" onclick="goBack()">
      <i class="fas fa-arrow-left"></i> Back to Reports
    </button>
    <div class="report-action-bar-inner">
      <button class="rc-btn rc-btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
      </button>
      <button class="rc-btn rc-btn-pdf" onclick="downloadPDF()">
        <i class="fas fa-file-pdf"></i> Download PDF
      </button>
    </div>
  </div>

  <div class="report-card-doc" id="report-card-doc">

    <!-- Header: School Info -->
    <div class="rc-header">
      <div class="rc-logo-box">🏫</div>
      <div class="rc-header-text">
        <div class="rc-school-name">ASPEJ School</div>
        <div class="rc-school-meta">
          Excellence · Discipline · Innovation<br>
          Kigali, Rwanda · P.O. Box 1234 · +250 788 000 000 · info@aspej.edu
        </div>
      </div>
      <div class="rc-report-badge">
        <div class="rc-badge-pill">Report Card</div>
        <div class="rc-term-info">
          Term 1 — Full Term<br>
          Academic Year 2024 – 2025
        </div>
      </div>
    </div>

    <!-- Student Info -->
    <div class="rc-student">
      <div class="rc-info-cell">
        <div class="rc-info-label">Student Name</div>
        <div class="rc-info-value">Jean-Baptiste Mugisha</div>
      </div>
      <div class="rc-info-cell">
        <div class="rc-info-label">Registration No.</div>
        <div class="rc-info-value" style="font-family:monospace">1045</div>
      </div>
      <div class="rc-info-cell">
        <div class="rc-info-label">Class / Combination</div>
        <div class="rc-info-value">Level 4 NIT</div>
      </div>
      <div class="rc-info-cell">
        <div class="rc-info-label">Class Rank</div>
        <div class="rc-info-value">
          <span style="background:var(--ink);color:white;padding:3px 12px;border-radius:100px;font-size:13px;font-weight:700">3rd / 28</span>
        </div>
      </div>
    </div>

    <!-- Body -->
    <div class="rc-body">

      <!-- Academic Performance Table -->
      <div class="rc-section-title"><i class="fas fa-chart-bar" style="color:var(--gold)"></i> Academic Performance</div>
      <table class="rc-table">
        <thead>
          <tr>
            <th style="width:28%">Subject</th>
            <th style="text-align:center">Test 1 (25%)</th>
            <th style="text-align:center">Test 2 (25%)</th>
            <th style="text-align:center">Exam (50%)</th>
            <th style="text-align:center">Total /100</th>
            <th style="text-align:center">Grade</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody id="report-tbody">
          <!-- Filled by JS -->
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;font-size:12px;color:var(--stone)">Overall Average:</td>
            <td style="text-align:center" class="score-cell" id="report-avg-cell">76.8</td>
            <td style="text-align:center"><span class="grade-cell grade-B">B</span></td>
            <td style="text-align:center"><span class="grade-pill grade-B" style="font-size:11px">PASS</span></td>
          </tr>
        </tfoot>
      </table>

      <!-- Summary -->
      <div class="rc-summary">
        <div class="rc-sum-box">
          <div class="rc-sum-val" style="color:var(--success)">76.8</div>
          <div class="rc-sum-label">Your Average</div>
        </div>
        <div class="rc-sum-box">
          <div class="rc-sum-val">71.4</div>
          <div class="rc-sum-label">Class Average</div>
        </div>
        <div class="rc-sum-box">
          <div class="rc-sum-val" style="color:var(--ink-2)">3 / 28</div>
          <div class="rc-sum-label">Class Position</div>
        </div>
      </div>

      <!-- Conduct -->
      <div class="rc-section-title"><i class="fas fa-shield-alt" style="color:var(--gold)"></i> Conduct</div>
      <div class="conduct-row">
        <div class="conduct-score">36<span style="font-size:16px;color:var(--stone)">/40</span></div>
        <div class="conduct-bar-wrap">
          <div class="conduct-label">Conduct Score — Very Good</div>
          <div class="conduct-bar"><div class="conduct-fill" style="width:90%"></div></div>
          <div class="conduct-note">Conduct is displayed for reference only and is not included in academic totals.</div>
        </div>
      </div>

      <!-- Class Teacher Comment -->
      <div class="rc-section-title"><i class="fas fa-comment-dots" style="color:var(--gold)"></i> Teacher's Comment</div>
      <div class="rc-comment-box">
        <div class="rc-comment-title"><i class="fas fa-chalkboard-teacher"></i> Class Teacher</div>
        <div class="rc-comment-text">"Jean-Baptiste has shown remarkable dedication this term. His performance in technical subjects, particularly Computer Networking and Database Management, is commendable. He is encouraged to strengthen his English Language skills and continue his excellent work ethic. We look forward to his continued growth in Term 2."</div>
      </div>
      <div class="rc-comment-box" style="margin-top:-12px">
        <div class="rc-comment-title"><i class="fas fa-graduation-cap"></i> Director of Studies</div>
        <div class="rc-comment-text">"A strong academic performance this term. Jean-Baptiste demonstrates good understanding of his combination subjects. Consistent effort should be maintained going into the examination period."</div>
      </div>

    </div>

    <!-- Signature Area -->
    <div class="rc-signatures">
      <div class="sig-block">
        <div class="sig-line-area"><div class="sig-line"></div></div>
        <div class="sig-name">Class Teacher</div>
        <div class="sig-title">Mr. Patrick Nzeyimana</div>
        <div class="sig-date">Date: ___________</div>
      </div>
      <div class="sig-block">
        <div class="sig-line-area"><div class="sig-line"></div></div>
        <div class="sig-name">Director of Studies</div>
        <div class="sig-title">Mr. Innocent Habimana</div>
        <div class="sig-date">Date: ___________</div>
      </div>
      <div class="sig-block">
        <div class="sig-line-area"><div class="sig-line"></div></div>
        <div class="sig-name">Director of Discipline</div>
        <div class="sig-title">Ms. Beatrice Mukamana</div>
        <div class="sig-date">Date: ___________</div>
      </div>
      <div class="sig-block">
        <div class="sig-line-area"><div class="sig-line"></div></div>
        <div class="sig-name">Head Teacher / Principal</div>
        <div class="sig-title">Ms. Claudine Uwimana</div>
        <div class="sig-date" style="color:var(--stone);font-style:italic;margin-top:8px">[ School Stamp ]</div>
      </div>
    </div>

    <div class="rc-footer">
      <div class="rc-footer-text">This report card is official only when bearing the school stamp and authorized signatures. · ASPEJ School, Kigali, Rwanda · Academic Year 2024–2025</div>
    </div>

  </div><!-- /report-card-doc -->
</div><!-- /page-report -->

<!-- ═══════════════════════════════════════════════════
     ANNOUNCEMENTS PAGE
═══════════════════════════════════════════════════ -->
<div id="page-announcements" class="page">
  <nav class="ann-nav">
    <div class="ann-nav-brand">🏫 <span style="color:var(--gold)">ASPEJ</span> School</div>
    <div class="ann-nav-links">
      <a onclick="goBack()">← Back to Portal</a>
      <a onclick="goToContact()">Contact Secretary</a>
    </div>
    <button class="ann-nav-btn" onclick="goBack()">Student Portal</button>
  </nav>

  <div class="ann-hero">
    <div class="ann-hero-eyebrow"><i class="fas fa-bullhorn"></i> School Announcements</div>
    <h1>Latest <em>News</em> &amp; Updates</h1>
    <p class="ann-hero-sub">Stay informed about school events, exam schedules, and important notices.</p>
  </div>

  <div class="ann-content">
    <div class="ann-filter">
      <button class="ann-filter-btn active" onclick="filterAnn('all',this)">All Posts</button>
      <button class="ann-filter-btn" onclick="filterAnn('pinned',this)"><i class="fas fa-thumbtack"></i> Pinned</button>
      <button class="ann-filter-btn" onclick="filterAnn('files',this)"><i class="fas fa-paperclip"></i> With Attachments</button>
    </div>

    <div class="ann-grid" id="ann-grid">
      <!-- Filled by JS -->
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     CONTACT SECRETARY (no login needed)
═══════════════════════════════════════════════════ -->
<div id="page-contact" class="page">
  <nav class="ann-nav">
    <div class="ann-nav-brand">🏫 <span style="color:var(--gold)">ASPEJ</span> School</div>
    <div class="ann-nav-links">
      <a onclick="navigateTo('page-login')">Login</a>
      <a onclick="goToAnnouncements()">Announcements</a>
    </div>
    <button class="ann-nav-btn" onclick="navigateTo('page-login')">Portal Login</button>
  </nav>

  <div class="contact-hero">
    <div class="ann-hero-eyebrow" style="margin-bottom:14px"><i class="fas fa-envelope"></i> Contact Us</div>
    <h1>Message the School Secretary</h1>
    <p>No account required. Parents &amp; visitors can message the school directly.</p>
  </div>

  <div class="contact-content">
    <div class="contact-form-card">
      <div class="contact-form-title">Send a Message</div>
      <div class="contact-form-sub">Fill in the form below and the school secretary will respond as soon as possible.</div>
      
      <div id="contact-success" class="hidden">
        <div class="success-screen" style="padding:40px 0">
          <div class="success-icon"><i class="fas fa-check"></i></div>
          <h3>Message Sent!</h3>
          <p>Your message has been delivered to the school secretary. Expect a response within 1–2 business days.</p>
        </div>
        <button class="contact-submit" style="margin-top:16px" onclick="resetContactForm()"><i class="fas fa-redo"></i> Send Another Message</button>
      </div>

      <div id="contact-form">
        <div class="contact-field-2">
          <div class="contact-field">
            <label>Your Name <span style="color:var(--danger)">*</span></label>
            <input type="text" id="c-name" placeholder="Full name">
          </div>
          <div class="contact-field">
            <label>Phone Number</label>
            <input type="tel" id="c-phone" placeholder="+250 7xx xxx xxx">
          </div>
        </div>
        <div class="contact-field">
          <label>Email Address</label>
          <input type="email" id="c-email" placeholder="your@email.com (optional)">
        </div>
        <div class="contact-field">
          <label>Subject <span style="color:var(--danger)">*</span></label>
          <input type="text" id="c-subject" placeholder="e.g. Question about admission, fee payment…">
        </div>
        <div class="contact-field">
          <label>Your Message <span style="color:var(--danger)">*</span></label>
          <textarea id="c-message" rows="5" placeholder="Write your message here…"></textarea>
        </div>
        <div id="contact-error" class="alert alert-danger hidden" style="margin-bottom:12px"></div>
        <button class="contact-submit" onclick="submitContactForm()">
          <i class="fas fa-paper-plane"></i> Send Message to Secretary
        </button>
      </div>
    </div>

    <div>
      <div class="contact-info-card">
        <div class="contact-info-title">School Contact Details</div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-school"></i></div>
          <div class="contact-info-text"><strong>ASPEJ School</strong><span>Excellence · Discipline · Innovation</span></div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="contact-info-text"><strong>Kigali, Rwanda</strong><span>Main Campus Address</span></div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
          <div class="contact-info-text"><strong>+250 788 000 000</strong><span>Office Hours: 7:30 AM – 5:00 PM</span></div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
          <div class="contact-info-text"><strong>info@aspej.edu</strong><span>General Inquiries</span></div>
        </div>
        <div class="no-account-note">
          <strong><i class="fas fa-info-circle"></i> No Account Required</strong>
          This form is open to all parents, guardians, and visitors. You do not need a school portal account to contact us.
        </div>
      </div>
      <div style="background:var(--white);border-radius:var(--r-lg);border:1px solid var(--border);padding:24px;margin-top:16px;text-align:center">
        <p style="font-size:13px;color:var(--stone);margin-bottom:14px">Are you a student? Access your reports and inbox:</p>
        <button onclick="navigateTo('page-login')" style="background:var(--ink);color:white;border:none;border-radius:10px;padding:10px 20px;font-family:'Outfit',sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px"><i class="fas fa-user-graduate"></i> Student Portal Login</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════ -->

<!-- Compose Message -->
<div id="modal-compose" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">New Message</div>
      <button class="modal-close" onclick="closeModal('modal-compose')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-sub">Send a message to a teacher or school leader.</div>
    <div class="field"><label>To</label>
      <select id="compose-to" style="width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:var(--r);font-family:'Outfit',sans-serif;font-size:13px;color:var(--ink);background:var(--white);outline:none;transition:border-color .15s;appearance:none;background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238B8070' d='M6 8L1 3h10z'/%3E%3C/svg%3E&quot;);background-repeat:no-repeat;background-position:right 14px center;margin-bottom:14px">
        <option value="">— Select recipient —</option>
        <optgroup label="Teachers">
          <option>Mr. Patrick Nzeyimana (Class Teacher)</option>
          <option>Mr. Emmanuel Nkurunziza (Networking)</option>
          <option>Ms. Solange Ingabire (English)</option>
        </optgroup>
        <optgroup label="School Leaders">
          <option>Mr. Innocent Habimana (Director of Studies)</option>
          <option>Ms. Beatrice Mukamana (Dir. of Discipline)</option>
          <option>Ms. Claudine Uwimana (Principal)</option>
          <option>Emmanuel Nkurunziza (Accountant)</option>
        </optgroup>
      </select>
    </div>
    <div class="field"><label>Subject</label>
      <input type="text" id="compose-subject" placeholder="What's this about?" style="width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:var(--r);font-family:'Outfit',sans-serif;font-size:13px;color:var(--ink);outline:none;transition:border-color .15s;margin-bottom:14px">
    </div>
    <div class="field"><label>Message <span style="color:var(--danger)">*</span></label>
      <textarea id="compose-body" rows="4" placeholder="Type your message…" style="width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:var(--r);font-family:'Outfit',sans-serif;font-size:13px;color:var(--ink);outline:none;resize:none;transition:border-color .15s;margin-bottom:4px"></textarea>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal('modal-compose')">Cancel</button>
      <button class="btn-submit" onclick="sendCompose()"><i class="fas fa-paper-plane"></i> Send Message</button>
    </div>
  </div>
</div>

<!-- Forgot Password -->
<div id="modal-forgot" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Reset Password</div>
      <button class="modal-close" onclick="closeModal('modal-forgot')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-sub">Enter your registration number and we'll send reset instructions to your email.</div>
    <div id="forgot-form-inner">
      <div class="field">
        <label>Registration Number</label>
        <input type="text" id="forgot-reg" placeholder="e.g. 1045" style="width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:var(--r);font-family:'Outfit',sans-serif;font-size:13px;color:var(--ink);outline:none;transition:border-color .15s;margin-bottom:14px">
      </div>
      <div class="field">
        <label>Email Address</label>
        <input type="email" id="forgot-email" placeholder="your@email.com" style="width:100%;padding:11px 14px;border:2px solid var(--border);border-radius:var(--r);font-family:'Outfit',sans-serif;font-size:13px;color:var(--ink);outline:none;transition:border-color .15s;margin-bottom:4px">
      </div>
      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModal('modal-forgot')">Cancel</button>
        <button class="btn-submit" onclick="submitForgot()"><i class="fas fa-envelope"></i> Send Reset Link</button>
      </div>
    </div>
    <div id="forgot-success" class="hidden">
      <div class="success-screen">
        <div class="success-icon"><i class="fas fa-envelope"></i></div>
        <h3>Email Sent!</h3>
        <p>If an account with that registration number exists, a password reset link has been sent to the associated email address.</p>
      </div>
      <div style="margin-top:16px">
        <button class="btn-submit" style="width:100%;justify-content:center" onclick="closeModal('modal-forgot')">Back to Login</button>
      </div>
    </div>
  </div>
</div>

<!-- Announcement Popup -->
<div id="modal-announcement" class="ann-modal-overlay" onclick="closeAnnModal(event)">
  <div class="ann-modal">
    <div class="ann-modal-top" id="ann-modal-top">
      <div class="ann-modal-top-emoji" id="ann-modal-emoji">📢</div>
      <button class="ann-modal-close" onclick="closeAnnModal(null,true)"><i class="fas fa-times"></i></button>
    </div>
    <div class="ann-modal-body">
      <div class="ann-modal-meta">
        <span class="ann-modal-date" id="ann-modal-date"></span>
        <span class="ann-modal-badge" id="ann-modal-category"></span>
      </div>
      <div class="ann-modal-title" id="ann-modal-title"></div>
      <div class="ann-modal-content" id="ann-modal-content"></div>
      <div class="ann-download-box" id="ann-modal-download" style="display:none">
        <div class="ann-file-info">
          <div class="ann-file-icon"><i class="fas fa-file-pdf"></i></div>
          <div>
            <div class="ann-file-name" id="ann-dl-name"></div>
            <div class="ann-file-size" id="ann-dl-size"></div>
          </div>
        </div>
        <a href="#" class="ann-download-btn" id="ann-dl-btn" download>
          <i class="fas fa-download"></i> Download
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
/* ── DATA ── */
const SUBJECTS = [
  { name:'English Language',       t1:72, t2:74, exam:70 },
  { name:'Kinyarwanda',            t1:80, t2:78, exam:82 },
  { name:'Entrepreneurship',       t1:85, t2:88, exam:86 },
  { name:'Physical Education',     t1:90, t2:92, exam:88 },
  { name:'History & Moral Educ.', t1:68, t2:70, exam:65 },
  { name:'Computer Networking',    t1:88, t2:86, exam:84 },
  { name:'Web Technologies',       t1:79, t2:82, exam:80 },
  { name:'Database Management',    t1:84, t2:82, exam:86 },
  { name:'Operating Systems',      t1:70, t2:72, exam:74 },
  { name:'ICT Project',            t1:78, t2:80, exam:76 },
];

const ANNOUNCEMENTS = [
  {
    id:1, emoji:'📢', pinned:true, hasFile:true,
    category:'Academic', date:'February 28, 2025',
    title:'Term 1 Examination Schedule Released',
    excerpt:'The Term 1 examination timetable has been finalized. Exams run from 15–29 November 2025.',
    content:`<p>The Term 1 examination timetable has been finalized and approved by the Director of Studies.</p>
    <p>Examinations will be held from <strong>15 November to 29 November 2025</strong>. Students are advised to prepare thoroughly and arrive 30 minutes before their scheduled exam time.</p>
    <p>Key reminders:</p><ul><li>Students must bring their school ID to every exam</li><li>No electronic devices permitted in exam rooms</li><li>Results will be released two weeks after the final exam</li></ul>
    <p>A downloadable PDF of the full timetable is attached below. Contact the Director of Studies with any questions.</p>`,
    fileName:'Term1_Exam_Timetable_2025.pdf', fileSize:'245 KB'
  },
  {
    id:2, emoji:'🎓', pinned:false, hasFile:false,
    category:'General', date:'February 20, 2025',
    title:'Welcome to Academic Year 2024–2025',
    excerpt:'We welcome all students, staff and parents to the new academic year. Classes begin September 2.',
    content:`<p>Dear students, parents, and staff,</p><p>We are pleased to welcome everyone to the new academic year 2024–2025. This year brings exciting new programs, improved facilities, and a renewed commitment to excellence.</p><p>Classes begin on <strong>Monday 2 September 2024</strong>. All students should report by 7:30 AM in full school uniform.</p><p>We wish everyone a productive and successful year!</p>`
  },
  {
    id:3, emoji:'💰', pinned:false, hasFile:true,
    category:'Finance', date:'February 10, 2025',
    title:'Term 2 Fee Payment Deadline',
    excerpt:'Term 2 school fees of RWF 50,000 are due by March 15, 2025. Late fees will apply thereafter.',
    content:`<p>This is a reminder that <strong>Term 2 tuition fees of RWF 50,000</strong> are due no later than <strong>March 15, 2025</strong>.</p><p>Payment can be made via:</p><ul><li>Mobile Money (MTN or Airtel)</li><li>Bank transfer to ASPEJ Account No. 0123456789</li><li>Cash payment at the school accounts office</li></ul><p>Students with outstanding fees after the deadline may be temporarily restricted from accessing their portal reports. Please contact the accounts office for any payment plan arrangements.</p>`,
    fileName:'Fee_Payment_Guide_T2_2025.pdf', fileSize:'128 KB'
  },
  {
    id:4, emoji:'🏆', pinned:false, hasFile:false,
    category:'Events', date:'February 1, 2025',
    title:'Inter-School Science & Technology Fair',
    excerpt:'ASPEJ will participate in the annual Inter-School S&T Fair on April 5, 2025. Project submissions due March 20.',
    content:`<p>ASPEJ School is proud to announce its participation in the <strong>Annual Inter-School Science & Technology Fair 2025</strong>.</p><p>The event will be held on <strong>April 5, 2025</strong> at Kigali Convention Centre. All NIT and BDC students are encouraged to participate.</p><p>Project submission deadline: <strong>March 20, 2025</strong>. Speak with your class teacher or the Director of Studies to register your project idea.</p>`
  },
  {
    id:5, emoji:'📚', pinned:false, hasFile:false,
    category:'Library', date:'January 25, 2025',
    title:'Library Extended Hours – Exam Season',
    excerpt:'The school library will be open until 8:00 PM Monday–Friday during the examination period.',
    content:`<p>To support students during the upcoming examination season, the school library will operate on <strong>extended hours</strong>:</p><ul><li><strong>Monday–Friday:</strong> 7:00 AM – 8:00 PM</li><li><strong>Saturday:</strong> 8:00 AM – 4:00 PM</li><li>Sunday: Closed</li></ul><p>All students with valid school IDs are welcome. Please maintain a quiet environment and return all borrowed books on time.</p>`
  },
  {
    id:6, emoji:'🎭', pinned:false, hasFile:false,
    category:'Events', date:'January 15, 2025',
    title:'Cultural Day — March 28, 2025',
    excerpt:'Annual Cultural Day celebration on March 28. Students are invited to showcase their heritage through food, music, and art.',
    content:`<p>Mark your calendars! ASPEJ School's Annual <strong>Cultural Celebration Day</strong> will take place on <strong>March 28, 2025</strong>.</p><p>This is an opportunity for students to celebrate Rwanda's rich cultural heritage and showcase talents in music, dance, traditional cuisine, and art.</p><p>Class representatives will be coordinating participation details. All students and parents are welcome.</p>`
  },
];

/* ── NAVIGATION ── */
let prevPage = 'page-login';
function navigateTo(id) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const target = document.getElementById(id);
  if (target) target.classList.add('active');
}
function goBack() { navigateTo(prevPage || 'page-dashboard'); }

/* ── LOGIN ── */
function switchTab(tab) {
  document.querySelectorAll('.form-tab').forEach((t,i) => {
    t.classList.toggle('active', (i===0&&tab==='login')||(i===1&&tab==='register'));
  });
  document.getElementById('tab-login').classList.toggle('active', tab==='login');
  document.getElementById('tab-register').classList.toggle('active', tab==='register');
}

function doLogin() {
  const u = document.getElementById('login-user').value.trim();
  const p = document.getElementById('login-pass').value;
  if ((u==='1045'||u==='student'||u==='jean')&&(p==='student123'||p==='admin'||p==='Admin@1234')){
    document.getElementById('login-error').classList.add('hidden');
    navigateTo('page-dashboard');
    showSection('home');
  } else {
    document.getElementById('login-error').classList.remove('hidden');
  }
}
document.getElementById('login-pass')?.addEventListener('keydown', e=>{ if(e.key==='Enter') doLogin(); });
document.getElementById('login-user')?.addEventListener('keydown', e=>{ if(e.key==='Enter') doLogin(); });

function doRegister() {
  const regNo   = document.getElementById('reg-number').value.trim();
  const name    = document.getElementById('reg-name').value.trim();
  const username= document.getElementById('reg-username').value.trim();
  const pass    = document.getElementById('reg-pass').value;
  const pass2   = document.getElementById('reg-pass2').value;
  const err     = document.getElementById('reg-error');
  err.classList.add('hidden');
  if (!regNo||!name||!username||!pass) { err.innerHTML='<i class="fas fa-circle-exclamation"></i> Please fill in all required fields.'; err.classList.remove('hidden'); return; }
  if (pass.length<6) { err.innerHTML='<i class="fas fa-circle-exclamation"></i> Password must be at least 6 characters.'; err.classList.remove('hidden'); return; }
  if (pass!==pass2) { err.innerHTML='<i class="fas fa-circle-exclamation"></i> Passwords do not match.'; err.classList.remove('hidden'); return; }
  // Simulate: check student exists (in real PHP this checks DB)
  // For demo accept any non-empty values
  document.getElementById('reg-form').classList.add('hidden');
  document.getElementById('reg-success').classList.remove('hidden');
}

function doLogout() {
  navigateTo('page-login');
  switchTab('login');
}

function openForgotModal() { openModal('modal-forgot'); }
function submitForgot() {
  const r=document.getElementById('forgot-reg').value.trim();
  const e=document.getElementById('forgot-email').value.trim();
  if(!r||!e){return;}
  document.getElementById('forgot-form-inner').classList.add('hidden');
  document.getElementById('forgot-success').classList.remove('hidden');
}

/* ── DASHBOARD ── */
function showSection(s) {
  document.querySelectorAll('.section').forEach(el=>el.classList.remove('active'));
  document.getElementById('section-'+s)?.classList.add('active');
  document.querySelectorAll('.nav-item').forEach(el=>el.classList.remove('active'));
  event?.currentTarget?.classList.add('active');
  const titles={'home':'Dashboard','reports':'My Reports','inbox':'Inbox'};
  document.getElementById('topbar-title').textContent = titles[s]||'Dashboard';
}

function goToReport() {
  prevPage='page-dashboard';
  buildReportTable();
  navigateTo('page-report');
}
function goToAnnouncements() {
  prevPage=document.querySelector('.page.active')?.id||'page-login';
  buildAnnouncements();
  navigateTo('page-announcements');
}
function goToContact() {
  prevPage=document.querySelector('.page.active')?.id||'page-login';
  navigateTo('page-contact');
}

/* ── REPORT TABLE ── */
function gradeInfo(score) {
  if(score>=80) return {letter:'A',cls:'grade-A'};
  if(score>=70) return {letter:'B',cls:'grade-B'};
  if(score>=60) return {letter:'C',cls:'grade-C'};
  if(score>=50) return {letter:'D',cls:'grade-C'};
  return {letter:'F',cls:'grade-F'};
}
function buildReportTable() {
  const tbody=document.getElementById('report-tbody');
  tbody.innerHTML='';
  let sum=0;
  SUBJECTS.forEach(s=>{
    const total=(s.t1*0.25+s.t2*0.25+s.exam*0.5).toFixed(1);
    sum+=parseFloat(total);
    const g=gradeInfo(parseFloat(total));
    const pass=parseFloat(total)>=50;
    tbody.innerHTML+=`<tr>
      <td style="font-weight:600">${s.name}</td>
      <td style="text-align:center">${s.t1}</td>
      <td style="text-align:center">${s.t2}</td>
      <td style="text-align:center">${s.exam}</td>
      <td style="text-align:center" class="score-cell">${total}</td>
      <td style="text-align:center"><span class="grade-cell ${g.cls}">${g.letter}</span></td>
      <td style="text-align:center"><span class="grade-pill ${pass?'grade-A':'grade-F'}" style="font-size:11px">${pass?'PASS':'FAIL'}</span></td>
    </tr>`;
  });
  const avg=(sum/SUBJECTS.length).toFixed(1);
  document.getElementById('report-avg-cell').textContent=avg;
}

/* ── PDF DOWNLOAD ── */
function downloadPDF() {
  const el=document.getElementById('report-card-doc');
  const btns=document.querySelectorAll('.report-action-bar');
  btns.forEach(b=>b.style.display='none');
  html2pdf().set({
    margin:[8,8,8,8],
    filename:'ASPEJ_Report_JBMugisha_T1_2025.pdf',
    image:{type:'jpeg',quality:1},
    html2canvas:{scale:2,useCORS:true},
    jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}
  }).from(el).save().then(()=>btns.forEach(b=>b.style.display=''));
}

/* ── INBOX ── */
const THREADS=[
  {from:'Mr. Innocent Habimana (DoS)',subject:'RE: Database Management Assignment – Grade Update'},
  {from:'Ms. Claudine Uwimana (Principal)',subject:'Welcome to Term 2'},
  {from:'Mr. Emmanuel Nkurunziza',subject:'Well done on your Networking exam!'},
  {from:'Director of Discipline',subject:'Conduct score update'},
];
function selectThread(i) {
  document.querySelectorAll('.inbox-item').forEach((el,j)=>el.classList.toggle('active',i===j));
  const msgs=[
    `<div class="message-bubble"><div class="msg-avatar teacher">${THREADS[i].from[0]+THREADS[i].from[1]}</div><div class="msg-content"><div class="msg-name">${THREADS[i].from}</div><div class="msg-bubble received">This is a message in this thread. Click reply to respond.</div><div class="msg-time">Today · 9:00 AM</div></div></div>`,
  ];
  document.getElementById('inbox-thread').innerHTML=`
    <div class="inbox-thread-header"><div class="thread-subject">${THREADS[i].subject}</div><div class="thread-meta">With ${THREADS[i].from}</div></div>
    <div class="inbox-messages" id="thread-messages">${msgs.join('')}</div>
    <div class="inbox-reply"><textarea class="reply-input" id="reply-text" placeholder="Write a reply…" rows="1" onkeydown="handleReplyKey(event)"></textarea><button class="send-btn" onclick="sendReply()"><i class="fas fa-paper-plane"></i></button></div>`;
}
function handleReplyKey(e) { if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendReply();} }
function sendReply() {
  const t=document.getElementById('reply-text');
  if(!t||!t.value.trim()) return;
  const m=document.getElementById('thread-messages');
  m.innerHTML+=`<div class="message-bubble sent"><div class="msg-avatar student">JB</div><div class="msg-content"><div class="msg-name">You</div><div class="msg-bubble sent-bubble">${t.value}</div><div class="msg-time">Just now</div></div></div>`;
  t.value='';
  m.scrollTop=m.scrollHeight;
}

function openComposeModal() { openModal('modal-compose'); }
function sendCompose() {
  const to=document.getElementById('compose-to').value;
  const body=document.getElementById('compose-body').value;
  if(!to||!body.trim()) return;
  closeModal('modal-compose');
  document.getElementById('compose-to').value='';
  document.getElementById('compose-subject').value='';
  document.getElementById('compose-body').value='';
  // Show brief toast-like feedback
  const toast=document.createElement('div');
  toast.style.cssText='position:fixed;bottom:24px;right:24px;background:var(--ink);color:white;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;align-items:center;gap:8px';
  toast.innerHTML='<i class="fas fa-check-circle" style="color:var(--gold-lt)"></i> Message sent!';
  document.body.appendChild(toast);
  setTimeout(()=>toast.remove(),3000);
}

/* ── ANNOUNCEMENTS ── */
function buildAnnouncements() {
  const grid=document.getElementById('ann-grid');
  grid.innerHTML=ANNOUNCEMENTS.map(a=>`
    <div class="ann-card ${a.pinned?'pinned':''}" data-pinned="${a.pinned?1:0}" data-hasfile="${a.hasFile?1:0}" onclick="openAnnouncement(${a.id})">
      <div class="ann-img">
        <div class="ann-img-emoji">${a.emoji}</div>
        ${a.pinned?'<div class="ann-pin-tag"><i class="fas fa-thumbtack"></i> Pinned</div>':''}
        ${a.hasFile?'<div class="ann-attach-tag"><i class="fas fa-paperclip"></i> Attachment</div>':''}
      </div>
      <div class="ann-body">
        <div class="ann-date">${a.date}</div>
        <div class="ann-card-title">${a.title}</div>
        <div class="ann-excerpt">${a.excerpt}</div>
      </div>
      <div class="ann-footer">
        <div class="ann-author">Posted by ASPEJ Admin</div>
        <div class="ann-read-more">Read more <i class="fas fa-chevron-right"></i></div>
      </div>
    </div>`).join('');
}

function filterAnn(type, btn) {
  document.querySelectorAll('.ann-filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.ann-card').forEach(c=>{
    if(type==='all') c.style.display='';
    else if(type==='pinned') c.style.display=c.dataset.pinned==='1'?'':'none';
    else if(type==='files') c.style.display=c.dataset.hasfile==='1'?'':'none';
  });
}

function openAnnouncement(id) {
  const a=ANNOUNCEMENTS.find(x=>x.id===id);
  if(!a) return;
  document.getElementById('ann-modal-emoji').textContent=a.emoji;
  document.getElementById('ann-modal-date').textContent=a.date;
  document.getElementById('ann-modal-category').textContent=a.category;
  document.getElementById('ann-modal-title').textContent=a.title;
  document.getElementById('ann-modal-content').innerHTML=a.content;
  const dlBox=document.getElementById('ann-modal-download');
  if(a.hasFile){
    document.getElementById('ann-dl-name').textContent=a.fileName;
    document.getElementById('ann-dl-size').textContent=a.fileSize;
    dlBox.style.display='flex';
  } else { dlBox.style.display='none'; }
  document.getElementById('modal-announcement').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeAnnModal(e,force=false) {
  if(force||e?.target===document.getElementById('modal-announcement')) {
    document.getElementById('modal-announcement').classList.remove('open');
    document.body.style.overflow='';
  }
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape'){closeAnnModal(null,true);closeAllModals();} });

/* ── CONTACT ── */
function submitContactForm() {
  const name=document.getElementById('c-name').value.trim();
  const subject=document.getElementById('c-subject').value.trim();
  const msg=document.getElementById('c-message').value.trim();
  const err=document.getElementById('contact-error');
  err.classList.add('hidden');
  if(!name||!subject||!msg){
    err.innerHTML='<i class="fas fa-circle-exclamation"></i> Please fill in your name, subject, and message.';
    err.classList.remove('hidden'); return;
  }
  document.getElementById('contact-form').classList.add('hidden');
  document.getElementById('contact-success').classList.remove('hidden');
}
function resetContactForm() {
  document.getElementById('contact-form').classList.remove('hidden');
  document.getElementById('contact-success').classList.add('hidden');
  ['c-name','c-phone','c-email','c-subject','c-message'].forEach(id=>document.getElementById(id).value='');
}

/* ── MODALS ── */
function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
function closeAllModals() { document.querySelectorAll('.modal-overlay').forEach(m=>{m.classList.remove('open');}); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',e=>{ if(e.target===m) closeModal(m.id); });
});

/* ── INIT ── */
buildReportTable();
buildAnnouncements();
</script>
</body>
</html>
