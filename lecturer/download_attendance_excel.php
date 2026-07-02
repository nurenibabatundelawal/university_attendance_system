<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');

require_once __DIR__ . '/../php/db_connect.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Attendance_Analytics.xls");

echo "
<table border='1'>
<tr>
<th>Matric No</th>
<th>Student</th>
<th>Course</th>
<th>Percentage</th>
<th>Status</th>
</tr>
";

$lecturer_id = $_SESSION['user_id'];

$query = "
SELECT
s.matric_no,
s.fullname,
c.id,
c.course_code,
COUNT(ar.id) AS attended,
(
SELECT COUNT(*)
FROM attendance_sessions ats
WHERE ats.course_id=c.id
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
";

$stmt = mysqli_prepare($conn,$query);
mysqli_stmt_bind_param($stmt,"i",$lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

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

    echo "
    <tr>
        <td>{$row['matric_no']}</td>
        <td>{$row['fullname']}</td>
        <td>{$row['course_code']}</td>
        <td>{$percentage}%</td>
        <td>{$status}</td>
    </tr>";
}

echo "</table>";
?>