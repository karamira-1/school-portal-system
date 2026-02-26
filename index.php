<?php
// ============================================================
// index.php  –  ASPEJ School Homepage
// ============================================================
require_once __DIR__ . '/includes/db.php';

$page_title  = 'Home';
$current_nav = 'home';

// Fetch latest 3 news items from DB
$pdo  = get_db();
$stmt = $pdo->query('SELECT * FROM news_events ORDER BY published_at DESC LIMIT 3');
$news = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy h-[70vh] flex items-center justify-center overflow-hidden">
    <img src="assets/images/school-hero.jpg" alt="School building and students" class="absolute inset-0 w-full h-full object-cover opacity-30">
    <div class="relative z-10 text-center p-6" data-aos="fade-up">
        <h1 class="text-5xl sm:text-7xl font-extrabold text-white leading-tight">
            <span class="block">Welcome to</span>
            <span class="block text-aspej-gold">ASPEJ School</span>
        </h1>
        <p class="mt-4 text-xl text-gray-200 italic font-light max-w-xl mx-auto">Educating the future, today.</p>
        <a href="/admissions.php" class="mt-8 inline-block bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-3 px-8 rounded-full text-lg shadow-xl transition-all duration-300 transform hover:scale-105">
            Admissions Open <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</section>

<!-- Mission snippet -->
<section class="py-16 bg-white dark:bg-gray-800">
    <div class="max-w-5xl mx-auto px-4 text-center" data-aos="fade-up" data-aos-delay="200">
        <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-4">A Tradition of Excellence</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
            At ASPEJ School, we foster a nurturing and challenging environment where every student is inspired to
            achieve their full potential. Our holistic approach ensures academic success, personal growth, and
            responsible citizenship.
        </p>
        <a href="/about.php" class="mt-6 inline-block text-aspej-navy dark:text-aspej-gold font-semibold hover:underline">
            Read Our Mission <i class="fas fa-chevron-right ml-1 text-sm"></i>
        </a>
    </div>
</section>

<!-- Latest News (populated from DB) -->
<section class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-aspej-navy dark:text-white mb-10" data-aos="fade-up">Latest News & Events</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($news as $i => $item): ?>
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg overflow-hidden transition-transform duration-300 hover:shadow-xl hover:scale-[1.02]"
                 data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <img class="h-48 w-full object-cover"
                     src="<?= htmlspecialchars($item['image']) ?>"
                     alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="p-6">
                    <span class="text-xs font-semibold uppercase tracking-wider text-aspej-gold">
                        <?= htmlspecialchars($item['type']) ?>
                    </span>
                    <h3 class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                        <?= htmlspecialchars($item['title']) ?>
                    </h3>
                    <p class="mt-3 text-gray-600 dark:text-gray-300 line-clamp-2">
                        <?= htmlspecialchars($item['summary']) ?>
                    </p>
                    <a href="<?= htmlspecialchars($item['link']) ?>"
                       class="mt-4 inline-block text-sm text-aspej-navy dark:text-aspej-gold font-medium hover:underline">
                        Read More &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="/news.php" class="inline-block bg-aspej-navy dark:bg-aspej-gold text-white dark:text-aspej-navy font-semibold py-3 px-6 rounded-lg hover:opacity-90 transition-opacity">
                View All News & Events
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
