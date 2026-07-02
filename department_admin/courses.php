<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $code = strtoupper(trim($_POST['course_code']));
    $title = trim($_POST['course_title']);
    $unit = (int)$_POST['course_unit'];
    $level = trim($_POST['level']);
    $semester_id = (int)$_POST['semester_id'];
    $stmt = mysqli_prepare($conn, "INSERT INTO courses (department_id, course_code, course_title, course_unit, level, semester_id) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issisi", $deptId, $code, $title, $unit, $level, $semester_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Course added successfully."; $message_class = "success";
        log_activity($conn, $deptAdmin['id'], 'department_admin', 'Add Course', "Added $code - $title");
    } else {
        $message = "Error: " . mysqli_error($conn); $message_class = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_course'])) {
    $cid = (int)$_POST['course_id'];
    mysqli_query($conn, "DELETE FROM courses WHERE id=$cid AND department_id=$deptId");
    $message = "Course deleted."; $message_class = "success";
}

$semesters = mysqli_query($conn, "SELECT * FROM semesters ORDER BY id ASC");
$courses = mysqli_query($conn, "SELECT c.*, s.semester_name FROM courses c LEFT JOIN semesters s ON c.semester_id=s.id WHERE c.department_id=$deptId ORDER BY c.course_code ASC");
?>
<div class="main-content">
<div class="page-title"><h1>Course Management</h1><p>Manage courses in your department</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Course</h3>
<label>Course Code</label>
<input type="text" name="course_code" placeholder="e.g. CSC401" required>
<label>Course Title</label>
<input type="text" name="course_title" placeholder="e.g. Artificial Intelligence" required>
<label>Credit Unit</label>
<input type="number" name="course_unit" min="1" max="6" required>
<label>Level</label>
<select name="level" required>
<option value="">Select Level</option>
<option value="100">100 Level</option>
<option value="200">200 Level</option>
<option value="300">300 Level</option>
<option value="400">400 Level</option>
<option value="500">500 Level</option>
</select>
<label>Semester</label>
<select name="semester_id" required>
<option value="">Select Semester</option>
<?php while ($sem = mysqli_fetch_assoc($semesters)) { ?>
<option value="<?php echo $sem['id']; ?>"><?php echo $sem['semester_name']; ?></option>
<?php } ?>
</select>
<br>
<button type="submit" name="add_course" class="btn btn-primary"><i class="fas fa-save"></i> Add Course</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Courses</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Code</th><th>Title</th><th>Units</th><th>Level</th><th>Semester</th><th>Action</th></tr></thead>
<tbody>
<?php if ($courses && mysqli_num_rows($courses) > 0) { while ($c = mysqli_fetch_assoc($courses)) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($c['course_code']); ?></span></td>
<td><strong><?php echo htmlspecialchars($c['course_title']); ?></strong></td>
<td><?php echo $c['course_unit']; ?></td>
<td><?php echo $c['level']; ?>L</td>
<td><?php echo $c['semester_name']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
<button type="submit" name="delete_course" class="btn btn-danger btn-sm btn-delete" data-item="this course"><i class="fas fa-trash"></i></button>
</form>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No courses yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
