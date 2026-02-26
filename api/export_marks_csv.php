<?php
// ============================================================
// api/export_marks_csv.php  –  CSV export of marks / reports
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
$user = auth_user();
if (!$user || !can('view_marks', $user)) { http_response_code(403); echo 'Forbidden'; exit; }

$pdo        = get_db();
$class_id   = (int)($_GET['class_id']   ?? 0);
$subject_id = (int)($_GET['subject_id'] ?? 0);
$term       = (int)($_GET['term']       ?? 1);
$year       = (int)($_GET['year']       ?? date('Y'));
$rpt_type   = $_GET['rpt_type']         ?? 'full';
$is_report  = !empty($_GET['report']);   // full class report CSV

$class_info = $pdo->prepare('SELECT * FROM classes WHERE id=?');
$class_info->execute([$class_id]);
$class_info = $class_info->fetch();
if (!$class_info) { echo 'Class not found'; exit; }

// Set headers
$filename = "ASPEJ_" . str_replace(' ','_',$class_info['name']) . "_T{$term}_{$year}";
if (!$is_report && $subject_id) {
    $subj = $pdo->prepare('SELECT name FROM subjects WHERE id=?');
    $subj->execute([$subject_id]);
    $filename .= '_' . str_replace(' ','_',$subj->fetchColumn());
}
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename={$filename}.csv");

$out = fopen('php://output','w');

if ($is_report) {
    // Full class report CSV
    require_once __DIR__ . '/../includes/auth.php';
    $subjects_list = get_subjects_for_combination($class_info['combination']);
    $students      = $pdo->prepare('SELECT * FROM students WHERE class_id=? AND is_active=1 ORDER BY full_name');
    $students->execute([$class_id]);
    $students = $students->fetchAll();

    // Header row
    $header = ['Rank','Student Number','Full Name'];
    foreach ($subjects_list as $s) $header[] = $s['name'];
    $header = array_merge($header, ['Average','Grade','Status','Conduct/40']);
    fputcsv($out, $header);

    // Data with calculated marks
    $rows = [];
    foreach ($students as $st) {
        $total=0; $count=0; $row_marks=[];
        foreach ($subjects_list as $subj) {
            $m = $pdo->prepare('SELECT test_type,mark_value FROM marks WHERE student_id=? AND subject_id=? AND term=? AND year=?');
            $m->execute([$st['id'],$subj['id'],$term,$year]);
            $by_type=[];
            foreach ($m->fetchAll() as $r) $by_type[$r['test_type']]=(float)$r['mark_value'];
            $score = !empty($by_type) ? calc_term_marks($by_type)['total'] : null;
            $row_marks[] = $score !== null ? number_format($score,1) : '';
            if ($score!==null){$total+=$score;$count++;}
        }
        $avg = $count>0?round($total/$count,1):0;
        $cond = $pdo->prepare('SELECT score FROM conduct_marks WHERE student_id=? AND term=? AND year=?');
        $cond->execute([$st['id'],$term,$year]);
        $cond_score = $cond->fetchColumn() ?? 40;
        $rows[] = [$st['average_mark']??0, $avg, $st['student_number'], $st['full_name'], ...$row_marks, $avg, grade_letter($avg), $avg>=50?'Pass':'Fail', $cond_score];
    }
    usort($rows, fn($a,$b)=>$b[1]<=>$a[1]);
    foreach ($rows as $rank=>$r) {
        array_shift($r); array_shift($r);
        fputcsv($out, array_merge([$rank+1], $r));
    }

} else {
    // Single subject marks CSV
    fputcsv($out, ['Student Number','Full Name','Test 1 /100','Test 2 /100','Exam /100','Term Total /100','Grade','Status']);
    $data = $pdo->prepare("
        SELECT s.student_number, s.full_name,
               MAX(CASE WHEN m.test_type='test1' THEN m.mark_value END) AS test1,
               MAX(CASE WHEN m.test_type='test2' THEN m.mark_value END) AS test2,
               MAX(CASE WHEN m.test_type='exam'  THEN m.mark_value END) AS exam
        FROM   students s
        LEFT JOIN marks m ON m.student_id=s.id AND m.subject_id=? AND m.term=? AND m.year=?
        WHERE  s.class_id=? AND s.is_active=1
        GROUP  BY s.id ORDER BY s.full_name
    ");
    $data->execute([$subject_id,$term,$year,$class_id]);
    foreach ($data->fetchAll() as $row) {
        $m     = calc_term_marks(['test1'=>$row['test1']??0,'test2'=>$row['test2']??0,'exam'=>$row['exam']??0]);
        $has   = $row['test1']!==null||$row['test2']!==null||$row['exam']!==null;
        fputcsv($out, [
            $row['student_number'], $row['full_name'],
            $row['test1']!==null?number_format($row['test1'],1):'',
            $row['test2']!==null?number_format($row['test2'],1):'',
            $row['exam']!==null ?number_format($row['exam'],1) :'',
            $has?number_format($m['total'],1):'',
            $has?grade_letter($m['total']):'',
            $has?($m['total']>=50?'Pass':'Fail'):'',
        ]);
    }
}
fclose($out);
