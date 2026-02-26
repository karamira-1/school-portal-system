// ============================================================
// assets/js/portal.js  –  Student Portal dashboard logic
// Depends on: PORTAL_STUDENT, PORTAL_ATTENDANCE, PORTAL_TERMS
//             (JSON objects injected by portal-student.php)
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ── Refs ─────────────────────────────────────────────────
    const sections       = document.querySelectorAll('.content-section');
    const sidebarLinks   = document.querySelectorAll('.sidebar-link');
    const quickBtns      = document.querySelectorAll('.quick-access-btn');
    const pageTitle      = document.getElementById('page-title');
    const termSelect     = document.getElementById('term-select');
    const termSelectGrades = document.getElementById('term-select-grades');
    const downloadBtn    = document.getElementById('download-report-btn');
    let   chart          = null;

    // ── Section Switcher ─────────────────────────────────────
    function switchTo(sectionId) {
        sections.forEach(s => s.classList.toggle('hidden', s.id !== sectionId));
        sidebarLinks.forEach(l => l.classList.toggle('active', l.dataset.section === sectionId));

        const active = document.getElementById(sectionId);
        if (active && pageTitle) pageTitle.textContent = active.dataset.sectionName || sectionId;

        if (sectionId === 'attendance') renderChart();
        if (sectionId === 'reports' && termSelect)  loadGrades(termSelect.value, 'report');
        if (sectionId === 'grades'   && termSelectGrades) loadGrades(termSelectGrades.value, 'grades');
    }

    // Sidebar links
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => { e.preventDefault(); switchTo(link.dataset.section); });
    });

    // Quick access buttons
    quickBtns.forEach(btn => {
        if (btn.dataset.target) {
            btn.addEventListener('click', () => switchTo(btn.dataset.target));
        }
    });

    // Term dropdowns
    if (termSelect) {
        termSelect.addEventListener('change', () => loadGrades(termSelect.value, 'report'));
    }
    if (termSelectGrades) {
        termSelectGrades.addEventListener('change', () => loadGrades(termSelectGrades.value, 'grades'));
    }

    // Init
    switchTo('dashboard');

    // ── Load Grades via AJAX ──────────────────────────────────
    async function loadGrades(term, target) {
        if (!term) return;
        try {
            const res  = await fetch(`/api/get_grades.php?term=${encodeURIComponent(term)}`);
            const data = await res.json();
            if (!data.success) return;

            if (target === 'grades') {
                renderGradesTable(data.grades, data.summary);
            } else {
                renderReportCard(data.grades, data.summary, term);
            }
        } catch (err) {
            console.error('Failed to load grades:', err);
        }
    }

    function renderGradesTable(grades, summary) {
        const tbody = document.getElementById('grades-table-body');
        const gpa   = document.getElementById('grades-gpa');
        if (!tbody) return;

        tbody.innerHTML = grades.map(g => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${esc(g.subject)}</td>
                <td class="px-6 py-4 text-lg font-semibold text-aspej-navy dark:text-aspej-gold">${esc(g.grade)}</td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">${esc(g.remarks)}</td>
            </tr>
        `).join('');

        if (gpa && summary) gpa.textContent = parseFloat(summary.overall_gpa).toFixed(1);
    }

    function renderReportCard(grades, summary, term) {
        const tbody   = document.getElementById('report-card-table-body');
        const termEl  = document.getElementById('report-term-year');
        const yearEl  = document.getElementById('report-student-year');
        const gpaEl   = document.getElementById('report-gpa');
        const comments= document.getElementById('report-teacher-comments');

        if (tbody) {
            tbody.innerHTML = grades.map(g => `
                <tr>
                    <td class="py-3 px-4">${esc(g.subject)}</td>
                    <td class="py-3 px-4 text-center font-semibold">${esc(g.grade)}</td>
                    <td class="py-3 px-4 hidden sm:table-cell text-sm text-gray-500 dark:text-gray-400">${esc(g.remarks)}</td>
                </tr>
            `).join('');
        }

        if (termEl)   termEl.textContent   = term;
        if (yearEl && summary) yearEl.textContent = summary.year;
        if (gpaEl && summary)  gpaEl.textContent  = parseFloat(summary.overall_gpa).toFixed(1);
        if (comments && summary) comments.textContent = summary.teacher_comments;
    }

    // ── Attendance Chart ──────────────────────────────────────
    function renderChart() {
        const canvas = document.getElementById('attendanceChart');
        if (!canvas || typeof PORTAL_ATTENDANCE === 'undefined') return;

        const labels  = PORTAL_ATTENDANCE.map(r => r.month_label);
        const present = PORTAL_ATTENDANCE.map(r => parseInt(r.days_present));
        const absent  = PORTAL_ATTENDANCE.map(r => parseInt(r.days_absent));

        if (chart) chart.destroy();

        chart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Days Present',
                        data: present,
                        backgroundColor: '#1D2A4D',
                        borderColor: '#1D2A4D',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Days Absent',
                        data: absent,
                        backgroundColor: '#FFC72C',
                        borderColor: '#FFC72C',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Monthly Attendance Log' },
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Number of Days' } },
                },
            },
        });
    }

    // ── PDF Download ──────────────────────────────────────────
    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => {
            const element = document.getElementById('report-card-content');
            const term    = (termSelect?.value || 'report').replace(/[,\s]+/g, '-');
            const id      = PORTAL_STUDENT?.id || 'student';

            html2pdf().from(element).set({
                margin:       10,
                filename:     `ASPEJ_ReportCard_${id}_${term}.pdf`,
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            }).save();
        });
    }

    // ── Dark mode toggle (portal page) ───────────────────────
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const setTheme = (theme) => {
            document.documentElement.classList.toggle('dark', theme === 'dark');
            localStorage.setItem('theme', theme);
        };
        setTheme(localStorage.getItem('theme') || 'light');
        themeToggle.addEventListener('click', () => {
            setTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
        });
    }

    // ── Utility: escape HTML ──────────────────────────────────
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});
