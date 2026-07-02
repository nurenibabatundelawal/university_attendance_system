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

$q = mysqli_query($conn, "SELECT * FROM devices WHERE id=$deviceId LIMIT 1");
if ($q && mysqli_num_rows($q) > 0) {
    $d = mysqli_fetch_assoc($q);
    $response["success"] = true;
    $response["device_id"] = $d['id'];
    $response["device_name"] = $d['device_name'];
    $response["device_type"] = $d['device_type'] ?? 'ESP32-C3';
    $response["device_code"] = $d['device_code'];
    $response["department_id"] = $d['department_id'];
    $response["lecturer_id"] = $d['lecturer_id'];
    $response["building"] = $d['building'] ?? '';
    $response["room"] = $d['room'] ?? '';
    $response["status"] = $d['status'];
    $response["server_time"] = date("Y-m-d H:i:s");
} else {
    $response["message"] = "Device not found in database.";
}

echo json_encode($response);
?>