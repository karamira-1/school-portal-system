<?php
// ============================================================
// includes/admin_header.php  –  Admin portal shell
// $admin_section  – active sidebar section key
// $admin_title    – page title
// ============================================================
if (!isset($admin_section)) $admin_section = 'dashboard';
if (!isset($admin_title))   $admin_title   = 'Admin';

$nav_items = [
    'dashboard'  => ['icon' => 'fa-tachometer-alt',   'label' => 'Dashboard'],
    'users'      => ['icon' => 'fa-users-cog',         'label' => 'User Management'],
    'students'   => ['icon' => 'fa-user-graduate',     'label' => 'Students'],
    'analytics'  => ['icon' => 'fa-chart-bar',         'label' => 'Analytics'],
    'news'       => ['icon' => 'fa-newspaper',         'label' => 'News / CMS'],
    'attendance' => ['icon' => 'fa-calendar-check',    'label' => 'Attendance'],
    'fees'       => ['icon' => 'fa-money-bill-wave',   'label' => 'Fee Payments'],
    'chat'       => ['icon' => 'fa-comments',          'label' => 'Messages'],
    'alerts'     => ['icon' => 'fa-bell',              'label' => 'Alerts'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ Admin – <?= htmlspecialchars($admin_title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { 'aspej-navy':'#1D2A4D','aspej-gold':'#FFC72C' } } }
        };
    </script>
    <?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body class="bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 transition-colors duration-300">

<div class="flex h-screen overflow-hidden">

    <!-- ── Sidebar ────────────────────────────────────────── -->
    <aside id="sidebar"
           class="w-64 bg-aspej-navy flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out">

        <!-- Logo -->
        <div class="flex items-center space-x-3 px-6 py-5 border-b border-white/10">
            <img src="/assets/images/logo.png" alt="ASPEJ" class="h-9 w-auto">
            <div>
                <p class="text-white font-bold text-sm leading-tight">ASPEJ School</p>
                <p class="text-aspej-gold text-xs font-medium">Admin Panel</p>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <?php foreach ($nav_items as $key => $item): ?>
            <a href="/portal-admin.php?section=<?= $key ?>"
               class="admin-nav-link <?= $admin_section === $key ? 'active' : '' ?>">
                <i class="fas <?= $item['icon'] ?> w-5 text-center mr-3"></i>
                <?= $item['label'] ?>
                <?php if ($key === 'alerts' && !empty($alert_count)): ?>
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-0.5"><?= $alert_count ?></span>
                <?php endif; ?>
                <?php if ($key === 'chat' && !empty($msg_count)): ?>
                    <span class="ml-auto bg-aspej-gold text-aspej-navy text-xs rounded-full px-2 py-0.5"><?= $msg_count ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- User info + logout -->
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center space-x-3 mb-3">
                <img src="<?= htmlspecialchars($current_user['profile_image'] ?? '/assets/images/default-avatar.png') ?>"
                     alt="Avatar"
                     class="w-9 h-9 rounded-full object-cover border-2 border-aspej-gold">
                <div class="min-w-0">
                    <p class="text-white text-sm font-semibold truncate"><?= htmlspecialchars($current_user['full_name']) ?></p>
                    <p class="text-gray-400 text-xs capitalize"><?= $current_user['role_name'] ?></p>
                </div>
            </div>
            <a href="/api/logout.php" class="flex items-center text-gray-400 hover:text-white text-sm transition">
                <i class="fas fa-sign-out-alt mr-2"></i> Log Out
            </a>
        </div>
    </aside>

    <!-- ── Main wrapper ───────────────────────────────────── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="bg-white dark:bg-gray-900 shadow-sm px-6 py-3 flex items-center justify-between flex-shrink-0 z-10">
            <div class="flex items-center space-x-4">
                <button id="sidebar-toggle" class="text-gray-500 dark:text-gray-400 hover:text-aspej-navy dark:hover:text-white md:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-semibold text-aspej-navy dark:text-white"><?= htmlspecialchars($admin_title) ?></h1>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Notification bell -->
                <a href="/portal-admin.php?section=alerts" class="relative text-gray-500 dark:text-gray-400 hover:text-aspej-navy dark:hover:text-white">
                    <i class="fas fa-bell text-xl"></i>
                    <?php if (!empty($alert_count)): ?>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $alert_count ?></span>
                    <?php endif; ?>
                </a>
                <!-- Dark mode -->
                <button id="theme-toggle" class="text-gray-500 dark:text-gray-400 hover:text-aspej-navy dark:hover:text-white">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline-block"></i>
                </button>
                <!-- Public site link -->
                <a href="/index.php" target="_blank" class="text-sm text-gray-500 dark:text-gray-400 hover:text-aspej-gold transition">
                    <i class="fas fa-external-link-alt mr-1"></i>View Site
                </a>
            </div>
        </header>

        <!-- Page content injected here -->
        <main class="flex-1 overflow-y-auto p-6">
