<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT ats.*, c.course_code, c.course_title, ac.session_name, s.semester_name FROM attendance_sessions ats JOIN courses c ON ats.course_id = c.id JOIN academic_sessions ac ON ats.academic_session_id = ac.id JOIN semesters s ON ats.semester_id = s.id WHERE ats.lecturer_id = $lecturer_id ORDER BY ats.id DESC");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Attendance Sessions</h1><p>All sessions you have created</p></div>
<a href="start_session.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Session</a>
</div>

<div class="form-box">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>ID</th><th>Course</th><th>Date</th><th>Start</th><th>End</th><th>Method</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0) { while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong><br><small><?php echo htmlspecialchars($row['course_title']); ?></small></td>
<td><?php echo date('d M Y', strtotime($row['session_date'])); ?></td>
<td><?php echo $row['start_time']; ?></td>
<td><?php echo $row['end_time'] ?? '-'; ?></td>
<td><span class="badge badge-info"><?php echo $row['attendance_method'] ?? 'both'; ?></span></td>
<td><?php echo $row['status'] == 'Active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-dark">Ended</span>'; ?></td>
<td>
<div style="display:flex;gap:5px;flex-wrap:wrap;">
<?php if ($row['status'] == 'Active') { ?>
<a href="end_session.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('End this session?')"><i class="fas fa-stop"></i> End</a>
<a href="manual_attendance.php?session_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success"><i class="fas fa-hand"></i> Attendance</a>
<?php } else { ?>
<a href="edit_attendance.php?session_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
<?php } ?>
</div>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="8" style="text-align:center;color:#999;padding:20px;">No sessions found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
