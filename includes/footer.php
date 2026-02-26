<?php
// ============================================================
// includes/footer.php  –  Shared footer + scripts
// ============================================================
?>
</main>

<footer class="bg-aspej-navy text-white py-12 dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-8 border-b border-gray-700 pb-8">
        <div>
            <h4 class="text-xl font-bold mb-4 text-aspej-gold">ASPEJ School</h4>
            <p class="text-sm text-gray-300">Kigali, Rwanda</p>
            <p class="text-sm text-gray-300 mt-2"><i class="fas fa-phone mr-2"></i> +250 788 000 000</p>
            <p class="text-sm text-gray-300"><i class="fas fa-envelope mr-2"></i> info@aspejschool.edu</p>
            <div class="flex space-x-3 mt-4">
                <a href="#" class="text-gray-400 hover:text-aspej-gold transition"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-400 hover:text-aspej-gold transition"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-400 hover:text-aspej-gold transition"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div>
            <h4 class="text-lg font-semibold mb-4 text-aspej-gold">Quick Links</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="/about.php"      class="hover:text-aspej-gold transition">Our History</a></li>
                <li><a href="/news.php"        class="hover:text-aspej-gold transition">Events Calendar</a></li>
                <li><a href="/contact.php"     class="hover:text-aspej-gold transition">Careers</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-lg font-semibold mb-4 text-aspej-gold">Portals</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="/portals.php#student" class="hover:text-aspej-gold transition"><i class="fas fa-user-graduate mr-2"></i>Student Portal</a></li>
                <li><a href="/portals.php#teacher" class="hover:text-aspej-gold transition"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Portal</a></li>
                <li><a href="/portals.php#parent"  class="hover:text-aspej-gold transition"><i class="fas fa-user-friends mr-2"></i>Parent Portal</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-lg font-semibold mb-4 text-aspej-gold">Resources</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="/admissions.php#fees"       class="hover:text-aspej-gold transition">Fee Structure</a></li>
                <li><a href="/academics.php#curriculum"  class="hover:text-aspej-gold transition">Curriculum Guide</a></li>
                <li><a href="/contact.php"               class="hover:text-aspej-gold transition">Contact Us</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 mt-6 text-center text-sm text-gray-400">
        &copy; <?= date('Y') ?> ASPEJ School. All rights reserved. |
        <a href="#" class="hover:text-aspej-gold">Privacy Policy</a>
    </div>
</footer>

<button id="back-to-top" class="fixed bottom-5 right-5 bg-aspej-gold text-aspej-navy p-3 rounded-full shadow-lg opacity-0 transition-opacity duration-300 z-40" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>AOS.init({ duration: 1000, once: true });</script>
<script src="/assets/js/script.js"></script>
<?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>
