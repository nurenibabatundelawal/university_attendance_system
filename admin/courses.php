<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $department_id = intval($_POST['department_id']);
    $course_code = strtoupper(trim($_POST['course_code']));
    $course_title = trim($_POST['course_title']);
    $course_unit = intval($_POST['course_unit']);
    $level = $_POST['level'];
    $semester_id = intval($_POST['semester_id']);

    if ($department_id <= 0 || $course_code == "" || $course_title == "" || $course_unit <= 0) {
        $message = "Please fill all required fields."; $message_class = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM courses WHERE course_code = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $course_code);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if ($result && mysqli_num_rows($result) > 0) {
            $message = "Course code already exists."; $message_class = "error";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO courses (department_id, course_code, course_title, course_unit, level, semester_id) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert, "issisi", $department_id, $course_code, $course_title, $course_unit, $level, $semester_id);
            if (mysqli_stmt_execute($insert)) {
                $message = "Course added successfully."; $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_course'])) {
    $delete = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($delete, "i", $_POST['course_id']);
    if (mysqli_stmt_execute($delete)) {
        $message = "Course deleted successfully."; $message_class = "success";
    } else {
        $message = "Cannot delete: linked to records."; $message_class = "error";
    }
}

$departments = mysqli_query($conn, "SELECT d.id, d.department_name, f.faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id ORDER BY d.department_name ASC");
$semesters = mysqli_query($conn, "SELECT * FROM semesters ORDER BY id ASC");
$courses = mysqli_query($conn, "SELECT c.*, d.department_name, s.semester_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id LEFT JOIN semesters s ON c.semester_id = s.id ORDER BY c.id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Courses</h1><p>Manage courses for all departments</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Course</h3>
<label>Department</label>
<select name="department_id" required>
<option value="">Select Department</option>
<?php while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>"><?php echo $d['department_name'] . " - " . $d['faculty_name']; ?></option>
<?php } ?>
</select>
<br><br>
<div class="form-row">
<div><label>Course Code</label><input type="text" name="course_code" placeholder="e.g. CSC301" required></div>
<div><label>Course Unit</label><input type="number" name="course_unit" placeholder="e.g. 3" min="1" required></div>
</div>
<br>
<label>Course Title</label>
<input type="text" name="course_title" placeholder="e.g. Embedded Systems" required>
<br><br>
<div class="form-row">
<div><label>Level</label>
<select name="level" required>
<option value="">Select Level</option>
<option value="100">100 Level</option><option value="200">200 Level</option>
<option value="300">300 Level</option><option value="400">400 Level</option>
<option value="500">500 Level</option>
</select></div>
<div><label>Semester</label>
<select name="semester_id" required>
<option value="">Select Semester</option>
<?php while ($sem = mysqli_fetch_assoc($semesters)) { ?>
<option value="<?php echo $sem['id']; ?>"><?php echo $sem['semester_name']; ?></option>
<?php } ?>
</select></div>
</div>
<br>
<button type="submit" name="add_course" class="btn btn-primary"><i class="fas fa-plus"></i> Add Course</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Courses</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Code</th><th>Title</th><th>Unit</th><th>Level</th><th>Department</th><th>Semester</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; while ($c = mysqli_fetch_assoc($courses)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><span class="badge badge-dark"><?php echo $c['course_code']; ?></span></td>
<td><?php echo $c['course_title']; ?></td>
<td><?php echo $c['course_unit']; ?></td>
<td><?php echo $c['level']; ?></td>
<td><?php echo $c['department_name']; ?></td>
<td><?php echo $c['semester_name']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
<button type="submit" name="delete_course" class="btn btn-danger btn-sm btn-delete" data-item="this course"><i class="fas fa-trash"></i></button>
</form>
</td>
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
