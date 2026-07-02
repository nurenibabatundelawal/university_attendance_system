<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_session'])) {
    $session_name = trim($_POST['session_name']);
    if ($session_name == "") {
        $message = "Session name is required."; $message_class = "error";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM academic_sessions WHERE session_name = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $session_name);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if ($result && mysqli_num_rows($result) > 0) {
            $message = "Academic session already exists."; $message_class = "error";
        } else {
            $insert = mysqli_prepare($conn, "INSERT INTO academic_sessions (session_name) VALUES (?)");
            mysqli_stmt_bind_param($insert, "s", $session_name);
            if (mysqli_stmt_execute($insert)) {
                $message = "Academic session added successfully."; $message_class = "success";
            } else {
                $message = "Error: " . mysqli_error($conn); $message_class = "error";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_session'])) {
    $delete = mysqli_prepare($conn, "DELETE FROM academic_sessions WHERE id = ?");
    mysqli_stmt_bind_param($delete, "i", $_POST['session_id']);
    if (mysqli_stmt_execute($delete)) {
        $message = "Session deleted successfully."; $message_class = "success";
    } else {
        $message = "Cannot delete: linked to records."; $message_class = "error";
    }
}

$sessions = mysqli_query($conn, "SELECT * FROM academic_sessions ORDER BY id DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Academic Sessions</h1><p>Manage academic sessions / semesters</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-plus-circle"></i> Add Session</h3>
<label>Session Name</label>
<input type="text" name="session_name" placeholder="e.g. 2025/2026" required>
<br><br>
<button type="submit" name="add_session" class="btn btn-primary"><i class="fas fa-plus"></i> Add Session</button>
</form>

<div>
<h3><i class="fas fa-list"></i> All Sessions</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>#</th><th>Session Name</th><th>Created</th><th>Action</th></tr></thead>
<tbody>
<?php $i=1; while ($s = mysqli_fetch_assoc($sessions)) { ?>
<tr>
<td><?php echo $i++; ?></td>
<td><span class="badge badge-info"><?php echo $s['session_name']; ?></span></td>
<td><?php echo $s['created_at']; ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
<button type="submit" name="delete_session" class="btn btn-danger btn-sm btn-delete" data-item="this session"><i class="fas fa-trash"></i></button>
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
