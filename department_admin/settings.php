<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $stmt = mysqli_prepare($conn, "UPDATE department_admins SET fullname=?, phone=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $fullname, $phone, $deptAdmin['id']);
    mysqli_stmt_execute($stmt);
    $_SESSION['fullname'] = $fullname;
    $message = "Profile updated."; $message_class = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $q = mysqli_query($conn, "SELECT password FROM department_admins WHERE id={$deptAdmin['id']} LIMIT 1");
    $r = mysqli_fetch_assoc($q);
    if (password_verify($old, $r['password'])) {
        mysqli_query($conn, "UPDATE department_admins SET password='$new' WHERE id={$deptAdmin['id']}");
        $message = "Password changed."; $message_class = "success";
    } else {
        $message = "Current password is incorrect."; $message_class = "error";
    }
}
?>
<div class="main-content">
<div class="page-title"><h1>Account Settings</h1><p>Update your profile and password</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-user-cog"></i> Profile</h3>
<label>Full Name</label>
<input type="text" name="fullname" value="<?php echo htmlspecialchars($deptAdmin['fullname']); ?>" required>
<label>Email</label>
<input type="email" value="<?php echo htmlspecialchars($deptAdmin['email']); ?>" disabled style="background:#f5f5f5;">
<label>Phone</label>
<input type="text" name="phone" value="<?php echo htmlspecialchars($deptAdmin['phone'] ?? ''); ?>">
<label>Department</label>
<input type="text" value="<?php echo htmlspecialchars($deptAdmin['department_name']); ?>" disabled style="background:#f5f5f5;">
<br>
<button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
</form>

<form method="POST">
<h3><i class="fas fa-lock"></i> Change Password</h3>
<label>Current Password</label>
<input type="password" name="old_password" required>
<label>New Password</label>
<input type="password" name="new_password" required minlength="6">
<label>Confirm New Password</label>
<input type="password" name="confirm_password" required minlength="6" oninput="if(this.value!=this.form.new_password.value) this.setCustomValidity('Passwords do not match'); else this.setCustomValidity('');">
<br>
<button type="submit" name="change_password" class="btn btn-warning"><i class="fas fa-key"></i> Change Password</button>
</form>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
