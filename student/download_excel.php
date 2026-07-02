<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");

echo "
<table border='1'>
<tr>
<th>Date</th>
<th>Course Code</th>
<th>Course Title</th>
<th>Status</th>
<th>Method</th>
</tr>
";

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
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    echo "<tr>";

    echo "<td>".$row['session_date']."</td>";
    echo "<td>".$row['course_code']."</td>";
    echo "<td>".$row['course_title']."</td>";
    echo "<td>".$row['attendance_status']."</td>";
    echo "<td>".$row['verification_method']."</td>";

    echo "</tr>";
}

echo "</table>";
?>