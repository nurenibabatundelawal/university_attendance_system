<?php
function audit_log($conn, $action, $description, $actor_type = 'system', $actor_id = null, $device_id = null, $department_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (action, description, actor_type, actor_id, device_id, department_id, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiiis", $action, $description, $actor_type, $actor_id, $device_id, $department_id, $ip);
    return mysqli_stmt_execute($stmt);
}

function device_authenticate($conn) {
    $token = $_GET['device_token'] ?? $_POST['device_token'] ?? '';
    if ($token == '') return null;

    $stmt = mysqli_prepare($conn, "SELECT dt.*, d.device_name, d.department_id, d.status
        FROM device_tokens dt
        JOIN devices d ON dt.device_id = d.id
        WHERE dt.device_token = ? AND dt.is_active = 1 AND d.status = 'Active'
        LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $device = mysqli_fetch_assoc($result);
        mysqli_query($conn, "UPDATE device_tokens SET last_used_at = NOW() WHERE id = {$device['id']}");
        return $device;
    }

    // Fallback: check old api_key system
    require_once __DIR__ . '/../api/config.php';
    $key = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
    if ($key !== '' && $key === ESP32_API_KEY) {
        if (isset($_GET['device_code'])) {
            $code = $_GET['device_code'];
        } elseif (isset($_POST['device_code'])) {
            $code = $_POST['device_code'];
        } else {
            $code = 'fallback';
        }
        $q = mysqli_query($conn, "SELECT d.*, 0 as dt_id, '' as device_secret FROM devices d WHERE d.device_code='$code' LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $device = mysqli_fetch_assoc($q);
            $device['device_token'] = $code;
            $device['device_id'] = $device['id'];
            return $device;
        }
        return ['id' => 0, 'device_id' => 0, 'device_name' => 'Legacy', 'department_id' => null, 'status' => 'Active', 'device_token' => $code, 'device_secret' => ''];
    }

    return null;
}

function stamp_heartbeat($conn, $device_id, $device_code = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($device_id > 0) {
        mysqli_query($conn, "UPDATE devices SET last_seen = NOW(), ip_address = '$ip', connection_status='online' WHERE id=$device_id");
    } elseif ($device_code) {
        $hb = mysqli_prepare($conn, "UPDATE devices SET last_seen = NOW(), ip_address = ?, connection_status='online' WHERE device_code = ?");
        mysqli_stmt_bind_param($hb, "ss", $ip, $device_code);
        mysqli_stmt_execute($hb);
        if (mysqli_affected_rows($conn) == 0) {
            $ins = mysqli_prepare($conn, "INSERT INTO devices (device_code, device_name, ip_address, last_seen, connection_status, status) VALUES (?, ?, ?, NOW(), 'online', 'Active')");
            mysqli_stmt_bind_param($ins, "sss", $device_code, $device_code, $ip);
            mysqli_stmt_execute($ins);
        }
    }
}
?>