<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT s.matric_no, s.fullname, c.course_code, c.course_title, COUNT(ar.id) AS attended, (SELECT COUNT(*) FROM attendance_sessions ats WHERE ats.course_id = c.id AND ats.status='Ended') AS total_classes FROM students s JOIN course_registrations cr ON s.id = cr.student_id JOIN courses c ON cr.course_id = c.id JOIN lecturer_courses lc ON c.id = lc.course_id LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.course_id = c.id AND ar.attendance_status IN ('Present','Late') WHERE lc.lecturer_id = ? GROUP BY s.id, c.id ORDER BY c.course_code, s.fullname");
mysqli_stmt_bind_param($stmt, "i", $lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $total = $row['total_classes'];
    $percentage = $total > 0 ? round(($row['attended'] / $total) * 100, 1) : 0;
    if ($percentage < 75) $data[] = $row;
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Students Below 75%</h1><p>Students at risk of not sitting for exams</p></div>

<?php if (count($data) > 0) { ?>
<div class="msg warning" style="margin-bottom:20px;"><i class="fas fa-exclamation-triangle"></i> <strong><?php echo count($data); ?> student(s)</strong> are below the 75% attendance threshold.</div>
<?php } ?>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Matric No</th><th>Student</th><th>Course</th><th>Attended</th><th>Total</th><th>Percentage</th><th>Status</th></tr></thead>
<tbody>
<?php if (count($data) > 0) { $sn = 1; foreach ($data as $row) {
    $total = $row['total_classes'];
    $percentage = $total > 0 ? round(($row['attended'] / $total) * 100, 1) : 0;
?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($row['matric_no']); ?></span></td>
<td><?php echo htmlspecialchars($row['fullname']); ?></td>
<td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
<td><?php echo $row['attended']; ?></td>
<td><?php echo $total; ?></td>
<td><span style="color:#e53935;font-weight:700;"><?php echo $percentage; ?>%</span></td>
<td><span class="badge badge-danger">Not Eligible</span></td>
</tr>
<?php } } else { ?>
<tr><td colspan="8" style="text-align:center;color:#999;padding:20px;">All students are above 75% attendance!</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
