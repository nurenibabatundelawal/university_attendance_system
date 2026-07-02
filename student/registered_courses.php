<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];
$result = $conn->prepare("SELECT c.course_code, c.course_title, c.course_unit, c.level, s.semester_name FROM course_registrations cr JOIN courses c ON cr.course_id = c.id JOIN semesters s ON cr.semester_id = s.id WHERE cr.student_id = ? ORDER BY c.course_code ASC");
$result->bind_param("i", $student_id);
$result->execute();
$result = $result->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Registered Courses</h1><p>All courses you are currently registered for</p></div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Course Code</th><th>Course Title</th><th>Credit Unit</th><th>Level</th><th>Semester</th></tr></thead>
<tbody>
<?php if ($result->num_rows > 0) { $sn = 1; while ($row = $result->fetch_assoc()) { ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
<td><strong><?php echo htmlspecialchars($row['course_title']); ?></strong></td>
<td><?php echo $row['course_unit']; ?></td>
<td><?php echo $row['level']; ?>L</td>
<td><?php echo htmlspecialchars($row['semester_name']); ?></td>
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
