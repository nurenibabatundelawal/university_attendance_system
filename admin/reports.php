<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$report_type = isset($_GET['type']) ? $_GET['type'] : 'overview';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

$students = mysqli_query($conn, "SELECT id, matric_no, fullname FROM students ORDER BY fullname ASC");
$courses_list = mysqli_query($conn, "SELECT id, course_code, course_title FROM courses ORDER BY course_code ASC");
$departments = mysqli_query($conn, "SELECT id, department_name FROM departments ORDER BY department_name ASC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Attendance Reports</h1><p>Analyse attendance data</p></div>

<div class="form-box">
<div class="tab-bar">
<a href="reports.php?type=overview" class="<?php echo $report_type=='overview'?'active':''; ?>"><i class="fas fa-chart-pie"></i> Overview</a>
<a href="reports.php?type=by_student" class="<?php echo $report_type=='by_student'?'active':''; ?>"><i class="fas fa-user-graduate"></i> By Student</a>
<a href="reports.php?type=by_course" class="<?php echo $report_type=='by_course'?'active':''; ?>"><i class="fas fa-book"></i> By Course</a>
<a href="reports.php?type=by_department" class="<?php echo $report_type=='by_department'?'active':''; ?>"><i class="fas fa-building"></i> By Department</a>
<a href="reports.php?type=low_attendance" class="<?php echo $report_type=='low_attendance'?'active':''; ?>"><i class="fas fa-exclamation-triangle"></i> Low Attendance</a>
</div>

<form method="GET" class="filter-form">
<input type="hidden" name="type" value="<?php echo $report_type; ?>">
<div class="field"><label>From</label><input type="date" name="date_from" value="<?php echo $date_from; ?>"></div>
<div class="field"><label>To</label><input type="date" name="date_to" value="<?php echo $date_to; ?>"></div>
<?php if ($report_type == 'by_student') { ?>
<div class="field"><label>Student</label><select name="student_id">
<option value="">All</option>
<?php mysqli_data_seek($students,0); while($s=mysqli_fetch_assoc($students)){ ?>
<option value="<?php echo $s['id'];?>" <?php echo $student_id==$s['id']?'selected':'';?>><?php echo $s['matric_no']." - ".$s['fullname'];?></option>
<?php } ?></select></div>
<?php } ?>
<?php if ($report_type == 'by_course') { ?>
<div class="field"><label>Course</label><select name="course_id">
<option value="">All</option>
<?php mysqli_data_seek($courses_list,0); while($c=mysqli_fetch_assoc($courses_list)){ ?>
<option value="<?php echo $c['id'];?>" <?php echo $course_id==$c['id']?'selected':'';?>><?php echo $c['course_code']." - ".$c['course_title'];?></option>
<?php } ?></select></div>
<?php } ?>
<?php if ($report_type == 'by_department') { ?>
<div class="field"><label>Department</label><select name="department_id">
<option value="">All</option>
<?php mysqli_data_seek($departments,0); while($d=mysqli_fetch_assoc($departments)){ ?>
<option value="<?php echo $d['id'];?>" <?php echo $department_id==$d['id']?'selected':'';?>><?php echo $d['department_name'];?></option>
<?php } ?></select></div>
<?php } ?>
<div class="field"><label>&nbsp;</label><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button></div>
</form>

<hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

<?php if ($report_type == 'overview') {
    $where = "DATE(marked_at) BETWEEN '$date_from' AND '$date_to'";
    $q = mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(CASE WHEN attendance_status='Present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN attendance_status='Absent' THEN 1 ELSE 0 END) AS absent FROM attendance_records WHERE $where");
    $summary = mysqli_fetch_assoc($q);
    $percent = $summary['total'] > 0 ? round(($summary['present']/$summary['total'])*100) : 0;
?>
<h3>Overall Attendance Summary</h3>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin:15px 0;">
<div style="background:#f8faff;padding:18px;border-radius:12px;text-align:center;"><h4 style="font-size:26px;color:var(--primary);"><?php echo $summary['total']; ?></h4><p style="color:#666;">Total Records</p></div>
<div style="background:#e8f5e9;padding:18px;border-radius:12px;text-align:center;"><h4 style="font-size:26px;color:#2e7d32;"><?php echo $summary['present']; ?></h4><p style="color:#666;">Present</p></div>
<div style="background:#ffebee;padding:18px;border-radius:12px;text-align:center;"><h4 style="font-size:26px;color:#c62828;"><?php echo $summary['absent']; ?></h4><p style="color:#666;">Absent</p></div>
</div>
<p>Attendance Rate: <strong><?php echo $percent; ?>%</strong></p>
<div class="progress-bar-wrap"><div class="progress-bar-fill bar-green" style="width:<?php echo $percent; ?>%;"></div></div>

<?php } elseif ($report_type == 'by_student') {
    $where = "DATE(ar.marked_at) BETWEEN '$date_from' AND '$date_to'";
    if ($student_id > 0) $where .= " AND ar.student_id = $student_id";
    $result = mysqli_query($conn, "SELECT ar.student_id, s.matric_no, s.fullname, d.department_name, COUNT(*) AS total, SUM(CASE WHEN ar.attendance_status='Present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.attendance_status='Absent' THEN 1 ELSE 0 END) AS absent FROM attendance_records ar JOIN students s ON ar.student_id = s.id LEFT JOIN departments d ON s.department_id = d.id WHERE $where GROUP BY ar.student_id ORDER BY total DESC");
?>
<h3>Attendance by Student</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Matric No</th><th>Name</th><th>Department</th><th>Total</th><th>Present</th><th>Absent</th><th>%</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($result)) { $pct = $r['total'] > 0 ? round(($r['present']/$r['total'])*100) : 0; ?>
<tr>
<td><span class="badge badge-dark"><?php echo $r['matric_no']; ?></span></td>
<td><?php echo $r['fullname']; ?></td>
<td><?php echo $r['department_name']; ?></td>
<td><?php echo $r['total']; ?></td>
<td><?php echo $r['present']; ?></td>
<td><?php echo $r['absent']; ?></td>
<td><span class="badge <?php echo $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger'); ?>"><?php echo $pct; ?>%</span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php } elseif ($report_type == 'by_course') {
    $where = "DATE(ar.marked_at) BETWEEN '$date_from' AND '$date_to'";
    if ($course_id > 0) $where .= " AND ar.course_id = $course_id";
    $result = mysqli_query($conn, "SELECT ar.course_id, c.course_code, c.course_title, COUNT(*) AS total, SUM(CASE WHEN ar.attendance_status='Present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.attendance_status='Absent' THEN 1 ELSE 0 END) AS absent FROM attendance_records ar JOIN courses c ON ar.course_id = c.id WHERE $where GROUP BY ar.course_id ORDER BY total DESC");
?>
<h3>Attendance by Course</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Code</th><th>Course Title</th><th>Total</th><th>Present</th><th>Absent</th><th>%</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($result)) { $pct = $r['total'] > 0 ? round(($r['present']/$r['total'])*100) : 0; ?>
<tr>
<td><span class="badge badge-dark"><?php echo $r['course_code']; ?></span></td>
<td><?php echo $r['course_title']; ?></td>
<td><?php echo $r['total']; ?></td>
<td><?php echo $r['present']; ?></td>
<td><?php echo $r['absent']; ?></td>
<td><span class="badge <?php echo $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger'); ?>"><?php echo $pct; ?>%</span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php } elseif ($report_type == 'by_department') {
    $where = "DATE(ar.marked_at) BETWEEN '$date_from' AND '$date_to'";
    if ($department_id > 0) $where .= " AND s.department_id = $department_id";
    $result = mysqli_query($conn, "SELECT s.department_id, d.department_name, COUNT(*) AS total, SUM(CASE WHEN ar.attendance_status='Present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.attendance_status='Absent' THEN 1 ELSE 0 END) AS absent FROM attendance_records ar JOIN students s ON ar.student_id = s.id LEFT JOIN departments d ON s.department_id = d.id WHERE $where GROUP BY s.department_id ORDER BY total DESC");
?>
<h3>Attendance by Department</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Department</th><th>Total</th><th>Present</th><th>Absent</th><th>%</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($result)) { $pct = $r['total'] > 0 ? round(($r['present']/$r['total'])*100) : 0; ?>
<tr>
<td><?php echo $r['department_name']; ?></td>
<td><?php echo $r['total']; ?></td>
<td><?php echo $r['present']; ?></td>
<td><?php echo $r['absent']; ?></td>
<td><span class="badge <?php echo $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger'); ?>"><?php echo $pct; ?>%</span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<?php } elseif ($report_type == 'low_attendance') {
    $where = "DATE(ar.marked_at) BETWEEN '$date_from' AND '$date_to'";
    $result = mysqli_query($conn, "SELECT s.id, s.matric_no, s.fullname, d.department_name, COUNT(*) AS total, SUM(CASE WHEN ar.attendance_status='Present' THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN ar.attendance_status='Absent' THEN 1 ELSE 0 END) AS absent FROM attendance_records ar JOIN students s ON ar.student_id = s.id LEFT JOIN departments d ON s.department_id = d.id WHERE $where GROUP BY ar.student_id HAVING (present/total) < 0.75 ORDER BY present ASC");
?>
<h3>Students with Low Attendance (&lt; 75%)</h3>
<?php if ($result && mysqli_num_rows($result) > 0) { ?>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Matric No</th><th>Name</th><th>Department</th><th>Total</th><th>Present</th><th>Absent</th><th>%</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($result)) { $pct = round(($r['present']/$r['total'])*100); ?>
<tr>
<td><span class="badge badge-dark"><?php echo $r['matric_no']; ?></span></td>
<td><?php echo $r['fullname']; ?></td>
<td><?php echo $r['department_name']; ?></td>
<td><?php echo $r['total']; ?></td>
<td><?php echo $r['present']; ?></td>
<td><?php echo $r['absent']; ?></td>
<td><?php echo $pct; ?>% <div class="progress-bar-wrap"><div class="progress-bar-fill <?php echo $pct<50?'bar-red':'bar-orange';?>" style="width:<?php echo $pct; ?>%;"></div></div></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } else { ?>
<p style="text-align:center;padding:30px;color:#999;">No students with low attendance found for this period.</p>
<?php } ?>
<?php } ?>
</div>
</div>

<?php include "includes/footer.php"; ?>
