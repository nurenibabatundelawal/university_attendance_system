<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$message = ""; $message_class = "";
$student_id = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old = $_POST['old_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $q = mysqli_query($conn, "SELECT password FROM students WHERE id=$student_id LIMIT 1");
    $r = mysqli_fetch_assoc($q);
    if (password_verify($old, $r['password']) || $old == $r['password']) {
        mysqli_query($conn, "UPDATE students SET password='$new' WHERE id=$student_id");
        $message = "Password changed successfully."; $message_class = "success";
    } else {
        $message = "Current password is incorrect."; $message_class = "error";
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Change Password</h1><p>Update your account password</p></div>

<?php if ($message != "") { ?>
<div class="alert-box <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div>
<?php } ?>

<div class="form-box" style="max-width:500px;">
<form method="POST">
<div class="form-row" style="grid-template-columns:1fr;">
<div>
<label>Current Password</label>
<input type="password" name="old_password" required>
</div>
<div>
<label>New Password</label>
<input type="password" name="new_password" minlength="6" required>
</div>
<div>
<label>Confirm New Password</label>
<input type="password" name="confirm" required oninput="if(this.value!=this.form.new_password.value) this.setCustomValidity('Passwords do not match'); else this.setCustomValidity('');">
</div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
</form>
</div>
</div>

<?php include 'includes/footer.php'; ?>
