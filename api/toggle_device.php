<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../php/db_connect.php';

$response = ["success" => false, "message" => ""];

if (session_status() === PHP_SESSION_NONE) session_start();

$lecturer_id = $_SESSION['lecturer_id'] ?? 0;
$dept_admin_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? '';

if (!$lecturer_id && !$dept_admin_id) {
    $response["message"] = "Unauthorized.";
    echo json_encode($response);
    exit();
}

$device_id = (int)($_POST['device_id'] ?? 0);
if ($device_id <= 0) {
    $response["message"] = "Invalid device ID.";
    echo json_encode($response);
    exit();
}

// For lecturers, check the device is in their department
if ($role === 'lecturer') {
    $deptQ = mysqli_prepare($conn, "SELECT department_id FROM lecturers WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($deptQ, "i", $lecturer_id);
    mysqli_stmt_execute($deptQ);
    $lecturerDept = mysqli_stmt_get_result($deptQ)->fetch_assoc()['department_id'] ?? 0;

    $check = mysqli_prepare($conn, "SELECT id FROM devices WHERE id = ? AND (department_id = ? OR department_id IS NULL) LIMIT 1");
    mysqli_stmt_bind_param($check, "ii", $device_id, $lecturerDept);
    mysqli_stmt_execute($check);
    if (mysqli_stmt_get_result($check)->num_rows === 0) {
        $response["message"] = "Device not found in your department.";
        echo json_encode($response);
        exit();
    }
}

$q = mysqli_prepare($conn, "SELECT status FROM devices WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($q, "i", $device_id);
mysqli_stmt_execute($q);
$r = mysqli_stmt_get_result($q);

if ($r && mysqli_num_rows($r) > 0) {
    $d = mysqli_fetch_assoc($r);
    $newStatus = $d['status'] === 'Active' ? 'Inactive' : 'Active';
    $up = mysqli_prepare($conn, "UPDATE devices SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($up, "si", $newStatus, $device_id);
    if (mysqli_stmt_execute($up)) {
        $response["success"] = true;
        $response["message"] = "Device " . ($newStatus === 'Active' ? 'activated' : 'deactivated') . ".";
        $response["new_status"] = $newStatus;
    } else {
        $response["message"] = "Failed to update device.";
    }
} else {
    $response["message"] = "Device not found.";
}

echo json_encode($response);
?>
