<?php
// ============================================================
// contact.php  –  Contact page (AJAX form → api/submit_contact.php)
// ============================================================
$page_title  = 'Contact Us';
$current_nav = 'contact';
$extra_scripts = '<script src="/assets/js/contact.js"></script>';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/contact-hero.jpg" alt="School office" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">Get in Touch</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">We're here to help. Contact us with any questions you may have.</p>
    </div>
</section>

<!-- Form + Info -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12">

        <!-- Contact Form (AJAX) -->
        <div class="bg-gray-50 dark:bg-gray-700/50 p-8 rounded-lg shadow-xl" data-aos="fade-right">
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-white mb-6">Send Us a Message</h2>

            <!-- Alert banners (shown by JS) -->
            <div id="contact-success" class="hidden mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i> Message sent! We'll get back to you shortly.
            </div>
            <div id="contact-error" class="hidden mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i> Something went wrong. Please try again.
            </div>

            <form id="contactForm" novalidate class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm p-2.5 focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm p-2.5 focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input type="text" name="subject" id="subject" required
                           class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm p-2.5 focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                    <textarea id="message" name="message" rows="5" required
                              class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm p-2.5 focus:border-aspej-gold focus:ring focus:ring-aspej-gold/30 dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                </div>
                <div>
                    <button type="submit" id="contactSubmitBtn"
                            class="w-full bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-3 px-6 rounded-lg text-lg shadow-lg transition-all duration-300">
                        Send Message <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Info + Map -->
        <div data-aos="fade-left">
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-white mb-6">Contact Information</h2>
            <div class="space-y-4 text-lg text-gray-600 dark:text-gray-300">
                <p class="flex items-center"><i class="fas fa-map-marker-alt text-aspej-gold w-6 mr-4"></i> Kigali, Rwanda</p>
                <p class="flex items-center"><i class="fas fa-phone           text-aspej-gold w-6 mr-4"></i> +250 788 000 000</p>
                <p class="flex items-center"><i class="fas fa-envelope        text-aspej-gold w-6 mr-4"></i> info@aspejschool.edu</p>
                <p class="flex items-center"><i class="fas fa-clock           text-aspej-gold w-6 mr-4"></i> Monday – Friday: 7:30 AM – 5:00 PM</p>
            </div>
            <h3 class="text-2xl font-bold text-aspej-navy dark:text-white mt-10 mb-4">Find Us</h3>
            <div class="w-full h-64 rounded-lg shadow-xl overflow-hidden">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63799.78638516278!2d30.019695!3d-1.944!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca42968ac8aed%3A0x8d31ef5c0f4faa0!2sKigali!5e0!3m2!1sen!2srw!4v1700000000000"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
