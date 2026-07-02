<?php
function notify_user($conn, $user_id, $user_type, $title, $message, $type = 'info', $device_id = null, $link = null) {
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, user_type, title, message, type, device_id, link) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssis", $user_id, $user_type, $title, $message, $type, $device_id, $link);
    return mysqli_stmt_execute($stmt);
}

function notify_device_admins($conn, $device_id, $title, $message, $type = 'warning') {
    $q = mysqli_query($conn, "SELECT department_id FROM devices WHERE id=$device_id LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $dept_id = mysqli_fetch_assoc($q)['department_id'];
        if ($dept_id) {
            $admins = mysqli_query($conn, "SELECT id FROM department_admins WHERE department_id=$dept_id AND status='Active'");
            while ($a = mysqli_fetch_assoc($admins)) {
                notify_user($conn, $a['id'], 'department_admin', $title, $message, $type, $device_id);
            }
        }
    }
    notify_user($conn, 1, 'admin', $title, $message, $type, $device_id);
}
?>