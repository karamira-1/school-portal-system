// ============================================================
// assets/js/admissions.js  –  Admissions modal & form logic
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ── DOM refs ─────────────────────────────────────────────
    const modal         = document.getElementById('applicationModal');
    const applyBtn      = document.getElementById('applyNowBtn');
    const closeBtn      = document.getElementById('closeModalBtn');
    const form          = document.getElementById('applicationForm');
    const formContainer = document.getElementById('formContainer');
    const successMsg    = document.getElementById('successMessage');
    const modalFooter   = document.getElementById('modalFooter');
    const prevBtn       = document.getElementById('prevStepBtn');
    const nextBtn       = document.getElementById('nextStepBtn');
    const startOverBtn  = document.getElementById('startOverBtn');
    const steps         = document.querySelectorAll('.form-step');
    const indicators    = document.querySelectorAll('.step-indicator');

    let currentStep = 1;
    const totalSteps = 3;

    // ── Open / Close Modal ────────────────────────────────────
    applyBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    function closeModal() {
        modal.classList.add('hidden');
    }

    // ── Stepper ───────────────────────────────────────────────
    function showStep(step) {
        steps.forEach(s => s.classList.add('hidden'));
        document.querySelector(`.form-step[data-step="${step}"]`)?.classList.remove('hidden');

        indicators.forEach(ind => {
            const n = parseInt(ind.dataset.step);
            ind.classList.remove('active-step', 'completed-step');
            if (n < step) ind.classList.add('completed-step');
            if (n === step) ind.classList.add('active-step');
        });

        prevBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
        nextBtn.textContent = step === totalSteps ? 'Submit' : 'Next';
    }

    showStep(currentStep);

    // ── Validate current step ────────────────────────────────
    function validateStep(step) {
        const required = document.querySelectorAll(`.form-step[data-step="${step}"] [required]`);
        const allOk = Array.from(required).every(f => f.value.trim() !== '');
        const errEl = document.getElementById(`step-${step}-error`);
        if (errEl) errEl.classList.toggle('hidden', allOk);
        return allOk;
    }

    // ── Next / Prev ──────────────────────────────────────────
    nextBtn.addEventListener('click', async () => {
        if (currentStep < totalSteps) {
            if (!validateStep(currentStep)) return;
            currentStep++;
            showStep(currentStep);
        } else {
            // Final step: submit via AJAX
            if (!validateStep(currentStep)) return;
            await submitForm();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) { currentStep--; showStep(currentStep); }
    });

    // ── AJAX Form Submission ──────────────────────────────────
    async function submitForm() {
        nextBtn.disabled = true;
        nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting…';

        const formData = new FormData(form);
        try {
            const res  = await fetch('/api/submit_application.php', { method: 'POST', body: formData });
            const json = await res.json();

            if (json.success) {
                form.classList.add('hidden');
                modalFooter.classList.add('hidden');
                successMsg.classList.remove('hidden');
            } else {
                alert('Error: ' + json.message);
                nextBtn.disabled = false;
                nextBtn.textContent = 'Submit';
            }
        } catch (err) {
            alert('Network error. Please try again.');
            nextBtn.disabled = false;
            nextBtn.textContent = 'Submit';
        }
    }

    // ── Start Over ────────────────────────────────────────────
    startOverBtn.addEventListener('click', () => {
        form.reset();
        successMsg.classList.add('hidden');
        modalFooter.classList.remove('hidden');
        form.classList.remove('hidden');
        currentStep = 1;
        showStep(1);
        nextBtn.disabled = false;
        nextBtn.textContent = 'Next';
        // Reset district
        const district = document.getElementById('districtSelect');
        if (district) { district.innerHTML = '<option value="">-- Select Province First --</option>'; district.disabled = true; }
    });

    // ── Province → District Logic ─────────────────────────────
    const PROVINCES = {
        Kigali:   ['Gasabo', 'Kicukiro', 'Nyarugenge'],
        Northern: ['Musanze', 'Gicumbi', 'Burera', 'Rulindo', 'Gakenke'],
        Eastern:  ['Rwamagana', 'Bugesera', 'Ngoma', 'Kirehe', 'Nyagatare', 'Gatsibo', 'Kayonza'],
        Southern: ['Huye', 'Gisagara', 'Nyanza', 'Ruhango', 'Muhanga', 'Kamonyi', 'Nyaruguru', 'Nyamagabe'],
        Western:  ['Rubavu', 'Rusizi', 'Nyamasheke', 'Karongi', 'Ngororero', 'Rutsiro', 'Nyabihu'],
    };

    const provinceSelect = document.getElementById('provinceSelect');
    const districtSelect = document.getElementById('districtSelect');

    if (provinceSelect && districtSelect) {
        provinceSelect.addEventListener('change', () => {
            districtSelect.innerHTML = '<option value="">-- Select District --</option>';
            districtSelect.disabled  = !provinceSelect.value;
            if (provinceSelect.value) {
                PROVINCES[provinceSelect.value].forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d; opt.textContent = d;
                    districtSelect.appendChild(opt);
                });
            }
        });
    }

    // ── Level → Trade Selection ───────────────────────────────
    const levelSelect       = document.getElementById('levelSelect');
    const tradeSelection    = document.getElementById('tradeSelection');
    const oLevelUploadGroup = document.getElementById('oLevelUploadGroup');

    if (levelSelect) {
        levelSelect.addEventListener('change', () => {
            const isALevel = levelSelect.value === 'A-Level';
            tradeSelection?.classList.toggle('hidden', !isALevel);
            oLevelUploadGroup?.classList.toggle('hidden', !isALevel);
        });
    }
});
