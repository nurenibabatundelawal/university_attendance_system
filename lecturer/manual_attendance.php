<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if ($session_id <= 0) { header("Location: attendance_sessions.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = ?");
$stmt->bind_param("i", $session_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
if (!$session) { die("Session not found."); }

$course_id = $session['course_id'];
$status = $session['status'];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $status == 'Active') {
    foreach ($_POST['attendance'] as $student_id => $attendance_status) {
        $check = $conn->prepare("SELECT id FROM attendance_records WHERE attendance_session_id = ? AND student_id = ?");
        $check->bind_param("ii", $session_id, $student_id);
        $check->execute();
        if ($check->get_result()->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO attendance_records (attendance_session_id, student_id, course_id, verification_method, attendance_status, marked_by) VALUES (?, ?, ?, 'Manual', ?, 'lecturer')");
            $insert->bind_param("iiis", $session_id, $student_id, $course_id, $attendance_status);
            $insert->execute();
        }
    }
    $success = "Attendance saved successfully.";
}

$students = $conn->prepare("SELECT s.id, s.matric_no, s.fullname FROM course_registrations cr JOIN students s ON cr.student_id = s.id WHERE cr.course_id = ? ORDER BY s.fullname");
$students->bind_param("i", $course_id);
$students->execute();
$students = $students->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Manual Attendance</h1><p>Session #<?php echo $session_id; ?> — Mark attendance manually</p></div>
<div>
<span class="badge <?php echo $status=='Active'?'badge-success':'badge-dark'; ?>" style="font-size:14px;padding:6px 16px;"><?php echo $status; ?></span>
</div>
</div>

<?php if ($success != "") { ?>
<div class="msg success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php } ?>

<?php if ($status != 'Active') { ?>
<div class="msg warning"><i class="fas fa-info-circle"></i> This session has ended. Use the <a href="edit_attendance.php?session_id=<?php echo $session_id; ?>">Edit Attendance</a> page to make changes.</div>
<?php } ?>

<div class="form-box">
<form method="POST">
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Matric No</th><th>Student Name</th><th>Attendance</th></tr></thead>
<tbody>
<?php if ($students->num_rows > 0) { $sn = 1; while ($s = $students->fetch_assoc()) { ?>
<tr>
<td><?php echo $sn++; ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($s['matric_no']); ?></span></td>
<td><strong><?php echo htmlspecialchars($s['fullname']); ?></strong></td>
<td>
<select name="attendance[<?php echo $s['id']; ?>]" class="form-control" style="padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
<option value="Present">Present</option>
<option value="Late">Late</option>
<option value="Absent">Absent</option>
</select>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:20px;">No students registered for this course.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
<br>
<?php if ($status == 'Active') { ?>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Attendance</button>
<?php } ?>
<a href="attendance_sessions.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</form>
</div>
</div>

<?php include 'includes/footer.php'; ?>
