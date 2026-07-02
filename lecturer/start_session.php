<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lecturer_course_id = (int)($_POST['lecturer_course_id'] ?? 0);
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 60);
    $attendance_method = $_POST['attendance_method'] ?? 'both';
    if (!in_array($attendance_method, ['fingerprint','rfid','both'])) $attendance_method = 'both';
    if ($duration_minutes <= 0) $duration_minutes = 60;

    if ($lecturer_course_id <= 0) {
        $message = "Please select a course."; $message_class = "error";
    } else {
        $find = mysqli_prepare($conn, "SELECT course_id, academic_session_id, semester_id FROM lecturer_courses WHERE id = ? AND lecturer_id = ? LIMIT 1");
        mysqli_stmt_bind_param($find, "ii", $lecturer_course_id, $lecturer_id);
        mysqli_stmt_execute($find);
        $result = mysqli_stmt_get_result($find);
        if ($result && mysqli_num_rows($result) == 1) {
            $course = mysqli_fetch_assoc($result);
            $course_id = $course['course_id'];
            $academic_session_id = $course['academic_session_id'];
            $semester_id = $course['semester_id'];
            $session_date = date("Y-m-d");
            $start_time = date("H:i:s");
            $auto_end_at = date("Y-m-d H:i:s", strtotime("+{$duration_minutes} minutes"));

            $insert = mysqli_prepare($conn, "INSERT INTO attendance_sessions (course_id, lecturer_id, academic_session_id, semester_id, session_date, start_time, status, duration_minutes, auto_end_at, attendance_method) VALUES (?, ?, ?, ?, ?, ?, 'Active', ?, ?, ?)");
            mysqli_stmt_bind_param($insert, "iiiississ", $course_id, $lecturer_id, $academic_session_id, $semester_id, $session_date, $start_time, $duration_minutes, $auto_end_at, $attendance_method);
            if (mysqli_stmt_execute($insert)) {
                $session_id = mysqli_insert_id($conn);
                $message = "Session started! ID: $session_id | Method: " . strtoupper($attendance_method) . " | Ends: $auto_end_at";
                $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        } else {
            $message = "Invalid course selected."; $message_class = "error";
        }
    }
}

$courses = mysqli_query($conn, "SELECT lc.id, c.course_code, c.course_title, ac.session_name, s.semester_name FROM lecturer_courses lc JOIN courses c ON lc.course_id = c.id JOIN academic_sessions ac ON lc.academic_session_id = ac.id JOIN semesters s ON lc.semester_id = s.id WHERE lc.lecturer_id = $lecturer_id ORDER BY c.course_code");

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title"><h1>Start Attendance Session</h1><p>Begin a new attendance session for a course</p></div>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div>
<?php } ?>

<div class="form-box" style="max-width:550px;">
<form method="POST">
<div class="form-group">
<label>Select Course</label>
<select name="lecturer_course_id" required>
<option value="">— Select Course —</option>
<?php while ($c = mysqli_fetch_assoc($courses)) { ?>
<option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_title'] . ' | ' . $c['session_name']); ?></option>
<?php } ?>
</select>
</div>

<div class="form-row">
<div class="form-group">
<label>Attendance Method</label>
<select name="attendance_method" required>
<option value="both">Fingerprint or RFID</option>
<option value="fingerprint">Fingerprint Only</option>
<option value="rfid">RFID Card Only</option>
</select>
</div>
<div class="form-group">
<label>Duration</label>
<select name="duration_minutes" required>
<option value="30">30 Minutes</option>
<option value="60" selected>60 Minutes (1 hour)</option>
<option value="90">90 Minutes</option>
<option value="120">120 Minutes (2 hours)</option>
<option value="180">180 Minutes (3 hours)</option>
</select>
</div>
</div>

<br>
<div style="display:flex;gap:10px;flex-wrap:wrap;">
<button type="submit" class="btn btn-success"><i class="fas fa-play"></i> Start Session</button>
<a href="attendance_sessions.php" class="btn btn-outline"><i class="fas fa-clock"></i> View Sessions</a>
</div>
</form>
</div>
</div>

<?php include 'includes/footer.php'; ?>
