<?php
// ============================================================
// includes/header.php  –  Shared HTML head + navigation
// $page_title  – set before including this file
// $current_nav – 'home'|'about'|'academics'|'admissions'|'news'|'contact'|'portals'
// ============================================================
if (!isset($page_title))  $page_title  = 'ASPEJ School';
if (!isset($current_nav)) $current_nav = '';

// Helper: adds 'current' class to the active nav link
function nav_class(string $page, string $current): string {
    return $page === $current ? 'nav-link current' : 'nav-link';
}
function mobile_nav_class(string $page, string $current): string {
    return $page === $current ? 'mobile-nav-link current' : 'mobile-nav-link';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASPEJ School – <?= htmlspecialchars($page_title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'aspej-navy': '#1D2A4D',
                        'aspej-gold': '#FFC72C',
                    }
                }
            }
        }
    </script>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-500">

<header class="sticky top-0 z-50 bg-white shadow-md dark:bg-aspej-navy/95 transition-all duration-300">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <div class="flex-shrink-0">
                <a href="/index.php" class="flex items-center space-x-2">
                    <img class="h-10 w-auto" src="/assets/images/logo.png" alt="ASPEJ School Logo">
                    <span class="text-xl font-bold text-aspej-navy dark:text-white">ASPEJ School</span>
                </a>
            </div>
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <a href="/index.php"       class="<?= nav_class('home',       $current_nav) ?>">Home</a>
                    <a href="/about.php"       class="<?= nav_class('about',      $current_nav) ?>">About Us</a>
                    <a href="/academics.php"   class="<?= nav_class('academics',  $current_nav) ?>">Academics</a>
                    <a href="/admissions.php"  class="<?= nav_class('admissions', $current_nav) ?>">Admissions</a>
                    <a href="/news.php"        class="<?= nav_class('news',       $current_nav) ?>">News</a>
                    <a href="/contact.php"     class="<?= nav_class('contact',    $current_nav) ?>">Contact</a>
                    <a href="/portals.php"     class="nav-link <?= $current_nav === 'portals' ? 'current' : '' ?> bg-aspej-gold hover:bg-yellow-500 text-aspej-navy font-semibold px-3 py-1.5 rounded-lg transition-colors">Portals</a>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-300 focus:outline-none" aria-label="Toggle dark mode">
                    <i class="fas fa-sun text-aspej-gold dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline-block text-white"></i>
                </button>
                <button id="mobile-menu-button" type="button" class="md:hidden text-gray-500 dark:text-gray-400 hover:text-aspej-navy dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-aspej-gold" aria-label="Open menu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 flex flex-col">
            <a href="/index.php"      class="<?= mobile_nav_class('home',       $current_nav) ?>">Home</a>
            <a href="/about.php"      class="<?= mobile_nav_class('about',      $current_nav) ?>">About Us</a>
            <a href="/academics.php"  class="<?= mobile_nav_class('academics',  $current_nav) ?>">Academics</a>
            <a href="/admissions.php" class="<?= mobile_nav_class('admissions', $current_nav) ?>">Admissions</a>
            <a href="/news.php"       class="<?= mobile_nav_class('news',       $current_nav) ?>">News</a>
            <a href="/contact.php"    class="<?= mobile_nav_class('contact',    $current_nav) ?>">Contact</a>
            <a href="/portals.php"    class="mobile-nav-link <?= $current_nav === 'portals' ? 'current' : '' ?> bg-aspej-gold text-aspej-navy">Portals</a>
        </div>
    </div>
</header>

<main>
