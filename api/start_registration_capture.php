<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = ["success" => false, "message" => ""];

$api_key = $_POST['api_key'] ?? '';
$request_type = $_POST['request_type'] ?? '';
$fingerprint_id = $_POST['fingerprint_id'] ?? null;

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($request_type != "fingerprint" && $request_type != "rfid") {
    $response["message"] = "Invalid request type.";
    echo json_encode($response);
    exit();
}

mysqli_query($conn, "UPDATE device_registration_requests SET status='Cancelled' WHERE status='Pending'");

$query = "INSERT INTO device_registration_requests (request_type, fingerprint_id, status) VALUES (?, ?, 'Pending')";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $request_type, $fingerprint_id);

if (mysqli_stmt_execute($stmt)) {
    $response["success"] = true;
    $response["message"] = "Registration capture request started.";
    $response["request_id"] = mysqli_insert_id($conn);
} else {
    $response["message"] = "Failed to start registration capture.";
}

echo json_encode($response);
?>
