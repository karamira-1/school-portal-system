// ============================================================
// assets/js/script.js  –  Global JS for all public pages
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Mobile Menu Toggle ──────────────────────────────────
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu       = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // ── 2. Dark / Light Mode Toggle ───────────────────────────
    const themeToggle = document.getElementById('theme-toggle');

    const setTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    // Restore saved preference or follow system
    const savedTheme   = localStorage.getItem('theme');
    const prefersDark  = window.matchMedia('(prefers-color-scheme: dark)').matches;
    setTheme(savedTheme || (prefersDark ? 'dark' : 'light'));

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            setTheme(isDark ? 'light' : 'dark');
        });
    }

    // ── 3. Back-to-Top Button ─────────────────────────────────
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('opacity-100', window.scrollY > 300);
            backToTop.classList.toggle('opacity-0',   window.scrollY <= 300);
        });
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // ── 4. Smooth Scroll for Hash Links ───────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // ── 5. Active nav-link highlight based on current path ────
    const path = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href.includes(path) && path !== '') {
            link.classList.add('current');
        }
    });
});
