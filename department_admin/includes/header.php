<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../php/auth_check.php';
require_once __DIR__ . '/../../php/department_auth.php';
check_department_admin();
$deptAdmin = get_department_admin($conn);
$unreadNotif = $deptAdmin ? count_notifications($conn, $deptAdmin['id'], 'department_admin') : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Department Admin - University Attendance System</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../admin/assets/css/style.css">
</head>
<body>
<div class="wrapper">
