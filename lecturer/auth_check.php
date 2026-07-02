<?php
session_start();

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit();
}
?>