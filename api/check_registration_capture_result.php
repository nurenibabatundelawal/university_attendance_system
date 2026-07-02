<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = ["success" => false, "message" => ""];

$api_key = $_GET['api_key'] ?? '';
$request_id = $_GET['request_id'] ?? '';

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($request_id == "") {
    $response["message"] = "Missing request ID.";
    echo json_encode($response);
    exit();
}

$query = "SELECT * FROM device_registration_requests WHERE id=? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $response["success"] = true;
    $response["message"] = "Capture request found.";
    $response["status"] = $row['status'];
    $response["request_type"] = $row['request_type'];
    $response["fingerprint_id"] = $row['fingerprint_id'];
    $response["captured_value"] = $row['captured_value'];
} else {
    $response["message"] = "Capture request not found.";
}

echo json_encode($response);
?>
