<?php
function check_department_admin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'department_admin') {
        header("Location: ../login.php");
        exit();
    }
}

function get_department_admin($conn) {
    $id = (int)$_SESSION['user_id'];
    $q = mysqli_query($conn, "SELECT da.*, d.department_name, d.faculty_id, f.faculty_name 
        FROM department_admins da 
        JOIN departments d ON da.department_id = d.id 
        JOIN faculties f ON d.faculty_id = f.id 
        WHERE da.id = $id LIMIT 1");
    return $q ? mysqli_fetch_assoc($q) : null;
}

function log_activity($conn, $user_id, $user_role, $action, $description = "") {
    $stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, user_role, action, description) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $user_role, $action, $description);
    mysqli_stmt_execute($stmt);
}

function add_notification($conn, $user_id, $user_role, $title, $message = "") {
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, user_role, title, message) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $user_role, $title, $message);
    mysqli_stmt_execute($stmt);
}

function count_notifications($conn, $user_id, $user_role) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM notifications WHERE user_id=? AND user_role=? AND is_read=0");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $user_role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
