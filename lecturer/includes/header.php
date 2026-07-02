<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['lecturer_id'])) { header("Location: login.php"); exit(); }
require_once __DIR__ . '/../../php/db_connect.php';
$lecturerName = $_SESSION['lecturer_name'] ?? $_SESSION['fullname'] ?? 'Lecturer';
$lecturerEmail = $_SESSION['lecturer_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lecturer Portal — UniAttend</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="../admin/assets/css/style.css">
</head>
<body>
<div class="wrapper">
