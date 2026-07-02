<?php
session_start();
require_once 'php/db_connect.php';

$message = ""; $message_class = "";
$token = $_GET['token'] ?? '';
$role = $_GET['role'] ?? 'student';
$valid = false;

if ($token) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM password_resets WHERE token=? AND is_used=0 AND expires_at > NOW() LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $valid = true;
        $reset = mysqli_fetch_assoc($result);
        $email = $reset['email'];
        $role = $reset['role'];
    } else {
        $message = "Invalid or expired reset link."; $message_class = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM password_resets WHERE token=? AND is_used=0 AND expires_at > NOW() LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $reset = mysqli_fetch_assoc($result);
        $email = $reset['email'];
        $role = $reset['role'];

        $table = '';
        if ($role == 'student') $table = 'students';
        elseif ($role == 'lecturer') $table = 'lecturers';
        elseif ($role == 'department_admin') $table = 'department_admins';
        elseif ($role == 'admin') $table = 'admins';

        if ($table) {
            mysqli_query($conn, "UPDATE $table SET password='$password' WHERE email='$email'");
            mysqli_query($conn, "UPDATE password_resets SET is_used=1 WHERE token='$token'");
            $message = "Password reset successful. <a href='" . ($role=='student'?'student_login.php':'login.php') . "'>Login now</a>"; $message_class = "success";
        }
    } else {
        $message = "Invalid or expired reset link."; $message_class = "error";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — UniAttend</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#f1f5f9; min-height:100vh; display:flex; justify-content:center; align-items:center; padding:20px; }
.card { background:#fff; border-radius:20px; padding:40px; width:100%; max-width:440px; box-shadow:0 15px 40px rgba(0,0,0,.06); }
.card h2 { font-size:24px; color:#0f172a; margin-bottom:6px; }
.card .sub { color:#64748b; font-size:14px; margin-bottom:28px; }
.form-group { margin-bottom:20px; }
.form-group label { font-size:13px; font-weight:600; color:#334155; display:block; margin-bottom:6px; }
.form-group input { width:100%; padding:13px 16px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:14px; outline:none; transition:.2s; background:#f8fafc; }
.form-group input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.btn { width:100%; padding:14px; background:#0f172a; color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:600; cursor:pointer; transition:.2s; }
.btn:hover { background:#1e293b; }
.msg { padding:14px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
.msg.success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.msg.error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
</style>
</head>
<body>
<div class="card">
<h2><i class="fas fa-lock" style="color:#3b82f6;"></i> Set new password</h2>
<p class="sub">Choose a strong password you haven't used before.</p>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>"><?php echo $message; ?></div>
<?php } ?>

<?php if ($valid || (isset($reset) && $reset)) { ?>
<form method="POST">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
<div class="form-group">
<label>New Password</label>
<input type="password" name="password" placeholder="Min 6 characters" minlength="6" required>
</div>
<div class="form-group">
<label>Confirm Password</label>
<input type="password" name="confirm" placeholder="Repeat password" required oninput="if(this.value!=this.form.password.value) this.setCustomValidity('Passwords do not match'); else this.setCustomValidity('');">
</div>
<button type="submit" name="reset" class="btn"><i class="fas fa-save"></i> Reset password</button>
</form>
<?php } ?>
</div>
</body>
</html>
