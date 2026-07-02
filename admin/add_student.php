<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matric_no = trim($_POST['matric_no']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $department_id = intval($_POST['department_id']);
    $level = trim($_POST['level']);
    $academic_session_id = intval($_POST['academic_session_id']);
    $fingerprint_id = trim($_POST['fingerprint_id']);
    $rfid_uid = strtoupper(trim($_POST['rfid_uid']));
    $status = $_POST['status'];

    if ($matric_no == "" || $fullname == "" || $email == "" || $password == "" || $department_id <= 0 || $level == "" || $academic_session_id <= 0) {
        $message = "Please fill all required fields, including academic session."; $message_class = "error";
    } else {
        $check_session = mysqli_prepare($conn, "SELECT id FROM academic_sessions WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($check_session, "i", $academic_session_id);
        mysqli_stmt_execute($check_session);
        $session_result = mysqli_stmt_get_result($check_session);
        if (!$session_result || mysqli_num_rows($session_result) == 0) {
            $message = "Invalid academic session selected."; $message_class = "error";
        }
        if ($message == "") {
            $check_matric = mysqli_prepare($conn, "SELECT id FROM students WHERE matric_no = ? LIMIT 1");
            mysqli_stmt_bind_param($check_matric, "s", $matric_no);
            mysqli_stmt_execute($check_matric);
            if (mysqli_num_rows(mysqli_stmt_get_result($check_matric)) > 0) {
                $message = "Matric number already exists."; $message_class = "error";
            }
        }
        if ($message == "") {
            $check_email = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($check_email, "s", $email);
            mysqli_stmt_execute($check_email);
            if (mysqli_num_rows(mysqli_stmt_get_result($check_email)) > 0) {
                $message = "Email already exists."; $message_class = "error";
            }
        }
        if ($message == "" && $fingerprint_id != "") {
            $check_finger = mysqli_prepare($conn, "SELECT id, fullname, matric_no FROM students WHERE fingerprint_id = ? LIMIT 1");
            mysqli_stmt_bind_param($check_finger, "i", $fingerprint_id);
            mysqli_stmt_execute($check_finger);
            $finger_result = mysqli_stmt_get_result($check_finger);
            if ($finger_result && mysqli_num_rows($finger_result) > 0) {
                $existing = mysqli_fetch_assoc($finger_result);
                $message = "Fingerprint ID already assigned to " . $existing['fullname']; $message_class = "error";
            }
        }
        if ($message == "" && $rfid_uid != "") {
            $check_rfid = mysqli_prepare($conn, "SELECT id, fullname, matric_no FROM students WHERE rfid_uid = ? LIMIT 1");
            mysqli_stmt_bind_param($check_rfid, "s", $rfid_uid);
            mysqli_stmt_execute($check_rfid);
            $rfid_result = mysqli_stmt_get_result($check_rfid);
            if ($rfid_result && mysqli_num_rows($rfid_result) > 0) {
                $existing = mysqli_fetch_assoc($rfid_result);
                $message = "RFID card already assigned to " . $existing['fullname']; $message_class = "error";
            }
        }
        if ($message == "") {
            $fingerprint_value = ($fingerprint_id == "") ? null : intval($fingerprint_id);
            $rfid_value = ($rfid_uid == "") ? null : $rfid_uid;
            $insert = "INSERT INTO students (matric_no, fullname, email, password, department_id, level, academic_session_id, fingerprint_id, rfid_uid, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt, "ssssisisss", $matric_no, $fullname, $email, $password, $department_id, $level, $academic_session_id, $fingerprint_value, $rfid_value, $status);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Student registered successfully."; $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        }
    }
}

$departments = mysqli_query($conn, "SELECT d.id, d.department_name, f.faculty_name FROM departments d LEFT JOIN faculties f ON d.faculty_id = f.id ORDER BY d.department_name ASC");
$academic_sessions = mysqli_query($conn, "SELECT id, session_name FROM academic_sessions ORDER BY id DESC");
$students = mysqli_query($conn, "SELECT s.*, d.department_name, a.session_name FROM students s LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN academic_sessions a ON s.academic_session_id = a.id ORDER BY s.id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Students</h1><p>Register and manage students</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<h3><i class="fas fa-user-plus"></i> Register New Student</h3>
<form method="POST">
<div class="form-row">
<div>
<label>Matric Number</label>
<input type="text" name="matric_no" placeholder="e.g. 22/1234" required>
</div>
<div>
<label>Full Name</label>
<input type="text" name="fullname" placeholder="Student full name" required>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Email</label>
<input type="email" name="email" placeholder="student@example.com" required>
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
<label>Department</label>
<select name="department_id" required>
<option value="">Select Department</option>
<?php while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>"><?php echo $d['department_name'] . " - " . $d['faculty_name']; ?></option>
<?php } ?>
</select>
</div>
<div>
<label>Level</label>
<select name="level" required>
<option value="">Select Level</option>
<option value="100">100 Level</option><option value="200">200 Level</option>
<option value="300">300 Level</option><option value="400">400 Level</option>
<option value="500">500 Level</option>
</select>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Academic Session</label>
<select name="academic_session_id" required>
<option value="">Select Session</option>
<?php while ($a = mysqli_fetch_assoc($academic_sessions)) { ?>
<option value="<?php echo $a['id']; ?>"><?php echo $a['session_name']; ?></option>
<?php } ?>
</select>
</div>
<div>
<label>Status</label>
<select name="status" required>
<option value="Active">Active</option>
<option value="Inactive">Inactive</option>
</select>
</div>
</div>
<br>
<div class="form-row">
<div>
<label>Fingerprint ID</label>
<input type="number" name="fingerprint_id" placeholder="e.g. 1">
<small>Enter ID after device enrollment</small>
</div>
<div>
<label>RFID UID</label>
<input type="text" name="rfid_uid" placeholder="e.g. C7FB4E07">
<small>Enter UID after scanning</small>
</div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-user-graduate"></i> Register Student</button>
</form>
</div>

<div class="form-box" style="margin-top:25px;">
<h3><i class="fas fa-list"></i> Registered Students</h3>
<div class="table-wrap">
<table class="datatable">
<thead>
<tr><th>#</th><th>Matric No</th><th>Name</th><th>Email</th><th>Department</th><th>Level</th><th>Session</th><th>Finger ID</th><th>RFID</th><th>Status</th></tr>
</thead>
<tbody>
<?php $i=1; while ($s = mysqli_fetch_assoc($students)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><span class="badge badge-dark"><?php echo $s['matric_no']; ?></span></td>
<td><?php echo $s['fullname']; ?></td>
<td><?php echo $s['email']; ?></td>
<td><?php echo $s['department_name']; ?></td>
<td><?php echo $s['level']; ?></td>
<td><?php echo $s['session_name']; ?></td>
<td><?php echo $s['fingerprint_id'] ?: '-'; ?></td>
<td><?php echo $s['rfid_uid'] ? '<span class="badge badge-info">' . $s['rfid_uid'] . '</span>' : '-'; ?></td>
<td><span class="badge <?php echo $s['status'] == 'Active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo $s['status']; ?></span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
