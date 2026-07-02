<?php
session_start();
require_once 'php/db_connect.php';
require_once 'php/email_helper.php';

$role = $_GET['role'] ?? 'student';
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'student';

    $table = '';
    if ($role == 'student') $table = 'students';
    elseif ($role == 'lecturer') $table = 'lecturers';
    elseif ($role == 'department_admin') $table = 'department_admins';

    if ($table) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM $table WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $token = bin2hex(random_bytes(32));

            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $ins = mysqli_prepare($conn, "INSERT INTO password_resets (email, role, token, expires_at) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $email, $role, $token, $expires);
            mysqli_stmt_execute($ins);

            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/university_attendance_system/reset_password.php?token=$token&role=$role";
            $fullname = $user['fullname'] ?? $user['student_name'] ?? 'User';
            $subject = "Password Reset - University Attendance System";
            $body = "<html><body style='font-family:Arial;padding:20px;'>
                <h2>Password Reset Request</h2>
                <p>Hello <strong>$fullname</strong>,</p>
                <p>Click the link below to reset your password. This link expires in 1 hour.</p>
                <p><a href='$resetLink' style='background:#1565c0;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block;'>Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
                <p>— University Attendance System</p>
                </body></html>";

            if (sendEmail($email, $subject, $body, $conn)) {
                $message = "Password reset link sent to your email."; $message_class = "success";
            } else {
                $message = "Reset link: <a href='$resetLink'>$resetLink</a> (mail not configured, use this direct link)"; $message_class = "warning";
            }
        } else {
            $message = "No account found with that email."; $message_class = "error";
        }
    }
}

$title = ucfirst($role) . " Password Reset";
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title; ?> — UniAttend</title>
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
.msg.warning { background:#fffbeb; color:#b45309; border:1px solid #fde68a; }
.back-link { display:block; text-align:center; margin-top:18px; color:#64748b; font-size:13px; text-decoration:none; }
.back-link:hover { color:#0f172a; }
</style>
</head>
<body>
<div class="card">
<h2><i class="fas fa-key" style="color:#3b82f6;"></i> Reset password</h2>
<p class="sub">Enter your email to receive a password reset link.</p>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
<div class="form-group">
<label>Email Address</label>
<input type="email" name="email" placeholder="your@email.com" required>
</div>
<button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Send reset link</button>
</form>
<a href="<?php echo $role == 'student' ? 'student_login.php' : 'login.php'; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to login</a>
</div>
</body>
</html>
