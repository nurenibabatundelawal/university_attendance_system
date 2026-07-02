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

$request_id = $_POST['request_id'] ?? '';
$value = strtoupper(trim($_POST['value'] ?? ''));

if ($request_id == "" || $value == "") {
    $response["message"] = "Missing request ID or value.";
    echo json_encode($response);
    exit();
}

$query = "UPDATE device_registration_requests SET captured_value=?, status='Completed', completed_at=NOW() WHERE id=? AND status='Pending'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $value, $request_id);

if (mysqli_stmt_execute($stmt) && mysqli_affected_rows($conn) > 0) {
    $response["success"] = true;
    $response["message"] = "Registration capture completed.";
    $response["value"] = $value;

    $devQuery = "SELECT * FROM device_registration_requests WHERE id=? LIMIT 1";
    $devStmt = mysqli_prepare($conn, $devQuery);
    mysqli_stmt_bind_param($devStmt, "i", $request_id);
    mysqli_stmt_execute($devStmt);
    $devResult = mysqli_stmt_get_result($devStmt);
    $devRow = mysqli_fetch_assoc($devResult);

    if ($devRow) {
        $rType = $devRow['request_type'];
        $fId = $devRow['fingerprint_id'];
        $erRow = null;

        if ($rType == "fingerprint") {
            if ($fId > 0) {
                $erQ = "SELECT id, student_id FROM enrollment_requests WHERE enrollment_type='fingerprint' AND fingerprint_id=? AND status='Pending' ORDER BY id DESC LIMIT 1";
                $erS = mysqli_prepare($conn, $erQ);
                mysqli_stmt_bind_param($erS, "i", $fId);
                mysqli_stmt_execute($erS);
                $erR = mysqli_stmt_get_result($erS);
                if ($erR && mysqli_num_rows($erR) > 0) {
                    $erRow = mysqli_fetch_assoc($erR);
                }
            }
            if (!$erRow) {
                $erR = mysqli_query($conn, "SELECT id, student_id FROM enrollment_requests WHERE enrollment_type='fingerprint' AND status='Pending' ORDER BY id DESC LIMIT 1");
                if ($erR && mysqli_num_rows($erR) > 0) {
                    $erRow = mysqli_fetch_assoc($erR);
                }
            }
            if ($erRow) {
                $erId = $erRow['id'];
                $studentId = $erRow['student_id'];
                $upSt = mysqli_prepare($conn, "UPDATE enrollment_requests SET status='Completed', completed_at=NOW() WHERE id=?");
                mysqli_stmt_bind_param($upSt, "i", $erId);
                mysqli_stmt_execute($upSt);
                if ($fId > 0) {
                    $stSt = mysqli_prepare($conn, "UPDATE students SET fingerprint_id=? WHERE id=? AND status='Active' LIMIT 1");
                    mysqli_stmt_bind_param($stSt, "ii", $fId, $studentId);
                    mysqli_stmt_execute($stSt);
                }
            }
        } elseif ($rType == "rfid") {
            $erR = mysqli_query($conn, "SELECT id, student_id FROM enrollment_requests WHERE enrollment_type='rfid' AND status='Pending' ORDER BY id DESC LIMIT 1");
            if ($erR && mysqli_num_rows($erR) > 0) {
                $erRow = mysqli_fetch_assoc($erR);
            }
            if ($erRow) {
                $erId = $erRow['id'];
                $studentId = $erRow['student_id'];
                $upSt = mysqli_prepare($conn, "UPDATE enrollment_requests SET status='Completed', rfid_uid=?, completed_at=NOW() WHERE id=?");
                mysqli_stmt_bind_param($upSt, "si", $value, $erId);
                mysqli_stmt_execute($upSt);
                $stSt = mysqli_prepare($conn, "UPDATE students SET rfid_uid=? WHERE id=? AND status='Active' LIMIT 1");
                mysqli_stmt_bind_param($stSt, "si", $value, $studentId);
                mysqli_stmt_execute($stSt);
            }
        }
    }
} else {
    $response["message"] = "Failed to complete registration capture.";
}

echo json_encode($response);
?>