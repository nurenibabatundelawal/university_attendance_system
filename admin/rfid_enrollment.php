<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $rfid_uid = strtoupper(trim($_POST['rfid_uid']));

    $check = mysqli_prepare($conn, "SELECT id, fullname, matric_no FROM students WHERE rfid_uid = ? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($check, "si", $rfid_uid, $student_id);
    mysqli_stmt_execute($check);
    $check_result = mysqli_stmt_get_result($check);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $existing = mysqli_fetch_assoc($check_result);
        $message = "RFID card already assigned to " . $existing['fullname']; $message_class = "error";
    } else {
        $update = mysqli_prepare($conn, "UPDATE students SET rfid_uid = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $rfid_uid, $student_id);
        if (mysqli_stmt_execute($update)) {
            $message = "RFID card enrolled successfully."; $message_class = "success";
        } else {
            $message = "Failed to enroll RFID card."; $message_class = "error";
        }
    }
}

$students = mysqli_query($conn, "SELECT id, matric_no, fullname, rfid_uid FROM students WHERE status = 'Active' ORDER BY fullname ASC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>RFID Enrollment</h1><p>Assign RFID cards to students</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-id-card"></i> Enroll RFID Card</h3>
<label>Student</label>
<select name="student_id" required>
<option value="">Select Student</option>
<?php mysqli_data_seek($students, 0); while ($s = mysqli_fetch_assoc($students)) { ?>
<option value="<?php echo $s['id']; ?>"><?php echo $s['matric_no'] . " - " . $s['fullname'] . " | RFID: " . ($s['rfid_uid'] ?: 'None'); ?></option>
<?php } ?>
</select>
<br><br>
<label>RFID UID</label>
<input type="text" name="rfid_uid" placeholder="e.g. C7FB4E07" required>
<br><br>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save RFID UID</button>
</form>

<div>
<h3><i class="fas fa-list"></i> Student RFID List</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Matric No</th><th>Name</th><th>RFID UID</th></tr></thead>
<tbody>
<?php mysqli_data_seek($students, 0); while ($s = mysqli_fetch_assoc($students)) { ?>
<tr>
<td><?php echo $s['matric_no']; ?></td>
<td><?php echo $s['fullname']; ?></td>
<td><?php echo $s['rfid_uid'] ? '<span class="badge badge-info">' . $s['rfid_uid'] . '</span>' : '<span class="badge badge-warning">Not assigned</span>'; ?></td>
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
