<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../php/email_helper.php';

$message = ""; $message_class = "";

$smtp = getEmailSettings($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_email'])) {
    $host = trim($_POST['smtp_host']);
    $port = (int)$_POST['smtp_port'];
    $email = trim($_POST['smtp_email']);
    $password = trim($_POST['smtp_password']);
    $encryption = $_POST['smtp_encryption'];
    $from_name = trim($_POST['from_name']);
    $api_key = trim($_POST['api_key']);

    if ($smtp) {
        $sql = "UPDATE email_settings SET smtp_host=?, smtp_port=?, smtp_email=?, smtp_encryption=?, from_name=?, api_key=?";
        $types = "sissss";
        $params = [$host, $port, $email, $encryption, $from_name, $api_key];

        if ($password && $password !== '********') {
            $sql .= ", smtp_password=?";
            $types .= "s";
            $params[] = $password;
        }

        $sql .= " WHERE id=?";
        $types .= "i";
        $params[] = $smtp['id'];

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO email_settings (smtp_host, smtp_port, smtp_email, smtp_password, smtp_encryption, from_name, api_key) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sisssss", $host, $port, $email, $password, $encryption, $from_name, $api_key);
        mysqli_stmt_execute($stmt);
    }

    $message = "Email settings saved.";
    $message_class = "success";
    $smtp = getEmailSettings($conn);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['test_email'])) {
    $to = trim($_POST['test_to']);
    $sent = sendEmail($to, "Test Email - UniAttend", "<h2>Test</h2><p>If you see this, email is working!</p>", $conn);
    $message = $sent ? "Test email sent to $to." : "Failed to send test email. Check your settings and API key.";
    $message_class = $sent ? "success" : "error";
}

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Email Settings</h1><p>Configure email sending for password resets and notifications</p></div>

<?php if ($message != "") { ?><div class="msg <?php echo $message_class; ?>"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div><?php } ?>

<div class="form-box">
<div class="form-row">
<form method="POST">
<h3><i class="fas fa-envelope"></i> Email Configuration</h3>

<label>Sender Email</label>
<input type="email" name="smtp_email" value="<?php echo htmlspecialchars($smtp['smtp_email'] ?? ''); ?>" placeholder="noreply@yourdomain.com" required>

<label>From Name</label>
<input type="text" name="from_name" value="<?php echo htmlspecialchars($smtp['from_name'] ?? 'University Attendance System'); ?>">

<hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

<p style="font-weight:600;color:var(--primary);margin-bottom:8px;"><i class="fas fa-cloud"></i> Resend.com API (recommended)</p>
<label>Resend API Key</label>
<input type="password" name="api_key" value="<?php echo $smtp['api_key'] ? '********' : ''; ?>" placeholder="re_xxxxxxxxxxxx">
<small>Get your API key from <a href="https://resend.com/api-keys" target="_blank">resend.com/api-keys</a>. If set, this takes priority over SMTP.</small>

<hr style="margin:18px 0;border:none;border-top:1px solid #eee;">

<p style="font-weight:600;color:var(--primary);margin-bottom:8px;"><i class="fas fa-server"></i> SMTP Fallback</p>
<label>SMTP Host</label>
<input type="text" name="smtp_host" value="<?php echo htmlspecialchars($smtp['smtp_host'] ?? 'smtp.resend.com'); ?>" placeholder="smtp.resend.com">

<label>SMTP Port</label>
<input type="number" name="smtp_port" value="<?php echo $smtp['smtp_port'] ?? 587; ?>" placeholder="587">

<label>SMTP Password</label>
<input type="password" name="smtp_password" placeholder="<?php echo $smtp ? 'Leave blank to keep current' : 'Enter password'; ?>" <?php echo $smtp ? '' : ''; ?>>

<label>Encryption</label>
<select name="smtp_encryption">
<option value="tls" <?php echo ($smtp['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
<option value="ssl" <?php echo ($smtp['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
</select>

<br>
<button type="submit" name="save_email" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
</form>

<form method="POST">
<h3><i class="fas fa-paper-plane"></i> Test Email</h3>
<p style="color:#666;margin-bottom:15px;">Send a test email to verify your configuration.</p>
<label>Send To</label>
<input type="email" name="test_to" placeholder="your@email.com" required>
<br>
<button type="submit" name="test_email" class="btn btn-success"><i class="fas fa-paper-plane"></i> Send Test</button>

<div style="margin-top:18px;padding:14px;background:#f0f4ff;border-radius:10px;font-size:12px;color:#555;">
<strong><i class="fas fa-info-circle"></i> Using Resend?</strong><br>
Enter your API key above (no SMTP details needed) and click Save, then send a test. Resend requires a verified sender domain.
</div>
</form>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
