// ============================================================
// assets/js/teacher.js  –  Teacher Portal specific JS
// ============================================================

// ── Attendance Form ───────────────────────────────────────
const attendanceForm = document.getElementById('attendanceForm');
const attMsg         = document.getElementById('attFormMsg');
const submitAttBtn   = document.getElementById('submitAttBtn');

if (attendanceForm) {
    // Mark-all helper buttons
    window.markAll = function(status) {
        document.querySelectorAll(`input[type="radio"][value="${status}"]`)
                .forEach(r => r.checked = true);
    };

    attendanceForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!confirm('Submit attendance? This cannot be changed once submitted.')) return;

        submitAttBtn.disabled = true;
        submitAttBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting…';
        if (attMsg) attMsg.textContent = '';

        const formData = new FormData(attendanceForm);

        try {
            const res  = await fetch('/api/attendance_manager.php', { method:'POST', body:formData });
            const data = await res.json();

            if (data.success) {
                if (attMsg) {
                    attMsg.textContent = data.message;
                    attMsg.className   = 'text-sm text-green-600 dark:text-green-400 self-center';
                }
                // Disable all inputs to show read-only state
                attendanceForm.querySelectorAll('input, select').forEach(el => el.disabled = true);
                submitAttBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Submitted!';
                submitAttBtn.className = submitAttBtn.className.replace('btn-gold','') + ' bg-green-500 text-white py-2 px-8 rounded-lg font-bold';
            } else {
                if (attMsg) {
                    attMsg.textContent = '⚠ ' + data.message;
                    attMsg.className   = 'text-sm text-red-500 self-center';
                }
                submitAttBtn.disabled = false;
                submitAttBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Submit Attendance';
            }
        } catch (err) {
            if (attMsg) {
                attMsg.textContent = 'Network error. Please try again.';
                attMsg.className   = 'text-sm text-red-500 self-center';
            }
            submitAttBtn.disabled = false;
            submitAttBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Submit Attendance';
        }
    });
}

// ── Grade Form ────────────────────────────────────────────
const gradesForm     = document.getElementById('gradesForm');
const gradeMsg       = document.getElementById('gradeFormMsg');
const submitGradeBtn = document.getElementById('submitGradeBtn');

if (gradesForm) {
    gradesForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitGradeBtn.disabled = true;
        submitGradeBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving…';
        if (gradeMsg) gradeMsg.textContent = '';

        const formData = new FormData(gradesForm);

        try {
            const res  = await fetch('/api/grade_manager.php', { method:'POST', body:formData });
            const data = await res.json();

            if (data.success) {
                if (gradeMsg) {
                    gradeMsg.textContent = '✓ ' + data.message;
                    gradeMsg.className   = 'text-sm text-green-600 dark:text-green-400 self-center';
                }
                submitGradeBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Saved!';
                setTimeout(() => {
                    submitGradeBtn.disabled = false;
                    submitGradeBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Grades';
                }, 2500);
            } else {
                if (gradeMsg) {
                    gradeMsg.textContent = '⚠ ' + data.message;
                    gradeMsg.className   = 'text-sm text-red-500 self-center';
                }
                submitGradeBtn.disabled = false;
                submitGradeBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Grades';
            }
        } catch (err) {
            if (gradeMsg) {
                gradeMsg.textContent = 'Network error. Please try again.';
                gradeMsg.className   = 'text-sm text-red-500 self-center';
            }
            submitGradeBtn.disabled = false;
            submitGradeBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Grades';
        }
    });
}
