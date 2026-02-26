<?php
// ============================================================
// about.php  –  About Us page
// ============================================================
$page_title  = 'About Us';
$current_nav = 'about';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/about-hero.jpg" alt="Diverse group of students" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">About ASPEJ School</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">Nurturing curiosity, fostering excellence, and building community since 1989.</p>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-right">
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-4">Our Mission</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-4">
                To provide a dynamic and inclusive learning environment that challenges students to achieve academic excellence,
                cultivate critical thinking, and develop strong moral character.
            </p>
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-4 mt-8">Our Vision</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                To be a leading educational institution recognized for inspiring students to become compassionate leaders,
                innovative thinkers, and responsible global citizens.
            </p>
        </div>
        <div data-aos="fade-left">
            <img src="assets/images/classroom.jpg" alt="Students in a classroom" class="rounded-lg shadow-xl object-cover w-full h-full">
        </div>
    </div>
</section>

<!-- History -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-aspej-navy dark:text-white mb-6" data-aos="fade-up">Our History</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Founded in 1989 by a group of dedicated educators and community leaders, ASPEJ School opened its doors with just
            50 students and a vision to create a new standard of education in the region. From our humble beginnings in a
            single building, we have grown into a vibrant campus serving over 1,200 students.
        </p>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl mx-auto mt-4" data-aos="fade-up" data-aos-delay="200">
            Throughout our history, we have remained committed to our founding principles of academic rigor, personal growth,
            and community service.
        </p>
    </div>
</section>

<!-- Leadership -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-aspej-navy dark:text-white mb-12" data-aos="fade-up">Meet Our Leadership</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $leaders = [
                ['name' => 'Murekatete Alphonsine', 'role' => 'Head of School',    'img' => 'leader-1.jpg'],
                ['name' => 'Ndemezo Eugeni',         'role' => 'School Accountant', 'img' => 'leader-2.jpg'],
                ['name' => 'Hategekimana J.Pierre',  'role' => 'School Secretary',  'img' => 'leader-3.jpg'],
                ['name' => 'Shimiyimana Aloys',      'role' => 'Director of Study', 'img' => 'leader-4.jpg'],
            ];
            foreach ($leaders as $delay => $leader):
            ?>
            <div class="text-center" data-aos="fade-up" data-aos-delay="<?= ($delay + 1) * 100 ?>">
                <img src="assets/images/<?= htmlspecialchars($leader['img']) ?>"
                     alt="<?= htmlspecialchars($leader['name']) ?>"
                     class="w-48 h-48 rounded-full mx-auto object-cover mb-4 shadow-lg border-4 border-aspej-gold">
                <h3 class="text-xl font-bold text-aspej-navy dark:text-white"><?= htmlspecialchars($leader['name']) ?></h3>
                <p class="text-aspej-gold font-semibold"><?= htmlspecialchars($leader['role']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
