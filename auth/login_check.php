<?php
session_start();
include "../config/db.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? '';

if (!$username || !$password || !$role) {
    header("Location: login.php?error=1"); exit;
}

// select only approved users
$sql = "SELECT * FROM users 
        WHERE username='$username' 
        AND LOWER(role)=LOWER('$role') 
        AND status='approved' 
        LIMIT 1";

$res = mysqli_query($conn, $sql);
if (!$res) die("DB error: " . mysqli_error($conn));

if (mysqli_num_rows($res) === 1) {
    $user = mysqli_fetch_assoc($res);
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = strtolower($user['role']);

        // redirect based on role
        if ($_SESSION['role'] === 'admin') header("Location: ../admin/dashboard.php");
        else header("Location: ../cashier/dashboard.php");
        exit;
    }
}

header("Location: login.php?error=1"); // fail
exit;
