<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];

function countDept($conn, $table, $deptId) {
    $r = mysqli_query($conn, "SELECT COUNT(*) AS t FROM `$table` WHERE department_id=$deptId");
    $d = mysqli_fetch_assoc($r);
    return $d['t'];
}

$totalStudents = countDept($conn, "students", $deptId);
$totalLecturers = countDept($conn, "lecturers", $deptId);
$totalCourses = countDept($conn, "courses", $deptId);
$pendingLecturers = 0;
$qr = mysqli_query($conn, "SELECT COUNT(*) AS t FROM lecturers WHERE department_id=$deptId AND approval_status='Pending'");
if ($qr) { $dr = mysqli_fetch_assoc($qr); $pendingLecturers = $dr['t']; }

$todayPresent = 0;
$qt = mysqli_query($conn, "SELECT COUNT(*) AS t FROM attendance_records ar JOIN courses c ON ar.course_id=c.id WHERE c.department_id=$deptId AND DATE(ar.marked_at)=CURDATE() AND ar.attendance_status='Present'");
if ($qt) { $dt = mysqli_fetch_assoc($qt); $todayPresent = $dt['t']; }

$studentWithFinger = 0;
$qf = mysqli_query($conn, "SELECT COUNT(*) AS t FROM students WHERE department_id=$deptId AND fingerprint_id IS NOT NULL");
if ($qf) { $df = mysqli_fetch_assoc($qf); $studentWithFinger = $df['t']; }

$studentWithRFID = 0;
$qr2 = mysqli_query($conn, "SELECT COUNT(*) AS t FROM students WHERE department_id=$deptId AND rfid_uid IS NOT NULL AND rfid_uid!=''");
if ($qr2) { $dr2 = mysqli_fetch_assoc($qr2); $studentWithRFID = $dr2['t']; }

$deviceCount = 0;
$qd = mysqli_query($conn, "SELECT COUNT(*) AS t FROM devices");
if ($qd) { $dd = mysqli_fetch_assoc($qd); $deviceCount = $dd['t']; }

$recentAttendance = mysqli_query($conn, "SELECT ar.*, s.fullname, s.matric_no, c.course_title FROM attendance_records ar JOIN students s ON ar.student_id=s.id JOIN courses c ON ar.course_id=c.id WHERE c.department_id=$deptId ORDER BY ar.marked_at DESC LIMIT 5");
$recentStudents = mysqli_query($conn, "SELECT * FROM students WHERE department_id=$deptId ORDER BY id DESC LIMIT 5");

log_activity($conn, $deptAdmin['id'], 'department_admin', 'Dashboard View', 'Viewed department dashboard');
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div>
<h1><?php echo htmlspecialchars($deptAdmin['department_name']); ?> Dashboard</h1>
<p>Welcome back, <?php echo htmlspecialchars($deptAdmin['fullname']); ?></p>
</div>
<div style="display:flex;gap:12px;align-items:center;">
<span style="background:#e8f0fe;color:var(--primary-dark);padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
</span>
</div>
</div>

<div class="cards">
<?php
$cards = [
    ["#2196f3","fa-user-graduate",$totalStudents,"Total Students"],
    ["#4caf50","fa-chalkboard-teacher",$totalLecturers,"Total Lecturers"],
    ["#fb8c00","fa-book",$totalCourses,"Total Courses"],
    ["#e53935","fa-clock",$pendingLecturers,"Pending Approvals"],
    ["#8e24aa","fa-calendar-check",$todayPresent,"Today's Attendance"],
    ["#37474f","fa-fingerprint",$studentWithFinger,"Fingerprints"],
    ["#1565c0","fa-id-card",$studentWithRFID,"RFID Cards"],
    ["#2e7d32","fa-microchip",$deviceCount,"Devices"]
];
foreach ($cards as $card): ?>
<div class="card" style="border-left:4px solid <?php echo $card[0]; ?>;">
<div class="card-icon" style="background:<?php echo $card[0]; ?>;"><i class="fas <?php echo $card[1]; ?>"></i></div>
<div><h2><?php echo $card[2]; ?></h2><p><?php echo $card[3]; ?></p></div>
</div>
<?php endforeach; ?>
</div>

<div class="dashboard-row">
<div class="dashboard-box">
<h3><i class="fas fa-clock"></i> Recent Attendance</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Student</th><th>Course</th><th>Status</th><th>Time</th></tr></thead>
<tbody>
<?php if ($recentAttendance && mysqli_num_rows($recentAttendance) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($recentAttendance)): ?>
<tr>
<td><strong><?php echo htmlspecialchars($row['fullname'] ?? ''); ?></strong><br><small><?php echo htmlspecialchars($row['matric_no'] ?? ''); ?></small></td>
<td><?php echo htmlspecialchars($row['course_title'] ?? ''); ?></td>
<td><span class="badge <?php echo $row['attendance_status']=='Present'?'badge-success':'badge-danger'; ?>"><?php echo $row['attendance_status']; ?></span></td>
<td><?php echo date('h:i A', strtotime($row['marked_at'])); ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No attendance records</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-user-plus"></i> Recent Students</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Name</th><th>Matric No</th><th>Finger</th><th>RFID</th></tr></thead>
<tbody>
<?php if ($recentStudents && mysqli_num_rows($recentStudents) > 0): ?>
<?php while ($s = mysqli_fetch_assoc($recentStudents)): ?>
<tr>
<td><strong><?php echo htmlspecialchars($s['fullname']); ?></strong></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($s['matric_no']); ?></span></td>
<td><?php echo $s['fingerprint_id'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'; ?></td>
<td><?php echo ($s['rfid_uid'] && $s['rfid_uid'] != '') ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'; ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No students yet</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
