<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];
$result = $conn->prepare("SELECT c.id, c.course_code, c.course_title, COUNT(ar.id) AS total_classes, SUM(CASE WHEN ar.attendance_status IN ('Present','Late') THEN 1 ELSE 0 END) AS attended FROM course_registrations cr JOIN courses c ON cr.course_id = c.id LEFT JOIN attendance_records ar ON ar.course_id = c.id AND ar.student_id = cr.student_id WHERE cr.student_id = ? GROUP BY c.id ORDER BY c.course_code");
$result->bind_param("i", $student_id);
$result->execute();
$result = $result->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Attendance Percentage</h1><p>Per-course attendance breakdown</p></div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Course</th><th>Classes Held</th><th>Attended</th><th>Percentage</th><th>Status</th></tr></thead>
<tbody>
<?php if ($result->num_rows > 0) { $sn = 1; while ($row = $result->fetch_assoc()) {
    $total = $row['total_classes'];
    $attended = $row['attended'];
    $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0;
    $eligible = $percentage >= 75;
    $barColor = $percentage >= 75 ? 'bar-green' : ($percentage >= 50 ? 'bar-orange' : 'bar-red');
?>
<tr>
<td><?php echo $sn++; ?></td>
<td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong><br><small><?php echo htmlspecialchars($row['course_title']); ?></small></td>
<td><?php echo $total; ?></td>
<td><?php echo $attended; ?></td>
<td>
<div style="display:flex;align-items:center;gap:10px;">
<span style="font-weight:600;min-width:45px;"><?php echo $percentage; ?>%</span>
<div class="progress-bar-wrap" style="flex:1;max-width:120px;"><div class="progress-bar-fill <?php echo $barColor; ?>" style="width:<?php echo $percentage; ?>%;"></div></div>
</div>
</td>
<td><?php echo $eligible ? '<span class="badge badge-success">Eligible</span>' : '<span class="badge badge-danger">Not Eligible</span>'; ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No registered courses found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
