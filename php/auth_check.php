<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login($role) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: ../login.php");
        exit();
    }
}
?>
