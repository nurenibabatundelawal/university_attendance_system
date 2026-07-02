<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_semester'])) {
    $semester_name = trim($_POST['semester_name']);
    if ($semester_name == "") {
        $message = "Semester name is required."; $message_class = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM semesters WHERE semester_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $semester_name);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if ($result && mysqli_num_rows($result) > 0) {
            $message = "Semester already exists."; $message_class = "error";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO semesters (semester_name) VALUES (?)");
            mysqli_stmt_bind_param($insert, "s", $semester_name);
            if (mysqli_stmt_execute($insert)) {
                $message = "Semester added successfully."; $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_semester'])) {
    $delete = mysqli_prepare($conn, "DELETE FROM semesters WHERE id = ?");
    mysqli_stmt_bind_param($delete, "i", $_POST['semester_id']);
    if (mysqli_stmt_execute($delete)) {
        $message = "Semester deleted successfully."; $message_class = "success";
    } else {
        $message = "Cannot delete: linked to records."; $message_class = "error";
    }
}

$semesters = mysqli_query($conn, "SELECT * FROM semesters ORDER BY id ASC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Semesters</h1><p>Manage academic semesters</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Semester</h3>
<label>Semester Name</label>
<input type="text" name="semester_name" placeholder="e.g. First Semester" required>
<br><br>
<button type="submit" name="add_semester" class="btn btn-primary"><i class="fas fa-plus"></i> Add Semester</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Semesters</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Semester Name</th><th>Created</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; while ($s = mysqli_fetch_assoc($semesters)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><span class="badge badge-info"><?php echo $s['semester_name']; ?></span></td>
<td><?php echo $s['created_at']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="semester_id" value="<?php echo $s['id']; ?>">
<button type="submit" name="delete_semester" class="btn btn-danger btn-sm btn-delete" data-item="this semester"><i class="fas fa-trash"></i></button>
</form>
</td>
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
