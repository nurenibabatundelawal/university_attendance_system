<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = ["success" => false, "message" => ""];

$api_key = $_GET['api_key'] ?? '';

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

$query = "
    SELECT enrollment_requests.id,
           enrollment_requests.student_id,
           enrollment_requests.enrollment_type,
           enrollment_requests.fingerprint_id,
           students.fullname,
           students.matric_no
    FROM enrollment_requests
    JOIN students ON enrollment_requests.student_id = students.id
    WHERE enrollment_requests.status = 'Pending'
    ORDER BY enrollment_requests.id DESC
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);

    $response["success"] = true;
    $response["message"] = "Pending enrollment found.";
    $response["request_id"] = $row['id'];
    $response["student_id"] = $row['student_id'];
    $response["enrollment_type"] = $row['enrollment_type'];
    $response["fingerprint_id"] = $row['fingerprint_id'];
    $response["student_name"] = $row['fullname'];
    $response["matric_no"] = $row['matric_no'];
} else {
    $response["message"] = "No pending enrollment.";
}

echo json_encode($response);
?>
