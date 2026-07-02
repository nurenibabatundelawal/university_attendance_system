<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT lc.id, c.id AS course_id, c.course_code, c.course_title, c.course_unit, c.level, ac.session_name, s.semester_name FROM lecturer_courses lc JOIN courses c ON lc.course_id = c.id JOIN academic_sessions ac ON lc.academic_session_id = ac.id JOIN semesters s ON lc.semester_id = s.id WHERE lc.lecturer_id = $lecturer_id ORDER BY c.course_code ASC");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>My Courses</h1><p>Courses assigned to you this session</p></div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Code</th><th>Title</th><th>Unit</th><th>Level</th><th>Session</th><th>Semester</th><th>Actions</th></tr></thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0) { while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><span class="badge badge-info"><?php echo htmlspecialchars($row['course_code']); ?></span></td>
<td><strong><?php echo htmlspecialchars($row['course_title']); ?></strong></td>
<td><?php echo $row['course_unit']; ?></td>
<td><?php echo $row['level']; ?>L</td>
<td><?php echo htmlspecialchars($row['session_name']); ?></td>
<td><?php echo htmlspecialchars($row['semester_name']); ?></td>
<td>
<div style="display:flex;gap:4px;">
<a href="course_students.php?course_id=<?php echo $row['course_id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-users"></i></a>
<a href="start_session.php" class="btn btn-sm btn-success"><i class="fas fa-play"></i></a>
<a href="attendance_sessions.php?course_id=<?php echo $row['course_id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-clock"></i></a>
</div>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:24px;">No courses assigned yet.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
