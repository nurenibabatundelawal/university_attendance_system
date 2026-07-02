<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'] ?? $_SESSION['fullname'] ?? 'Student';
$matricNo = $_SESSION['matric_no'] ?? '';

$stmt = $conn->prepare("SELECT COUNT(*) AS total_courses FROM course_registrations WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total_courses = $stmt->get_result()->fetch_assoc()['total_courses'];

$stmt = $conn->prepare("SELECT COUNT(*) AS attended FROM attendance_records WHERE student_id = ? AND attendance_status IN ('Present','Late')");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total_attended = $stmt->get_result()->fetch_assoc()['attended'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_classes FROM attendance_records WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total_classes = $stmt->get_result()->fetch_assoc()['total_classes'];

$overall_percentage = 0;
if ($total_classes > 0) $overall_percentage = round(($total_attended / $total_classes) * 100);

$stmt = $conn->prepare("SELECT c.id, c.course_code, c.course_title, COUNT(ar.id) AS total_classes, SUM(CASE WHEN ar.attendance_status IN ('Present','Late') THEN 1 ELSE 0 END) AS attended FROM course_registrations cr JOIN courses c ON cr.course_id = c.id LEFT JOIN attendance_records ar ON ar.course_id = c.id AND ar.student_id = cr.student_id WHERE cr.student_id = ? GROUP BY c.id");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$courseStats = $stmt->get_result();

$below_75 = 0;
$courseLabels = []; $coursePercentages = []; $courseColors = [];
while ($row = $courseStats->fetch_assoc()) {
    $total = $row['total_classes'];
    $attended = $row['attended'];
    $percentage = $total > 0 ? round(($attended / $total) * 100) : 0;
    if ($percentage < 75) $below_75++;
    $courseLabels[] = $row['course_code'];
    $coursePercentages[] = $percentage;
    $courseColors[] = $percentage >= 75 ? '#4caf50' : ($percentage >= 50 ? '#fb8c00' : '#e53935');
}

$recent = $conn->prepare("SELECT ar.*, c.course_code, c.course_title FROM attendance_records ar JOIN courses c ON ar.course_id = c.id WHERE ar.student_id = ? ORDER BY ar.marked_at DESC LIMIT 5");
$recent->bind_param("i", $student_id);
$recent->execute();
$recentRecords = $recent->get_result();

$courses = $conn->prepare("SELECT c.course_code, c.course_title, s.semester_name FROM course_registrations cr JOIN courses c ON cr.course_id = c.id JOIN semesters s ON cr.semester_id = s.id WHERE cr.student_id = ? ORDER BY c.course_code");
$courses->bind_param("i", $student_id);
$courses->execute();
$coursesResult = $courses->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div>
<h1>Student Portal</h1>
<p style="margin-top:5px;"><strong><?php echo htmlspecialchars($studentName); ?></strong> &middot; <?php echo htmlspecialchars($matricNo); ?></p>
</div>
<div style="display:flex;gap:12px;align-items:center;">
<span style="background:#e8f0fe;color:var(--primary-dark);padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
</span>
<span style="background:#e8f5e9;color:#2e7d32;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-clock"></i> <span id="liveClock"><?php echo date('h:i A'); ?></span>
</span>
</div>
</div>

<?php if ($below_75 > 0) { ?>
<div class="msg warning" style="margin-bottom:20px;">
<i class="fas fa-exclamation-triangle"></i>
You have <strong><?php echo $below_75; ?> course(s)</strong> below 75% attendance. You may not be eligible to sit for the examination.
</div>
<?php } ?>

<div class="cards">
<div class="card" style="border-left:4px solid #2196f3;">
<div class="card-icon blue"><i class="fas fa-book"></i></div>
<div><h2><?php echo $total_courses; ?></h2><p>Registered Courses</p></div>
</div>
<div class="card" style="border-left:4px solid #4caf50;">
<div class="card-icon green"><i class="fas fa-check-circle"></i></div>
<div><h2><?php echo $total_attended; ?></h2><p>Classes Attended</p></div>
</div>
<div class="card" style="border-left:4px solid #fb8c00;">
<div class="card-icon orange"><i class="fas fa-chart-line"></i></div>
<div><h2><?php echo $overall_percentage; ?>%</h2><p>Attendance Rate</p></div>
</div>
<div class="card" style="border-left:4px solid <?php echo $below_75 > 0 ? '#e53935' : '#4caf50'; ?>;">
<div class="card-icon <?php echo $below_75 > 0 ? 'red' : 'green'; ?>"><i class="fas fa-exclamation-triangle"></i></div>
<div><h2><?php echo $below_75; ?></h2><p>Below 75%</p></div>
</div>
</div>

<div class="dashboard-row">
<div class="dashboard-box" style="text-align:center;padding:30px;">
<h3 style="justify-content:center;"><i class="fas fa-chart-pie" style="color:var(--primary);"></i> Overall Attendance</h3>
<div style="position:relative;width:160px;height:160px;margin:15px auto;">
<canvas id="attendanceGauge" width="160" height="160"></canvas>
<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
<h1 style="font-size:36px;color:var(--primary-dark);margin:0;line-height:1;"><?php echo $overall_percentage; ?>%</h1>
<p style="font-size:12px;color:#888;margin:0;">Attendance</p>
</div>
</div>
<p style="color:#666;"><?php echo $total_attended; ?> attended out of <?php echo $total_classes; ?> classes</p>
<div class="progress-bar-wrap" style="max-width:300px;margin:10px auto 0;"><div class="progress-bar-fill bar-green" style="width:<?php echo $overall_percentage; ?>%;"></div></div>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-bolt"></i> Quick Actions</h3>
<div style="display:grid;gap:12px;">
<a href="registered_courses.php" class="btn btn-primary" style="justify-content:center;"><i class="fas fa-book"></i> View My Courses</a>
<a href="attendance_history.php" class="btn btn-success" style="justify-content:center;"><i class="fas fa-clock"></i> Attendance History</a>
<a href="attendance_percentage.php" class="btn btn-warning" style="justify-content:center;color:#fff;"><i class="fas fa-chart-pie"></i> Attendance Percentage</a>
<a href="profile.php" class="btn btn-outline" style="justify-content:center;"><i class="fas fa-user"></i> My Profile</a>
</div>
<br>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
<div style="background:#e8f5e9;padding:14px;border-radius:10px;text-align:center;">
<p style="font-size:22px;font-weight:700;color:#2e7d32;">Active</p>
<p style="font-size:12px;color:#666;">Account Status</p>
</div>
<div style="background:#fff3e0;padding:14px;border-radius:10px;text-align:center;">
<p style="font-size:22px;font-weight:700;color:#e65100;"><?php echo date('Y') . '/' . (date('Y')+1); ?></p>
<p style="font-size:12px;color:#666;">Academic Year</p>
</div>
</div>
</div>
</div>

<div class="dashboard-row">
<div class="dashboard-box">
<h3><i class="fas fa-chart-bar"></i> Per-Course Attendance</h3>
<canvas id="courseChart" style="max-height:250px;"></canvas>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-clock"></i> Recent Attendance</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Date</th><th>Course</th><th>Status</th><th>Method</th><th>Time</th></tr></thead>
<tbody>
<?php if ($recentRecords && $recentRecords->num_rows > 0) { while ($r = $recentRecords->fetch_assoc()) { ?>
<tr>
<td style="white-space:nowrap;"><?php echo date('d M Y', strtotime($r['marked_at'])); ?></td>
<td><strong><?php echo htmlspecialchars($r['course_code']); ?></strong><br><small><?php echo htmlspecialchars($r['course_title']); ?></small></td>
<td><span class="badge <?php echo $r['attendance_status']=='Present'?'badge-success':($r['attendance_status']=='Late'?'badge-warning':'badge-danger'); ?>"><?php echo $r['attendance_status']; ?></span></td>
<td><span class="badge badge-dark"><?php echo $r['verification_method']; ?></span></td>
<td style="white-space:nowrap;"><?php echo date('h:i A', strtotime($r['marked_at'])); ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:30px;">No attendance records yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="dashboard-box" style="margin-top:25px;">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
<h3 style="margin:0;"><i class="fas fa-book"></i> My Registered Courses</h3>
<a href="registered_courses.php" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View All</a>
</div>
<div class="table-wrap">
<table>
<thead><tr><th>Course Code</th><th>Course Title</th><th>Semester</th></tr></thead>
<tbody>
<?php if ($coursesResult->num_rows > 0) { while ($c = $coursesResult->fetch_assoc()) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($c['course_code']); ?></span></td>
<td><strong><?php echo htmlspecialchars($c['course_title']); ?></strong></td>
<td><?php echo htmlspecialchars($c['semester_name']); ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="3" style="text-align:center;color:#999;padding:30px;">No courses registered</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});
}
setInterval(updateClock, 10000);

new Chart(document.getElementById('attendanceGauge'), {
  type: 'doughnut',
  data: {
    labels: ['Attended', 'Missed'],
    datasets: [{
      data: [<?php echo $overall_percentage; ?>, <?php echo 100 - $overall_percentage; ?>],
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

new Chart(document.getElementById('courseChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($courseLabels); ?>,
    datasets: [{
      label: 'Attendance %',
      data: <?php echo json_encode($coursePercentages); ?>,
      backgroundColor: <?php echo json_encode($courseColors); ?>,
      borderRadius: 6,
      borderSkipped: false,
      barPercentage: .6
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { callback: function(v) { return v + '%'; } } },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php include 'includes/footer.php'; ?>
