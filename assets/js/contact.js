// ============================================================
// assets/js/contact.js  –  Contact form AJAX submission
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const form      = document.getElementById('contactForm');
    const successEl = document.getElementById('contact-success');
    const errorEl   = document.getElementById('contact-error');
    const submitBtn = document.getElementById('contactSubmitBtn');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        successEl.classList.add('hidden');
        errorEl.classList.add('hidden');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending…';

        const formData = new FormData(form);
        try {
            const res  = await fetch('/api/submit_contact.php', { method: 'POST', body: formData });
            const json = await res.json();

            if (json.success) {
                form.reset();
                successEl.classList.remove('hidden');
                successEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                errorEl.textContent = json.message || 'Something went wrong.';
                errorEl.classList.remove('hidden');
            }
        } catch (err) {
            errorEl.textContent = 'Network error. Please check your connection and try again.';
            errorEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Send Message <i class="fas fa-paper-plane ml-2"></i>';
        }
    });
});
