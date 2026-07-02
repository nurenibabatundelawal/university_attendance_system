<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$message = ""; $message_class = "";
$deptId = (int)$deptAdmin['department_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $did = (int)$_POST['device_id'];
    $q = mysqli_prepare($conn, "SELECT status FROM devices WHERE id=? AND (department_id=? OR department_id IS NULL) LIMIT 1");
    mysqli_stmt_bind_param($q, "ii", $did, $deptId);
    mysqli_stmt_execute($q);
    $r = mysqli_stmt_get_result($q);
    if ($r && mysqli_num_rows($r) > 0) {
        $d = mysqli_fetch_assoc($r);
        $newStatus = $d['status'] == 'Active' ? 'Inactive' : 'Active';
        mysqli_query($conn, "UPDATE devices SET status='$newStatus' WHERE id=$did");
        $message = "Device status updated."; $message_class = "success";
    }
}

$devices = mysqli_query($conn, "SELECT *, TIMESTAMPDIFF(SECOND, last_seen, NOW()) AS seconds_since_heartbeat FROM devices WHERE department_id=$deptId OR department_id IS NULL ORDER BY device_name ASC");
?>
<div class="main-content">
<div class="page-title"><h1>Device Management</h1><p>Monitor ESP32 devices connected to the system</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<h3><i class="fas fa-microchip"></i> Department Devices</h3>
<div class="table-wrap">
<table id="deptDevicesTable">
<thead><tr><th>Device Name</th><th>Device Code</th><th>Location</th><th>Online</th><th>IP Address</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php if ($devices && mysqli_num_rows($devices) > 0) { while ($d = mysqli_fetch_assoc($devices)) {
    $online = false;
    $onlineLabel = 'Never';
    if ($d['last_seen']) {
        if ($d['seconds_since_heartbeat'] < 60) { $online = true; $onlineLabel = 'Online'; }
        elseif ($d['seconds_since_heartbeat'] < 300) { $onlineLabel = floor($d['seconds_since_heartbeat']/60) . 'm ago'; }
        else { $onlineLabel = floor($d['seconds_since_heartbeat']/60) . 'm ago'; }
    }
?>
<tr>
<td><strong><?php echo htmlspecialchars($d['device_name']); ?></strong></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($d['device_code']); ?></span></td>
<td><?php echo htmlspecialchars($d['location'] ?? '-'); ?></td>
<td><span class="badge <?php echo $online?'badge-success':'badge-danger'; ?>" style="font-size:11px;"><?php echo $onlineLabel; ?></span></td>
<td><?php echo htmlspecialchars($d['ip_address'] ?? '-'); ?></td>
<td><span class="badge <?php echo $d['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $d['status']; ?></span></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="device_id" value="<?php echo $d['id']; ?>">
<button type="submit" name="toggle_status" class="btn btn-sm <?php echo $d['status']=='Active'?'btn-warning':'btn-success'; ?>">
<i class="fas fa-<?php echo $d['status']=='Active'?'pause':'play'; ?>"></i> <?php echo $d['status']=='Active'?'Deactivate':'Activate'; ?>
</button>
</form>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="7" style="text-align:center;color:#999;padding:30px;">No devices configured for your department yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<script>
(function initDT(){
    if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable && jQuery('#deptDevicesTable').length) {
        jQuery('#deptDevicesTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: { search: '', searchPlaceholder: 'Search...' },
            dom: '<"table-top"f>rt<"table-bottom"lip>',
            columns: [null, null, null, null, null, null, { orderable: false }]
        });
    } else {
        setTimeout(initDT, 50);
    }
})();
</script>
<?php include "includes/footer.php"; ?>
