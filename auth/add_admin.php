<?php
include "../config/db.php"; // adjust path if needed

$fullname = "Rejisha";
$username = "rejisha";
$password = "1234567890"; // your chosen password
$role     = "admin";
$status   = "approved";

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (fullname, username, password, role, status) 
        VALUES ('$fullname', '$username', '$hash', '$role', '$status')";

if (mysqli_query($conn, $sql)) {
    echo "Admin user added successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
