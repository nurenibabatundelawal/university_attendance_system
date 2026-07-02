<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$id = (int)$deptAdmin['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_read'])) {
    $nid = (int)$_POST['notif_id'];
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id=$nid AND user_id=$id AND user_role='department_admin'");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_id=$id AND user_role='department_admin'");
}

$notifs = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id=$id AND user_role='department_admin' ORDER BY created_at DESC");
?>
<div class="main-content">
<div class="page-title" style="display:flex;justify-content:space-between;align-items:center;">
<div>
<h1>Notifications</h1>
<p>Recent system notifications</p>
</div>
<form method="POST">
<button type="submit" name="mark_all_read" class="btn btn-sm btn-primary"><i class="fas fa-check-double"></i> Mark All Read</button>
</form>
</div>

<div class="form-box">
<?php if ($notifs && mysqli_num_rows($notifs) > 0) { while ($n = mysqli_fetch_assoc($notifs)) { ?>
<div style="display:flex;align-items:flex-start;gap:15px;padding:15px 20px;border-radius:12px;margin-bottom:10px;background:<?php echo $n['is_read'] ? '#f9f9f9' : '#e3f2fd'; ?>;border-left:4px solid <?php echo $n['is_read'] ? '#ccc' : '#1565c0'; ?>;">
<div style="flex:1;">
<strong><?php echo htmlspecialchars($n['title']); ?></strong>
<p style="color:#666;font-size:13px;margin-top:3px;"><?php echo htmlspecialchars($n['message'] ?? ''); ?></p>
<small style="color:#999;"><?php echo date('d M Y h:i A', strtotime($n['created_at'])); ?></small>
</div>
<?php if (!$n['is_read']) { ?>
<form method="POST">
<input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
<button type="submit" name="mark_read" class="btn btn-sm btn-outline"><i class="fas fa-check"></i></button>
</form>
<?php } ?>
</div>
<?php } } else { ?>
<p style="text-align:center;color:#999;padding:30px;">No notifications yet</p>
<?php } ?>
</div>
</div>

<?php include "includes/footer.php"; ?>
