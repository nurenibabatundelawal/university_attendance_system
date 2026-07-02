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
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
mysqli_query($conn, "UPDATE devices SET last_seen = NOW(), ip_address='$ip', last_sync_time=NOW(), connection_status='online' WHERE id=$deviceId");

$records_json = $_POST['records'] ?? '';
if ($records_json == "") {
    $response["message"] = "No records to sync.";
    $response["synced"] = 0;
    echo json_encode($response);
    exit();
}

$records = json_decode($records_json, true);
if (!is_array($records) || count($records) == 0) {
    $response["message"] = "Invalid records format.";
    echo json_encode($response);
    exit();
}

$synced = 0;
$failed = 0;

foreach ($records as $rec) {
    $session_id = (int)($rec['session_id'] ?? 0);
    $identifier_type = $rec['identifier_type'] ?? '';
    $identifier_value = $rec['identifier_value'] ?? '';
    $attendance_time = $rec['attendance_time'] ?? '';
    $unique_id = $rec['unique_id'] ?? '';

    if ($session_id == 0 || $identifier_type == '' || $identifier_value == '' || $unique_id == '') continue;

    $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO attendance_records (session_id, student_id, identifier_type, identifier_value, attendance_time, marked_by) VALUES (?, 0, ?, ?, ?, 'device')");
    mysqli_stmt_bind_param($stmt, "isss", $session_id, $identifier_type, $identifier_value, $attendance_time);
    if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0) {
        $synced++;
    } else {
        $failed++;
    }
}

if ($synced > 0) {
    audit_log($conn, 'attendance_synced', "Synced $synced offline attendance records from device {$device['device_name']}", 'device', null, $deviceId);
}

$response["success"] = true;
$response["synced"] = $synced;
$response["failed"] = $failed;
$response["message"] = "Synced $synced records, $failed duplicates skipped.";

echo json_encode($response);
?>