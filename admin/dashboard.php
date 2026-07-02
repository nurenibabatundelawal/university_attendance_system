<?php
require_once __DIR__ . '/../php/auth_check.php';
require_once __DIR__ . '/../php/db_connect.php';
check_login("admin");

function countTable($conn,$table) {
    $sql="SELECT COUNT(*) AS total FROM `$table`";
    $result=mysqli_query($conn,$sql);
    if(!$result) die(mysqli_error($conn));
    $row=mysqli_fetch_assoc($result);
    return $row['total'];
}

$totalStudents=countTable($conn,"students");
$totalLecturers=countTable($conn,"lecturers");
$totalCourses=countTable($conn,"courses");
$totalDepartments=countTable($conn,"departments");
$totalFaculties=countTable($conn,"faculties");
$totalRFID=countTable($conn,"rfid_cards");
$totalFingerprint=countTable($conn,"fingerprint_templates");

$todayAttendance=0;
$q=mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance_records WHERE DATE(marked_at)=CURDATE() AND attendance_status='Present'");
if($q){ $data=mysqli_fetch_assoc($q); $todayAttendance=$data['total']; }

$totalToday=0;
$qt=mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance_records WHERE DATE(marked_at)=CURDATE()");
if($qt){ $dt=mysqli_fetch_assoc($qt); $totalToday=$dt['total']; }

$attendancePercent=0;
if($totalStudents>0) $attendancePercent=round(($todayAttendance/$totalStudents)*100);

$days=["Monday","Tuesday","Wednesday","Thursday","Friday"];
$weeklyAttendance=[];
foreach($days as $day){
    $q=mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance_records WHERE DAYNAME(marked_at)='$day' AND attendance_status='Present'");
    if($q){ $row=mysqli_fetch_assoc($q); $weeklyAttendance[]=$row['total']; }
    else $weeklyAttendance[]=0;
}

$recentAttendance=mysqli_query($conn,"SELECT ar.*, s.fullname, s.matric_no, c.course_title AS course_name FROM attendance_records ar LEFT JOIN students s ON s.id = ar.student_id LEFT JOIN courses c ON c.id = ar.course_id ORDER BY ar.marked_at DESC LIMIT 5");
$deviceQuery=mysqli_query($conn,"SELECT * FROM devices LIMIT 5");
$recentStudents=mysqli_query($conn,"SELECT * FROM students ORDER BY id DESC LIMIT 5");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div>
<h1 style="font-size:26px;">Welcome Back, <?=htmlspecialchars($_SESSION['fullname'] ?? 'Administrator'); ?></h1>
<p style="margin-top:3px;">University Attendance Management System Dashboard</p>
</div>
<div style="display:flex;gap:12px;align-items:center;">
<span style="background:#e8f0fe;color:var(--primary-dark);padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-calendar-alt"></i> <?=date('l, F j, Y');?>
</span>
<span style="background:#e8f5e9;color:#2e7d32;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-clock"></i> <span id="liveClock"><?=date('h:i A');?></span>
</span>
</div>
</div>

<div class="cards">
<?php
$cards=[
    ["#2196f3","fa-user-graduate",$totalStudents,"Total Students"],
    ["#4caf50","fa-chalkboard-teacher",$totalLecturers,"Total Lecturers"],
    ["#fb8c00","fa-book",$totalCourses,"Total Courses"],
    ["#e53935","fa-building",$totalDepartments,"Departments"],
    ["#8e24aa","fa-university",$totalFaculties,"Faculties"],
    ["#37474f","fa-id-card",$totalRFID,"RFID Cards"],
    ["#37474f","fa-fingerprint",$totalFingerprint,"Fingerprints"],
    ["#1565c0","fa-calendar-check",$todayAttendance,"Attendance Today"]
];
foreach($cards as $card): ?>
<div class="card" style="border-left:4px solid <?=$card[0];?>;">
<div class="card-icon" style="background:<?=$card[0];?>;"><i class="fas <?=$card[1];?>"></i></div>
<div><h2><?=$card[2];?></h2><p><?=$card[3];?></p></div>
</div>
<?php endforeach; ?>
</div>

<div class="dashboard-row">
<div class="dashboard-box" style="text-align:center;padding:30px;">
<h3 style="justify-content:center;"><i class="fas fa-chart-pie" style="color:var(--primary);"></i> Today's Attendance</h3>
<div style="position:relative;width:160px;height:160px;margin:15px auto;">
<canvas id="attendanceGauge" width="160" height="160"></canvas>
<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
<h1 style="font-size:36px;color:var(--primary-dark);margin:0;line-height:1;"><?=$attendancePercent;?>%</h1>
<p style="font-size:12px;color:#888;margin:0;">Attendance</p>
</div>
</div>
<p style="color:#666;"><?=$todayAttendance;?> present out of <?=$totalStudents;?> students today</p>
<div class="progress-bar-wrap" style="max-width:300px;margin:10px auto 0;"><div class="progress-bar-fill bar-green" style="width:<?=$attendancePercent;?>%;"></div></div>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-microchip"></i> System Status</h3>
<div style="display:grid;gap:12px;">
<?php if($deviceQuery && mysqli_num_rows($deviceQuery)>0): ?>
<?php while($device=mysqli_fetch_assoc($deviceQuery)): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f8faff;border-radius:10px;">
<span style="font-weight:500;"><i class="fas fa-circle" style="font-size:8px;color:<?=$device['status']=='Active'?'#4caf50':'#e53935';?>;margin-right:8px;"></i><?=htmlspecialchars($device['device_name']);?></span>
<span class="badge <?=$device['status']=='Active'?'badge-success':'badge-danger';?>"><?=$device['status'];?></span>
</div>
<?php endwhile; ?>
<?php else: ?>
<div style="text-align:center;padding:30px;color:#999;">
<i class="fas fa-wifi" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
<p>No devices registered</p>
<a href="device_settings.php" class="btn btn-sm btn-primary" style="margin-top:12px;"><i class="fas fa-plus"></i> Add Device</a>
</div>
<?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:5px;">
<div style="background:#e8f5e9;padding:14px;border-radius:10px;text-align:center;">
<p style="font-size:22px;font-weight:700;color:#2e7d32;">Active</p>
<p style="font-size:12px;color:#666;">Current Session</p>
</div>
<div style="background:#fff3e0;padding:14px;border-radius:10px;text-align:center;">
<p style="font-size:22px;font-weight:700;color:#e65100;">2026/2027</p>
<p style="font-size:12px;color:#666;">Academic Year</p>
</div>
</div>
</div>
</div>
</div>

<div class="dashboard-row">
<div class="dashboard-box">
<h3><i class="fas fa-chart-line"></i> Weekly Attendance Trend</h3>
<canvas id="attendanceChart"></canvas>
</div>
<div class="dashboard-box">
<h3><i class="fas fa-clock"></i> Recent Check-ins</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Student</th><th>Course</th><th>Method</th><th>Status</th><th>Time</th></tr></thead>
<tbody>
<?php if($recentAttendance && mysqli_num_rows($recentAttendance)>0): ?>
<?php while($row=mysqli_fetch_assoc($recentAttendance)): ?>
<tr>
<td><strong><?=htmlspecialchars($row['fullname'] ?? 'Unknown');?></strong></td>
<td><?=htmlspecialchars($row['course_name'] ?? 'N/A');?></td>
<td><span class="badge badge-dark"><?=htmlspecialchars($row['verification_method']);?></span></td>
<td><span class="badge <?=$row['attendance_status']=='Present'?'badge-success':'badge-danger';?>"><?=htmlspecialchars($row['attendance_status']);?></span></td>
<td style="white-space:nowrap;"><?=date('h:i A',strtotime($row['marked_at']));?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:30px;">No recent attendance records</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="dashboard-box" style="margin-top:25px;">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
<h3 style="margin:0;"><i class="fas fa-user-plus"></i> Recent Registrations</h3>
<a href="add_student.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Student</a>
</div>
<div class="table-wrap">
<table>
<thead><tr><th>Name</th><th>Matric No</th><th>Email</th><th>Status</th></tr></thead>
<tbody>
<?php if($recentStudents && mysqli_num_rows($recentStudents)>0): ?>
<?php while($student=mysqli_fetch_assoc($recentStudents)): ?>
<tr>
<td><strong><?=htmlspecialchars($student['fullname']);?></strong></td>
<td><span class="badge badge-dark"><?=htmlspecialchars($student['matric_no']);?></span></td>
<td><?=htmlspecialchars($student['email']);?></td>
<td><span class="badge <?=$student['status']=='Active'?'badge-success':'badge-danger';?>"><?=htmlspecialchars($student['status']);?></span></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No students registered yet</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<script>
// Live Clock
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});
}
setInterval(updateClock, 10000);

// Attendance Gauge (doughnut)
new Chart(document.getElementById('attendanceGauge'), {
  type: 'doughnut',
  data: {
    labels: ['Present', 'Remaining'],
    datasets: [{
      data: [<?=$attendancePercent;?>, <?=100-$attendancePercent;?>],
      backgroundColor: ['#4caf50', '#e8f5e9'],
      borderWidth: 0,
      cutout: '80%'
    }]
  },
  options: {
    responsive: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } }
  }
});

// Weekly Line Chart
new Chart(document.getElementById('attendanceChart'), {
  type: 'line',
  data: {
    labels: <?=json_encode($days);?>,
    datasets: [{
      label: 'Attendance',
      data: <?=json_encode($weeklyAttendance);?>,
      borderColor: '#1565c0',
      backgroundColor: 'rgba(21,101,192,.08)',
      fill: true,
      tension: .4,
      pointBackgroundColor: '#1565c0',
      pointRadius: 4,
      pointHoverRadius: 7,
      borderWidth: 3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 1 } },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php include "includes/footer.php"; ?>
