<?php
// ============================================================
// news.php  –  News & Events (all items from DB)
// ============================================================
require_once __DIR__ . '/includes/db.php';

$page_title  = 'News & Events';
$current_nav = 'news';

$pdo  = get_db();
$stmt = $pdo->query('SELECT * FROM news_events ORDER BY published_at DESC');
$news = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/news-hero.jpg" alt="School assembly" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">News & Events</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">Stay up-to-date with the latest happenings at ASPEJ School.</p>
    </div>
</section>

<!-- All news cards -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-aspej-navy dark:text-white mb-10" data-aos="fade-up">All Updates</h2>

        <?php if (empty($news)): ?>
        <p class="text-center text-gray-500 dark:text-gray-400">No news items found.</p>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($news as $i => $item): ?>
            <div id="article-<?= $item['id'] ?>"
                 class="bg-white dark:bg-gray-700 rounded-lg shadow-lg overflow-hidden transition-transform duration-300 hover:shadow-xl hover:scale-[1.02]"
                 data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
                <img class="h-48 w-full object-cover"
                     src="<?= htmlspecialchars($item['image']) ?>"
                     alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-aspej-gold">
                            <?= htmlspecialchars($item['type']) ?>
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            <?= date('M j, Y', strtotime($item['published_at'])) ?>
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        <?= htmlspecialchars($item['title']) ?>
                    </h3>
                    <p class="mt-3 text-gray-600 dark:text-gray-300">
                        <?= htmlspecialchars($item['summary']) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
