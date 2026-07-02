<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $faculty_name = trim($_POST['faculty_name']);
    if ($faculty_name == "") {
        $message = "Faculty name is required."; $message_class = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM faculties WHERE faculty_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $faculty_name);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if ($result && mysqli_num_rows($result) > 0) {
            $message = "Faculty already exists."; $message_class = "error";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO faculties (faculty_name) VALUES (?)");
            mysqli_stmt_bind_param($insert, "s", $faculty_name);
            if (mysqli_stmt_execute($insert)) {
                $message = "Faculty added successfully."; $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_faculty'])) {
    $delete = mysqli_prepare($conn, "DELETE FROM faculties WHERE id = ?");
    mysqli_stmt_bind_param($delete, "i", $_POST['faculty_id']);
    if (mysqli_stmt_execute($delete)) {
        $message = "Faculty deleted successfully."; $message_class = "success";
    } else {
        $message = "Cannot delete: linked to departments."; $message_class = "error";
    }
}

$faculties = mysqli_query($conn, "SELECT * FROM faculties ORDER BY faculty_name ASC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Faculties</h1><p>Manage university faculties</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Faculty</h3>
<label>Faculty Name</label>
<input type="text" name="faculty_name" placeholder="e.g. Faculty of Science" required>
<br><br>
<button type="submit" name="add_faculty" class="btn btn-primary"><i class="fas fa-plus"></i> Add Faculty</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Faculties</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Faculty Name</th><th>Date Added</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; while ($f = mysqli_fetch_assoc($faculties)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $f['faculty_name']; ?></td>
<td><?php echo $f['created_at']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="faculty_id" value="<?php echo $f['id']; ?>">
<button type="submit" name="delete_faculty" class="btn btn-danger btn-sm btn-delete" data-item="this faculty"><i class="fas fa-trash"></i></button>
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
