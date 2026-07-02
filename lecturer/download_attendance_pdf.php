<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');

require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

$lecturer_id = $_SESSION['user_id'];

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,10,'Attendance Analytics Report',0,1,'C');

$pdf->SetFont('Arial','B',10);

$pdf->Cell(30,10,'Matric No',1);
$pdf->Cell(55,10,'Student',1);
$pdf->Cell(30,10,'Course',1);
$pdf->Cell(25,10,'Percent',1);
$pdf->Cell(50,10,'Status',1);
$pdf->Ln();

$query = "
SELECT
    s.matric_no,
    s.fullname,
    c.id AS course_id,
    c.course_code,
    COUNT(ar.id) AS attended,
    (
        SELECT COUNT(*)
        FROM attendance_sessions ats
        WHERE ats.course_id = c.id
        AND ats.status='Ended'
    ) AS total_classes
FROM students s
JOIN course_registrations cr
ON s.id=cr.student_id
JOIN courses c
ON cr.course_id=c.id
JOIN lecturer_courses lc
ON c.id=lc.course_id
LEFT JOIN attendance_records ar
ON ar.student_id=s.id
AND ar.course_id=c.id
AND ar.attendance_status IN ('Present','Late')
WHERE lc.lecturer_id=?
GROUP BY s.id,c.id
ORDER BY c.course_code,s.fullname
";

$stmt = mysqli_prepare($conn,$query);
mysqli_stmt_bind_param($stmt,"i",$lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pdf->SetFont('Arial','',9);

while($row = mysqli_fetch_assoc($result))
{
    $total = $row['total_classes'];

    $percentage =
        $total > 0
        ? round(($row['attended']/$total)*100,2)
        : 0;

    $status =
        $percentage >= 75
        ? 'Eligible'
        : 'Not Eligible';

    $pdf->Cell(30,10,$row['matric_no'],1);
    $pdf->Cell(55,10,substr($row['fullname'],0,25),1);
    $pdf->Cell(30,10,$row['course_code'],1);
    $pdf->Cell(25,10,$percentage.'%',1);
    $pdf->Cell(50,10,$status,1);
    $pdf->Ln();
}

$pdf->Output('D','Attendance_Analytics_Report.pdf');
exit;
?>