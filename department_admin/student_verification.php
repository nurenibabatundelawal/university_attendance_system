<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";
$foundStudent = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search_student'])) {
        $matric = trim($_POST['matric_no']);
        $q = mysqli_query($conn, "SELECT * FROM students WHERE matric_no='$matric' AND department_id=$deptId LIMIT 1");
        if ($q && mysqli_num_rows($q) > 0) {
            $foundStudent = mysqli_fetch_assoc($q);
        } else {
            $message = "Student not found in your department."; $message_class = "error";
        }
    }
    if (isset($_POST['set_fingerprint'])) {
        $sid = (int)$_POST['student_id'];
        $fingerId = (int)$_POST['fingerprint_id'];
        $check = mysqli_query($conn, "SELECT id FROM students WHERE fingerprint_id=$fingerId AND id!=$sid LIMIT 1");
        if ($check && mysqli_num_rows($check) > 0) {
            $message = "Fingerprint ID $fingerId is already assigned to another student."; $message_class = "error";
        } else {
            mysqli_query($conn, "UPDATE students SET fingerprint_id=$fingerId WHERE id=$sid AND department_id=$deptId");
            $message = "Fingerprint ID $fingerId assigned to student."; $message_class = "success";
            log_activity($conn, $deptAdmin['id'], 'department_admin', 'Assign Fingerprint', "FP ID $fingerId to student ID $sid");
            $q = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid LIMIT 1");
            if ($q) $foundStudent = mysqli_fetch_assoc($q);
        }
    }
    if (isset($_POST['set_rfid'])) {
        $sid = (int)$_POST['student_id'];
        $rfidUid = strtoupper(trim($_POST['rfid_uid']));
        $check = mysqli_query($conn, "SELECT id FROM students WHERE rfid_uid='$rfidUid' AND id!=$sid LIMIT 1");
        if ($check && mysqli_num_rows($check) > 0) {
            $message = "RFID UID $rfidUid is already assigned to another student."; $message_class = "error";
        } else {
            mysqli_query($conn, "UPDATE students SET rfid_uid='$rfidUid' WHERE id=$sid AND department_id=$deptId");
            $message = "RFID UID assigned to student."; $message_class = "success";
            log_activity($conn, $deptAdmin['id'], 'department_admin', 'Assign RFID', "RFID $rfidUid to student ID $sid");
            $q = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid LIMIT 1");
            if ($q) $foundStudent = mysqli_fetch_assoc($q);
        }
    }
    if (isset($_POST['clear_fingerprint'])) {
        $sid = (int)$_POST['student_id'];
        mysqli_query($conn, "UPDATE students SET fingerprint_id=NULL WHERE id=$sid AND department_id=$deptId");
        $message = "Fingerprint cleared."; $message_class = "success";
        $q = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid LIMIT 1");
        if ($q) $foundStudent = mysqli_fetch_assoc($q);
    }
    if (isset($_POST['clear_rfid'])) {
        $sid = (int)$_POST['student_id'];
        mysqli_query($conn, "UPDATE students SET rfid_uid=NULL WHERE id=$sid AND department_id=$deptId");
        $message = "RFID cleared."; $message_class = "success";
        $q = mysqli_query($conn, "SELECT * FROM students WHERE id=$sid LIMIT 1");
        if ($q) $foundStudent = mysqli_fetch_assoc($q);
    }
}

$searchMatric = $_POST['matric_no'] ?? ($foundStudent ? $foundStudent['matric_no'] : '');
?>
<div class="main-content">
<div class="page-title"><h1>Student Verification</h1><p>Register fingerprint and RFID for students</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<h3><i class="fas fa-search"></i> Find Student</h3>
<form method="POST" style="display:flex;gap:10px;align-items:end;max-width:500px;">
<div style="flex:1;">
<label>Matric Number</label>
<input type="text" name="matric_no" placeholder="e.g. CSC/2026/001" value="<?php echo htmlspecialchars($searchMatric); ?>" required>
</div>
<button type="submit" name="search_student" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
</form>
</div>

<?php if ($foundStudent) { ?>
<div class="form-box">
<h3><i class="fas fa-user-graduate"></i> Student: <?php echo htmlspecialchars($foundStudent['fullname']); ?></h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:20px;">
<div>
<p><strong>Matric No:</strong> <?php echo htmlspecialchars($foundStudent['matric_no']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($foundStudent['email'] ?? '-'); ?></p>
<p><strong>Level:</strong> <?php echo $foundStudent['level']; ?>L</p>
</div>
<div>
<p><strong>Status:</strong> <span class="badge <?php echo $foundStudent['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $foundStudent['status']; ?></span></p>
<p><strong>Fingerprint:</strong> <?php echo $foundStudent['fingerprint_id'] ? '<span class="badge badge-success">ID: ' . $foundStudent['fingerprint_id'] . '</span>' : '<span class="badge badge-danger">Not Registered</span>'; ?></p>
<p><strong>RFID:</strong> <?php echo ($foundStudent['rfid_uid'] && $foundStudent['rfid_uid'] != '') ? '<span class="badge badge-success">' . $foundStudent['rfid_uid'] . '</span>' : '<span class="badge badge-danger">Not Registered</span>'; ?></p>
</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div style="background:#f8faff;padding:20px;border-radius:14px;border:1.5px solid #e0e7f0;">
<h4 style="color:var(--primary);margin-bottom:12px;"><i class="fas fa-fingerprint"></i> Fingerprint Enrollment</h4>
<form method="POST">
<input type="hidden" name="student_id" value="<?php echo $foundStudent['id']; ?>">
<label>Fingerprint ID (1-127)</label>
<input type="number" name="fingerprint_id" min="1" max="127" placeholder="e.g. 5" value="<?php echo $foundStudent['fingerprint_id'] ?? ''; ?>" <?php echo $foundStudent['fingerprint_id'] ? '' : 'required'; ?>>
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
<?php if ($foundStudent['fingerprint_id']) { ?>
<button type="submit" name="clear_fingerprint" class="btn btn-warning btn-sm"><i class="fas fa-undo"></i> Clear</button>
<?php } else { ?>
<button type="submit" name="set_fingerprint" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Register Fingerprint</button>
<?php } ?>
</div>
</form>
</div>

<div style="background:#f8faff;padding:20px;border-radius:14px;border:1.5px solid #e0e7f0;">
<h4 style="color:var(--primary);margin-bottom:12px;"><i class="fas fa-id-card"></i> RFID Enrollment</h4>
<form method="POST">
<input type="hidden" name="student_id" value="<?php echo $foundStudent['id']; ?>">
<label>RFID UID</label>
<input type="text" name="rfid_uid" placeholder="e.g. C7FB4E07" value="<?php echo htmlspecialchars($foundStudent['rfid_uid'] ?? ''); ?>" style="text-transform:uppercase;">
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
<?php if ($foundStudent['rfid_uid'] && $foundStudent['rfid_uid'] != '') { ?>
<button type="submit" name="clear_rfid" class="btn btn-warning btn-sm"><i class="fas fa-undo"></i> Clear</button>
<?php } else { ?>
<button type="submit" name="set_rfid" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Register RFID</button>
<?php } ?>
</div>
</form>
</div>
</div>
</div>
<?php } ?>
</div>

<?php include "includes/footer.php"; ?>
