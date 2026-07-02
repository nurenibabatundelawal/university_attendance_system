<?php
session_start();
require_once __DIR__ . '/php/db_connect.php';

$error = "";
$roleDefault = $_GET['role'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role == "admin") { $table = "admins"; $redirect = "admin/dashboard.php"; }
    elseif ($role == "department_admin") { $table = "department_admins"; $redirect = "department_admin/dashboard.php"; }
    elseif ($role == "lecturer") { $table = "lecturers"; $redirect = "lecturer/dashboard.php"; }
    else { $table = ""; }

    if ($table != "") {
        $query = "SELECT * FROM $table WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password']) || $password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $role;
                if ($role == 'lecturer') {
                    $_SESSION['lecturer_id'] = $user['id'];
                    $_SESSION['lecturer_name'] = $user['fullname'];
                    $_SESSION['lecturer_email'] = $user['email'];
                }
                if ($role == 'department_admin') {
                    $_SESSION['department_admin_id'] = $user['id'];
                }
                $_SESSION['login_success'] = true;
                header("Location: $redirect");
                exit();
            } else { $error = "Incorrect password."; }
        } else { $error = "No account found with this email."; }
    } else { $error = "Invalid login role."; }
}

$roleLabels = ['admin'=>'Main Admin','department_admin'=>'Department Admin','lecturer'=>'Lecturer'];
$roleIcons = ['admin'=>'fa-user-shield','department_admin'=>'fa-building','lecturer'=>'fa-chalkboard-teacher'];
$roleDesc = ['admin'=>'Full system control & oversight','department_admin'=>'Manage your department','lecturer'=>'Start sessions & view reports'];
$roleTitle = $roleDefault ? $roleLabels[$roleDefault] . ' Login' : 'Staff Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $roleTitle; ?> — UniAttend</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#0f172a; min-height:100vh; display:flex; }
.left-panel {
  flex:1; display:flex; flex-direction:column; justify-content:center; padding:60px;
  background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  position:relative; overflow:hidden;
}
.left-panel::before {
  content:''; position:absolute; width:600px; height:600px; border-radius:50%;
  background:radial-gradient(circle, rgba(59,130,246,.08) 0%, transparent 70%);
  top:-150px; right:-100px;
}
.left-panel::after {
  content:''; position:absolute; width:400px; height:400px; border-radius:50%;
  background:radial-gradient(circle, rgba(139,92,246,.06) 0%, transparent 70%);
  bottom:-100px; left:-80px;
}
.left-content { position:relative; z-index:1; max-width:500px; }
.left-content .logo { display:flex; align-items:center; gap:12px; margin-bottom:40px; }
.left-content .logo i { font-size:32px; color:#3b82f6; }
.left-content .logo span { font-size:22px; font-weight:700; color:#fff; }
.left-content .logo span span { color:#3b82f6; }
.left-content h1 { font-size:40px; font-weight:800; color:#fff; line-height:1.15; margin-bottom:16px; }
.left-content p { color:#94a3b8; font-size:15px; line-height:1.7; margin-bottom:32px; }
.role-cards { display:flex; flex-direction:column; gap:10px; }
.role-card {
  display:flex; align-items:center; gap:16px; padding:16px 20px; border-radius:14px;
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.06);
  cursor:pointer; transition:.25s; text-decoration:none;
}
.role-card:hover { background:rgba(255,255,255,.08); border-color:rgba(59,130,246,.3); transform:translateX(4px); }
.role-card .rc-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.rc-admin .rc-icon { background:rgba(59,130,246,.15); color:#60a5fa; }
.rc-dept .rc-icon { background:rgba(139,92,246,.15); color:#a78bfa; }
.rc-lecturer .rc-icon { background:rgba(16,185,129,.15); color:#34d399; }
.role-card .rc-text { flex:1; }
.role-card .rc-text strong { display:block; color:#f1f5f9; font-size:14px; }
.role-card .rc-text small { color:#64748b; font-size:12px; }
.role-card .rc-arrow { color:#475569; font-size:14px; }

.right-panel {
  width:460px; display:flex; flex-direction:column; justify-content:center; padding:60px 50px;
  background:#fff; position:relative;
}
.right-panel .back-link { position:absolute; top:30px; left:30px; color:#64748b; font-size:14px; text-decoration:none; display:flex; align-items:center; gap:6px; font-weight:500; }
.right-panel .back-link:hover { color:#0f172a; }
.right-panel h2 { font-size:26px; font-weight:700; color:#0f172a; margin-bottom:6px; }
.right-panel .subtitle { color:#64748b; font-size:14px; margin-bottom:30px; }
.role-badge { display:inline-flex; align-items:center; gap:6px; background:#eef2ff; color:#4338ca; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:20px; }
.form-group { margin-bottom:18px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px; }
.form-group .input-wrap { position:relative; }
.form-group .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:16px; }
.form-group input, .form-group select { width:100%; padding:13px 14px 13px 44px; border:1.5px solid #e2e8f0; border-radius:12px; font-size:14px; outline:none; transition:.2s; background:#f8fafc; font-family:'Inter',sans-serif; }
.form-group select { padding-left:14px; cursor:pointer; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; }
.form-group input:focus, .form-group select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); background:#fff; }
.form-group .toggle-pass { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8; }
.form-options { display:flex; justify-content:space-between; align-items:center; margin:22px 0; }
.form-options a { color:#3b82f6; font-size:13px; font-weight:500; text-decoration:none; }
.form-options a:hover { color:#2563eb; text-decoration:underline; }
.btn-login { width:100%; padding:14px; background:#0f172a; color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:600; cursor:pointer; transition:.2s; font-family:'Inter',sans-serif; }
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
<h1>Staff &amp; Admin<br>portal</h1>
<p>Select your role below to access your dashboard.</p>

<div class="role-cards">
<a href="?role=admin" class="role-card rc-admin <?php echo $roleDefault=='admin'?'active':''; ?>">
<div class="rc-icon"><i class="fas fa-user-shield"></i></div>
<div class="rc-text"><strong>Main Admin</strong><br><small>Full system control &amp; oversight</small></div>
<div class="rc-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
<a href="?role=department_admin" class="role-card rc-dept <?php echo $roleDefault=='department_admin'?'active':''; ?>">
<div class="rc-icon"><i class="fas fa-building"></i></div>
<div class="rc-text"><strong>Department Admin</strong><br><small>Manage your department</small></div>
<div class="rc-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
<a href="?role=lecturer" class="role-card rc-lecturer <?php echo $roleDefault=='lecturer'?'active':''; ?>">
<div class="rc-icon"><i class="fas fa-chalkboard-teacher"></i></div>
<div class="rc-text"><strong>Lecturer</strong><br><small>Start sessions &amp; view reports</small></div>
<div class="rc-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
</div>
</div>
</div>

<div class="right-panel">
<a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Home</a>

<?php if ($roleDefault): ?>
<div class="role-badge"><i class="<?php echo $roleIcons[$roleDefault]; ?>"></i> <?php echo $roleLabels[$roleDefault]; ?></div>
<?php endif; ?>

<h2><?php echo $roleDefault ? $roleLabels[$roleDefault] : 'Staff'; ?> login</h2>
<p class="subtitle"><?php echo $roleDefault ? $roleDesc[$roleDefault] : 'Select a role to continue.'; ?></p>

<?php if ($error != "") { ?>
<div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php } ?>

<form method="POST">
<input type="hidden" name="role" value="<?php echo htmlspecialchars($roleDefault); ?>">

<div class="form-group">
<label>Email Address</label>
<div class="input-wrap">
<i class="fas fa-envelope"></i>
<input type="email" name="email" placeholder="your@email.com" required <?php echo $roleDefault ? '' : 'disabled'; ?>>
</div>
</div>

<div class="form-group">
<label>Password</label>
<div class="input-wrap">
<i class="fas fa-lock"></i>
<input type="password" name="password" id="loginPass" placeholder="Enter password" required <?php echo $roleDefault ? '' : 'disabled'; ?>>
<span class="toggle-pass" onclick="togglePass()"><i class="fas fa-eye"></i></span>
</div>
</div>

<div class="form-options">
<?php if ($roleDefault == 'lecturer'): ?>
<a href="forgot_password.php?role=lecturer">Forgot password?</a>
<?php elseif ($roleDefault == 'department_admin'): ?>
<a href="forgot_password.php?role=department_admin">Forgot password?</a>
<?php elseif ($roleDefault == 'admin'): ?>
<a href="forgot_password.php?role=admin">Forgot password?</a>
<?php else: ?>
<span></span>
<?php endif; ?>
</div>

<button type="submit" class="btn-login" <?php echo $roleDefault ? '' : 'disabled'; ?>><i class="fas fa-arrow-right"></i> Sign in</button>
</form>

<p style="text-align:center;margin-top:20px;font-size:13px;color:#94a3b8;">
<a href="student_login.php" style="color:#3b82f6;font-weight:500;text-decoration:none;">Student? Login here →</a>
</p>
</div>

<script>
function togglePass() {
  let inp = document.getElementById('loginPass');
  let icon = event.currentTarget.querySelector('i');
  if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
  else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
}
<?php if (isset($_SESSION['login_success'])) { ?>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({ icon:'success', title:'Welcome back!', text:'<?php echo addslashes($_SESSION["fullname"]); ?>', timer:2000, showConfirmButton:false });
});
<?php unset($_SESSION['login_success']); } ?>
</script>
</body>
</html>
