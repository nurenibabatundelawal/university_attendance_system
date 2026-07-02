<?php
require_once __DIR__ . '/../php/db_connect.php';
include "includes/header.php";
include "includes/sidebar.php";
include "includes/navbar.php";

$deptId = (int)$deptAdmin['department_id'];
$search = $_GET['search'] ?? '';
$where = "department_id=$deptId";
if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (fullname LIKE '%$s%' OR matric_no LIKE '%$s%' OR email LIKE '%$s%')";
}
$students = mysqli_query($conn, "SELECT * FROM students WHERE $where ORDER BY matric_no ASC");
?>
<div class="main-content">
<div class="page-title"><h1>All Students</h1><p>View students in your department</p></div>

<div class="form-box">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
<form method="GET" style="display:flex;gap:10px;flex:1;max-width:400px;">
<input type="text" name="search" placeholder="Search by name, matric no, email..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-size:14px;">
<button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
</form>
<span><strong><?php echo $students ? mysqli_num_rows($students) : 0; ?></strong> students found</span>
</div>

<div class="table-wrap">
<table class="datatable">
<thead><tr><th>Matric No</th><th>Name</th><th>Email</th><th>Level</th><th>Fingerprint</th><th>RFID</th><th>Status</th></tr></thead>
<tbody>
<?php if ($students && mysqli_num_rows($students) > 0) { while ($s = mysqli_fetch_assoc($students)) { ?>
<tr>
<td><span class="badge badge-dark"><?php echo htmlspecialchars($s['matric_no']); ?></span></td>
<td><strong><?php echo htmlspecialchars($s['fullname']); ?></strong></td>
<td><?php echo htmlspecialchars($s['email'] ?? '-'); ?></td>
<td><?php echo $s['level']; ?>L</td>
<td><?php echo $s['fingerprint_id'] ? '<span class="badge badge-success">ID: ' . $s['fingerprint_id'] . '</span>' : '<span class="badge badge-danger">Not Set</span>'; ?></td>
<td><?php echo ($s['rfid_uid'] && $s['rfid_uid'] != '') ? '<span class="badge badge-success">' . $s['rfid_uid'] . '</span>' : '<span class="badge badge-danger">Not Set</span>'; ?></td>
<td><span class="badge <?php echo $s['status']=='Active'?'badge-success':'badge-danger'; ?>"><?php echo $s['status']; ?></span></td>
</tr>
<?php } } else { ?>
<tr><td colspan="7" style="text-align:center;color:#999;padding:30px;"><?php echo $search ? 'No students match your search.' : 'No students in this department.'; ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include "includes/footer.php"; ?>
