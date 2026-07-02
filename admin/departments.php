<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_department'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $department_name = trim($_POST['department_name']);
    if ($faculty_id <= 0 || $department_name == "") {
        $message = "Please select a faculty and enter department name."; $message_class = "error";
    } else {
        $insert = mysqli_prepare($conn, "INSERT INTO departments (faculty_id, department_name) VALUES (?, ?)");
        mysqli_stmt_bind_param($insert, "is", $faculty_id, $department_name);
        if (mysqli_stmt_execute($insert)) {
            $message = "Department added successfully."; $message_class = "success";
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_department'])) {
    $delete = mysqli_prepare($conn, "DELETE FROM departments WHERE id = ?");
    mysqli_stmt_bind_param($delete, "i", $_POST['department_id']);
    if (mysqli_stmt_execute($delete)) {
        $message = "Department deleted successfully."; $message_class = "success";
    } else {
        $message = "Cannot delete: linked to courses or users."; $message_class = "error";
    }
}

$faculties = mysqli_query($conn, "SELECT * FROM faculties ORDER BY faculty_name ASC");
$departments = mysqli_query($conn, "SELECT d.*, f.faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id ORDER BY d.department_name ASC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Departments</h1><p>Manage university departments under faculties</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Department</h3>
<label>Faculty</label>
<select name="faculty_id" required>
<option value="">Select Faculty</option>
<?php while ($f = mysqli_fetch_assoc($faculties)) { ?>
<option value="<?php echo $f['id']; ?>"><?php echo $f['faculty_name']; ?></option>
<?php } ?>
</select>
<br><br>
<label>Department Name</label>
<input type="text" name="department_name" placeholder="e.g. Computer Science" required>
<br><br>
<button type="submit" name="add_department" class="btn btn-primary"><i class="fas fa-plus"></i> Add Department</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Departments</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Department</th><th>Faculty</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; while ($d = mysqli_fetch_assoc($departments)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $d['department_name']; ?></td>
<td><span class="badge badge-info"><?php echo $d['faculty_name']; ?></span></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="department_id" value="<?php echo $d['id']; ?>">
<button type="submit" name="delete_department" class="btn btn-danger btn-sm btn-delete" data-item="this department"><i class="fas fa-trash"></i></button>
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
