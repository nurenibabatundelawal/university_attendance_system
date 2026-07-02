<?php
session_start();
require_once __DIR__ . '/../php/db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

   if ($role == "admin") {
    $table = "admins";
    $redirect = "../admin/dashboard.php";
} elseif ($role == "lecturer") {
    $table = "lecturers";
    $redirect = "dashboard.php";
} elseif ($role == "student") {
    $table = "students";
    $redirect = "../student/dashboard.php";
} else {
        $table = "";
    }

    if ($table != "") {
        $query = "SELECT * FROM $table WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            if ($password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['role'] = $role;

if ($role == "lecturer") {
    $_SESSION['lecturer_id'] = $user['id'];
    $_SESSION['lecturer_name'] = $user['fullname'];
    $_SESSION['lecturer_email'] = $user['email'];
}

if ($role == "student") {
    $_SESSION['student_id'] = $user['id'];
    $_SESSION['student_name'] = $user['fullname'];
}

if ($role == "admin") {
    $_SESSION['admin_id'] = $user['id'];
}
                header("Location: $redirect");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with this email.";
        }
    } else {
        $error = "Invalid login role.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - University Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-box">
    <h2>University Attendance System</h2>
    <h3>Login</h3>

    <?php if ($error != "") { ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php } ?>

    <form method="POST">
        <label>Email Address</label><br>
        <input type="email" name="email" required style="width:100%; padding:10px;"><br><br>

        <label>Password</label><br>
        <input type="password" name="password" required style="width:100%; padding:10px;"><br><br>

        <label>Login As</label><br>
        <select name="role" required style="width:100%; padding:10px;">
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="lecturer">Lecturer</option>
            <option value="student">Student</option>
        </select><br><br>

        <button type="submit" style="width:100%; padding:10px;">Login</button>
    </form>
</div>

</body>
</html>
