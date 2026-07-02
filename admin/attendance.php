<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

$sessions = mysqli_query($conn, "
    SELECT s.*, c.course_code, c.course_title, l.fullname AS lecturer_name
    FROM attendance_sessions s
    JOIN courses c ON s.course_id = c.id
    JOIN lecturers l ON s.lecturer_id = l.id
    ORDER BY s.id DESC LIMIT 50
");

$session_info = null; $records = null;
if ($session_id > 0) {
    $q = mysqli_prepare($conn, "SELECT * FROM attendance_sessions WHERE id = ?");
    mysqli_stmt_bind_param($q, "i", $session_id);
    mysqli_stmt_execute($q);
    $session_info = mysqli_stmt_get_result($q);
    $session_info = mysqli_fetch_assoc($session_info);

    $records = mysqli_query($conn, "
        SELECT ar.*, s.fullname, s.matric_no, c.course_title AS course_name
        FROM attendance_records ar
        JOIN students s ON ar.student_id = s.id
        JOIN courses c ON ar.course_id = c.id
        WHERE ar.attendance_session_id = $session_id
        ORDER BY ar.marked_at DESC
    ");
}

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Attendance Monitoring</h1><p>View all attendance sessions and records</p></div>

<div class="form-box">
<h3><i class="fas fa-clock"></i> Attendance Sessions</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Course</th><th>Lecturer</th><th>Date</th><th>Start</th><th>Status</th><th style="width:100px;">Action</th></tr></thead>
<tbody>
<?php $i=1; while ($s = mysqli_fetch_assoc($sessions)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $s['course_code'] . " - " . $s['course_title']; ?></td>
<td><?php echo $s['lecturer_name']; ?></td>
<td><?php echo $s['session_date']; ?></td>
<td><?php echo $s['start_time']; ?></td>
<td><span class="badge <?php echo $s['status'] == 'Active' ? 'badge-success' : 'badge-dark'; ?>"><?php echo $s['status']; ?></span></td>
<td><a href="attendance.php?session_id=<?php echo $s['id']; ?>" class="btn btn-primary btn-sm" style="white-space:nowrap;"><i class="fas fa-eye"></i> Records</a></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<?php if ($session_info) { ?>
<div class="form-box" style="margin-top:25px;">
<h3><i class="fas fa-list"></i> Session Records #<?php echo $session_info['id']; ?></h3>
<?php if ($records && mysqli_num_rows($records) > 0) { ?>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Student</th><th>Matric No</th><th>Course</th><th>Method</th><th>Status</th><th>Time</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($records)) { ?>
<tr>
<td><?php echo $r['fullname']; ?></td>
<td><?php echo $r['matric_no']; ?></td>
<td><?php echo $r['course_name']; ?></td>
<td><span class="badge badge-dark"><?php echo $r['verification_method']; ?></span></td>
<td><span class="badge <?php echo $r['attendance_status'] == 'Present' ? 'badge-success' : 'badge-danger'; ?>"><?php echo $r['attendance_status']; ?></span></td>
<td><?php echo $r['marked_at']; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php } else { ?>
<p style="text-align:center;padding:30px;color:#999;">No attendance records for this session.</p>
<?php } ?>
</div>
<?php } ?>
</div>

<?php include "includes/footer.php"; ?>
