<?php
session_start();
require_once "../php/db_connect.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['fullname'] = $admin['fullname'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = "admin";
                $_SESSION['login_success'] = true;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - University Attendance System</title>
<link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
<div class="login-container">
<div class="login-box">
<div class="logo">
<i class="fa-solid fa-user-shield"></i>
<h2>University Attendance System</h2>
<p>Administrator Login</p>
</div>

<?php if($error!=""): ?>
<div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
<div class="input-group">
<label>Email Address</label>
<input type="email" name="email" placeholder="Enter email" required>
</div>

<div class="input-group">
<label>Password</label>
<div class="password-wrap">
<input type="password" name="password" id="password" placeholder="Enter password" required>
<button type="button" class="toggle-pwd" onclick="togglePassword()">
<i class="fas fa-eye" id="pwdIcon"></i>
</button>
</div>
</div>

<button type="submit" class="btn-login">
<i class="fa-solid fa-right-to-bracket"></i> Login
</button>
</form>
<div class="footer-text">&copy; <?php echo date('Y'); ?> UniAttend System</div>
</div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('pwdIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>
