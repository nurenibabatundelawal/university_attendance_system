<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Date',
    'Course Code',
    'Course Title',
    'Status',
    'Verification Method'
]);

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

while ($row = $result->fetch_assoc()) {

    fputcsv($output, [
        $row['session_date'],
        $row['course_code'],
        $row['course_title'],
        $row['attendance_status'],
        $row['verification_method']
    ]);
}

fclose($output);
exit;
?>