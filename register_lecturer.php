<?php
session_start();
require_once __DIR__ . '/php/db_connect.php';

$message = ""; $message_class = "";
$token = $_GET['token'] ?? '';
$deptId = 0;
$deptName = "";

if ($token) {
    $stmt = mysqli_prepare($conn, "SELECT lrt.*, d.department_name FROM lecturer_registration_tokens lrt JOIN departments d ON lrt.department_id=d.id WHERE lrt.token=? AND lrt.is_used=0 AND (lrt.expires_at IS NULL OR lrt.expires_at > NOW()) LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $deptId = $row['department_id'];
        $deptName = $row['department_name'];
    } else {
        $message = "Invalid or expired registration link."; $message_class = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $token = $_POST['token'] ?? '';
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $staff_id = trim($_POST['staff_id']);
    $qualification = trim($_POST['qualification']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dept_id = (int)$_POST['dept_id'];

    if ($dept_id <= 0) {
        $message = "Invalid department."; $message_class = "error";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO lecturers (staff_id, fullname, email, password, phone, department_id, status, approval_status, qualification) VALUES (?, ?, ?, ?, ?, ?, 'Active', 'Pending', ?)");
        mysqli_stmt_bind_param($stmt, "sssssis", $staff_id, $fullname, $email, $password, $phone, $dept_id, $qualification);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_query($conn, "UPDATE lecturer_registration_tokens SET is_used=1 WHERE token='$token'");
            $lecturerId = mysqli_insert_id($conn);
            $notifMsg = "$fullname ($staff_id) registered as lecturer and pending approval.";
            $nStmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, user_role, title, message) VALUES ((SELECT id FROM department_admins WHERE department_id=? LIMIT 1), 'department_admin', 'New Lecturer Registration', ?)");
            mysqli_stmt_bind_param($nStmt, "is", $dept_id, $notifMsg);
            mysqli_stmt_execute($nStmt);
            $message = "Registration submitted! Your account is pending approval by the Department Admin."; $message_class = "success";
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lecturer Registration - University Attendance System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#f0f4ff; min-height:100vh; display:flex; justify-content:center; align-items:center; padding:30px; }
.card { background:#fff; border-radius:18px; padding:35px; width:100%; max-width:520px; box-shadow:0 15px 40px rgba(21,101,192,.12); }
.card h2 { color:#0d47a1; margin-bottom:5px; font-size:24px; }
.card p { color:#666; margin-bottom:20px; font-size:14px; }
label { display:block; font-weight:500; font-size:13px; color:#555; margin-bottom:4px; margin-top:14px; }
input { width:100%; padding:11px 14px; border:1.5px solid #e0e0e0; border-radius:10px; font-size:14px; outline:none; transition:.3s; }
input:focus { border-color:#1565c0; box-shadow:0 0 0 3px rgba(21,101,192,.12); }
input[readonly] { background:#f5f5f5; color:#888; }
.btn { width:100%; padding:12px; background:#1565c0; color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer; margin-top:20px; transition:.3s; }
.btn:hover { background:#0d47a1; }
.msg { padding:14px 18px; border-radius:10px; margin-bottom:18px; font-size:14px; font-weight:500; text-align:center; }
.msg.success { background:#e8f5e9; color:#2e7d32; }
.msg.error { background:#ffebee; color:#c62828; }
.dept-badge { background:#e3f2fd; color:#1565c0; padding:4px 14px; border-radius:20px; font-size:13px; font-weight:500; display:inline-block; }
</style>
</head>
<body>
<div class="card">
<h2><i class="fas fa-chalkboard-teacher"></i> Lecturer Registration</h2>
<p>Fill in your details to register. Your account will need approval.</p>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>"><?php echo $message; ?></div>
<?php } ?>

<?php if ($deptId > 0 && (!$message || strpos($message, 'submitted') === false)) { ?>
<form method="POST">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input type="hidden" name="dept_id" value="<?php echo $deptId; ?>">

<label>Department</label>
<input type="text" value="<?php echo htmlspecialchars($deptName); ?>" readonly>

<label>Full Name</label>
<input type="text" name="fullname" placeholder="e.g. Dr John Doe" required>

<label>Email Address</label>
<input type="email" name="email" placeholder="john@university.edu" required>

<label>Phone Number</label>
<input type="text" name="phone" placeholder="e.g. 08012345678">

<label>Staff ID</label>
<input type="text" name="staff_id" placeholder="e.g. LEC/CS/001" required>

<label>Qualification</label>
<input type="text" name="qualification" placeholder="e.g. PhD Computer Science">

<label>Password</label>
<input type="password" name="password" placeholder="Create a password" required>

<button type="submit" name="register" class="btn"><i class="fas fa-paper-plane"></i> Submit Registration</button>
</form>
<?php } elseif ($deptId <= 0 && !$message) { ?>
<p style="text-align:center;color:#c62828;">⚠️ Invalid or expired registration link. Please contact the department admin.</p>
<?php } ?>
</div>
</body>
</html>
