<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if ($session_id <= 0) { die("Invalid session."); }

$session = mysqli_query($conn, "SELECT * FROM attendance_sessions WHERE id = $session_id");
if (!$session || mysqli_num_rows($session) == 0) { die("Session not found."); }
$session = mysqli_fetch_assoc($session);
if ($session['status'] != 'Ended') { die("Session must be ended before editing."); }

$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attendance'])) {
    foreach ($_POST['attendance'] as $record_id => $att_status) {
        mysqli_query($conn, "UPDATE attendance_records SET attendance_status = '$att_status' WHERE id = $record_id");
    }
    $success = "Attendance updated successfully.";
}

$result = mysqli_query($conn, "SELECT ar.id, s.matric_no, s.fullname, ar.attendance_status, ar.verification_method FROM attendance_records ar JOIN students s ON ar.student_id = s.id WHERE ar.attendance_session_id = $session_id ORDER BY s.fullname");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Edit Attendance</h1><p>Session #<?php echo $session_id; ?> — Modify attendance records</p></div>
<a href="attendance_sessions.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<?php if ($success != "") { ?>
<div class="msg success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php } ?>

<?php if ($result && mysqli_num_rows($result) == 0) { ?>
<div class="msg info"><i class="fas fa-info-circle"></i> No attendance records for this session.</div>
<?php } else { ?>

<div class="form-box">
<form method="POST">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Matric No</th><th>Student</th><th>Verification</th><th>Status</th></tr></thead>
<tbody>
<?php $sn = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($row['matric_no']); ?></span></td>
<td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
<td><span class="badge badge-info"><?php echo htmlspecialchars($row['verification_method']); ?></span></td>
<td>
<select name="attendance[<?php echo $row['id']; ?>]" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;background:#fff;">
<option value="Present" <?php echo $row['attendance_status']=='Present'?'selected':''; ?>>Present</option>
<option value="Late" <?php echo $row['attendance_status']=='Late'?'selected':''; ?>>Late</option>
<option value="Absent" <?php echo $row['attendance_status']=='Absent'?'selected':''; ?>>Absent</option>
</select>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Attendance</button>
</form>
</div>
<?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
