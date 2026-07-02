<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../php/audit_helper.php';

$response = ["success" => false, "message" => ""];

$device = device_authenticate($conn);
if (!$device) {
    $response["message"] = "Invalid or missing device token.";
    echo json_encode($response);
    exit();
}

$deviceId = (int)$device['device_id'];
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

mysqli_query($conn, "UPDATE devices SET last_seen = NOW(), ip_address = '$ip', connection_status='online', last_sync_time=NOW() WHERE id=$deviceId");
mysqli_query($conn, "UPDATE device_tokens SET last_used_at = NOW() WHERE device_id=$deviceId AND is_active=1");

$response["success"] = true;
$response["message"] = "Heartbeat received.";
$response["device_name"] = $device['device_name'];
$response["server_time"] = date("Y-m-d H:i:s");

echo json_encode($response);
?>