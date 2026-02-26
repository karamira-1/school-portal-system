<?php
// ============================================================
// includes/report_card.php
// Generates a printable/downloadable report card.
// Call: generate_report_card($student_data, $report_data, $tpl)
// $report_data = from portal-dos.php $report_data['students'][$i]
// $tpl         = from report_template table
// ============================================================
if (!function_exists('generate_report_card')) :

function generate_report_card(array $st, array $rd, array $tpl, bool $return = false): string {
    $school_name  = htmlspecialchars($tpl['school_name']    ?? 'ASPEJ School');
    $school_motto = htmlspecialchars($tpl['school_motto']   ?? '');
    $school_addr  = htmlspecialchars($tpl['school_address'] ?? '');
    $school_phone = htmlspecialchars($tpl['school_phone']   ?? '');
    $school_email = htmlspecialchars($tpl['school_email']   ?? '');
    $school_pobox = htmlspecialchars($tpl['school_po_box']  ?? '');
    $logo_path    = $tpl['school_logo_path'] ?? '';
    $acad_year    = htmlspecialchars($tpl['academic_year']  ?? '');
    $principal    = htmlspecialchars($tpl['principal_name'] ?? 'Principal');
    $prin_title   = htmlspecialchars($tpl['principal_title']?? 'Head Teacher');
    $dos_name     = htmlspecialchars($tpl['dos_name']       ?? 'Director of Studies');
    $dod_name     = htmlspecialchars($tpl['dod_name']       ?? 'Director of Discipline');
    $ct_label     = htmlspecialchars($tpl['class_teacher_label'] ?? 'Class Teacher');
    $stamp_label  = htmlspecialchars($tpl['stamp_note']     ?? 'School Stamp');
    $footer_note  = htmlspecialchars($tpl['footer_note']    ?? '');

    $class_name   = htmlspecialchars($rd['class']['name']   ?? '');
    $term         = $rd['term']   ?? 1;
    $year         = $rd['year']   ?? date('Y');
    $rpt_type     = $rd['type']   ?? 'full';
    $class_avg    = $rd['class_avg'] ?? 0;
    $total_stu    = count($rd['students'] ?? []);

    $student_name = htmlspecialchars($st['full_name']      ?? '');
    $student_num  = htmlspecialchars($st['student_number'] ?? '');
    $rank         = $st['rank']    ?? '—';
    $average      = $st['average'] ?? 0;
    $conduct      = $st['conduct'] ?? 40;
    $subjects     = $st['subjects'] ?? [];

    $rpt_label = match($rpt_type) {
        'period1' => '1st Period Report (Test 1)',
        'period2' => '2nd Period Report (Test 2)',
        default   => 'Full Term Report',
    };

    $grade_color = function(string $g): string {
        return match($g) { 'A'=>'#166534','B'=>'#1d4ed8','C'=>'#92400e','D'=>'#7c3aed','F'=>'#991b1b', default=>'#374151' };
    };
    $pass_color = fn(bool $p) => $p ? '#166534' : '#991b1b';

    ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Report Card – <?= $student_name ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f0;
            color: #1a1a1a;
            font-size: 13px;
        }
        @media screen {
            body { padding: 20px; }
            .report-wrap { max-width: 820px; margin: 0 auto; }
            .action-bar {
                position: sticky; top: 0; z-index: 10;
                background: #1D2A4D;
                display: flex; align-items: center; justify-content: space-between;
                padding: 12px 20px; border-radius: 12px;
                margin-bottom: 16px; gap: 12px;
            }
            .action-bar span { color: rgba(255,255,255,0.7); font-size: 13px; }
            .action-btn {
                display: inline-flex; align-items: center; gap: 7px;
                padding: 9px 18px; border-radius: 8px; border: none;
                font-family: 'DM Sans', sans-serif; font-size: 13px;
                font-weight: 600; cursor: pointer; text-decoration: none;
                transition: all .15s;
            }
            .btn-print { background: #FFC72C; color: #1D2A4D; }
            .btn-pdf   { background: #EF4444; color: white; }
            .btn-back  { background: rgba(255,255,255,0.12); color: white; }
            .action-btn:hover { opacity: 0.85; transform: translateY(-1px); }
        }
        @media print {
            .action-bar, .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .report-card { box-shadow: none !important; border-radius: 0 !important; }
        }

        /* ── Report Card ── */
        .report-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 30px rgba(0,0,0,0.12);
            margin-bottom: 24px;
            page-break-after: always;
        }

        /* Header band */
        .rc-header {
            background: linear-gradient(135deg, #0F1F3D 0%, #1D2A4D 100%);
            padding: 24px 32px;
            display: flex; align-items: flex-start; justify-content: space-between; gap: 20px;
        }
        .rc-logo-area { display: flex; align-items: center; gap: 16px; }
        .rc-logo {
            width: 64px; height: 64px; border-radius: 10px;
            background: rgba(255,255,255,0.1); overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .rc-logo img { width: 100%; height: 100%; object-fit: contain; }
        .rc-logo-placeholder { font-size: 30px; }
        .rc-school-name {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 900; color: white;
            line-height: 1.1; margin-bottom: 3px;
        }
        .rc-school-motto { color: #FFC72C; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
        .rc-school-contact { color: rgba(255,255,255,0.55); font-size: 11px; margin-top: 4px; }
        .rc-title-area { text-align: right; }
        .rc-report-label {
            display: inline-block;
            background: #FFC72C; color: #1D2A4D;
            font-size: 11px; font-weight: 700; padding: 4px 12px;
            border-radius: 100px; margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .rc-term-info { color: rgba(255,255,255,0.7); font-size: 12px; }
        .rc-year { color: white; font-weight: 600; font-size: 14px; }

        /* Student info bar */
        .rc-student-bar {
            background: #f8f7f4;
            border-bottom: 1px solid #e8e4dc;
            padding: 16px 32px;
            display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 16px;
        }
        .rc-info-item label {
            display: block; font-size: 10px; font-weight: 600;
            color: #888; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;
        }
        .rc-info-item p { font-weight: 600; color: #1D2A4D; font-size: 13px; }
        .rc-rank-badge {
            background: #1D2A4D; color: white;
            padding: 4px 12px; border-radius: 100px;
            font-size: 13px; font-weight: 700; display: inline-block;
        }

        /* Marks table */
        .rc-body { padding: 24px 32px; }
        .rc-table-title {
            font-size: 11px; font-weight: 700; color: #888;
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 12px;
        }
        .rc-table {
            width: 100%; border-collapse: collapse;
            margin-bottom: 20px;
        }
        .rc-table th {
            background: #1D2A4D; color: white;
            padding: 9px 12px; text-align: left;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .rc-table th:first-child { border-radius: 6px 0 0 6px; }
        .rc-table th:last-child  { border-radius: 0 6px 6px 0; }
        .rc-table td {
            padding: 9px 12px; border-bottom: 1px solid #f0ede8;
            font-size: 13px; vertical-align: middle;
        }
        .rc-table tr:last-child td { border-bottom: none; }
        .rc-table tr:hover td { background: #fafaf8; }
        .rc-table .score-cell { font-weight: 700; font-size: 15px; }
        .rc-table tfoot td {
            background: #f8f7f4; font-weight: 700;
            border-top: 2px solid #e8e4dc;
        }
        .grade-badge {
            display: inline-block; width: 28px; height: 28px;
            border-radius: 6px; text-align: center;
            line-height: 28px; font-weight: 800; font-size: 13px;
            color: white;
        }
        .status-pill {
            padding: 3px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 700; display: inline-block;
        }

        /* Summary section */
        .rc-summary {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 16px; margin-bottom: 24px;
        }
        .rc-stat-box {
            background: #f8f7f4; border-radius: 10px;
            padding: 16px; text-align: center;
        }
        .rc-stat-box .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 28px; font-weight: 700; color: #1D2A4D;
        }
        .rc-stat-box .stat-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4px; }
        .conduct-box {
            background: linear-gradient(135deg, #fefce8, #fef9c3);
            border: 1px solid #fde68a; border-radius: 10px;
            padding: 16px; text-align: center;
        }
        .conduct-note { font-size: 10px; color: #92400e; margin-top: 4px; font-style: italic; }

        /* Conduct bar */
        .conduct-bar-wrap { margin-top: 4px; background: #fde68a; border-radius: 100px; height: 6px; overflow: hidden; }
        .conduct-bar-fill { height: 100%; border-radius: 100px; background: linear-gradient(90deg,#d97706,#f59e0b); }

        /* Signature section */
        .rc-signatures {
            border-top: 1px solid #e8e4dc;
            padding: 20px 32px;
            display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 16px;
        }
        .sig-block { text-align: center; }
        .sig-line {
            border-bottom: 1.5px solid #ccc;
            height: 44px; margin-bottom: 6px;
            position: relative;
        }
        .sig-line::after {
            content: 'X';
            position: absolute; bottom: -10px; left: 50%;
            transform: translateX(-50%);
            font-size: 14px; color: #ccc;
        }
        .sig-name  { font-weight: 700; font-size: 12px; color: #1D2A4D; margin-top: 10px; }
        .sig-title { font-size: 10px; color: #888; margin-top: 2px; }
        .stamp-box {
            border: 2px dashed #ddd; border-radius: 12px;
            height: 80px; display: flex; align-items: center;
            justify-content: center; color: #ccc; font-size: 11px;
            font-style: italic;
        }

        /* Footer note */
        .rc-footer {
            background: #f8f7f4; border-top: 1px solid #e8e4dc;
            padding: 12px 32px; font-size: 11px; color: #888;
            text-align: center; font-style: italic;
        }
    </style>
</head>
<body>
<div class="report-wrap">

<!-- Action bar (screen only) -->
<div class="action-bar no-print">
    <a href="javascript:history.back()" class="action-btn btn-back"><i class="fas fa-arrow-left"></i> Back</a>
    <span>Report: <strong style="color:white"><?= $student_name ?></strong> — <?= $class_name ?> — <?= $rpt_label ?></span>
    <div style="display:flex;gap:8px">
        <button class="action-btn btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <button class="action-btn btn-pdf"   onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Download PDF</button>
    </div>
</div>

<!-- Report Card -->
<div class="report-card" id="reportCard">

    <!-- Header -->
    <div class="rc-header">
        <div class="rc-logo-area">
            <div class="rc-logo">
                <?php if ($logo_path): ?>
                <img src="<?= htmlspecialchars($logo_path) ?>" alt="School Logo">
                <?php else: ?>
                <span class="rc-logo-placeholder">🏫</span>
                <?php endif; ?>
            </div>
            <div>
                <div class="rc-school-name"><?= $school_name ?></div>
                <?php if ($school_motto): ?><div class="rc-school-motto"><?= $school_motto ?></div><?php endif; ?>
                <div class="rc-school-contact">
                    <?php if ($school_addr): ?><?= $school_addr ?><?php endif; ?>
                    <?php if ($school_phone): ?> · <?= $school_phone ?><?php endif; ?>
                    <?php if ($school_email): ?> · <?= $school_email ?><?php endif; ?>
                    <?php if ($school_pobox): ?> · <?= $school_pobox ?><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="rc-title-area">
            <div class="rc-report-label">Report Card</div>
            <div class="rc-term-info"><?= $rpt_label ?></div>
            <div class="rc-year">Term <?= $term ?> &nbsp;·&nbsp; <?= $year ?></div>
            <?php if ($acad_year): ?><div class="rc-term-info">Academic Year <?= $acad_year ?></div><?php endif; ?>
        </div>
    </div>

    <!-- Student Info Bar -->
    <div class="rc-student-bar">
        <div class="rc-info-item">
            <label>Student Name</label>
            <p><?= $student_name ?></p>
        </div>
        <div class="rc-info-item">
            <label>Registration No.</label>
            <p><?= $student_num ?></p>
        </div>
        <div class="rc-info-item">
            <label>Class / Combination</label>
            <p><?= $class_name ?></p>
        </div>
        <div class="rc-info-item">
            <label>Class Rank</label>
            <p><span class="rc-rank-badge"><?= $rank ?> / <?= $total_stu ?></span></p>
        </div>
    </div>

    <!-- Body -->
    <div class="rc-body">
        <div class="rc-table-title">Subject Performance</div>
        <table class="rc-table">
            <thead>
                <tr>
                    <th style="width:30%">Subject</th>
                    <?php if ($rpt_type==='full'): ?>
                    <th style="text-align:center">Test 1 <span style="font-weight:400;opacity:.7">/100</span></th>
                    <th style="text-align:center">Test 2 <span style="font-weight:400;opacity:.7">/100</span></th>
                    <th style="text-align:center">Exam <span style="font-weight:400;opacity:.7">/100</span></th>
                    <?php endif; ?>
                    <th style="text-align:center">Score <span style="font-weight:400;opacity:.7">/100</span></th>
                    <th style="text-align:center">Grade</th>
                    <th style="text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $subj):
                $gc = $grade_color($subj['grade']);
                $pc = $pass_color($subj['pass']);
            ?>
            <tr>
                <td style="font-weight:600;color:#1D2A4D"><?= htmlspecialchars($subj['name']) ?></td>
                <?php if ($rpt_type==='full'): ?>
                <td style="text-align:center;color:#555"><?= is_numeric($subj['test1'])?number_format($subj['test1'],1):'—' ?></td>
                <td style="text-align:center;color:#555"><?= is_numeric($subj['test2'])?number_format($subj['test2'],1):'—' ?></td>
                <td style="text-align:center;color:#555"><?= is_numeric($subj['exam'])?number_format($subj['exam'],1):'—' ?></td>
                <?php endif; ?>
                <td class="score-cell" style="text-align:center;color:<?= $pc ?>"><?= number_format($subj['score'],1) ?></td>
                <td style="text-align:center">
                    <span class="grade-badge" style="background:<?= $gc ?>"><?= $subj['grade'] ?></span>
                </td>
                <td style="text-align:center">
                    <span class="status-pill" style="background:<?= $subj['pass']?'#dcfce7':'#fee2e2' ?>;color:<?= $pc ?>">
                        <?= $subj['pass']?'PASS':'FAIL' ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?= $rpt_type==='full'?4:1 ?>" style="text-align:right">Overall Average:</td>
                    <td style="text-align:center;font-size:16px;color:<?= $average>=50?'#166534':'#991b1b' ?>"><?= number_format($average,1) ?></td>
                    <td style="text-align:center"><span class="grade-badge" style="background:<?= $grade_color(grade_letter($average)) ?>"><?= grade_letter($average) ?></span></td>
                    <td style="text-align:center"><span class="status-pill" style="background:<?= $average>=50?'#dcfce7':'#fee2e2' ?>;color:<?= $pass_color($average>=50) ?>"><?= $average>=50?'PASS':'FAIL' ?></span></td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary -->
        <div class="rc-summary">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="rc-stat-box">
                    <div class="stat-value" style="color:<?= $average>=50?'#166534':'#991b1b' ?>"><?= number_format($average,1) ?></div>
                    <div class="stat-label">Your Average</div>
                </div>
                <div class="rc-stat-box">
                    <div class="stat-value"><?= number_format($class_avg,1) ?></div>
                    <div class="stat-label">Class Average</div>
                </div>
                <div class="rc-stat-box">
                    <div class="stat-value" style="color:#1D2A4D"><?= $rank ?></div>
                    <div class="stat-label">Your Rank</div>
                </div>
                <div class="rc-stat-box">
                    <div class="stat-value"><?= $total_stu ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
            <div class="conduct-box">
                <div class="stat-value" style="color:#92400e;font-family:'Playfair Display',serif"><?= number_format($conduct,1) ?><span style="font-size:14px;font-weight:400;color:#aaa"> / 40</span></div>
                <div class="stat-label" style="color:#92400e">Conduct Mark</div>
                <div class="conduct-bar-wrap"><div class="conduct-bar-fill" style="width:<?= ($conduct/40)*100 ?>%"></div></div>
                <div class="conduct-note">Conduct is displayed for reference only and is not included in academic totals.</div>
            </div>
        </div>
    </div>

    <!-- Signature section -->
    <div class="rc-signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name"><?= $ct_label ?></div>
            <div class="sig-title">Signature</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name"><?= $dos_name ?: 'Director of Studies' ?></div>
            <div class="sig-title">Director of Studies</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name"><?= $principal ?></div>
            <div class="sig-title"><?= $prin_title ?></div>
        </div>
        <div class="sig-block">
            <div class="stamp-box"><?= $stamp_label ?></div>
        </div>
    </div>

    <!-- Footer note -->
    <?php if ($footer_note): ?>
    <div class="rc-footer"><?= $footer_note ?></div>
    <?php endif; ?>

</div><!-- /report-card -->
</div><!-- /report-wrap -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
    const el  = document.getElementById('reportCard');
    const btn = document.querySelectorAll('.action-btn');
    btn.forEach(b=>b.style.display='none');
    html2pdf().set({
        margin:      [10,10,10,10],
        filename:    'ASPEJ_Report_<?= preg_replace('/[^a-z0-9]/i','_',$student_name) ?>_T<?= $term ?>_<?= $year ?>.pdf',
        image:       { type:'jpeg', quality:1 },
        html2canvas: { scale:2, useCORS:true },
        jsPDF:       { unit:'mm', format:'a4', orientation:'portrait' }
    }).from(el).save().then(()=>btn.forEach(b=>b.style.display=''));
}
</script>
</body>
</html>
<?php
    $html = ob_get_clean();
    if ($return) return $html;
    echo $html;
    return '';
}
endif;
