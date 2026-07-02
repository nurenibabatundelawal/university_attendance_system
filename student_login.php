<?php
session_start();
require_once 'php/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE matric_no = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        if (password_verify($password, $student['password']) || $password == $student['password']) {
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['fullname'] = $student['fullname'];
            $_SESSION['role'] = 'student';
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['fullname'];
            $_SESSION['matric_no'] = $student['matric_no'];
            $_SESSION['login_success'] = true;
            header("Location: student/dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Student not found.";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login — UniAttend</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#0f172a; min-height:100vh; display:flex; }
.left-panel {
  flex:1; display:flex; flex-direction:column; justify-content:center; padding:60px;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  position:relative; overflow:hidden;
}
.left-panel::before {
  content:''; position:absolute; width:500px; height:500px; border-radius:50%;
  background:radial-gradient(circle, rgba(59,130,246,.08) 0%, transparent 70%);
  top:-100px; right:-100px;
}
.left-panel::after {
  content:''; position:absolute; width:400px; height:400px; border-radius:50%;
  background:radial-gradient(circle, rgba(139,92,246,.06) 0%, transparent 70%);
  bottom:-80px; left:-80px;
}
.left-content { position:relative; z-index:1; max-width:480px; }
.left-content .logo { display:flex; align-items:center; gap:12px; margin-bottom:40px; }
.left-content .logo i { font-size:32px; color:#3b82f6; }
.left-content .logo span { font-size:22px; font-weight:700; color:#fff; }
.left-content .logo span span { color:#3b82f6; }
.left-content h1 { font-size:36px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:16px; }
.left-content p { color:#94a3b8; font-size:15px; line-height:1.7; margin-bottom:32px; }
.feature-list { display:flex; flex-direction:column; gap:16px; }
.feature-item { display:flex; align-items:center; gap:14px; color:#cbd5e1; font-size:14px; }
.feature-item i { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; }
.feature-item .fi-blue { background:rgba(59,130,246,.15); color:#60a5fa; }
.feature-item .fi-gold { background:rgba(245,158,11,.15); color:#fbbf24; }
.feature-item .fi-green { background:rgba(16,185,129,.15); color:#34d399; }

.right-panel {
  width:480px; display:flex; flex-direction:column; justify-content:center; padding:60px 50px;
  background:#fff; position:relative;
}
.right-panel .back-link { position:absolute; top:30px; left:30px; color:#64748b; font-size:14px; text-decoration:none; display:flex; align-items:center; gap:6px; font-weight:500; transition:.2s; }
.right-panel .back-link:hover { color:#0f172a; }
.right-panel h2 { font-size:26px; font-weight:700; color:#0f172a; margin-bottom:6px; }
.right-panel .subtitle { color:#64748b; font-size:14px; margin-bottom:30px; }
.form-group { margin-bottom:20px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px; }
.form-group .input-wrap { position:relative; }
.form-group .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; }
.form-group input { width:100%; padding:13px 14px 13px 44px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:14px; outline:none; transition:.2s; background:#f8fafc; font-family:'Inter',sans-serif; }
.form-group input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); background:#fff; }
.form-group .toggle-pass { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8; font-size:16px; }
.form-options { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
.form-options a { color:#3b82f6; font-size:13px; font-weight:500; text-decoration:none; transition:.2s; }
.form-options a:hover { color:#2563eb; text-decoration:underline; }
.btn-login { width:100%; padding:14px; background:#0f172a; color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:600; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; font-family:'Inter',sans-serif; }
.btn-login:hover { background:#1e293b; }
.error-msg { background:#fef2f2; color:#dc2626; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:8px; border:1px solid #fecaca; }
@media(max-width:900px) {
  body { flex-direction:column; }
  .left-panel { padding:40px 30px; }
  .right-panel { width:100%; padding:40px 30px; min-height:100vh; }
}
</style>
</head>
<body>
<div class="left-panel">
<div class="left-content">
<div class="logo"><i class="fas fa-graduation-cap"></i><span>Uni<span>Attend</span></span></div>
<h1>Welcome back,<br>student</h1>
<p>Sign in to track your attendance, view course progress, and download your records.</p>
<div class="feature-list">
<div class="feature-item"><i class="fas fa-check-circle fi-blue"></i> View real-time attendance</div>
<div class="feature-item"><i class="fas fa-chart-line fi-gold"></i> Track per-course percentages</div>
<div class="feature-item"><i class="fas fa-download fi-green"></i> Download attendance history</div>
</div>
</div>
</div>

<div class="right-panel">
<a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to home</a>
<h2>Student login</h2>
<p class="subtitle">Enter your matric number or email to continue</p>

<?php if ($error != '') { ?>
<div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php } ?>

<form method="POST">
<div class="form-group">
<label>Matric Number or Email</label>
<div class="input-wrap">
<i class="fas fa-user"></i>
<input type="text" name="username" placeholder="e.g. CSC/2026/001 or email" required>
</div>
</div>

<div class="form-group">
<label>Password</label>
<div class="input-wrap">
<i class="fas fa-lock"></i>
<input type="password" name="password" id="studentPass" placeholder="Enter your password" required>
<span class="toggle-pass" onclick="togglePass()"><i class="fas fa-eye"></i></span>
</div>
</div>

<div class="form-options">
<a href="forgot_password.php?role=student">Forgot password?</a>
</div>

<button type="submit" class="btn-login"><i class="fas fa-arrow-right"></i> Sign in</button>

<div style="text-align:center;margin-top:20px;color:#64748b;font-size:14px;">
Don't have an account? <a href="register_student.php" style="color:#3b82f6;font-weight:600;text-decoration:none;">Register here</a>
</div>
</form>
</div>

<script>
function togglePass() {
  let inp = document.getElementById('studentPass');
  let icon = event.currentTarget.querySelector('i');
  if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
  else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
}
</script>
</body>
</html>
