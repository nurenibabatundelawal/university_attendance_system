<?php
session_start();
require_once 'php/db_connect.php';
require_once 'php/email_helper.php';

$error = '';
$success = '';

$departments = mysqli_query($conn, "SELECT id, department_name FROM departments ORDER BY department_name");
$sessions = mysqli_query($conn, "SELECT id, session_name FROM academic_sessions ORDER BY session_name DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $matric_no = trim($_POST['matric_no']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? '';
    $department_id = (int)$_POST['department_id'];
    $level = trim($_POST['level']);
    $academic_session_id = (int)$_POST['academic_session_id'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE matric_no = ? OR email = ? LIMIT 1");
        $check->bind_param("ss", $matric_no, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'A student with this matric number or email already exists.';
        } else {
            $passport = '';
            if (isset($_FILES['passport']) && $_FILES['passport']['error'] == 0) {
                $allowed = ['jpg','jpeg','png','gif','webp'];
                $ext = strtolower(pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $passport = $matric_no . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['passport']['tmp_name'], 'uploads/students/' . $passport);
                } else {
                    $error = 'Passport must be JPG, PNG, GIF or WebP.';
                }
            }

            if (!$error) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO students (matric_no, fullname, email, password, phone, gender, department_id, level, academic_session_id, passport, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                $stmt->bind_param("ssssssiiss", $matric_no, $fullname, $email, $hashed, $phone, $gender, $department_id, $level, $academic_session_id, $passport);
                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now sign in.';

                    $dept = mysqli_query($conn, "SELECT department_name FROM departments WHERE id = $department_id");
                    $deptName = $dept ? mysqli_fetch_assoc($dept)['department_name'] : 'your department';
                    $subject = "Welcome to UniAttend — Next Steps";
                    $body = "<html><body style='font-family:Arial;padding:20px;'>
                        <h2 style='color:#1565c0;'>Welcome, $fullname!</h2>
                        <p>Your account has been created successfully.</p>
                        <p><strong>Matric Number:</strong> $matric_no</p>
                        <hr>
                        <h3>Next Steps — Please visit the $deptName office:</h3>
                        <ol>
                            <li><strong>Verify your identity</strong> — Present your student ID or this email at the department.</li>
                            <li><strong>Collect your RFID card</strong> — You'll be issued a card for attendance tracking.</li>
                            <li><strong>Enroll your fingerprint</strong> — Visit the department to have your fingerprint captured on the attendance device.</li>
                        </ol>
                        <p>Once verified and enrolled, you can use your RFID card or fingerprint to mark attendance for all your registered courses.</p>
                        <p>If you have any questions, please contact your department office.</p>
                        <p>— UniAttend Team</p>
                        </body></html>";
                    if (!sendEmail($email, $subject, $body, $conn)) {
                        $success .= ' Also, check your email for next-step instructions (or email settings may not be configured).';
                    }
                } else {
                    $error = 'Registration failed: ' . $stmt->error;
                }
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration — UniAttend</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#0f172a; min-height:100vh; display:flex; }
.left-panel {
  flex:1; display:flex; flex-direction:column; justify-content:center; padding:60px;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  position:relative; overflow:hidden;
}
.left-panel::before {
  content:''; position:absolute; width:500px; height:500px; border-radius:50%;
  background:radial-gradient(circle, rgba(59,130,246,.08) 0%, transparent 70%);
  top:-100px; right:-100px;
}
.left-panel::after {
  content:''; position:absolute; width:400px; height:400px; border-radius:50%;
  background:radial-gradient(circle, rgba(139,92,246,.06) 0%, transparent 70%);
  bottom:-80px; left:-80px;
}
.left-content { position:relative; z-index:1; max-width:480px; }
.left-content .logo { display:flex; align-items:center; gap:12px; margin-bottom:40px; }
.left-content .logo i { font-size:32px; color:#3b82f6; }
.left-content .logo span { font-size:22px; font-weight:700; color:#fff; }
.left-content .logo span span { color:#3b82f6; }
.left-content h1 { font-size:36px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:16px; }
.left-content p { color:#94a3b8; font-size:15px; line-height:1.7; margin-bottom:32px; }
.feature-list { display:flex; flex-direction:column; gap:16px; }
.feature-item { display:flex; align-items:center; gap:14px; color:#cbd5e1; font-size:14px; }
.feature-item i { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; }
.fi-blue { background:rgba(59,130,246,.15); color:#60a5fa; }
.fi-gold { background:rgba(245,158,11,.15); color:#fbbf24; }
.fi-green { background:rgba(16,185,129,.15); color:#34d399; }
.fi-purple { background:rgba(139,92,246,.15); color:#a78bfa; }

.right-panel {
  width:520px; display:flex; flex-direction:column; justify-content:center; padding:50px 45px;
  background:#fff; position:relative; overflow-y:auto; max-height:100vh;
}
.right-panel .back-link { position:absolute; top:25px; left:25px; color:#64748b; font-size:14px; text-decoration:none; display:flex; align-items:center; gap:6px; font-weight:500; transition:.2s; }
.right-panel .back-link:hover { color:#0f172a; }
.right-panel h2 { font-size:24px; font-weight:700; color:#0f172a; margin-bottom:6px; }
.right-panel .subtitle { color:#64748b; font-size:14px; margin-bottom:22px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:5px; }
.form-group .input-wrap { position:relative; }
.form-group .input-wrap i:not(.toggle-pass):not(.upload-icon) { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:15px; }
.form-group input:not([type="file"]), .form-group select {
  width:100%; padding:11px 14px 11px 42px; border:1.5px solid #e2e8f0; border-radius:10px;
  font-size:14px; outline:none; transition:.2s; background:#f8fafc; font-family:'Inter',sans-serif;
  -webkit-appearance:none; appearance:none;
}
.form-group select { background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 12px center; padding-right:36px; }
.form-group input:focus, .form-group select:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); background:#fff; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group .toggle-pass { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8; font-size:15px; }
.form-footer { text-align:center; margin-top:20px; color:#64748b; font-size:14px; }
.form-footer a { color:#3b82f6; font-weight:600; text-decoration:none; }
.form-footer a:hover { text-decoration:underline; }
.btn-register { width:100%; padding:14px; background:#0f172a; color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:600; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; font-family:'Inter',sans-serif; }
.btn-register:hover { background:#1e293b; }
.error-msg { background:#fef2f2; color:#dc2626; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:8px; border:1px solid #fecaca; }
.success-msg { background:#f0fdf4; color:#16a34a; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:8px; border:1px solid #bbf7d0; }
.file-upload { position:relative; border:2px dashed #e2e8f0; border-radius:10px; padding:24px; text-align:center; cursor:pointer; transition:.2s; background:#fafafa; }
.file-upload:hover { border-color:#3b82f6; background:#f0f4ff; }
.file-upload input { position:absolute; inset:0; opacity:0; cursor:pointer; }
.file-upload i { font-size:28px; color:#94a3b8; margin-bottom:8px; }
.file-upload p { font-size:13px; color:#64748b; }
.file-upload p strong { color:#3b82f6; }
.file-upload .preview { display:none; margin-top:12px; }
.file-upload .preview img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #e2e8f0; }
@media(max-width:960px) {
  body { flex-direction:column; }
  .left-panel { padding:30px 25px; }
  .right-panel { width:100%; padding:35px 25px; max-height:none; }
  .form-row { grid-template-columns:1fr; }
}
</style>
</head>
<body>
<div class="left-panel">
<div class="left-content">
<div class="logo"><i class="fas fa-graduation-cap"></i><span>Uni<span>Attend</span></span></div>
<h1>Join the<br>platform</h1>
<p>Create your student account to track attendance, view courses, and stay on top of your academic progress.</p>
<div class="feature-list">
<div class="feature-item"><i class="fas fa-user-plus fi-blue"></i> Free self-registration</div>
<div class="feature-item"><i class="fas fa-fingerprint fi-gold"></i> Biometric-ready account</div>
<div class="feature-item"><i class="fas fa-chart-simple fi-green"></i> Real-time attendance tracking</div>
<div class="feature-item"><i class="fas fa-shield-halved fi-purple"></i> Secure password protection</div>
</div>
</div>
</div>

<div class="right-panel">
<a href="student_login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to sign in</a>
<h2>Create account</h2>
<p class="subtitle">Fill in your details to register</p>

<?php if ($error != '') { ?>
<div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php } ?>
<?php if ($success != '') { ?>
<div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">
<div class="form-group">
<label>Full Name</label>
<div class="input-wrap">
<i class="fas fa-user"></i>
<input type="text" name="fullname" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
</div>
</div>

<div class="form-row">
<div class="form-group">
<label>Matric Number</label>
<div class="input-wrap">
<i class="fas fa-id-card"></i>
<input type="text" name="matric_no" placeholder="e.g. CSC/2026/001" required value="<?php echo htmlspecialchars($_POST['matric_no'] ?? ''); ?>">
</div>
</div>
<div class="form-group">
<label>Email</label>
<div class="input-wrap">
<i class="fas fa-envelope"></i>
<input type="email" name="email" placeholder="your@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
</div>
</div>
</div>

<div class="form-row">
<div class="form-group">
<label>Phone (optional)</label>
<div class="input-wrap">
<i class="fas fa-phone"></i>
<input type="tel" name="phone" placeholder="080xxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
</div>
</div>
<div class="form-group">
<label>Gender (optional)</label>
<div class="input-wrap" style="position:relative;">
<i class="fas fa-venus-mars" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;z-index:1;pointer-events:none;"></i>
<select name="gender" style="padding-left:42px;">
<option value="">Select gender</option>
<option value="Male" <?php echo ($_POST['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
<option value="Female" <?php echo ($_POST['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
</select>
</div>
</div>
</div>

<div class="form-row">
<div class="form-group">
<label>Department</label>
<div class="input-wrap" style="position:relative;">
<i class="fas fa-building" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;z-index:1;pointer-events:none;"></i>
<select name="department_id" required style="padding-left:42px;">
<option value="">Select department</option>
<?php while ($d = mysqli_fetch_assoc($departments)) { ?>
<option value="<?php echo $d['id']; ?>" <?php echo ($_POST['department_id'] ?? '') == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['department_name']); ?></option>
<?php } ?>
</select>
</div>
</div>
<div class="form-group">
<label>Level</label>
<div class="input-wrap">
<i class="fas fa-layer-group"></i>
<select name="level" required style="padding-left:42px;">
<option value="">Select level</option>
<?php foreach (['100','200','300','400','500','600','700'] as $l) { ?>
<option value="<?php echo $l; ?>" <?php echo ($_POST['level'] ?? '') == $l ? 'selected' : ''; ?>><?php echo $l; ?> Level</option>
<?php } ?>
</select>
</div>
</div>
</div>

<div class="form-group">
<label>Academic Session</label>
<div class="input-wrap" style="position:relative;">
<i class="fas fa-calendar" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:15px;z-index:1;pointer-events:none;"></i>
<select name="academic_session_id" required style="padding-left:42px;">
<option value="">Select session</option>
<?php while ($s = mysqli_fetch_assoc($sessions)) { ?>
<option value="<?php echo $s['id']; ?>" <?php echo ($_POST['academic_session_id'] ?? '') == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['session_name']); ?></option>
<?php } ?>
</select>
</div>
</div>

<div class="form-group">
<label>Passport Photo (optional)</label>
<label class="file-upload">
<input type="file" name="passport" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewFile(this)">
<i class="fas fa-camera"></i>
<p><strong>Click to upload</strong> or drag and drop</p>
<p style="font-size:11px;color:#94a3b8;margin-top:4px;">JPG, PNG, GIF or WebP</p>
<div class="preview" id="passportPreview"><img src="" alt="Preview"></div>
</label>
</div>

<div class="form-row">
<div class="form-group">
<label>Password</label>
<div class="input-wrap">
<i class="fas fa-lock"></i>
<input type="password" name="password" id="pass1" placeholder="Min. 6 characters" minlength="6" required>
<span class="toggle-pass" onclick="togglePass('pass1',this)"><i class="fas fa-eye"></i></span>
</div>
</div>
<div class="form-group">
<label>Confirm Password</label>
<div class="input-wrap">
<i class="fas fa-lock"></i>
<input type="password" name="confirm" id="pass2" placeholder="Repeat password" minlength="6" required>
<span class="toggle-pass" onclick="togglePass('pass2',this)"><i class="fas fa-eye"></i></span>
</div>
</div>
</div>

<button type="submit" class="btn-register"><i class="fas fa-user-plus"></i> Create Account</button>

<div class="form-footer">
Already have an account? <a href="student_login.php">Sign in</a>
</div>
</form>
</div>

<script>
function togglePass(id, el) {
  let inp = document.getElementById(id);
  let icon = el.querySelector('i');
  if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash'; }
  else { inp.type = 'password'; icon.className = 'fas fa-eye'; }
}
function previewFile(input) {
  const preview = document.getElementById('passportPreview');
  const img = preview.querySelector('img');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
</body>
</html>
