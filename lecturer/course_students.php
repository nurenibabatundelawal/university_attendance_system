<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($course_id <= 0) { header("Location: assigned_courses.php"); exit(); }

$result = $conn->prepare("SELECT s.id, s.matric_no, s.fullname, s.email, s.level FROM course_registrations cr JOIN students s ON cr.student_id = s.id WHERE cr.course_id = ? ORDER BY s.fullname");
$result->bind_param("i", $course_id);
$result->execute();
$result = $result->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Registered Students</h1><p>Students enrolled in this course</p></div>
<a href="assigned_courses.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Matric Number</th><th>Full Name</th><th>Email</th><th>Level</th></tr></thead>
<tbody>
<?php if ($result->num_rows > 0) { $sn = 1; while ($row = $result->fetch_assoc()) { ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($row['matric_no']); ?></span></td>
<td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
<td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
<td><?php echo $row['level']; ?>L</td>
</tr>
<?php } } else { ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No students registered for this course.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
