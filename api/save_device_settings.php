<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../php/audit_helper.php';

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response["message"] = "POST only.";
    echo json_encode($response);
    exit();
}

$device = device_authenticate($conn);
if (!$device) {
    $response["message"] = "Invalid device token.";
    echo json_encode($response);
    exit();
}

$deviceId = (int)$device['device_id'];
$device_code = $device['device_token'];
stamp_heartbeat($conn, $deviceId);

$wifi_ssid = $_POST['wifi_ssid'] ?? '';
$wifi_pass = $_POST['wifi_pass'] ?? '';
$wifi_ssid2 = $_POST['wifi_ssid2'] ?? '';
$wifi_pass2 = $_POST['wifi_pass2'] ?? '';
$api_base_url = $_POST['api_base_url'] ?? '';

$upsert = "INSERT INTO device_settings (device_code, wifi_ssid_1, wifi_pass_1, wifi_ssid_2, wifi_pass_2, api_base_url, api_key, is_active)
           VALUES (?, ?, ?, ?, ?, ?, ?, 1)
           ON DUPLICATE KEY UPDATE wifi_ssid_1=VALUES(wifi_ssid_1), wifi_pass_1=VALUES(wifi_pass_1),
                                   wifi_ssid_2=VALUES(wifi_ssid_2), wifi_pass_2=VALUES(wifi_pass_2),
                                   api_base_url=VALUES(api_base_url), api_key=VALUES(api_key)";
$stmt = mysqli_prepare($conn, $upsert);
$device_api_key = $device_code;
mysqli_stmt_bind_param($stmt, "sssssss", $device_code, $wifi_ssid, $wifi_pass, $wifi_ssid2, $wifi_pass2, $api_base_url, $device_api_key);
mysqli_stmt_execute($stmt);

mysqli_query($conn, "UPDATE devices SET device_name='$device_code' WHERE id=$deviceId");

$response["success"] = true;
$response["message"] = "Device settings saved.";
$response["device_token"] = $device_code;
echo json_encode($response);
?>