<?php
// ============================================================
// portals.php  –  Portal selection page
// ============================================================
$page_title  = 'Portals';
$current_nav = 'portals';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/school-hero.jpg" alt="School building" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">Community Portals</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">Access your dedicated resources. Please select your portal to log in.</p>
    </div>
</section>

<!-- Portal Cards -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">

        <?php
        $portals = [
            [
                'icon'   => 'fa-user-graduate',
                'title'  => 'Student Portal',
                'desc'   => 'Access your grades, attendance, assignments, and report cards.',
                'link'   => '/portal-student.php',
                'active' => true,
                'delay'  => 100,
            ],
            [
                'icon'   => 'fa-user-friends',
                'title'  => 'Parent Portal',
                'desc'   => 'Track your child\'s progress, view announcements, and communicate with teachers.',
                'link'   => '#',
                'active' => false,
                'delay'  => 200,
            ],
            [
                'icon'   => 'fa-chalkboard-teacher',
                'title'  => 'Teacher Portal',
                'desc'   => 'Manage your classes, enter grades, take attendance, and post assignments.',
                'link'   => '#',
                'active' => false,
                'delay'  => 300,
            ],
        ];
        foreach ($portals as $portal):
        ?>
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl text-center flex flex-col items-center border-t-4 border-aspej-gold"
             data-aos="fade-up" data-aos-delay="<?= $portal['delay'] ?>">
            <div class="bg-aspej-gold text-aspej-navy rounded-full h-20 w-20 flex items-center justify-center mb-6">
                <i class="fas <?= $portal['icon'] ?> text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-aspej-navy dark:text-white mb-3"><?= $portal['title'] ?></h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6"><?= htmlspecialchars($portal['desc']) ?></p>
            <a href="<?= $portal['link'] ?>"
               class="mt-auto inline-block bg-aspej-navy text-white font-bold py-3 px-8 rounded-full text-lg shadow-lg transition-all duration-300
                      <?= $portal['active'] ? 'hover:bg-opacity-90' : 'opacity-50 cursor-not-allowed pointer-events-none' ?>"
               <?= $portal['active'] ? '' : 'title="Coming Soon"' ?>>
                <?= $portal['active'] ? 'Log In' : 'Coming Soon' ?>
                <?php if ($portal['active']): ?><i class="fas fa-arrow-right ml-2"></i><?php endif; ?>
            </a>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
