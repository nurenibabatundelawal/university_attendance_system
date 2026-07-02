<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = trim($_POST['staff_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $department_id = intval($_POST['department_id']);
    $status = $_POST['status'];

    if ($staff_id == "" || $fullname == "" || $email == "" || $password == "" || $department_id <= 0) {
        $message = "Please fill all required fields."; $message_class = "error";
    } else {
        $check_staff = mysqli_prepare($conn, "SELECT id FROM lecturers WHERE staff_id = ? LIMIT 1");
        mysqli_stmt_bind_param($check_staff, "s", $staff_id);
        mysqli_stmt_execute($check_staff);
        if (mysqli_num_rows(mysqli_stmt_get_result($check_staff)) > 0) {
            $message = "Staff ID already exists."; $message_class = "error";
        } else {
            $check_email = mysqli_prepare($conn, "SELECT id FROM lecturers WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($check_email, "s", $email);
            mysqli_stmt_execute($check_email);
            if (mysqli_num_rows(mysqli_stmt_get_result($check_email)) > 0) {
                $message = "Email already exists."; $message_class = "error";
            } else {
                $insert = mysqli_prepare($conn, "INSERT INTO lecturers (staff_id, fullname, email, password, phone, department_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert, "sssssis", $staff_id, $fullname, $email, $password, $phone, $department_id, $status);
                if (mysqli_stmt_execute($insert)) {
                    $message = "Lecturer registered successfully."; $message_class = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn); $message_class = "error";
                }
            }
        }
    }
}

$departments = mysqli_query($conn, "SELECT d.id, d.department_name, f.faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id ORDER BY d.department_name ASC");
$lecturers = mysqli_query($conn, "SELECT l.*, d.department_name FROM lecturers l LEFT JOIN departments d ON l.department_id = d.id ORDER BY l.id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Lecturers</h1><p>Register and manage lecturers</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<h3><i class="fas fa-chalkboard-teacher"></i> Register New Lecturer</h3>
<form method="POST">
<div class="form-row">
<div>
<label>Staff ID</label>
<input type="text" name="staff_id" placeholder="e.g. LEC002" required>
</div>
<div>
<label>Full Name</label>
<input type="text" name="fullname" placeholder="Lecturer full name" required>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Email</label>
<input type="email" name="email" placeholder="lecturer@example.com" required>
</div>
<div>
<label>Password</label>
<div class="password-wrap" style="display:flex;gap:8px;">
<input type="password" name="password" placeholder="Default password" required style="flex:1;">
<button type="button" class="btn btn-sm btn-outline toggle-password" style="flex-shrink:0;"><i class="fas fa-eye"></i></button>
</div>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Phone</label>
<input type="tel" name="phone" placeholder="Optional">
</div>
<div>
<label>Department</label>
<select name="department_id" required>
<option value="">Select Department</option>
<?php while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>"><?php echo $d['department_name'] . " - " . $d['faculty_name']; ?></option>
<?php } ?>
</select>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Status</label>
<select name="status" required>
<option value="Active">Active</option>
<option value="Inactive">Inactive</option>
</select>
</div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-chalkboard-teacher"></i> Register Lecturer</button>
</form>
</div>

<div class="form-box" style="margin-top:25px;">
<h3><i class="fas fa-list"></i> Registered Lecturers</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Staff ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Status</th></tr></thead>
<tbody>
<?php $i=1; while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><span class="badge badge-dark"><?php echo $l['staff_id']; ?></span></td>
<td><?php echo $l['fullname']; ?></td>
<td><?php echo $l['email']; ?></td>
<td><?php echo $l['phone'] ?: '-'; ?></td>
<td><?php echo $l['department_name']; ?></td>
<td><span class="badge <?php echo $l['status'] == 'Active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo $l['status']; ?></span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
