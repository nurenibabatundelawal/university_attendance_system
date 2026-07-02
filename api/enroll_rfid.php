<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = [
    "success" => false,
    "message" => ""
];

$api_key = $_POST['api_key'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$rfid_uid = strtoupper(trim($_POST['rfid_uid'] ?? ''));

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($student_id == "" || $rfid_uid == "") {
    $response["message"] = "Missing student ID or RFID UID.";
    echo json_encode($response);
    exit();
}

// Check if RFID card is already assigned to another student
$check = "
    SELECT id, fullname, matric_no
    FROM students
    WHERE rfid_uid = ?
    AND id != ?
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $check);
mysqli_stmt_bind_param($stmt, "si", $rfid_uid, $student_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    $student = mysqli_fetch_assoc($check_result);
    $response["message"] = "RFID card already assigned to " . $student['fullname'] . " (" . $student['matric_no'] . ").";
    echo json_encode($response);
    exit();
}

// Update student RFID UID
$update = "
    UPDATE students
    SET rfid_uid = ?
    WHERE id = ?
";
$stmt2 = mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($stmt2, "si", $rfid_uid, $student_id);

if (mysqli_stmt_execute($stmt2)) {
    $student_query = "SELECT fullname, matric_no FROM students WHERE id = ? LIMIT 1";
    $stmt3 = mysqli_prepare($conn, $student_query);
    mysqli_stmt_bind_param($stmt3, "i", $student_id);
    mysqli_stmt_execute($stmt3);
    $student_result = mysqli_stmt_get_result($stmt3);
    $student = mysqli_fetch_assoc($student_result);

    $response["success"] = true;
    $response["message"] = "RFID enrolled successfully.";
    $response["student_name"] = $student['fullname'];
    $response["matric_no"] = $student['matric_no'];
    $response["rfid_uid"] = $rfid_uid;
} else {
    $response["message"] = "Failed to enroll RFID.";
}

echo json_encode($response);
?>
