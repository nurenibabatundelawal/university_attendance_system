<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../api/config.php';

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Device Capture Registration</h1><p>Capture fingerprint or RFID directly from ESP32 device</p></div>

<div class="form-box">
<div class="form-row">
<div>
<h3><i class="fas fa-fingerprint"></i> Fingerprint Capture</h3>
<label>Fingerprint ID / Slot</label>
<input type="number" id="fingerprint_id_input" placeholder="e.g. 1">
<br><br>
<button type="button" class="btn btn-primary" onclick="startCapture('fingerprint')"><i class="fas fa-fingerprint"></i> Capture Fingerprint</button>
</div>
<div>
<h3><i class="fas fa-id-card"></i> RFID Capture</h3>
<label>RFID UID</label>
<input type="text" id="rfid_uid_input" placeholder="Captured UID will appear here" readonly>
<br><br>
<button type="button" class="btn btn-primary" onclick="startCapture('rfid')"><i class="fas fa-id-card"></i> Capture RFID</button>
</div>
</div>
<br>
<div id="capture_status" style="padding:14px;border-radius:10px;background:#f0f4ff;color:#1565c0;text-align:center;font-weight:500;">
Ready to capture. Click a button above to start.
</div>
<br>
<p style="text-align:center;">After capture, copy the value into the <a href="add_student.php">Add Student</a> page.</p>
</div>
</div>

<script>
const API_KEY = "<?php echo ESP32_API_KEY; ?>";
let currentRequestId = null;

async function startCapture(type) {
    let fingerprintId = "";
    if (type === "fingerprint") {
        fingerprintId = document.getElementById("fingerprint_id_input").value;
        if (!fingerprintId) {
            Swal.fire('Error', 'Enter fingerprint ID first, e.g. 1', 'warning');
            return;
        }
    }

    const formData = new URLSearchParams();
    formData.append("api_key", API_KEY);
    formData.append("request_type", type);
    formData.append("fingerprint_id", fingerprintId);

    const statusEl = document.getElementById("capture_status");
    statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending request to ESP32...';

    try {
        const res = await fetch("../api/start_registration_capture.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: formData.toString()
        });
        const data = await res.json();

        if (data.success) {
            currentRequestId = data.request_id;
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Request sent. Use the ESP32 device to capture now. Waiting...';
            setTimeout(checkResult, 2000);
        } else {
            statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:#e53935;"></i> ' + data.message;
        }
    } catch (err) {
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:#e53935;"></i> Connection error: ' + err.message;
    }
}

async function checkResult() {
    if (!currentRequestId) return;

    const url = "../api/check_registration_capture_result.php?api_key=" + API_KEY + "&request_id=" + currentRequestId;
    const statusEl = document.getElementById("capture_status");

    try {
        const res = await fetch(url);
        const data = await res.json();

        if (data.success && data.status === "Completed") {
            if (data.request_type === "fingerprint") {
                document.getElementById("fingerprint_id_input").value = data.captured_value;
            } else {
                document.getElementById("rfid_uid_input").value = data.captured_value;
            }
            statusEl.innerHTML = '<i class="fas fa-check-circle" style="color:#4caf50;"></i> Capture completed: <strong>' + data.captured_value + '</strong>';
            currentRequestId = null;
            Swal.fire('Success', 'Capture completed successfully!', 'success');
            return;
        }

        if (data.status === "Failed") {
            statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:#e53935;"></i> Capture failed on device.';
            currentRequestId = null;
            return;
        }

        setTimeout(checkResult, 2000);
    } catch (err) {
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:#e53935;"></i> Polling error: ' + err.message;
        setTimeout(checkResult, 2000);
    }
}
</script>

<?php include "includes/footer.php"; ?>
