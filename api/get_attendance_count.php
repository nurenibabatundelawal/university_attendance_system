<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = [
    "success" => false,
    "message" => ""
];

$api_key = $_GET['api_key'] ?? '';
$session_id = $_GET['session_id'] ?? '';

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($session_id == "") {
    $response["message"] = "Missing session ID.";
    echo json_encode($response);
    exit();
}

$query = "
    SELECT COUNT(*) AS total_present
    FROM attendance_records
    WHERE attendance_session_id = ?
    AND attendance_status = 'Present'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $session_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $row = mysqli_fetch_assoc($result);

    $response["success"] = true;
    $response["message"] = "Attendance count fetched.";
    $response["session_id"] = $session_id;
    $response["total_present"] = $row['total_present'];
} else {
    $response["message"] = "Failed to fetch attendance count.";
}

echo json_encode($response);
?>
