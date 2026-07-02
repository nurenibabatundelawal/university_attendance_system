<?php
require_once __DIR__ . '/../php/auth_check.php';
check_login('lecturer');
require_once __DIR__ . '/../php/db_connect.php';

$lecturer_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: attendance_sessions.php");
    exit();
}

$session_id = $_GET['id'];
$end_time = date("H:i:s");

$query = "
    UPDATE attendance_sessions
    SET status = 'Ended', end_time = ?
    WHERE id = ? AND lecturer_id = ? AND status = 'Active'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sii", $end_time, $session_id, $lecturer_id);
mysqli_stmt_execute($stmt);

header("Location: attendance_sessions.php");
exit();
?>
