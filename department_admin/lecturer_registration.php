<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$message = ""; $message_class = "";
$generatedLink = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmt = mysqli_prepare($conn, "INSERT INTO lecturer_registration_tokens (department_id, token, created_by, expires_at) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isis", $deptId, $token, $deptAdmin['id'], $expires);
    if (mysqli_stmt_execute($stmt)) {
        $generatedLink = "../register_lecturer.php?token=$token";
        $message = "Registration link generated!"; $message_class = "success";
        log_activity($conn, $deptAdmin['id'], 'department_admin', 'Generate Reg Link', 'Generated lecturer registration link');
    }
}

$tokens = mysqli_query($conn, "SELECT * FROM lecturer_registration_tokens WHERE department_id=$deptId ORDER BY created_at DESC LIMIT 20");
?>
<div class="main-content">
<div class="page-title"><h1>Lecturer Registration Links</h1><p>Generate private registration links for lecturers</p></div>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?>">
<i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> 
<?php echo $message; ?>
<?php if ($generatedLink) { ?>
<br><br>
<strong style="word-break:break-all;"><?php echo $generatedLink; ?></strong>
<br>
<button class="btn btn-sm btn-primary" style="margin-top:8px;" onclick="copyLink()"><i class="fas fa-copy"></i> Copy Link</button>
<?php } ?>
</div>
<?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-link"></i> Generate New Link</h3>
<p style="color:#666;margin-bottom:15px;">This creates a private registration link for <?php echo htmlspecialchars($deptAdmin['department_name']); ?>. The link expires in 7 days.</p>
<button type="submit" name="generate" class="btn btn-success"><i class="fas fa-qrcode"></i> Generate Registration Link</button>
</form>

<div>
<h3><i class="fas fa-history"></i> Generated Links</h3>
<div class="table-wrap">
<table>
<thead><tr><th>Token</th><th>Created</th><th>Expires</th><th>Used</th></tr></thead>
<tbody>
<?php if ($tokens && mysqli_num_rows($tokens) > 0) { while ($t = mysqli_fetch_assoc($tokens)) { ?>
<tr>
<td style="font-family:monospace;font-size:12px;"><?php echo substr($t['token'], 0, 20) . '...'; ?></td>
<td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
<td><?php echo $t['expires_at'] ? date('d M Y', strtotime($t['expires_at'])) : 'Never'; ?></td>
<td><?php echo $t['is_used'] ? '<span class="badge badge-success">Used</span>' : '<span class="badge badge-warning">Active</span>'; ?></td>
</tr>
<?php } } else { ?>
<tr><td colspan="4" style="text-align:center;color:#999;padding:30px;">No links generated yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>

<script>
function copyLink() {
    var link = '<?php echo addslashes($generatedLink); ?>';
    navigator.clipboard.writeText(link).then(function() {
        Swal.fire({ icon:'success', title:'Copied!', text:'Link copied to clipboard', timer:1500, showConfirmButton:false });
    });
}
</script>

<?php include "includes/footer.php"; ?>
