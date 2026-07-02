<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($fullname == "" || $email == "") {
        $message = "Full name and email are required."; $message_class = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check, "si", $email, $_SESSION['user_id']);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if ($result && mysqli_num_rows($result) > 0) {
            $message = "Email already in use by another admin."; $message_class = "error";
        } else {
            $update = mysqli_prepare($conn, "UPDATE admins SET fullname = ?, email = ?, phone = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "sssi", $fullname, $email, $phone, $_SESSION['user_id']);
            if (mysqli_stmt_execute($update)) {
                $_SESSION['fullname'] = $fullname; $_SESSION['email'] = $email;
                $message = "Profile updated successfully."; $message_class = "success";
            } else {
                $message = "Error updating profile."; $message_class = "error";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if ($current == "" || $new_pass == "" || $confirm == "") {
        $message = "Please fill all password fields."; $message_class = "error";
    } elseif ($new_pass !== $confirm) {
        $message = "New passwords do not match."; $message_class = "error";
    } else {
        $q = mysqli_prepare($conn, "SELECT password FROM admins WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($q, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($q);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
        if (password_verify($current, $admin['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $hashed, $_SESSION['user_id']);
            if (mysqli_stmt_execute($update)) {
                $message = "Password changed successfully."; $message_class = "success";
            } else {
                $message = "Error changing password."; $message_class = "error";
            }
        } else {
            $message = "Current password is incorrect."; $message_class = "error";
        }
    }
}

$q = mysqli_prepare($conn, "SELECT * FROM admins WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($q, "i", $_SESSION['user_id']);
mysqli_stmt_execute($q);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($q));

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Settings</h1><p>Manage your profile and password</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<h3><i class="fas fa-user"></i> Profile Information</h3>
<form method="POST">
<div class="form-row">
<div><label>Full Name</label><input type="text" name="fullname" value="<?php echo $admin['fullname']; ?>" required></div>
<div><label>Email</label><input type="email" name="email" value="<?php echo $admin['email']; ?>" required></div>
</div>
<br>
<label>Phone</label>
<input type="tel" name="phone" value="<?php echo $admin['phone']; ?>" placeholder="Optional">
<br><br>
<button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
</form>
</div>

<div class="form-box" style="margin-top:25px;">
<h3><i class="fas fa-lock"></i> Change Password</h3>
<form method="POST">
<div class="form-row">
<div>
<label>Current Password</label>
<div class="password-wrap" style="display:flex;gap:8px;">
<input type="password" name="current_password" required style="flex:1;">
<button type="button" class="btn btn-sm btn-outline toggle-password" style="flex-shrink:0;"><i class="fas fa-eye"></i></button>
</div>
</div>
<div>
<label>New Password</label>
<input type="password" name="new_password" required>
</div>
</div>
<br>
<label>Confirm New Password</label>
<input type="password" name="confirm_password" required>
<br><br>
<button type="submit" name="change_password" class="btn btn-warning"><i class="fas fa-key"></i> Change Password</button>
</form>
</div>
</div>

<?php include "includes/footer.php"; ?>
