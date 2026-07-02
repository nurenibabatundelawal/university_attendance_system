<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';
require_once '../fpdf/fpdf.php';

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("
SELECT fullname, matric_no
FROM students
WHERE id = ?
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Student Attendance Report',0,1,'C');

$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(190,10,'Name: '.$student['fullname'],0,1);
$pdf->Cell(190,10,'Matric No: '.$student['matric_no'],0,1);

$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(30,10,'Date',1);
$pdf->Cell(30,10,'Course',1);
$pdf->Cell(60,10,'Title',1);
$pdf->Cell(35,10,'Status',1);
$pdf->Cell(35,10,'Method',1);

$pdf->Ln();

$sql = "
SELECT
    ats.session_date,
    c.course_code,
    c.course_title,
    ar.attendance_status,
    ar.verification_method
FROM attendance_records ar
JOIN attendance_sessions ats
    ON ar.attendance_session_id = ats.id
JOIN courses c
    ON ar.course_id = c.id
WHERE ar.student_id = ?
ORDER BY ats.session_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$pdf->SetFont('Arial','',9);

while ($row = $result->fetch_assoc()) {

    $pdf->Cell(30,10,$row['session_date'],1);
    $pdf->Cell(30,10,$row['course_code'],1);
    $pdf->Cell(60,10,substr($row['course_title'],0,30),1);
    $pdf->Cell(35,10,$row['attendance_status'],1);
    $pdf->Cell(35,10,$row['verification_method'],1);

    $pdf->Ln();
}

$pdf->Output('D','Attendance_Report.pdf');
exit;
?>