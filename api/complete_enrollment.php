<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/config.php';

$response = ["success" => false, "message" => ""];

$api_key = $_POST['api_key'] ?? '';
$request_id = $_POST['request_id'] ?? '';
$value = strtoupper(trim($_POST['value'] ?? ''));

if ($api_key !== ESP32_API_KEY) {
    $response["message"] = "Invalid API key.";
    echo json_encode($response);
    exit();
}

if ($request_id == "" || $value == "") {
    $response["message"] = "Missing request ID or value.";
    echo json_encode($response);
    exit();
}

$query = "
    SELECT *
    FROM enrollment_requests
    WHERE id = ? AND status = 'Pending'
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    $response["message"] = "Pending request not found.";
    echo json_encode($response);
    exit();
}

$request = mysqli_fetch_assoc($result);
$student_id = $request['student_id'];
$type = $request['enrollment_type'];

if ($type == "fingerprint") {
    $fingerprint_id = intval($value);

    $check = "SELECT id FROM students WHERE fingerprint_id = ? AND id != ? LIMIT 1";
    $stmt2 = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt2, "ii", $fingerprint_id, $student_id);
    mysqli_stmt_execute($stmt2);
    $check_result = mysqli_stmt_get_result($stmt2);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $response["message"] = "Fingerprint ID already assigned to another student.";
        echo json_encode($response);
        exit();
    }

    $update = "UPDATE students SET fingerprint_id = ? WHERE id = ?";
    $stmt3 = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt3, "ii", $fingerprint_id, $student_id);
} else {
    $check = "SELECT id FROM students WHERE rfid_uid = ? AND id != ? LIMIT 1";
    $stmt2 = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt2, "si", $value, $student_id);
    mysqli_stmt_execute($stmt2);
    $check_result = mysqli_stmt_get_result($stmt2);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $response["message"] = "RFID UID already assigned to another student.";
        echo json_encode($response);
        exit();
    }

    $update = "UPDATE students SET rfid_uid = ? WHERE id = ?";
    $stmt3 = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt3, "si", $value, $student_id);
}

if (mysqli_stmt_execute($stmt3)) {
    $done = "UPDATE enrollment_requests SET status='Completed', completed_at=NOW() WHERE id=?";
    $stmt4 = mysqli_prepare($conn, $done);
    mysqli_stmt_bind_param($stmt4, "i", $request_id);
    mysqli_stmt_execute($stmt4);

    $student_query = "SELECT fullname, matric_no FROM students WHERE id=? LIMIT 1";
    $stmt5 = mysqli_prepare($conn, $student_query);
    mysqli_stmt_bind_param($stmt5, "i", $student_id);
    mysqli_stmt_execute($stmt5);
    $student_result = mysqli_stmt_get_result($stmt5);
    $student = mysqli_fetch_assoc($student_result);

    $response["success"] = true;
    $response["message"] = "Enrollment completed successfully.";
    $response["student_name"] = $student['fullname'];
    $response["matric_no"] = $student['matric_no'];
    $response["type"] = $type;
    $response["value"] = $value;
} else {
    $response["message"] = "Failed to update student.";
}

echo json_encode($response);
?>
