<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('admin');
require_once __DIR__ . '/../php/db_connect.php';
require_once __DIR__ . '/../php/department_auth.php';

$message = ""; $message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_dept_admin'])) {
    $dept_id = (int)$_POST['dept_id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $staff_id = trim($_POST['staff_id']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM department_admins WHERE department_id=$dept_id AND status='Active' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $message = "This department already has an active admin. Revoke them first."; $message_class = "error";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO department_admins (department_id, fullname, email, password, phone, staff_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        mysqli_stmt_bind_param($stmt, "isssss", $dept_id, $fullname, $email, $password, $phone, $staff_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Department Admin created successfully."; $message_class = "success";
            log_activity($conn, $_SESSION['user_id'], 'admin', 'Create Dept Admin', "Created admin for department ID $dept_id");
        } else {
            $message = "Error: " . mysqli_error($conn); $message_class = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_lecturer_as_admin'])) {
    $dept_id = (int)$_POST['dept_id'];
    $lecturer_id = (int)$_POST['lecturer_id'];
    $check = mysqli_query($conn, "SELECT id FROM department_admins WHERE department_id=$dept_id AND status='Active' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $message = "This department already has an active admin. Revoke them first."; $message_class = "error";
    } else {
        $lec = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM lecturers WHERE id=$lecturer_id LIMIT 1"));
        if ($lec) {
            $defaultPass = password_hash('deptadmin123', PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO department_admins (department_id, fullname, email, password, phone, staff_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
            mysqli_stmt_bind_param($stmt, "isssss", $dept_id, $lec['fullname'], $lec['email'], $defaultPass, $lec['phone'], $lec['staff_id']);
            if (mysqli_stmt_execute($stmt)) {
                $message = "{$lec['fullname']} assigned as Department Admin. Default password: deptadmin123"; $message_class = "success";
                log_activity($conn, $_SESSION['user_id'], 'admin', 'Assign Dept Admin', "Assigned {$lec['fullname']} as admin for department ID $dept_id");
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['revoke_dept_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    mysqli_query($conn, "UPDATE department_admins SET status='Inactive' WHERE id=$admin_id");
    $message = "Department Admin access revoked. You can now assign a new admin."; $message_class = "success";
    log_activity($conn, $_SESSION['user_id'], 'admin', 'Revoke Dept Admin', "Revoked admin ID $admin_id");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reactivate_dept_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    $dept_id = (int)$_POST['dept_id'];
    $check = mysqli_query($conn, "SELECT id FROM department_admins WHERE department_id=$dept_id AND status='Active' AND id!=$admin_id LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $message = "Another admin is already active in this department. Revoke them first."; $message_class = "error";
    } else {
        mysqli_query($conn, "UPDATE department_admins SET status='Active' WHERE id=$admin_id");
        $message = "Department Admin reactivated."; $message_class = "success";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_token'])) {
    $dept_id = (int)$_POST['dept_id'];
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmt = mysqli_prepare($conn, "INSERT INTO lecturer_registration_tokens (department_id, token, created_by, expires_at) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isis", $dept_id, $token, $_SESSION['user_id'], $expires);
    if (mysqli_stmt_execute($stmt)) {
        $link = "../register_lecturer.php?token=$token";
        $message = "Registration link generated: <a href='$link' target='_blank'>$link</a>"; $message_class = "success";
        log_activity($conn, $_SESSION['user_id'], 'admin', 'Generate Reg Token', "Generated token for department ID $dept_id");
    } else {
        $message = "Error: " . mysqli_error($conn); $message_class = "error";
    }
}

$deptQuery = mysqli_query($conn, "SELECT d.*, f.faculty_name,
    (SELECT id FROM department_admins WHERE department_id=d.id AND status='Active' LIMIT 1) as admin_id,
    (SELECT fullname FROM department_admins WHERE department_id=d.id AND status='Active' LIMIT 1) as admin_name,
    (SELECT email FROM department_admins WHERE department_id=d.id AND status='Active' LIMIT 1) as admin_email,
    (SELECT staff_id FROM department_admins WHERE department_id=d.id AND status='Active' LIMIT 1) as admin_staff_id,
    (SELECT COUNT(*) FROM lecturers WHERE department_id=d.id) as lecturer_count,
    (SELECT COUNT(*) FROM students WHERE department_id=d.id) as student_count
    FROM departments d LEFT JOIN faculties f ON d.faculty_id=f.id ORDER BY d.department_name ASC");

$inactiveAdmins = mysqli_query($conn, "SELECT da.*, d.department_name FROM department_admins da LEFT JOIN departments d ON da.department_id=d.id WHERE da.status='Inactive' ORDER BY da.created_at DESC");

include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";
?>
<div class="main-content">
<div class="page-title"><h1>Department Management</h1><p>Assign, revoke, and manage department admins</p></div>

<?php if ($message != "") { ?>
<div class="msg <?php echo $message_class; ?> msg-auto-hide"><i class="fas fa-<?php echo $message_class=='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo $message; ?></div>
<?php } ?>

<div class="form-box">
<h3><i class="fas fa-university"></i> All Departments</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Department</th><th>Faculty</th><th>Dept Admin</th><th>Status</th><th>Lecturers</th><th>Students</th><th>Actions</th></tr></thead>
<tbody>
<?php while ($d = mysqli_fetch_assoc($deptQuery)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($d['department_name']); ?></strong></td>
<td><span class="badge badge-info"><?php echo htmlspecialchars($d['faculty_name']); ?></span></td>
<td>
<?php if ($d['admin_name']) { ?>
<strong><?php echo htmlspecialchars($d['admin_name']); ?></strong><br>
<small><?php echo htmlspecialchars($d['admin_email'] . ' | ' . $d['admin_staff_id']); ?></small>
<?php } else { ?>
<span class="badge badge-danger">No Active Admin</span>
<?php } ?>
</td>
<td><?php echo $d['admin_name'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Not Assigned</span>'; ?></td>
<td><?php echo $d['lecturer_count']; ?></td>
<td><?php echo $d['student_count']; ?></td>
<td>
<div style="display:flex;gap:5px;flex-wrap:wrap;">
<?php if ($d['admin_name']) { ?>
<form method="POST" style="display:inline;" onsubmit="return confirmRevoke('<?php echo htmlspecialchars($d['admin_name'], ENT_QUOTES); ?>')">
<input type="hidden" name="admin_id" value="<?php echo $d['admin_id']; ?>">
<button type="submit" name="revoke_dept_admin" class="btn btn-danger btn-sm"><i class="fas fa-user-slash"></i> Revoke</button>
</form>
<?php } else { ?>
<button class="btn btn-primary btn-sm" onclick="showCreateAdmin(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['department_name'], ENT_QUOTES); ?>')"><i class="fas fa-user-plus"></i> New Admin</button>
<button class="btn btn-info btn-sm" onclick="showAssignLecturer(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['department_name'], ENT_QUOTES); ?>')"><i class="fas fa-user-tag"></i> From Lecturer</button>
<?php } ?>
<button class="btn btn-success btn-sm" onclick="showGenerateToken(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['department_name'], ENT_QUOTES); ?>')"><i class="fas fa-link"></i> Reg Link</button>
</div>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<?php if ($inactiveAdmins && mysqli_num_rows($inactiveAdmins) > 0) { ?>
<div class="form-box">
<h3><i class="fas fa-history"></i> Previous (Revoked) Department Admins</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Revoked On</th><th>Action</th></tr></thead>
<tbody>
<?php while ($ia = mysqli_fetch_assoc($inactiveAdmins)) { ?>
<tr>
<td><strong><?php echo htmlspecialchars($ia['fullname']); ?></strong></td>
<td><?php echo htmlspecialchars($ia['email']); ?></td>
<td><?php echo htmlspecialchars($ia['department_name']); ?></td>
<td><?php echo date('d M Y', strtotime($ia['created_at'])); ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="admin_id" value="<?php echo $ia['id']; ?>">
<input type="hidden" name="dept_id" value="<?php echo $ia['department_id']; ?>">
<button type="submit" name="reactivate_dept_admin" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Reactivate</button>
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
<h3><i class="fas fa-link"></i> Lecturer Registration Tokens</h3>
<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Token</th><th>Department</th><th>Created</th><th>Expires</th><th>Status</th></tr></thead>
<tbody>
<?php
$tokens = mysqli_query($conn, "SELECT lrt.*, d.department_name FROM lecturer_registration_tokens lrt LEFT JOIN departments d ON lrt.department_id=d.id ORDER BY lrt.created_at DESC LIMIT 20");
if ($tokens && mysqli_num_rows($tokens) > 0) {
    while ($t = mysqli_fetch_assoc($tokens)) {
        $expired = strtotime($t['expires_at']) < time();
?>
<tr>
<td><code style="font-size:11px;"><?php echo substr($t['token'], 0, 20); ?>...</code></td>
<td><?php echo htmlspecialchars($t['department_name']); ?></td>
<td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
<td><?php echo date('d M Y', strtotime($t['expires_at'])); ?></td>
<td><?php echo $expired ? '<span class="badge badge-danger">Expired</span>' : '<span class="badge badge-success">Active</span>'; ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

<!-- Create New Admin Modal -->
<div id="adminModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;justify-content:center;align-items:center;">
<div style="background:#fff;padding:30px;border-radius:18px;width:90%;max-width:450px;max-height:90vh;overflow-y:auto;">
<span style="float:right;font-size:24px;cursor:pointer;" onclick="closeModal('adminModal')">&times;</span>
<h3><i class="fas fa-user-tie"></i> Create Department Admin</h3>
<p id="adminDeptName" style="color:#666;margin-bottom:15px;"></p>
<form method="POST">
<input type="hidden" name="dept_id" id="adminDeptId">
<label>Full Name</label>
<input type="text" name="fullname" required><br><br>
<label>Staff ID</label>
<input type="text" name="staff_id" placeholder="e.g. ADM/CS/001" required><br><br>
<label>Email</label>
<input type="email" name="email" required><br><br>
<label>Phone</label>
<input type="text" name="phone"><br><br>
<label>Password</label>
<div style="position:relative;">
<input type="password" name="password" id="adminPass" required style="padding-right:40px;">
<span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#888;" onclick="togglePass('adminPass', this)"><i class="fas fa-eye"></i></span>
</div><br>
<button type="submit" name="create_dept_admin" class="btn btn-primary"><i class="fas fa-save"></i> Create Admin</button>
</form>
</div>
</div>

<!-- Assign from Lecturer Modal -->
<div id="assignModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;justify-content:center;align-items:center;">
<div style="background:#fff;padding:30px;border-radius:18px;width:90%;max-width:550px;max-height:80vh;overflow-y:auto;">
<span style="float:right;font-size:24px;cursor:pointer;" onclick="closeModal('assignModal')">&times;</span>
<h3><i class="fas fa-user-tag"></i> Assign Lecturer as Department Admin</h3>
<p id="assignDeptName" style="color:#666;margin-bottom:15px;"></p>
<div id="assignLecturerList"></div>
</div>
</div>

<!-- Generate Token Modal -->
<div id="tokenModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;justify-content:center;align-items:center;">
<div style="background:#fff;padding:30px;border-radius:18px;width:90%;max-width:450px;">
<span style="float:right;font-size:24px;cursor:pointer;" onclick="closeModal('tokenModal')">&times;</span>
<h3><i class="fas fa-link"></i> Generate Lecturer Registration Link</h3>
<p id="tokenDeptName" style="color:#666;margin-bottom:15px;"></p>
<form method="POST">
<input type="hidden" name="dept_id" id="tokenDeptId">
<p style="margin-bottom:15px;">This link will expire in 7 days. Share it with lecturers for self-registration.</p>
<button type="submit" name="generate_token" class="btn btn-success"><i class="fas fa-qrcode"></i> Generate Link</button>
</form>
</div>
</div>

<script>
function showCreateAdmin(id, name) {
    document.getElementById('adminDeptId').value = id;
    document.getElementById('adminDeptName').textContent = 'Department: ' + name;
    document.getElementById('adminModal').style.display = 'flex';
}
function showGenerateToken(id, name) {
    document.getElementById('tokenDeptId').value = id;
    document.getElementById('tokenDeptName').textContent = 'Department: ' + name;
    document.getElementById('tokenModal').style.display = 'flex';
}
function togglePass(inputId, el) {
    let inp = document.getElementById(inputId);
    if (inp.type === 'password') { inp.type = 'text'; el.innerHTML = '<i class="fas fa-eye-slash"></i>'; }
    else { inp.type = 'password'; el.innerHTML = '<i class="fas fa-eye"></i>'; }
}
function showAssignLecturer(id, name) {
    document.getElementById('assignDeptName').textContent = 'Department: ' + name;
    fetch('<?php echo $_SERVER['PHP_SELF']; ?>?ajax=get_lecturers&dept_id=' + id)
    .then(r => r.text()).then(html => {
        document.getElementById('assignLecturerList').innerHTML = html;
        document.getElementById('assignModal').style.display = 'flex';
    });
}
function confirmRevoke(name) {
    return confirm('Revoke "' + name + '" as department admin? They will lose access immediately. You can assign a new admin afterwards.');
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(e) {
    if (e.target.id === 'adminModal') closeModal('adminModal');
    if (e.target.id === 'assignModal') closeModal('assignModal');
    if (e.target.id === 'tokenModal') closeModal('tokenModal');
};
</script>

<?php if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_lecturers') {
    $dept_id = (int)$_GET['dept_id'];
    $lecturers = mysqli_query($conn, "SELECT * FROM lecturers WHERE department_id=$dept_id AND approval_status='Approved' ORDER BY fullname ASC");
    if ($lecturers && mysqli_num_rows($lecturers) > 0) {
        while ($l = mysqli_fetch_assoc($lecturers)) { ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;margin-bottom:8px;background:#f8faff;border-radius:10px;">
<div>
<strong><?php echo htmlspecialchars($l['fullname']); ?></strong><br>
<small><?php echo htmlspecialchars($l['staff_id'] . ' | ' . $l['email']); ?></small>
</div>
<form method="POST">
<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
<input type="hidden" name="lecturer_id" value="<?php echo $l['id']; ?>">
<button type="submit" name="assign_lecturer_as_admin" class="btn btn-success btn-sm"><i class="fas fa-user-check"></i> Assign</button>
</form>
</div>
<?php }
    } else {
        echo '<p style="text-align:center;color:#999;padding:20px;">No approved lecturers in this department.</p>';
    }
    exit();
}
include "includes/footer.php"; ?>
