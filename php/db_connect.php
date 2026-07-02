<?php
$host = "bmvog3d4wn5nn6u1i36m-mysql.services.clever-cloud.com";
$user = "uhu2jnt6yksmwxim";
$password = "university_attendance_system";
$database = "bmvog3d4wn5nn6u1i36m";
$port = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "university_attendance_system");
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
