<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';

$api_key = $_GET['api_key'] ?? '';
$device_code = $_GET['device_code'] ?? '';
$device_token = $_GET['device_token'] ?? '';

require_once __DIR__ . '/../php/audit_helper.php';

$device = device_authenticate($conn);
if (!$device) {
    echo json_encode(["success" => false, "message" => "Invalid device credentials."]);
    exit();
}

$deviceId = (int)$device['device_id'];
stamp_heartbeat($conn, $deviceId);

$response = ["success" => false, "message" => "No active session."];

$session = mysqli_query($conn, "SELECT s.*, c.course_name, c.course_code
    FROM attendance_sessions s
    JOIN courses c ON s.course_id = c.id
    WHERE s.status = 'Active'
    ORDER BY s.id DESC LIMIT 1");

if ($session && mysqli_num_rows($session) > 0) {
    $row = mysqli_fetch_assoc($session);
    $response = [
        "success" => true,
        "session_id" => (int)$row['id'],
        "course_name" => $row['course_name'],
        "course_code" => $row['course_code'],
        "attendance_method" => $row['attendance_method'],
        "session_date" => $row['session_date'],
        "start_time" => $row['start_time'],
        "end_time" => $row['end_time']
    ];
}

echo json_encode($response);
?>