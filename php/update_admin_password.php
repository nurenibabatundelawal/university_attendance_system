<?php
require_once __DIR__ . '/db_connect.php';

$email = "admin@example.com";
$new_password = password_hash("admin123", PASSWORD_DEFAULT);

$query = "UPDATE admins SET password = ? WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $new_password, $email);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin password updated successfully. You can now delete this file.";
} else {
    echo "Error updating password.";
}
?>
