<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT s.id, s.matric_no, s.fullname, c.course_code, c.course_title, COUNT(ar.id) AS classes_attended, (SELECT COUNT(*) FROM attendance_sessions ats WHERE ats.course_id = c.id AND ats.status = 'Ended') AS total_classes FROM students s JOIN course_registrations cr ON s.id = cr.student_id JOIN courses c ON cr.course_id = c.id JOIN lecturer_courses lc ON c.id = lc.course_id LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.course_id = c.id AND ar.attendance_status IN ('Present','Late') WHERE lc.lecturer_id = ? GROUP BY s.id, c.id ORDER BY c.course_code, s.fullname");
mysqli_stmt_bind_param($stmt, "i", $lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Attendance Analytics</h1><p>Per-student attendance breakdown by course</p></div>
<div style="display:flex;gap:8px;">
<a href="download_attendance_excel.php" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
<a href="download_attendance_csv.php" class="btn btn-info btn-sm"><i class="fas fa-file-csv"></i> CSV</a>
</div>
</div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Matric No</th><th>Student</th><th>Course</th><th>Attended</th><th>Total</th><th>Percentage</th><th>Status</th></tr></thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0) { $sn = 1; while ($row = mysqli_fetch_assoc($result)) {
    $total = $row['total_classes'];
    $percentage = $total > 0 ? round(($row['classes_attended'] / $total) * 100, 1) : 0;
    $eligible = $percentage >= 75;
?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($row['matric_no']); ?></span></td>
<td><?php echo htmlspecialchars($row['fullname']); ?></td>
<td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
<td><?php echo $row['classes_attended']; ?></td>
<td><?php echo $total; ?></td>
<td>
<div style="display:flex;align-items:center;gap:8px;">
<span style="font-weight:600;min-width:40px;"><?php echo $percentage; ?>%</span>
 <div class="progress-bar-wrap" style="flex:1;max-width:100px;margin:0;"><div class="progress-bar-fill <?php echo $eligible?'bar-green':'bar-red'; ?>" style="width:<?php echo $percentage; ?>%;"></div></div>
</div>
</td>
<td><?php echo $eligible ? '<span class="badge badge-success">Eligible</span>' : '<span class="badge badge-danger">At Risk</span>'; ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="8" style="text-align:center;color:#999;padding:20px;">No data available</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
