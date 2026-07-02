<?php
require_once 'auth_check.php';
require_once '../php/db_connect.php';

$lecturer_id = $_SESSION['lecturer_id'];

$deptQ = mysqli_query($conn, "SELECT department_id FROM lecturers WHERE id = $lecturer_id LIMIT 1");
$lecturer_dept_id = 0;
if ($deptQ && mysqli_num_rows($deptQ) > 0) {
    $lecturer_dept_id = (int)mysqli_fetch_assoc($deptQ)['department_id'];
}

if ($lecturer_dept_id > 0) {
    $devices = mysqli_query($conn, "SELECT *, TIMESTAMPDIFF(SECOND, last_seen, NOW()) AS seconds_since FROM devices WHERE department_id = $lecturer_dept_id OR department_id IS NULL ORDER BY device_name");
} else {
    $devices = mysqli_query($conn, "SELECT *, TIMESTAMPDIFF(SECOND, last_seen, NOW()) AS seconds_since FROM devices ORDER BY device_name");
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topbar.php';
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
<div><h1>Device Management</h1><p>Activate, deactivate and monitor ESP32 attendance devices</p></div>
<a href="http://192.168.4.1/" target="_blank" class="btn btn-primary"><i class="fas fa-wifi"></i> Setup Device (192.168.4.1)</a>
</div>

<div class="dashboard-box">
<h3><i class="fas fa-microchip" style="color:var(--primary);"></i> All Devices</h3>
<div class="table-wrap">
<table id="devicesTable">
<thead><tr><th>Device</th><th>Code</th><th>Location</th><th>Online</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php if ($devices && mysqli_num_rows($devices) > 0) { while ($d = mysqli_fetch_assoc($devices)) {
    $online = false;
    $onlineLabel = 'Never';
    if ($d['last_seen']) {
        if ($d['seconds_since'] < 60) { $online = true; $onlineLabel = 'Online'; }
        elseif ($d['seconds_since'] < 300) { $onlineLabel = floor($d['seconds_since']/60) . 'm ago'; }
        else { $onlineLabel = floor($d['seconds_since']/60) . 'm ago'; }
    }
?>
<tr id="device-<?php echo $d['id']; ?>">
<td><strong><?php echo htmlspecialchars($d['device_name']); ?></strong></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($d['device_code']); ?></span></td>
<td><?php echo htmlspecialchars($d['location'] ?? '-'); ?></td>
<td><span class="badge <?php echo $online?'badge-success':'badge-danger'; ?>"><?php echo $onlineLabel; ?></span></td>
<td><span class="badge <?php echo $d['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $d['status']; ?></span></td>
<td>
<button onclick="toggleDevice(<?php echo $d['id']; ?>, this)" class="btn btn-sm <?php echo $d['status']=='Active'?'btn-warning':'btn-success'; ?>">
<i class="fas fa-<?php echo $d['status']=='Active'?'pause':'play'; ?>"></i> <?php echo $d['status']=='Active'?'Deactivate':'Activate'; ?>
</button>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="6" style="text-align:center;padding:40px 20px;">
<div style="font-size:40px;color:#1565c0;margin-bottom:15px;"><i class="fas fa-wifi"></i></div>
<h3 style="margin:0 0 8px;color:#333;">No Devices Yet</h3>
<p style="color:#777;margin:0 0 20px;max-width:400px;margin-left:auto;margin-right:auto;">Plug in your ESP32, connect to <strong>"Attendance_Device_Setup"</strong> WiFi (password: <strong>12345678</strong>), then click the button below to configure it.</p>
<a href="http://192.168.4.1/" target="_blank" class="btn btn-primary" style="font-size:16px;padding:12px 28px;"><i class="fas fa-external-link-alt"></i> Open 192.168.4.1</a>
</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<script>
(function initDT(){
    if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.DataTable && jQuery('#devicesTable').length) {
        jQuery('#devicesTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: { search: '', searchPlaceholder: 'Search...' },
            dom: '<"table-top"f>rt<"table-bottom"lip>',
            columns: [
                null, null, null, null, null, { orderable: false }
            ]
        });
    } else {
        setTimeout(initDT, 50);
    }
})();
</script>
<?php include 'includes/footer.php'; ?>
<script>
function toggleDevice(id, btn) {
    const row = document.getElementById('device-' + id);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
    $.post('../api/toggle_device.php', { device_id: id }, function(res) {
        if (res.success) {
            const isActive = res.new_status === 'Active';
            btn.className = 'btn btn-sm ' + (isActive ? 'btn-warning' : 'btn-success');
            btn.innerHTML = '<i class="fas fa-' + (isActive ? 'pause' : 'play') + '"></i> ' + (isActive ? 'Deactivate' : 'Activate');
            const badges = row.querySelectorAll('.badge');
            if (badges.length >= 2) badges[badges.length - 1].textContent = res.new_status;
            if (badges.length >= 2) badges[badges.length - 1].className = 'badge ' + (isActive ? 'badge-success' : 'badge-danger');
            Swal.fire({ icon:'success', title:res.message, timer:1500, showConfirmButton:false });
        } else {
            Swal.fire({ icon:'error', title:'Failed', text:res.message });
        }
    }).fail(function() {
        Swal.fire({ icon:'error', title:'Error', text:'Could not reach server.' });
    }).always(function() {
        btn.disabled = false;
    });
}
</script>
