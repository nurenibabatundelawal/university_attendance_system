<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];
$message = ''; $msg_class = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['passport'])) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $q = $conn->prepare("SELECT matric_no, passport FROM students WHERE id = ?");
        $q->bind_param("i", $student_id);
        $q->execute();
        $old = $q->get_result()->fetch_assoc();
        if (!empty($old['passport']) && file_exists('../uploads/students/' . $old['passport'])) {
            unlink('../uploads/students/' . $old['passport']);
        }
        $filename = $old['matric_no'] . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['passport']['tmp_name'], '../uploads/students/' . $filename);
        $upd = $conn->prepare("UPDATE students SET passport = ? WHERE id = ?");
        $upd->bind_param("si", $filename, $student_id);
        $upd->execute();
        $message = 'Passport photo updated successfully.';
        $msg_class = 'success';
    } else {
        $message = 'Invalid file type. Allowed: JPG, PNG, GIF, WebP.';
        $msg_class = 'error';
    }
}

$student = $conn->prepare("SELECT s.*, d.department_name, ac.session_name FROM students s LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN academic_sessions ac ON s.academic_session_id = ac.id WHERE s.id = ?");
$student->bind_param("i", $student_id);
$student->execute();
$student = $student->get_result()->fetch_assoc();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>My Profile</h1><p>Your personal information and account details</p></div>

<?php if ($message != '') { ?>
<div class="msg <?php echo $msg_class; ?>"><i class="fas fa-<?php echo $msg_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div>
<?php } ?>

<div class="form-box">
<div class="profile-card">
<?php if (!empty($student['passport'])): ?>
<img src="../uploads/students/<?php echo $student['passport']; ?>" class="profile-avatar" alt="Photo">
<?php else: ?>
<div class="profile-avatar-placeholder"><i class="fas fa-user-graduate"></i></div>
<?php endif; ?>

<div class="profile-info" style="flex:1;">
<div class="info-row"><span class="info-label">Matric Number</span><span class="info-value"><strong><?php echo htmlspecialchars($student['matric_no']); ?></strong></span></div>
<div class="info-row"><span class="info-label">Full Name</span><span class="info-value"><?php echo htmlspecialchars($student['fullname']); ?></span></div>
<div class="info-row"><span class="info-label">Email</span><span class="info-value"><?php echo htmlspecialchars($student['email'] ?? '-'); ?></span></div>
<div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?php echo htmlspecialchars($student['phone'] ?? '-'); ?></span></div>
<div class="info-row"><span class="info-label">Gender</span><span class="info-value"><?php echo htmlspecialchars($student['gender'] ?? '-'); ?></span></div>
<div class="info-row"><span class="info-label">Department</span><span class="info-value"><?php echo htmlspecialchars($student['department_name'] ?? '-'); ?></span></div>
<div class="info-row"><span class="info-label">Level</span><span class="info-value"><?php echo $student['level']; ?> Level</span></div>
<div class="info-row"><span class="info-label">Academic Session</span><span class="info-value"><?php echo htmlspecialchars($student['session_name'] ?? '-'); ?></span></div>
<div class="info-row"><span class="info-label">Fingerprint ID</span><span class="info-value"><?php echo $student['fingerprint_id'] ? '<span class="badge badge-success">ID: ' . $student['fingerprint_id'] . '</span>' : '<span class="badge badge-danger">Not Set</span>'; ?></span></div>
<div class="info-row"><span class="info-label">RFID UID</span><span class="info-value"><?php echo ($student['rfid_uid'] ?? '') ? '<span class="badge badge-success">' . $student['rfid_uid'] . '</span>' : '<span class="badge badge-danger">Not Set</span>'; ?></span></div>
<div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="badge <?php echo $student['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $student['status']; ?></span></span></div>
</div>

<div style="text-align:center;flex-shrink:0;">
<form method="POST" enctype="multipart/form-data" style="margin-top:10px;">
<label style="display:inline-block;cursor:pointer;padding:10px 18px;border:2px dashed #e2e8f0;border-radius:10px;transition:.2s;">
<input type="file" name="passport" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()" style="display:none;">
<i class="fas fa-camera" style="font-size:20px;color:#94a3b8;display:block;margin-bottom:4px;"></i>
<span style="font-size:12px;color:#64748b;font-weight:500;">Change Photo</span>
</label>
</form>
<div style="margin-top:16px;">
<a href="change_password.php" class="btn btn-primary btn-sm"><i class="fas fa-key"></i> Change Password</a>
</div>
</div>
</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
