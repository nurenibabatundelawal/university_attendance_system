<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../php/audit_helper.php';

$response = ["success" => false, "message" => ""];

$device = device_authenticate($conn);
if (!$device) {
    $response["message"] = "Invalid device token.";
    echo json_encode($response);
    exit();
}

$deviceId = (int)$device['device_id'];
stamp_heartbeat($conn, $deviceId);

$session_id = $_POST['session_id'] ?? '';
$identifier_type = $_POST['identifier_type'] ?? '';
$identifier_value = $_POST['identifier_value'] ?? '';

if ($session_id == "" || $identifier_type == "" || $identifier_value == "") {
    $response["message"] = "Missing required fields.";
    echo json_encode($response);
    exit();
}

if ($identifier_type !== "fingerprint" && $identifier_type !== "rfid") {
    $response["message"] = "Invalid identifier type.";
    echo json_encode($response);
    exit();
}

$expire_query = "
    UPDATE attendance_sessions
    SET status = 'Ended'
    WHERE status = 'Active'
    AND auto_end_at IS NOT NULL
    AND NOW() >= auto_end_at
";
mysqli_query($conn, $expire_query);

$session_query = "
    SELECT *
    FROM attendance_sessions
    WHERE id = ? AND status = 'Active'
    AND (auto_end_at IS NULL OR NOW() < auto_end_at)
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $session_query);
mysqli_stmt_bind_param($stmt, "i", $session_id);
mysqli_stmt_execute($stmt);
$session_result = mysqli_stmt_get_result($stmt);

if (!$session_result || mysqli_num_rows($session_result) == 0) {
    $response["message"] = "Session is not active or has ended.";
    echo json_encode($response);
    exit();
}

$session = mysqli_fetch_assoc($session_result);
$course_id = $session['course_id'];
$academic_session_id = $session['academic_session_id'];
$semester_id = $session['semester_id'];
$attendance_method = $session['attendance_method'] ?? 'both';

if ($attendance_method === "fingerprint" && $identifier_type !== "fingerprint") {
    $response["message"] = "RFID not allowed. Use fingerprint.";
    echo json_encode($response);
    exit();
}
if ($attendance_method === "rfid" && $identifier_type !== "rfid") {
    $response["message"] = "Fingerprint not allowed. Use RFID card.";
    echo json_encode($response);
    exit();
}

if ($identifier_type == "fingerprint") {
    $student_query = "SELECT * FROM students WHERE fingerprint_id = ? AND status = 'Active' LIMIT 1";
    $stmt2 = mysqli_prepare($conn, $student_query);
    mysqli_stmt_bind_param($stmt2, "i", $identifier_value);
} else {
    $student_query = "SELECT * FROM students WHERE rfid_uid = ? AND status = 'Active' LIMIT 1";
    $stmt2 = mysqli_prepare($conn, $student_query);
    mysqli_stmt_bind_param($stmt2, "s", $identifier_value);
}

mysqli_stmt_execute($stmt2);
$student_result = mysqli_stmt_get_result($stmt2);

if (!$student_result || mysqli_num_rows($student_result) == 0) {
    $response["message"] = "Student not found.";
    echo json_encode($response);
    exit();
}

$student = mysqli_fetch_assoc($student_result);
$student_id = $student['id'];

$reg_query = "
    SELECT id FROM course_registrations
    WHERE student_id = ? AND course_id = ?
    AND academic_session_id = ? AND semester_id = ?
    LIMIT 1
";
$stmt3 = mysqli_prepare($conn, $reg_query);
mysqli_stmt_bind_param($stmt3, "iiii", $student_id, $course_id, $academic_session_id, $semester_id);
mysqli_stmt_execute($stmt3);
$reg_result = mysqli_stmt_get_result($stmt3);

if (!$reg_result || mysqli_num_rows($reg_result) == 0) {
    $response["message"] = "Student is not registered for this course.";
    echo json_encode($response);
    exit();
}

$check_query = "SELECT id FROM attendance_records WHERE attendance_session_id = ? AND student_id = ? LIMIT 1";
$stmt4 = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt4, "ii", $session_id, $student_id);
mysqli_stmt_execute($stmt4);
$check_result = mysqli_stmt_get_result($stmt4);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    $response["success"] = true;
    $response["message"] = "Attendance already marked.";
    $response["student_name"] = $student['fullname'];
    $response["matric_no"] = $student['matric_no'];
    echo json_encode($response);
    exit();
}

$verification_method = ($identifier_type == "fingerprint") ? "Fingerprint" : "RFID";

$insert_query = "INSERT INTO attendance_records (attendance_session_id, student_id, course_id, verification_method, attendance_status) VALUES (?, ?, ?, ?, 'Present')";
$stmt5 = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt5, "iiis", $session_id, $student_id, $course_id, $verification_method);

if (mysqli_stmt_execute($stmt5)) {
    $response["success"] = true;
    $response["message"] = "Attendance marked successfully.";
    $response["student_name"] = $student['fullname'];
    $response["matric_no"] = $student['matric_no'];
    $response["method"] = $verification_method;
} else {
    $response["message"] = "Failed to mark attendance.";
}

echo json_encode($response);
?>