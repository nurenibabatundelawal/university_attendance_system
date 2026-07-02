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

stamp_heartbeat($conn, (int)$device['device_id']);

$query = "SELECT * FROM device_registration_requests WHERE status='Pending' ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $response["success"] = true;
    $response["message"] = "Pending registration capture found.";
    $response["request_id"] = $row['id'];
    $response["request_type"] = $row['request_type'];
    $response["fingerprint_id"] = $row['fingerprint_id'];
} else {
    $query2 = "SELECT er.*, s.fullname as student_name FROM enrollment_requests er JOIN students s ON er.student_id = s.id WHERE er.status='Pending' ORDER BY er.id DESC LIMIT 1";
    $result2 = mysqli_query($conn, $query2);
    if ($result2 && mysqli_num_rows($result2) == 1) {
        $row2 = mysqli_fetch_assoc($result2);
        $inQuery = "INSERT INTO device_registration_requests (request_type, fingerprint_id, status) VALUES (?, ?, 'Pending')";
        $inStmt = mysqli_prepare($conn, $inQuery);
        mysqli_stmt_bind_param($inStmt, "si", $row2['enrollment_type'], $row2['fingerprint_id']);
        mysqli_stmt_execute($inStmt);
        $newId = mysqli_insert_id($conn);
        $response["success"] = true;
        $response["message"] = "Pending registration capture found.";
        $response["request_id"] = $newId;
        $response["request_type"] = $row2['enrollment_type'];
        $response["fingerprint_id"] = $row2['fingerprint_id'];
    } else {
        $response["message"] = "No pending registration capture.";
    }
}

echo json_encode($response);
?>