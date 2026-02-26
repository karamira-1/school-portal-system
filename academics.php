<?php
// ============================================================
// academics.php  –  Academics page
// ============================================================
$page_title  = 'Academics';
$current_nav = 'academics';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/academics-hero.jpg" alt="Student writing in a notebook" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">Academics</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">A curriculum designed to challenge, inspire, and prepare students for the future.</p>
    </div>
</section>

<!-- Philosophy -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-6" data-aos="fade-up">Our Academic Philosophy</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            At ASPEJ School, we believe in a holistic approach to education. Our curriculum combines rigorous academic standards
            with a strong emphasis on critical thinking, creativity, and real-world problem-solving. We provide a supportive
            environment where students are encouraged to ask questions, explore their interests, and take intellectual risks.
        </p>
    </div>
</section>

<!-- Programs -->
<section id="curriculum" class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $programs = [
                ['icon' => 'fa-shapes',  'title' => 'Primary School',    'delay' => 100,
                 'text' => 'Our primary program (Grades 1–6) focuses on building strong foundational skills in literacy and numeracy through inquiry-based learning and hands-on activities.'],
                ['icon' => 'fa-atom',    'title' => 'Lower Secondary',   'delay' => 200,
                 'text' => 'In lower secondary (Grades 7–9 / O-Level), students transition to a more specialised curriculum, exploring a wide range of subjects while developing key study skills.'],
                ['icon' => 'fa-scroll',  'title' => 'Upper Secondary',   'delay' => 300,
                 'text' => 'Our upper secondary (A-Level) offers diverse academic tracks — Sciences, Humanities, Languages, and ICT — preparing students for university and beyond.'],
            ];
            foreach ($programs as $p):
            ?>
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl text-center" data-aos="fade-up" data-aos-delay="<?= $p['delay'] ?>">
                <i class="fas <?= $p['icon'] ?> text-5xl text-aspej-gold mb-6"></i>
                <h3 class="text-2xl font-bold text-aspej-navy dark:text-white mb-3"><?= $p['title'] ?></h3>
                <p class="text-gray-600 dark:text-gray-300"><?= $p['text'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Extracurricular -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-right">
            <img src="assets/images/robotics.jpg" alt="Robotics club" class="rounded-lg shadow-xl object-cover w-full h-full">
        </div>
        <div data-aos="fade-left">
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-4">Beyond the Classroom</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                Learning at ASPEJ extends far beyond academics. We offer a rich variety of extracurricular activities to help
                students discover new passions, build teamwork skills, and develop into well-rounded individuals.
            </p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                <li class="flex items-center"><i class="fas fa-check-circle text-aspej-gold mr-3"></i> Competitive Athletics (Soccer, Basketball, Swimming)</li>
                <li class="flex items-center"><i class="fas fa-check-circle text-aspej-gold mr-3"></i> Arts & Culture (Theater, Choir, Visual Arts)</li>
                <li class="flex items-center"><i class="fas fa-check-circle text-aspej-gold mr-3"></i> STEM Clubs (Robotics, Coding, Science Olympiad)</li>
                <li class="flex items-center"><i class="fas fa-check-circle text-aspej-gold mr-3"></i> Leadership & Service (Model UN, Student Council)</li>
            </ul>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
