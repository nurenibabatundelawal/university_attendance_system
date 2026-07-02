<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_enrollment'])) {
    require_once __DIR__ . '/../api/config.php';
    $student_id = (int)$_POST['student_id'];
    $fingerprint_id = (int)$_POST['fingerprint_id'];

    mysqli_query($conn, "UPDATE enrollment_requests SET status='Cancelled' WHERE status='Pending'");

    $stmt = mysqli_prepare($conn, "INSERT INTO enrollment_requests (student_id, enrollment_type, fingerprint_id, status) VALUES (?, 'fingerprint', ?, 'Pending')");
    mysqli_stmt_bind_param($stmt, "ii", $student_id, $fingerprint_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Enrollment request sent to ESP32. Use the device now."; $message_class = "success";
        log_activity($conn, $deptAdmin['id'], 'department_admin', 'Fingerprint Enrollment', "FP ID $fingerprint_id for student ID $student_id");
    } else {
        $message = "Error: " . mysqli_error($conn); $message_class = "error";
    }
}

$students = mysqli_query($conn, "SELECT id, matric_no, fullname, fingerprint_id, rfid_uid FROM students WHERE department_id=$deptId AND status='Active' ORDER BY fullname ASC");
$requests = mysqli_query($conn, "SELECT er.*, s.fullname, s.matric_no FROM enrollment_requests er JOIN students s ON er.student_id=s.id WHERE s.department_id=$deptId ORDER BY er.id DESC LIMIT 10");
?>
<div class="main-content">
<div class="page-title"><h1>Fingerprint Enrollment</h1><p>Send fingerprint enrollment requests to ESP32</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-fingerprint"></i> New Enrollment Request</h3>
<label>Student</label>
<select name="student_id" required>
<option value="">Select Student</option>
<?php while ($s = mysqli_fetch_assoc($students)) { ?>
<option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['matric_no'] . ' - ' . $s['fullname']); ?></option>
<?php } ?>
</select>
<label>Fingerprint ID (1-127)</label>
<input type="number" name="fingerprint_id" min="1" max="127" placeholder="e.g. 10" required>
<br>
<button type="submit" name="send_enrollment" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send to Device</button>
</form>

<div>
<h3><i class="fas fa-history"></i> Recent Requests</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Student</th><th>Finger ID</th><th>Status</th><th>Created</th></tr></thead>
<tbody>
<?php if ($requests && mysqli_num_rows($requests) > 0) { while ($r = mysqli_fetch_assoc($requests)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($r['fullname']); ?></strong><br><small><?php echo htmlspecialchars($r['matric_no']); ?></small></td>
<td><?php echo $r['fingerprint_id'] ?? '-'; ?></td>
<td><span class="badge <?php echo $r['status']=='Completed'?'badge-success':($r['status']=='Cancelled'?'badge-danger':'badge-warning'); ?>"><?php echo $r['status']; ?></span></td>
<td><?php echo date('d M Y h:i A', strtotime($r['created_at'])); ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No requests yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
