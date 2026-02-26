<?php
// ============================================================
// admissions.php  –  Admissions page + multi-step application modal
// ============================================================
require_once __DIR__ . '/includes/db.php';

$page_title  = 'Admissions';
$current_nav = 'admissions';

// Extra inline styles for the modal stepper
$extra_head = <<<'CSS'
<style>
.step-indicator {
    width: 2.5rem; height: 2.5rem; border-radius: 9999px;
    background: #e5e7eb; display: flex; align-items: center;
    justify-content: center; font-weight: 700; color: #6b7280;
    transition: all .3s;
}
.step-indicator.active-step   { background: #1D2A4D; color: #fff; transform: scale(1.1); }
.step-indicator.completed-step{ background: #FFC72C; color: #1D2A4D; }
.form-input, .form-select {
    margin-top: .25rem; display: block; width: 100%; border-radius: .375rem;
    border: 1px solid #d1d5db; padding: .5rem .75rem;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
    transition: border-color .2s;
}
.form-input:focus, .form-select:focus {
    outline: none; border-color: #FFC72C; box-shadow: 0 0 0 2px rgba(255,199,44,.3);
}
.dark .form-input, .dark .form-select {
    background: #111827; border-color: #4b5563; color: #f3f4f6;
}
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; }
input[type=number] { -moz-appearance: textfield; }
</style>
CSS;

$extra_scripts = '<script src="/assets/js/admissions.js"></script>';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="relative bg-aspej-navy py-28 text-white">
    <img src="assets/images/entrance.jpg" alt="School entrance" class="absolute inset-0 w-full h-full object-cover opacity-20">
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center" data-aos="fade-up">
        <h1 class="text-5xl font-extrabold">Admissions</h1>
        <p class="mt-4 text-xl text-gray-200 max-w-2xl mx-auto">Join the ASPEJ School community. Start your journey with us.</p>
        <button id="applyNowBtn"
                class="mt-8 inline-block bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-3 px-8 rounded-full text-lg shadow-xl transition-all duration-300 transform hover:scale-105">
            Apply Now <i class="fas fa-pen ml-2"></i>
        </button>
    </div>
</section>

<!-- How to Apply -->
<section id="apply" class="py-20 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center text-aspej-navy dark:text-white mb-12" data-aos="fade-up">How to Apply</h2>
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <?php
            $steps = [
                ['n'=>1,'title'=>'Inquire & Visit',       'desc'=>'Submit an online inquiry and schedule a campus tour to see our community in action.','delay'=>100],
                ['n'=>2,'title'=>'Submit Application',    'desc'=>'Complete our online application and submit required documents including transcripts.','delay'=>200],
                ['n'=>3,'title'=>'Interview & Decision',  'desc'=>'Applicants will be invited for a family interview. Decisions are communicated shortly after.','delay'=>300],
            ];
            foreach ($steps as $s):
            ?>
            <div class="relative" data-aos="fade-up" data-aos-delay="<?= $s['delay'] ?>">
                <div class="flex items-center justify-center bg-aspej-gold text-aspej-navy rounded-full h-20 w-20 mx-auto text-3xl font-bold shadow-lg">
                    <?= $s['n'] ?>
                </div>
                <h3 class="text-xl font-bold text-aspej-navy dark:text-white mt-6 mb-2"><?= $s['title'] ?></h3>
                <p class="text-gray-600 dark:text-gray-300"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Tuition & Fees -->
<section id="fees" class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-right">
            <h2 class="text-3xl font-bold text-aspej-navy dark:text-aspej-gold mb-4">Tuition & Fees</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                We are committed to providing an exceptional education that is both valuable and accessible. Our fees cover
                all core instruction, technology, and most extracurricular activities.
            </p>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                Financial aid and scholarship opportunities are available for qualifying families. Contact our admissions
                office for a detailed fee structure and to discuss options.
            </p>
            <a href="/contact.php" class="inline-block bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-3 px-6 rounded-lg text-lg shadow-lg transition-all duration-300">
                Contact Admissions
            </a>
        </div>
        <div data-aos="fade-left">
            <img src="assets/images/admissions-info.jpg" alt="Parents talking to staff" class="rounded-lg shadow-xl object-cover w-full h-full">
        </div>
    </div>
</section>

<!-- ============================================================
     APPLICATION MODAL (multi-step)
============================================================ -->
<div id="applicationModal" class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b dark:border-gray-700 flex-shrink-0">
            <h2 class="text-2xl font-bold text-aspej-navy dark:text-white">ASPEJ Application Form</h2>
            <button id="closeModalBtn" class="text-gray-400 hover:text-aspej-gold text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Stepper -->
        <div class="p-6 border-b dark:border-gray-700 flex-shrink-0">
            <div class="flex items-center justify-between max-w-lg mx-auto">
                <div class="step-indicator" data-step="1">1</div>
                <div class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 mx-2 rounded-full"></div>
                <div class="step-indicator" data-step="2">2</div>
                <div class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 mx-2 rounded-full"></div>
                <div class="step-indicator" data-step="3">3</div>
            </div>
            <div class="flex justify-between max-w-lg mx-auto mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span class="w-20 text-center">Applicant Info</span>
                <span class="w-20 text-center">Home & Guardian</span>
                <span class="w-20 text-center">Academic Choice</span>
            </div>
        </div>

        <!-- Form (submitted via AJAX to api/submit_application.php) -->
        <div class="p-8 overflow-y-auto" id="formContainer">
            <form id="applicationForm" enctype="multipart/form-data" novalidate>

                <!-- Step 1 -->
                <div class="form-step" data-step="1">
                    <h3 class="text-xl font-semibold mb-6 text-aspej-navy dark:text-aspej-gold">Applicant Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                            <input type="text" name="firstName" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                            <input type="text" name="lastName" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                            <input type="date" name="dob" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                            <select name="gender" required class="form-select">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <p id="step-1-error" class="text-red-500 text-sm mt-4 hidden">Please fill all required fields.</p>
                </div>

                <!-- Step 2 -->
                <div class="form-step hidden" data-step="2">
                    <h3 class="text-xl font-semibold mb-6 text-aspej-navy dark:text-aspej-gold">Home & Guardian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent/Guardian Full Name</label>
                            <input type="text" name="parentName" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent/Guardian Phone</label>
                            <input type="tel" name="parentPhone" placeholder="e.g., 078…" required class="form-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent/Guardian ID Number (16 digits)</label>
                            <input type="text" name="parentID" required maxlength="16" pattern="\d{16}" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Province (Intara)</label>
                            <select name="province" id="provinceSelect" required class="form-select">
                                <option value="">-- Select Province --</option>
                                <option value="Kigali">Kigali</option>
                                <option value="Northern">Northern</option>
                                <option value="Eastern">Eastern</option>
                                <option value="Southern">Southern</option>
                                <option value="Western">Western</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">District (Akarere)</label>
                            <select name="district" id="districtSelect" required class="form-select" disabled>
                                <option value="">-- Select Province First --</option>
                            </select>
                        </div>
                    </div>
                    <p id="step-2-error" class="text-red-500 text-sm mt-4 hidden">Please fill all required fields.</p>
                </div>

                <!-- Step 3 -->
                <div class="form-step hidden" data-step="3">
                    <h3 class="text-xl font-semibold mb-6 text-aspej-navy dark:text-aspej-gold">Academic Choice</h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Level</label>
                            <select name="level" id="levelSelect" required class="form-select">
                                <option value="">-- Choose your level --</option>
                                <option value="O-Level">O-Level (Ordinary Level)</option>
                                <option value="A-Level">A-Level (Advanced Level)</option>
                            </select>
                        </div>
                        <div id="tradeSelection" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select A-Level Trade</label>
                            <select name="trade" id="tradeSelect" class="form-select">
                                <option value="">-- Select A-Level Trade --</option>
                                <option value="Sciences">Sciences (PCM, PCB, MCB)</option>
                                <option value="Humanities">Humanities (HGL, HEG)</option>
                                <option value="Languages">Languages (KLF, ELF)</option>
                                <option value="ICT">ICT & Computer Studies</option>
                            </select>
                        </div>
                        <div id="oLevelUploadGroup" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload O-Level Certificate (PDF, max 2 MB)</label>
                            <input type="file" name="oLevelUpload" id="oLevelUpload" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 dark:file:bg-gray-700 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-aspej-navy dark:file:text-aspej-gold">
                        </div>
                    </div>
                    <p id="step-3-error" class="text-red-500 text-sm mt-4 hidden">Please fill all required fields.</p>
                </div>

            </form>
        </div><!-- /formContainer -->

        <!-- Success Message -->
        <div id="successMessage" class="hidden p-8 text-center overflow-y-auto">
            <div class="w-20 h-20 bg-green-100 dark:bg-green-900 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-aspej-navy dark:text-white mb-4">Application Received!</h2>
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
                Thank you. Your registration will be reviewed and approved after paying the application fee of
                <strong class="text-aspej-navy dark:text-aspej-gold">5,000 RWF</strong>.
            </p>
            <div class="bg-gray-100 dark:bg-gray-700/50 p-6 rounded-lg text-left">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Next Steps:</h4>
                <ol class="list-decimal list-inside space-y-2 text-gray-600 dark:text-gray-300">
                    <li>Pay <strong>5,000 RWF</strong> to Bank of Kigali (BK) Account: <strong class="text-aspej-navy dark:text-aspej-gold">1000 1234 5678 90</strong> (ASPEJ School).</li>
                    <li>Call the Bursar's office at <strong class="text-aspej-navy dark:text-aspej-gold">0788 000 000</strong> with your payment confirmation.</li>
                    <li>Our admissions team will contact you within 3 business days.</li>
                </ol>
            </div>
            <button id="startOverBtn" class="mt-8 bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-2 px-6 rounded-lg shadow-lg transition-all duration-300">
                Apply for Another Student
            </button>
        </div>

        <!-- Modal Footer -->
        <div id="modalFooter" class="flex justify-between items-center p-6 border-t dark:border-gray-700 flex-shrink-0 bg-gray-50 dark:bg-gray-900">
            <button id="prevStepBtn" class="bg-gray-300 text-gray-700 hover:bg-gray-400 font-bold py-2 px-6 rounded-lg transition-colors duration-300" style="visibility:hidden">
                Previous
            </button>
            <button id="nextStepBtn" class="bg-aspej-gold text-aspej-navy hover:bg-yellow-500 font-bold py-2 px-6 rounded-lg shadow-lg transition-all duration-300">
                Next
            </button>
        </div>

    </div><!-- /modal box -->
</div><!-- /applicationModal -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
