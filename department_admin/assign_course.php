<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign'])) {
    $lecturer_id = (int)$_POST['lecturer_id'];
    $course_id = (int)$_POST['course_id'];
    $academic_session_id = (int)$_POST['academic_session_id'];
    $semester_id = (int)$_POST['semester_id'];
    $stmt = mysqli_prepare($conn, "INSERT INTO lecturer_courses (lecturer_id, course_id, academic_session_id, semester_id) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiii", $lecturer_id, $course_id, $academic_session_id, $semester_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Course assigned successfully."; $message_class = "success";
        log_activity($conn, $deptAdmin['id'], 'department_admin', 'Assign Course', "Assigned course ID $course_id to lecturer ID $lecturer_id");
    } else {
        $message = "Error: " . mysqli_error($conn); $message_class = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unassign'])) {
    $lcid = (int)$_POST['lc_id'];
    mysqli_query($conn, "DELETE FROM lecturer_courses WHERE id=$lcid");
    $message = "Course unassigned."; $message_class = "success";
}

$lecturers = mysqli_query($conn, "SELECT * FROM lecturers WHERE department_id=$deptId AND approval_status='Approved' ORDER BY fullname ASC");
$coursesList = mysqli_query($conn, "SELECT * FROM courses WHERE department_id=$deptId ORDER BY course_code ASC");
$sessions = mysqli_query($conn, "SELECT * FROM academic_sessions ORDER BY session_name DESC");
$semesters = mysqli_query($conn, "SELECT * FROM semesters ORDER BY id ASC");

$assignments = mysqli_query($conn, "SELECT lc.*, l.fullname, l.staff_id, c.course_code, c.course_title, s.semester_name, a_s.session_name FROM lecturer_courses lc JOIN lecturers l ON lc.lecturer_id=l.id JOIN courses c ON lc.course_id=c.id LEFT JOIN semesters s ON lc.semester_id=s.id LEFT JOIN academic_sessions a_s ON lc.academic_session_id=a_s.id WHERE l.department_id=$deptId ORDER BY l.fullname ASC");
?>
<div class="main-content">
<div class="page-title"><h1>Course Assignment</h1><p>Assign courses to lecturers</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-share-alt"></i> Assign Course</h3>
<label>Lecturer</label>
<select name="lecturer_id" required>
<option value="">Select Lecturer</option>
<?php while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['fullname'] . ' (' . $l['staff_id'] . ')'); ?></option>
<?php } ?>
</select>
<label>Course</label>
<select name="course_id" required>
<option value="">Select Course</option>
<?php while ($c = mysqli_fetch_assoc($coursesList)) { ?>
<option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_title']); ?></option>
<?php } ?>
</select>
<label>Academic Session</label>
<select name="academic_session_id" required>
<option value="">Select Session</option>
<?php while ($as = mysqli_fetch_assoc($sessions)) { ?>
<option value="<?php echo $as['id']; ?>"><?php echo $as['session_name']; ?></option>
<?php } ?>
</select>
<label>Semester</label>
<select name="semester_id" required>
<option value="">Select Semester</option>
<?php while ($sem = mysqli_fetch_assoc($semesters)) { ?>
<option value="<?php echo $sem['id']; ?>"><?php echo $sem['semester_name']; ?></option>
<?php } ?>
</select>
<br>
<button type="submit" name="assign" class="btn btn-primary"><i class="fas fa-check"></i> Assign</button>
</form>

<div>
<h3><i class="fas fa-list"></i> Current Assignments</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Lecturer</th><th>Course</th><th>Session</th><th>Semester</th><th>Action</th></tr></thead>
<tbody>
<?php if ($assignments && mysqli_num_rows($assignments) > 0) { while ($a = mysqli_fetch_assoc($assignments)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($a['fullname']); ?></strong><br><small><?php echo htmlspecialchars($a['staff_id']); ?></small></td>
<td><?php echo htmlspecialchars($a['course_code'] . ' - ' . $a['course_title']); ?></td>
<td><?php echo $a['session_name']; ?></td>
<td><?php echo $a['semester_name']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="lc_id" value="<?php echo $a['id']; ?>">
<button type="submit" name="unassign" class="btn btn-danger btn-sm btn-delete" data-item="this assignment"><i class="fas fa-unlink"></i></button>
</form>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:30px;">No assignments yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
