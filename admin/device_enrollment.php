<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../api/config.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $enrollment_type = $_POST['enrollment_type'];
    $fingerprint_id = $_POST['fingerprint_id'] ?? '';
    $fingerprint_id = ($fingerprint_id === '' || $fingerprint_id === null) ? null : (int)$fingerprint_id;

    mysqli_query($conn, "UPDATE enrollment_requests SET status='Cancelled' WHERE status='Pending'");

    $query = "INSERT INTO enrollment_requests (student_id, enrollment_type, fingerprint_id, status) VALUES (?, ?, ?, 'Pending')";
    $stmt = mysqli_prepare($conn, $query);
    $nullFp = null;
    $fpBind = ($fingerprint_id === null) ? $nullFp : $fingerprint_id;
    mysqli_stmt_bind_param($stmt, "isi", $student_id, $enrollment_type, $fpBind);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Enrollment request sent to ESP32. Use the device now."; $message_class = "success";
    } else {
        $message = "Failed to create enrollment request."; $message_class = "error";
    }
}

$students = mysqli_query($conn, "SELECT id, matric_no, fullname, fingerprint_id, rfid_uid FROM students WHERE status = 'Active' ORDER BY fullname ASC");
$requests = mysqli_query($conn, "SELECT r.*, s.fullname, s.matric_no FROM enrollment_requests r JOIN students s ON r.student_id = s.id ORDER BY r.id DESC LIMIT 10");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Fingerprint Enrollment</h1><p>Send fingerprint/RFID enrollment requests to ESP32</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> New Enrollment Request</h3>
<label>Student</label>
<select name="student_id" required>
<option value="">Select Student</option>
<?php while ($s = mysqli_fetch_assoc($students)) { ?>
<option value="<?php echo $s['id']; ?>"><?php echo $s['matric_no'] . " - " . $s['fullname'] . " | Finger: " . ($s['fingerprint_id'] ?: 'None') . " | RFID: " . ($s['rfid_uid'] ?: 'None'); ?></option>
<?php } ?>
</select>
<br><br>
<div class="form-row">
<div><label>Type</label><select name="enrollment_type" required>
<option value="fingerprint">Fingerprint</option>
<option value="rfid">RFID Card</option>
</select></div>
<div><label>Fingerprint ID</label><input type="number" name="fingerprint_id" placeholder="e.g. 1, 2, 3"><small>Leave empty for RFID</small></div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Request</button>
</form>

<div>
<h3><i class="fas fa-history"></i> Recent Requests</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Student</th><th>Type</th><th>Finger ID</th><th>RFID</th><th>Status</th><th>Created</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($requests)) { ?>
<tr>
<td><?php echo $r['matric_no'] . " - " . $r['fullname']; ?></td>
<td><span class="badge badge-dark"><?php echo $r['enrollment_type']; ?></span></td>
<td><?php echo $r['fingerprint_id'] ?: '-'; ?></td>
<td><?php echo $r['rfid_uid'] ?: '-'; ?></td>
<td><span class="badge <?php echo $r['status']=='Completed'?'badge-success':($r['status']=='Cancelled'?'badge-danger':'badge-warning'); ?>"><?php echo $r['status']; ?></span></td>
<td><?php echo $r['created_at']; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
