<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = "";
$message_class = "";

$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");
$lecturers = mysqli_query($conn, "SELECT id, fullname, staff_id FROM lecturers WHERE approval_status='Approved' ORDER BY fullname ASC");

// Register new device
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_device'])) {
    $device_name = trim($_POST['device_name']);
    $device_type = trim($_POST['device_type']);
    $department_id = (int)$_POST['department_id'];
    $lecturer_id = !empty($_POST['lecturer_id']) ? (int)$_POST['lecturer_id'] : null;
    $building = trim($_POST['building'] ?? '');
    $room = trim($_POST['room'] ?? '');

    if ($device_name == "") {
        $message = "Device name is required."; $message_class = "error";
    } else {
        $token = bin2hex(random_bytes(24));
        $secret = bin2hex(random_bytes(32));

        $stmt = mysqli_prepare($conn, "INSERT INTO devices (device_name, device_code, device_type, department_id, lecturer_id, building, room, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
        mysqli_stmt_bind_param($stmt, "sssiiss", $device_name, $token, $device_type, $department_id, $lecturer_id, $building, $room);
        if (mysqli_stmt_execute($stmt)) {
            $device_id = mysqli_insert_id($conn);
            $tStmt = mysqli_prepare($conn, "INSERT INTO device_tokens (device_id, device_token, device_secret) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($tStmt, "iss", $device_id, $token, $secret);
            mysqli_stmt_execute($tStmt);

            $desc = "Registered device: $device_name (Token: " . substr($token, 0, 8) . "...)";
            mysqli_query($conn, "INSERT INTO audit_logs (action, description, actor_type, actor_id) VALUES ('device_registered', '$desc', 'admin', 1)");

            $message = "Device registered successfully. <br><strong>Device Token:</strong> <code>$token</code><br><strong>Device Secret:</strong> <code>$secret</code><br><small>Save these — the secret is shown only once.</small>";
            $message_class = "success";
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}

// Edit device
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $ed = mysqli_query($conn, "SELECT * FROM devices WHERE id=$eid LIMIT 1");
    $editDevice = $ed && mysqli_num_rows($ed) > 0 ? mysqli_fetch_assoc($ed) : null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_device'])) {
    $did = (int)$_POST['device_id'];
    $device_name = trim($_POST['device_name']);
    $device_type = trim($_POST['device_type']);
    $department_id = (int)$_POST['department_id'];
    $lecturer_id = !empty($_POST['lecturer_id']) ? (int)$_POST['lecturer_id'] : null;
    $building = trim($_POST['building'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    $up = mysqli_prepare($conn, "UPDATE devices SET device_name=?, device_type=?, department_id=?, lecturer_id=?, building=?, room=?, status=? WHERE id=?");
    mysqli_stmt_bind_param($up, "ssiisssi", $device_name, $device_type, $department_id, $lecturer_id, $building, $room, $status, $did);
    if (mysqli_stmt_execute($up)) {
        $message = "Device updated."; $message_class = "success";
    } else {
        $message = "Error: " . mysqli_error($conn); $message_class = "error";
    }
}

// Toggle status
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $tq = mysqli_query($conn, "SELECT id, device_name, status FROM devices WHERE id=$tid LIMIT 1");
    if ($tq && mysqli_num_rows($tq) > 0) {
        $t = mysqli_fetch_assoc($tq);
        $ns = $t['status'] == 'Active' ? 'Inactive' : 'Active';
        mysqli_query($conn, "UPDATE devices SET status='$ns' WHERE id=$tid");
        $desc = ($ns == 'Active' ? "Activated" : "Deactivated") . " device: {$t['device_name']}";
        mysqli_query($conn, "INSERT INTO audit_logs (action, description, actor_type, actor_id) VALUES ('device_" . ($ns == 'Active' ? 'activated' : 'deactivated') . "', '$desc', 'admin', 1)");
    }
    header("Location: devices.php"); exit();
}

// Delete device
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $dq = mysqli_query($conn, "SELECT device_name FROM devices WHERE id=$did LIMIT 1");
    if ($dq && mysqli_num_rows($dq) > 0) {
        $dn = mysqli_fetch_assoc($dq)['device_name'];
        mysqli_query($conn, "DELETE FROM devices WHERE id=$did");
        mysqli_query($conn, "INSERT INTO audit_logs (action, description, actor_type, actor_id) VALUES ('device_deleted', 'Deleted device: $dn', 'admin', 1)");
    }
    header("Location: devices.php"); exit();
}

// Regenerate token
if (isset($_GET['newtoken'])) {
    $nid = (int)$_GET['newtoken'];
    $newToken = bin2hex(random_bytes(24));
    $newSecret = bin2hex(random_bytes(32));
    mysqli_query($conn, "UPDATE device_tokens SET is_active=0 WHERE device_id=$nid");
    $ins = mysqli_prepare($conn, "INSERT INTO device_tokens (device_id, device_token, device_secret) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($ins, "iss", $nid, $newToken, $newSecret);
    mysqli_stmt_execute($ins);
    mysqli_query($conn, "UPDATE devices SET device_code='$newToken' WHERE id=$nid");
    $message = "New token generated. <br><strong>Device Token:</strong> <code>$newToken</code><br><strong>Device Secret:</strong> <code>$newSecret</code>";
    $message_class = "success";
}

$filter_dept = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
if ($filter_dept > 0) $where .= " AND d.department_id=$filter_dept";
if ($filter_status) $where .= " AND d.status='$filter_status'";

$devices = mysqli_query($conn, "SELECT d.*, dp.department_name, dt.device_token,
    TIMESTAMPDIFF(SECOND, d.last_seen, NOW()) AS seconds_since
    FROM devices d
    LEFT JOIN departments dp ON d.department_id = dp.id
    LEFT JOIN device_tokens dt ON dt.device_id = d.id AND dt.is_active=1
    $where
    ORDER BY d.created_at DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Device Management</h1><p>Register, monitor, and manage all ESP32 attendance devices</p></div>
<button class="btn btn-primary" onclick="document.getElementById('registerForm').style.display='block'"><i class="fas fa-plus"></i> Register Device</button>
</div>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div>
<?php } ?>

<!-- Register Device Form -->
<div id="registerForm" style="display:<?php echo isset($_GET['edit']) ? 'none' : 'none'; ?>;margin-bottom:20px;">
<div class="form-box">
<h3><i class="fas fa-microchip"></i> Register New Device</h3>
<form method="POST">
<div class="form-row">
<div><label>Device Name *</label><input type="text" name="device_name" required></div>
<div><label>Device Type</label>
<select name="device_type"><option value="ESP32-C3">ESP32-C3</option><option value="ESP32">ESP32</option><option value="ESP32-S3">ESP32-S3</option></select></div>
</div>
<div class="form-row">
<div><label>Department</label>
<select name="department_id">
<option value="0">None</option>
<?php mysqli_data_seek($departments, 0); while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
<?php } ?>
</select></div>
<div><label>Assigned Lecturer</label>
<select name="lecturer_id">
<option value="">None</option>
<?php mysqli_data_seek($lecturers, 0); while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['fullname']); ?></option>
<?php } ?>
</select></div>
</div>
<div class="form-row">
<div><label>Building</label><input type="text" name="building" placeholder="e.g. Science Building"></div>
<div><label>Room</label><input type="text" name="room" placeholder="e.g. Room 204"></div>
</div>
<button type="submit" name="register_device" class="btn btn-primary"><i class="fas fa-save"></i> Register</button>
<button type="button" class="btn btn-sm btn-outline" onclick="this.closest('#registerForm').style.display='none'">Cancel</button>
</form>
</div>
</div>

<!-- Filters -->
<div class="dashboard-box" style="margin-bottom:20px;">
<div class="form-row" style="align-items:end;">
<form method="GET" style="display:contents;">
<div><label>Department</label>
<select name="dept" onchange="this.form.submit()">
<option value="0">All Departments</option>
<?php mysqli_data_seek($departments, 0); while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>" <?php echo $filter_dept==$d['id']?'selected':''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
<?php } ?>
</select></div>
<div><label>Status</label>
<select name="status" onchange="this.form.submit()">
<option value="">All</option>
<option value="Active" <?php echo $filter_status=='Active'?'selected':''; ?>>Active</option>
<option value="Inactive" <?php echo $filter_status=='Inactive'?'selected':''; ?>>Inactive</option>
</select></div>
<div><label>&nbsp;</label><a href="devices.php" class="btn btn-sm btn-outline">Clear</a></div>
</form>
</div>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-list"></i> All Devices</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr>
<th>Device Name</th><th>Type</th><th>Department</th><th>Location</th><th>Status</th><th>Online</th><th>Last Seen</th><th>Actions</th>
</tr></thead>
<tbody>
<?php while ($d = mysqli_fetch_assoc($devices)) {
    $online = false; $onlineLabel = 'Never';
    if ($d['last_seen']) {
        $sec = (int)$d['seconds_since'];
        if ($sec < 90) { $online = true; $onlineLabel = 'Online'; }
        elseif ($sec < 300) { $onlineLabel = floor($sec/60) . 'm ago'; }
        else { $onlineLabel = floor($sec/60) . 'm ago'; }
    }
    $loc = trim(($d['building'] ?? '') . ' ' . ($d['room'] ?? ''));
    if ($loc == '') $loc = $d['location'] ?? '-';
?>
<tr>
<td><strong><?php echo htmlspecialchars($d['device_name']); ?></strong><br><small style="color:#999;"><?php echo htmlspecialchars(substr($d['device_token'] ?? '', 0, 16)); ?>...</small></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($d['device_type'] ?? 'ESP32-C3'); ?></span></td>
<td><?php echo htmlspecialchars($d['department_name'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($loc); ?></td>
<td><span class="badge <?php echo $d['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $d['status']; ?></span></td>
<td><span class="badge <?php echo $online?'badge-success':'badge-secondary'; ?>" style="font-size:11px;"><?php echo $onlineLabel; ?></span></td>
<td><?php echo $d['last_seen'] ? date('d/m H:i', strtotime($d['last_seen'])) : 'Never'; ?></td>
<td style="white-space:nowrap;">
<a href="devices.php?edit=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fas fa-edit"></i></a>
<a href="devices.php?toggle=<?php echo $d['id']; ?>" class="btn btn-sm <?php echo $d['status']=='Active'?'btn-warning':'btn-success'; ?>" title="<?php echo $d['status']=='Active'?'Deactivate':'Activate'; ?>"><i class="fas fa-<?php echo $d['status']=='Active'?'pause':'play'; ?>"></i></a>
<a href="devices.php?newtoken=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline" title="New Token" onclick="return confirm('Generate new token? The old one will stop working.');"><i class="fas fa-key"></i></a>
<a href="devices.php?delete=<?php echo $d['id']; ?>" class="btn btn-sm btn-danger btn-delete" data-item="device" title="Delete"><i class="fas fa-trash"></i></a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<?php if (isset($editDevice)) { ?>
<div class="form-box" style="margin-top:20px;">
<h3><i class="fas fa-edit"></i> Edit Device: <?php echo htmlspecialchars($editDevice['device_name']); ?></h3>
<form method="POST">
<input type="hidden" name="device_id" value="<?php echo $editDevice['id']; ?>">
<div class="form-row">
<div><label>Device Name *</label><input type="text" name="device_name" value="<?php echo htmlspecialchars($editDevice['device_name']); ?>" required></div>
<div><label>Device Type</label>
<select name="device_type">
<?php foreach (['ESP32-C3','ESP32','ESP32-S3'] as $t) { ?>
<option value="<?php echo $t; ?>" <?php echo ($editDevice['device_type']??'ESP32-C3')==$t?'selected':''; ?>><?php echo $t; ?></option>
<?php } ?>
</select></div>
</div>
<div class="form-row">
<div><label>Department</label>
<select name="department_id">
<option value="0">None</option>
<?php mysqli_data_seek($departments, 0); while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>" <?php echo $editDevice['department_id']==$d['id']?'selected':''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
<?php } ?>
</select></div>
<div><label>Assigned Lecturer</label>
<select name="lecturer_id">
<option value="">None</option>
<?php mysqli_data_seek($lecturers, 0); while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<option value="<?php echo $l['id']; ?>" <?php echo $editDevice['lecturer_id']==$l['id']?'selected':''; ?>><?php echo htmlspecialchars($l['fullname']); ?></option>
<?php } ?>
</select></div>
</div>
<div class="form-row">
<div><label>Building</label><input type="text" name="building" value="<?php echo htmlspecialchars($editDevice['building'] ?? ''); ?>"></div>
<div><label>Room</label><input type="text" name="room" value="<?php echo htmlspecialchars($editDevice['room'] ?? ''); ?>"></div>
<div><label>Status</label>
<select name="status">
<option value="Active" <?php echo $editDevice['status']=='Active'?'selected':''; ?>>Active</option>
<option value="Inactive" <?php echo $editDevice['status']=='Inactive'?'selected':''; ?>>Inactive</option>
</select></div>
</div>
<button type="submit" name="update_device" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
<a href="devices.php" class="btn btn-sm btn-outline">Cancel</a>
</form>
</div>
<?php } ?>
</div>

<?php include "includes/footer.php"; ?>
