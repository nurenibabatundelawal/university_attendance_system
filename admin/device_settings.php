<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `device_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `device_code` VARCHAR(100) NOT NULL UNIQUE,
    `wifi_ssid_1` VARCHAR(100) DEFAULT NULL,
    `wifi_pass_1` VARCHAR(100) DEFAULT NULL,
    `wifi_ssid_2` VARCHAR(100) DEFAULT NULL,
    `wifi_pass_2` VARCHAR(100) DEFAULT NULL,
    `api_base_url` VARCHAR(255) NOT NULL,
    `api_key` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $device_code = trim($_POST['device_code']);
    $wifi_ssid_1 = trim($_POST['wifi_ssid_1']);
    $wifi_pass_1 = trim($_POST['wifi_pass_1']);
    $wifi_ssid_2 = trim($_POST['wifi_ssid_2']);
    $wifi_pass_2 = trim($_POST['wifi_pass_2']);
    $api_base_url = trim($_POST['api_base_url']);
    $api_key = trim($_POST['api_key']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($device_code == "" || $api_base_url == "" || $api_key == "") {
        $message = "Device code, API base URL and API key are required."; $message_class = "error";
    } else {
        $query = "INSERT INTO device_settings (device_code, wifi_ssid_1, wifi_pass_1, wifi_ssid_2, wifi_pass_2, api_base_url, api_key, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE wifi_ssid_1=VALUES(wifi_ssid_1), wifi_pass_1=VALUES(wifi_pass_1), wifi_ssid_2=VALUES(wifi_ssid_2), wifi_pass_2=VALUES(wifi_pass_2), api_base_url=VALUES(api_base_url), api_key=VALUES(api_key), is_active=VALUES(is_active)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $device_code, $wifi_ssid_1, $wifi_pass_1, $wifi_ssid_2, $wifi_pass_2, $api_base_url, $api_key, $is_active);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Device settings saved successfully."; $message_class = "success";
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}

$settings = mysqli_query($conn, "SELECT * FROM device_settings ORDER BY id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Device Settings</h1><p>Configure ESP32 device connection</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-microchip"></i> Device Configuration</h3>
<label>Device Code</label>
<input type="text" name="device_code" value="ESP32-C3-DEVICE-001" required>
<br><br>
<div class="form-row">
<div><label>WiFi SSID 1 (Main)</label><input type="text" name="wifi_ssid_1" placeholder="Main WiFi"></div>
<div><label>WiFi Password 1</label><input type="password" name="wifi_pass_1" placeholder="Main password"></div>
</div>
<br>
<div class="form-row">
<div><label>WiFi SSID 2 (Backup)</label><input type="text" name="wifi_ssid_2" placeholder="Backup WiFi"></div>
<div><label>WiFi Password 2</label><input type="password" name="wifi_pass_2" placeholder="Backup password"></div>
</div>
<br>
<label>API Base URL</label>
<input type="text" name="api_base_url" value="http://10.35.23.201/university_attendance_system/api" required>
<br><br>
<div class="form-row">
<div>
<label>API Key</label>
<div class="password-wrap" style="display:flex;gap:8px;">
<input type="password" name="api_key" value="CHANGE_THIS_SECRET_KEY_12345" required style="flex:1;">
<button type="button" class="btn btn-sm btn-outline toggle-password" style="flex-shrink:0;"><i class="fas fa-eye"></i></button>
</div>
</div>
<div>
<label>&nbsp;</label>
<label style="display:flex;align-items:center;gap:8px;padding-top:8px;">
<input type="checkbox" name="is_active" checked> <span>Active Device</span>
</label>
</div>
</div>
<br>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
</form>

<div>
<h3><i class="fas fa-list"></i> Saved Devices</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Device Code</th><th>WiFi 1</th><th>WiFi 2</th><th>API Base URL</th><th>Active</th><th>Updated</th></tr></thead>
<tbody>
<?php while ($r = mysqli_fetch_assoc($settings)) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo $r['device_code']; ?></span></td>
<td><?php echo $r['wifi_ssid_1'] ?: '-'; ?></td>
<td><?php echo $r['wifi_ssid_2'] ?: '-'; ?></td>
<td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;"><?php echo $r['api_base_url']; ?></td>
<td><span class="badge <?php echo $r['is_active'] ? 'badge-success' : 'badge-danger'; ?>"><?php echo $r['is_active'] ? 'Yes' : 'No'; ?></span></td>
<td><?php echo $r['updated_at']; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
