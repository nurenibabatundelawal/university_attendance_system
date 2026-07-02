<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];
$result = $conn->prepare("SELECT ar.attendance_status, ar.verification_method, ar.marked_at, ats.session_date, c.course_code, c.course_title FROM attendance_records ar JOIN attendance_sessions ats ON ar.attendance_session_id = ats.id JOIN courses c ON ar.course_id = c.id WHERE ar.student_id = ? ORDER BY ats.session_date DESC, ar.marked_at DESC");
$result->bind_param("i", $student_id);
$result->execute();
$result = $result->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Attendance History</h1><p>Your complete attendance record across all courses</p></div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Date</th><th>Course</th><th>Status</th><th>Method</th><th>Time</th></tr></thead>
<tbody>
<?php if ($result->num_rows > 0) { $sn = 1; while ($row = $result->fetch_assoc()) { ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><?php echo date('d M Y', strtotime($row['session_date'])); ?></td>
<td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong><br><small><?php echo htmlspecialchars($row['course_title']); ?></small></td>
<td>
<?php
$s = $row['attendance_status'];
if ($s == 'Present') echo '<span class="badge badge-success">Present</span>';
elseif ($s == 'Late') echo '<span class="badge badge-warning">Late</span>';
else echo '<span class="badge badge-danger">Absent</span>';
?>
</td>
<td><span class="badge badge-dark"><?php echo $row['verification_method'] ?? '-'; ?></span></td>
<td><?php echo date('h:i A', strtotime($row['marked_at'])); ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No attendance records found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
