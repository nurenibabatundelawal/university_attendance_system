<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$tab = $_GET['tab'] ?? 'attendance';
$courseId = (int)($_GET['course_id'] ?? 0);
$sessionId = (int)($_GET['academic_session_id'] ?? 0);

$courses = mysqli_query($conn, "SELECT * FROM courses WHERE department_id=$deptId ORDER BY course_code ASC");
$sessions = mysqli_query($conn, "SELECT * FROM academic_sessions ORDER BY session_name DESC");
?>
<div class="main-content">
<div class="page-title"><h1>Department Reports</h1><p><?php echo htmlspecialchars($deptAdmin['department_name']); ?></p></div>

<div class="tab-bar">
<a href="?tab=attendance<?php echo $courseId ? '&course_id='.$courseId : ''; ?>" class="<?php echo $tab=='attendance'?'active':''; ?>"><i class="fas fa-clipboard-check"></i> Attendance</a>
<a href="?tab=students<?php echo $courseId ? '&course_id='.$courseId : ''; ?>" class="<?php echo $tab=='students'?'active':''; ?>"><i class="fas fa-user-graduate"></i> Students</a>
<a href="?tab=lecturers<?php echo $courseId ? '&course_id='.$courseId : ''; ?>" class="<?php echo $tab=='lecturers'?'active':''; ?>"><i class="fas fa-chalkboard-teacher"></i> Lecturers</a>
<a href="?tab=low_attendance<?php echo $courseId ? '&course_id='.$courseId : ''; ?>" class="<?php echo $tab=='low_attendance'?'active':''; ?>"><i class="fas fa-exclamation-triangle"></i> Low Attendance</a>
</div>

<?php if ($tab == 'attendance') { ?>
<div class="form-box">
<h3><i class="fas fa-filter"></i> Filter</h3>
<form method="GET" style="display:flex;gap:15px;align-items:end;flex-wrap:wrap;">
<input type="hidden" name="tab" value="attendance">
<div>
<label>Course</label>
<select name="course_id">
<option value="0">All Courses</option>
<?php while ($c = mysqli_fetch_assoc($courses)) { ?>
<option value="<?php echo $c['id']; ?>" <?php echo $courseId==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['course_code']); ?></option>
<?php } mysqli_data_seek($courses, 0); ?>
</select>
</div>
<div>
<label>Session</label>
<select name="academic_session_id">
<option value="0">All Sessions</option>
<?php while ($s = mysqli_fetch_assoc($sessions)) { ?>
<option value="<?php echo $s['id']; ?>" <?php echo $sessionId==$s['id']?'selected':''; ?>><?php echo $s['session_name']; ?></option>
<?php } ?>
</select>
</div>
<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
</form>
</div>

<div class="form-box">
<h3><i class="fas fa-calendar-check"></i> Attendance Records</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Date</th><th>Student</th><th>Course</th><th>Method</th><th>Status</th><th>Time</th></tr></thead>
<tbody>
<?php
$where = "c.department_id=$deptId";
if ($courseId > 0) $where .= " AND ar.course_id=$courseId";
$attQuery = mysqli_query($conn, "SELECT ar.*, s.fullname, s.matric_no, c.course_code, c.course_title FROM attendance_records ar JOIN students s ON ar.student_id=s.id JOIN courses c ON ar.course_id=c.id WHERE $where ORDER BY ar.marked_at DESC LIMIT 200");
if ($attQuery && mysqli_num_rows($attQuery) > 0) {
    while ($a = mysqli_fetch_assoc($attQuery)) { ?>
<tr>
<td><?php echo date('d M Y', strtotime($a['marked_at'])); ?></td>
<td><strong><?php echo htmlspecialchars($a['fullname']); ?></strong><br><small><?php echo htmlspecialchars($a['matric_no']); ?></small></td>
<td><?php echo htmlspecialchars($a['course_code']); ?></td>
<td><span class="badge badge-dark"><?php echo $a['verification_method']; ?></span></td>
<td><span class="badge <?php echo $a['attendance_status']=='Present'?'badge-success':'badge-danger'; ?>"><?php echo $a['attendance_status']; ?></span></td>
<td><?php echo date('h:i A', strtotime($a['marked_at'])); ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No records found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } elseif ($tab == 'students') { ?>
<div class="form-box">
<h3><i class="fas fa-user-graduate"></i> Students in Department</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Matric No</th><th>Name</th><th>Level</th><th>Fingerprint</th><th>RFID</th><th>Status</th></tr></thead>
<tbody>
<?php
$stQuery = mysqli_query($conn, "SELECT * FROM students WHERE department_id=$deptId ORDER BY matric_no ASC");
if ($stQuery && mysqli_num_rows($stQuery) > 0) {
    while ($s = mysqli_fetch_assoc($stQuery)) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($s['matric_no']); ?></span></td>
<td><strong><?php echo htmlspecialchars($s['fullname']); ?></strong></td>
<td><?php echo $s['level']; ?>L</td>
<td><?php echo $s['fingerprint_id'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'; ?></td>
<td><?php echo ($s['rfid_uid'] && $s['rfid_uid'] != '') ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'; ?></td>
<td><span class="badge <?php echo $s['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $s['status']; ?></span></td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No students</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } elseif ($tab == 'lecturers') { ?>
<div class="form-box">
<h3><i class="fas fa-chalkboard-teacher"></i> Lecturers in Department</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Staff ID</th><th>Name</th><th>Email</th><th>Qualification</th><th>Status</th><th>Approval</th></tr></thead>
<tbody>
<?php
$lecQuery = mysqli_query($conn, "SELECT * FROM lecturers WHERE department_id=$deptId ORDER BY fullname ASC");
if ($lecQuery && mysqli_num_rows($lecQuery) > 0) {
    while ($l = mysqli_fetch_assoc($lecQuery)) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($l['staff_id']); ?></span></td>
<td><strong><?php echo htmlspecialchars($l['fullname']); ?></strong></td>
<td><?php echo htmlspecialchars($l['email']); ?></td>
<td><?php echo htmlspecialchars($l['qualification'] ?? '-'); ?></td>
<td><span class="badge <?php echo $l['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $l['status']; ?></span></td>
<td>
<?php if ($l['approval_status'] == 'Approved') { ?><span class="badge badge-success">Approved</span>
<?php } elseif ($l['approval_status'] == 'Pending') { ?><span class="badge badge-warning">Pending</span>
<?php } else { ?><span class="badge badge-danger">Rejected</span><?php } ?>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;color:#999;padding:30px;">No lecturers</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } elseif ($tab == 'low_attendance') { ?>
<div class="form-box">
<h3><i class="fas fa-exclamation-triangle" style="color:#e53935;"></i> Students with Low Attendance</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Student</th><th>Matric No</th><th>Total Sessions</th><th>Present</th><th>Percentage</th></tr></thead>
<tbody>
<?php
$lowQuery = mysqli_query($conn, "SELECT s.id, s.fullname, s.matric_no,
    (SELECT COUNT(*) FROM attendance_records ar JOIN courses c ON ar.course_id=c.id WHERE ar.student_id=s.id AND c.department_id=$deptId) as total,
    (SELECT COUNT(*) FROM attendance_records ar2 JOIN courses c2 ON ar2.course_id=c2.id WHERE ar2.student_id=s.id AND c2.department_id=$deptId AND ar2.attendance_status='Present') as present
    FROM students s WHERE s.department_id=$deptId HAVING total > 0 ORDER BY (present/total) ASC LIMIT 50");
if ($lowQuery && mysqli_num_rows($lowQuery) > 0) {
    while ($r = mysqli_fetch_assoc($lowQuery)) {
        $pct = round(($r['present'] / $r['total']) * 100);
        $barColor = $pct >= 75 ? 'bar-green' : ($pct >= 50 ? 'bar-orange' : 'bar-red');
?>
<tr>
<td><strong><?php echo htmlspecialchars($r['fullname']); ?></strong></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($r['matric_no']); ?></span></td>
<td><?php echo $r['total']; ?></td>
<td><?php echo $r['present']; ?></td>
<td>
<div style="display:flex;align-items:center;gap:8px;">
<span style="font-weight:600;min-width:40px;"><?php echo $pct; ?>%</span>
<div class="progress-bar-wrap" style="flex:1;"><div class="progress-bar-fill <?php echo $barColor; ?>" style="width:<?php echo $pct; ?>%;"></div></div>
</div>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:30px;">No attendance data yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } ?>
</div>

<?php include "includes/footer.php"; ?>
