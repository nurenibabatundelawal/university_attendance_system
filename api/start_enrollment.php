<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = ["success" => false, "message" => ""];

$api_key = $_POST['api_key'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$enrollment_type = $_POST['enrollment_type'] ?? '';
$fingerprint_id = $_POST['fingerprint_id'] ?? null;

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($student_id == "" || ($enrollment_type != "fingerprint" && $enrollment_type != "rfid")) {
    $response["message"] = "Invalid enrollment request.";
    echo json_encode($response);
    exit();
}

mysqli_query($conn, "UPDATE enrollment_requests SET status='Cancelled' WHERE status='Pending'");

$query = "
    INSERT INTO enrollment_requests (student_id, enrollment_type, fingerprint_id, status)
    VALUES (?, ?, ?, 'Pending')
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "isi", $student_id, $enrollment_type, $fingerprint_id);

if (mysqli_stmt_execute($stmt)) {
    $response["success"] = true;
    $response["message"] = "Enrollment request started.";
    $response["request_id"] = mysqli_insert_id($conn);
} else {
    $response["message"] = "Failed to start enrollment.";
}

echo json_encode($response);
?>
