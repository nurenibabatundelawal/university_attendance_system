<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve'])) {
        $lid = (int)$_POST['lecturer_id'];
        mysqli_query($conn, "UPDATE lecturers SET approval_status='Approved' WHERE id=$lid AND department_id=$deptId");
        $message = "Lecturer approved."; $message_class = "success";
        log_activity($conn, $deptAdmin['id'], 'department_admin', 'Approve Lecturer', "Approved lecturer ID $lid");
    }
    if (isset($_POST['reject'])) {
        $lid = (int)$_POST['lecturer_id'];
        mysqli_query($conn, "UPDATE lecturers SET approval_status='Rejected' WHERE id=$lid AND department_id=$deptId");
        $message = "Lecturer rejected."; $message_class = "warning";
    }
    if (isset($_POST['delete'])) {
        $lid = (int)$_POST['lecturer_id'];
        mysqli_query($conn, "DELETE FROM lecturers WHERE id=$lid AND department_id=$deptId");
        $message = "Lecturer removed."; $message_class = "success";
    }
    if (isset($_POST['add_lecturer'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $staff_id = trim($_POST['staff_id']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO lecturers (staff_id, fullname, email, password, phone, department_id, status, approval_status) VALUES (?, ?, ?, ?, ?, ?, 'Active', 'Approved')");
        mysqli_stmt_bind_param($stmt, "sssssi", $staff_id, $fullname, $email, $password, $phone, $deptId);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Lecturer added successfully."; $message_class = "success";
            log_activity($conn, $deptAdmin['id'], 'department_admin', 'Add Lecturer', "Added $fullname");
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}

$pendingQuery = mysqli_query($conn, "SELECT * FROM lecturers WHERE department_id=$deptId AND approval_status='Pending' ORDER BY created_at DESC");
$lecturers = mysqli_query($conn, "SELECT * FROM lecturers WHERE department_id=$deptId ORDER BY approval_status ASC, fullname ASC");
?>
<div class="main-content">
<div class="page-title"><h1>Lecturer Management</h1><p>Manage lecturers in your department</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<?php if ($pendingQuery && mysqli_num_rows($pendingQuery) > 0) { ?>
<div class="form-box" style="border-left:4px solid #fb8c00;">
<h3><i class="fas fa-clock" style="color:#fb8c00;"></i> Pending Approvals</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Name</th><th>Email</th><th>Staff ID</th><th>Qualification</th><th>Registered</th><th>Action</th></tr></thead>
<tbody>
<?php while ($p = mysqli_fetch_assoc($pendingQuery)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($p['fullname']); ?></strong></td>
<td><?php echo htmlspecialchars($p['email']); ?></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($p['staff_id']); ?></span></td>
<td><?php echo htmlspecialchars($p['qualification'] ?? '-'); ?></td>
<td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="lecturer_id" value="<?php echo $p['id']; ?>">
<button type="submit" name="approve" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
<button type="submit" name="reject" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
</form>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Lecturer</h3>
<label>Full Name</label>
<input type="text" name="fullname" required>
<label>Email</label>
<input type="email" name="email" required>
<label>Phone</label>
<input type="text" name="phone">
<label>Staff ID</label>
<input type="text" name="staff_id" required>
<label>Password</label>
<input type="password" name="password" required>
<br>
<button type="submit" name="add_lecturer" class="btn btn-primary"><i class="fas fa-save"></i> Save Lecturer</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Lecturers</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Name</th><th>Staff ID</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php if ($lecturers && mysqli_num_rows($lecturers) > 0) { while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($l['fullname']); ?></strong></td>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($l['staff_id']); ?></span></td>
<td><?php echo htmlspecialchars($l['email']); ?></td>
<td>
<?php if ($l['approval_status'] == 'Approved') { ?><span class="badge badge-success">Approved</span>
<?php } elseif ($l['approval_status'] == 'Pending') { ?><span class="badge badge-warning">Pending</span>
<?php } else { ?><span class="badge badge-danger">Rejected</span><?php } ?>
</td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="lecturer_id" value="<?php echo $l['id']; ?>">
<button type="submit" name="delete" class="btn btn-danger btn-sm btn-delete" data-item="this lecturer"><i class="fas fa-trash"></i></button>
</form>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="5" style="text-align:center;color:#999;padding:30px;">No lecturers yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
