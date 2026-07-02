<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $academic_session_id = $_POST['academic_session_id'];
    $semester_id = $_POST['semester_id'];

    $query = "INSERT INTO course_registrations (student_id, course_id, academic_session_id, semester_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiii", $student_id, $course_id, $academic_session_id, $semester_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Student registered for course successfully."; $message_class = "success";
    } else {
        $message = "Error: This student may already be registered for this course in the selected session and semester."; $message_class = "error";
    }
}

$students = mysqli_query($conn, "SELECT id, matric_no, fullname FROM students WHERE status='Active' ORDER BY fullname ASC");
$courses = mysqli_query($conn, "SELECT id, course_code, course_title FROM courses ORDER BY course_code ASC");
$sessions = mysqli_query($conn, "SELECT id, session_name FROM academic_sessions ORDER BY session_name ASC");
$semesters = mysqli_query($conn, "SELECT id, semester_name FROM semesters ORDER BY id ASC");
$registrations = mysqli_query($conn, "SELECT r.id, s.fullname, s.matric_no, c.course_code, c.course_title, a.session_name, sm.semester_name FROM course_registrations r JOIN students s ON r.student_id = s.id JOIN courses c ON r.course_id = c.id JOIN academic_sessions a ON r.academic_session_id = a.id JOIN semesters sm ON r.semester_id = sm.id ORDER BY r.id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Course Registration</h1><p>Register students for courses</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Register Student for Course</h3>
<label>Student</label>
<select name="student_id" required>
<option value="">Select Student</option>
<?php while ($s = mysqli_fetch_assoc($students)) { ?>
<option value="<?php echo $s['id']; ?>"><?php echo $s['matric_no'] . " - " . $s['fullname']; ?></option>
<?php } ?>
</select>
<br><br>
<label>Course</label>
<select name="course_id" required>
<option value="">Select Course</option>
<?php while ($c = mysqli_fetch_assoc($courses)) { ?>
<option value="<?php echo $c['id']; ?>"><?php echo $c['course_code'] . " - " . $c['course_title']; ?></option>
<?php } ?>
</select>
<br><br>
<div class="form-row">
<div><label>Session</label><select name="academic_session_id" required>
<option value="">Select Session</option>
<?php while ($a = mysqli_fetch_assoc($sessions)) { ?>
<option value="<?php echo $a['id']; ?>"><?php echo $a['session_name']; ?></option>
<?php } ?>
</select></div>
<div><label>Semester</label><select name="semester_id" required>
<option value="">Select Semester</option>
<?php while ($sm = mysqli_fetch_assoc($semesters)) { ?>
<option value="<?php echo $sm['id']; ?>"><?php echo $sm['semester_name']; ?></option>
<?php } ?>
</select></div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-file-signature"></i> Register Course</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Registrations</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Student</th><th>Course</th><th>Session</th><th>Semester</th></tr></thead>
<tbody>
<?php $i=1; while ($r = mysqli_fetch_assoc($registrations)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $r['matric_no'] . " - " . $r['fullname']; ?></td>
<td><?php echo $r['course_code'] . " - " . $r['course_title']; ?></td>
<td><?php echo $r['session_name']; ?></td>
<td><?php echo $r['semester_name']; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
