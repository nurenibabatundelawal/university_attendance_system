<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$lecturer_id = $_SESSION['lecturer_id'];
$lecturerName = $_SESSION['lecturer_name'] ?? $_SESSION['fullname'] ?? 'Lecturer';

// Fetch lecturer's department
$deptQ = $conn->prepare("SELECT department_id FROM lecturers WHERE id = ? LIMIT 1");
$deptQ->bind_param("i", $lecturer_id);
$deptQ->execute();
$lecturer_dept_id = $deptQ->get_result()->fetch_assoc()['department_id'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM lecturer_courses WHERE lecturer_id = ?");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$total_courses = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM attendance_sessions WHERE lecturer_id = ? AND status = 'Active'");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$active_sessions = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(DISTINCT cr.student_id) AS total FROM course_registrations cr JOIN lecturer_courses lc ON cr.course_id = lc.course_id WHERE lc.lecturer_id = ?");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM attendance_sessions WHERE lecturer_id = ? AND status = 'Ended'");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$ended_sessions = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM attendance_records ar JOIN attendance_sessions ats ON ar.attendance_session_id = ats.id WHERE ats.lecturer_id = ? AND DATE(ar.marked_at) = CURDATE() AND ar.attendance_status = 'Present'");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$today_attendance = $stmt->get_result()->fetch_assoc()['total'];

$lowAll = $conn->prepare("SELECT s.id, c.id AS cid, COUNT(ar.id) AS attended, (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = c.id AND status = 'Ended') AS total FROM students s JOIN course_registrations cr ON s.id = cr.student_id JOIN courses c ON cr.course_id = c.id JOIN lecturer_courses lc ON c.id = lc.course_id LEFT JOIN attendance_records ar ON ar.student_id = s.id AND ar.course_id = c.id AND ar.attendance_status IN ('Present','Late') WHERE lc.lecturer_id = ? GROUP BY s.id, c.id");
$lowAll->bind_param("i", $lecturer_id);
$lowAll->execute();
$lowRows = $lowAll->get_result();
$lowCount = 0;
while ($r = $lowRows->fetch_assoc()) {
    if ($r['total'] > 0 && ($r['attended'] / $r['total']) * 100 < 75) $lowCount++;
}

$stmt = $conn->prepare("SELECT c.course_code, COUNT(DISTINCT ar.id) AS attended, (SELECT COUNT(*) FROM attendance_sessions WHERE course_id = c.id AND status = 'Ended') AS total FROM lecturer_courses lc JOIN courses c ON lc.course_id = c.id LEFT JOIN attendance_records ar ON ar.course_id = c.id AND ar.attendance_status IN ('Present','Late') WHERE lc.lecturer_id = ? GROUP BY c.id");
$stmt->bind_param("i", $lecturer_id);
$stmt->execute();
$courseStats = $stmt->get_result();
$courseLabels = []; $courseData = [];
while ($row = $courseStats->fetch_assoc()) {
    $courseLabels[] = $row['course_code'];
    $courseData[] = $row['total'] > 0 ? round(($row['attended'] / $row['total']) * 100) : 0;
}

$weeklyLabels = []; $weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $weeklyLabels[] = date('D', strtotime("-$i days"));
    $s = $conn->prepare("SELECT COUNT(*) AS total FROM attendance_records ar JOIN attendance_sessions ats ON ar.attendance_session_id = ats.id WHERE ats.lecturer_id = ? AND DATE(ar.marked_at) = ?");
    $s->bind_param("is", $lecturer_id, $d);
    $s->execute();
    $weeklyData[] = $s->get_result()->fetch_assoc()['total'];
}

$recent = $conn->prepare("SELECT ats.*, c.course_code FROM attendance_sessions ats JOIN courses c ON ats.course_id = c.id WHERE ats.lecturer_id = ? ORDER BY ats.id DESC LIMIT 5");
$recent->bind_param("i", $lecturer_id);
$recent->execute();
$recentSessions = $recent->get_result();

// Fetch devices — prefer department-specific, fall back to all
if ($lecturer_dept_id > 0) {
    $devices = $conn->prepare("SELECT * FROM devices WHERE department_id = ? OR department_id IS NULL ORDER BY device_name");
    $devices->bind_param("i", $lecturer_dept_id);
} else {
    $devices = $conn->prepare("SELECT * FROM devices ORDER BY device_name");
}
$devices->execute();
$deviceList = $devices->get_result();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
<div>
<h1>Good <?php echo date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening'); ?>, <?php echo htmlspecialchars(explode(' ', $lecturerName)[0]); ?></h1>
<p><?php echo date('l, F j, Y'); ?> &middot; Your teaching overview at a glance</p>
</div>
<span style="background:#e8f0fe;color:var(--primary-dark);padding:8px 16px;border-radius:10px;font-size:13px;font-weight:500;">
<i class="fas fa-clock"></i> <span id="liveClock"><?php echo date('h:i A'); ?></span>
</span>
</div>

<div class="cards">
<div class="card" style="border-left:4px solid #1565c0;">
<div class="card-icon" style="background:#1565c0;"><i class="fas fa-book-open"></i></div>
<div><h2><?php echo $total_courses; ?></h2><p>Active Courses</p></div>
</div>
<div class="card" style="border-left:4px solid #4caf50;">
<div class="card-icon" style="background:#4caf50;"><i class="fas fa-play-circle"></i></div>
<div><h2><?php echo $active_sessions; ?></h2><p>Live Sessions</p></div>
</div>
<div class="card" style="border-left:4px solid #2196f3;">
<div class="card-icon" style="background:#2196f3;"><i class="fas fa-user-graduate"></i></div>
<div><h2><?php echo $total_students; ?></h2><p>Enrolled Students</p></div>
</div>
<div class="card" style="border-left:4px solid #fb8c00;">
<div class="card-icon" style="background:#fb8c00;"><i class="fas fa-check-circle"></i></div>
<div><h2><?php echo $today_attendance; ?></h2><p>Marked Today</p></div>
</div>
<div class="card" style="border-left:4px solid #8e24aa;">
<div class="card-icon" style="background:#8e24aa;"><i class="fas fa-chart-line"></i></div>
<div><h2><?php echo $ended_sessions; ?></h2><p>Completed Sessions</p></div>
</div>
<div class="card" style="border-left:4px solid #e53935;">
<div class="card-icon" style="background:#e53935;"><i class="fas fa-exclamation-triangle"></i></div>
<div><h2><?php echo $lowCount; ?></h2><p>Below 75%</p></div>
</div>
</div>

<div class="dashboard-row">
<div class="dashboard-box">
<h3><i class="fas fa-chart-line" style="color:var(--primary);"></i> Weekly Trend</h3>
<canvas id="weeklyChart" style="max-height:220px;"></canvas>
</div>
<div class="dashboard-box">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
<h3 style="margin:0;"><i class="fas fa-clock" style="color:var(--primary);"></i> Recent Sessions</h3>
<a href="attendance_sessions.php" class="btn btn-sm btn-primary">View All</a>
</div>
<div class="table-wrap" style="margin-top:0;">
<table>
<thead><tr><th>Course</th><th>Date</th><th>Status</th><th>Method</th></tr></thead>
<tbody>
<?php if ($recentSessions && $recentSessions->num_rows > 0) { while ($s = $recentSessions->fetch_assoc()) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($s['course_code']); ?></strong></td>
<td style="white-space:nowrap;"><?php echo date('d M', strtotime($s['session_date'])); ?></td>
<td><span class="badge <?php echo $s['status']=='Active'?'badge-success':'badge-dark'; ?>"><?php echo $s['status']; ?></span></td>
<td><span class="badge badge-info"><?php echo $s['attendance_method'] ?? 'N/A'; ?></span></td>
</tr>
<?php } } else { ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No sessions yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<div class="dashboard-row">
<div class="dashboard-box">
<h3><i class="fas fa-chart-bar" style="color:var(--primary);"></i> Course Attendance %</h3>
<canvas id="courseChart" style="max-height:220px;"></canvas>
</div>
<div class="dashboard-box">
<h3><i class="fas fa-bolt" style="color:var(--primary);"></i> Quick Actions</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
<a href="start_session.php" class="btn btn-primary" style="justify-content:center;"><i class="fas fa-play"></i> Start Session</a>
<a href="manual_attendance.php" class="btn btn-success" style="justify-content:center;"><i class="fas fa-hand"></i> Manual Mark</a>
<a href="attendance_analytics.php" class="btn btn-warning" style="justify-content:center;"><i class="fas fa-chart-bar"></i> Analytics</a>
<a href="assigned_courses.php" class="btn btn-outline" style="justify-content:center;"><i class="fas fa-book-open"></i> Courses</a>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
<div style="background:#e8f5e9;padding:16px;border-radius:10px;text-align:center;">
<p style="font-size:28px;font-weight:700;color:#2e7d32;"><?php echo $active_sessions; ?></p>
<p style="font-size:13px;color:#666;">Active Now</p>
</div>
<div style="background:#fff3e0;padding:16px;border-radius:10px;text-align:center;">
<p style="font-size:28px;font-weight:700;color:#e65100;"><?php echo $total_courses; ?></p>
<p style="font-size:13px;color:#666;">Total Courses</p>
</div>
</div>
</div>
</div>

<?php if ($lowCount > 0) { ?>
<div class="dashboard-box" style="margin-top:25px;">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
<h3 style="margin:0;"><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Attendance Alerts</h3>
<a href="low_attendance.php" class="btn btn-sm btn-warning">View All</a>
</div>
<div style="display:flex;align-items:center;gap:14px;padding:16px 20px;background:var(--warning-bg);border-radius:10px;border:1px solid #ffe0b2;">
<i class="fas fa-exclamation-circle" style="color:var(--warning-text);font-size:22px;"></i>
<div>
<strong style="color:var(--warning-text);"><?php echo $lowCount; ?> student(s)</strong>
<span style="color:var(--warning-text);font-size:14px;"> are below the 75% attendance threshold across your courses. They may not be eligible for exams.</span>
</div>
</div>
</div>
<?php } ?>

<!-- Device Status Section -->
<div class="dashboard-box" style="margin-top:25px;">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:15px;">
<h3 style="margin:0;"><i class="fas fa-microchip" style="color:var(--primary);"></i> Device Status</h3>
<a href="devices.php" class="btn btn-sm btn-outline">Manage</a>
</div>
<?php if ($deviceList && $deviceList->num_rows > 0) { ?>
<div style="display:grid;gap:10px;">
<?php while ($d = $deviceList->fetch_assoc()) {
    $online = false;
    $statusClass = 'badge-danger';
    $statusText = 'Offline';
    $issue = '';
    $solution = '';
    if ($d['last_seen']) {
        $diff = time() - strtotime($d['last_seen']);
        if ($diff < 60) {
            $online = true;
            $statusClass = 'badge-success';
            $statusText = 'Online';
        } elseif ($diff < 300) {
            $statusClass = 'badge-warning';
            $statusText = 'Connecting... (' . floor($diff/60) . 'm ago)';
            $issue = 'Device was recently seen but hasn\'t checked in over a minute.';
            $solution = 'Ensure the device has power and WiFi connection. The device automatically pings every 30 seconds.';
        } else {
            $statusClass = 'badge-danger';
            $statusText = 'Offline (' . floor($diff/60) . 'm ago)';
            $issue = 'No communication from device for ' . floor($diff/60) . ' minutes.';
            $solution = '1. Check device power supply. 2. Verify WiFi credentials in Device Settings. 3. Ensure the device can reach ' . $_SERVER['HTTP_HOST'] . '. 4. Restart the device.';
        }
    } else {
        $statusText = 'Never connected';
        $issue = 'Device has never reported in.';
        $solution = '1. Configure WiFi SSID/password in Admin → Device Settings. 2. Ensure device code "' . htmlspecialchars($d['device_code']) . '" is correct. 3. Power cycle the device. 4. Check that the device firmware is up-to-date.';
    }
?>
<div class="device-row" id="device-<?php echo $d['id']; ?>">
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 18px;background:#f8faff;border-radius:10px;border:1px solid #e8eaf6;">
<div style="display:flex;align-items:center;gap:12px;">
<i class="fas fa-circle" style="font-size:10px;color:<?php echo $online ? '#4caf50' : '#e53935'; ?>;"></i>
<div>
<strong style="font-size:14px;"><?php echo htmlspecialchars($d['device_name']); ?></strong>
<?php if ($d['location']) { ?><br><small style="color:#888;"><?php echo htmlspecialchars($d['location']); ?></small><?php } ?>
</div>
</div>
<div style="display:flex;align-items:center;gap:8px;">
<span class="badge <?php echo $statusClass; ?>" style="font-size:12px;padding:4px 14px;"><?php echo $statusText; ?></span>
<button onclick="toggleDevice(<?php echo $d['id']; ?>, this)" class="btn btn-sm <?php echo $d['status']=='Active'?'btn-warning':'btn-success'; ?>">
<i class="fas fa-<?php echo $d['status']=='Active'?'pause':'play'; ?>"></i> <?php echo $d['status']=='Active'?'Deactivate':'Activate'; ?>
</button>
</div>
</div>
<?php if (!$online) { ?>
<div style="margin-top:4px;margin-left:28px;padding:10px 14px;background:#fff8e1;border-radius:8px;border:1px solid #ffe082;">
<p style="font-size:12px;color:#e65100;margin-bottom:4px;"><i class="fas fa-exclamation-triangle"></i> <strong>Issue:</strong> <?php echo $issue; ?></p>
<p style="font-size:12px;color:#795548;"><i class="fas fa-wrench"></i> <strong>Solution:</strong> <?php echo $solution; ?></p>
</div>
<?php } ?>
</div>
<?php } ?>
</div>
<?php } else { ?>
<div style="text-align:center;padding:30px;color:#999;">
<i class="fas fa-wifi" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
<p>No devices found<?php echo $lecturer_dept_id > 0 ? ' for your department' : ''; ?>.</p>
<p style="font-size:12px;margin-top:4px;">Contact the main admin to register a device and assign it to your department.</p>
</div>
<?php } ?>
</div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});
}
setInterval(updateClock, 10000);

new Chart(document.getElementById('weeklyChart'), {
  type: 'line',
  data: {
    labels: <?php echo json_encode($weeklyLabels); ?>,
    datasets: [{
      label: 'Attendance',
      data: <?php echo json_encode($weeklyData); ?>,
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

new Chart(document.getElementById('courseChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($courseLabels); ?>,
    datasets: [{
      label: 'Attendance %',
      data: <?php echo json_encode($courseData); ?>,
      backgroundColor: <?php echo json_encode(array_map(function($v) { return $v >= 75 ? '#4caf50' : ($v >= 50 ? '#fb8c00' : '#e53935'); }, $courseData)); ?>,
      borderRadius: 6,
      borderSkipped: false,
      barPercentage: .55
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

function toggleDevice(id, btn) {
    const row = document.getElementById('device-' + id);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
    $.post('../api/toggle_device.php', { device_id: id }, function(res) {
        if (res.success) {
            const isActive = res.new_status === 'Active';
            btn.className = 'btn btn-sm ' + (isActive ? 'btn-warning' : 'btn-success');
            btn.innerHTML = '<i class="fas fa-' + (isActive ? 'pause' : 'play') + '"></i> ' + (isActive ? 'Deactivate' : 'Activate');
            const badge = row.querySelector('.badge');
            if (badge) {
                badge.className = 'badge ' + (isActive ? 'badge-success' : 'badge-danger');
                badge.textContent = isActive ? 'Online' : 'Offline';
            }
            Swal.fire({ icon:'success', title:res.message, timer:1500, showConfirmButton:false });
        } else {
            Swal.fire({ icon:'error', title:'Failed', text:res.message });
        }
    }).fail(function() {
        Swal.fire({ icon:'error', title:'Error', text:'Could not reach server.' });
    }).always(function() {
        btn.disabled = false;
    });
}
</script>
