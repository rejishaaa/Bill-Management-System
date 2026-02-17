<?php
include "../config/db.php";
session_start();

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $role     = $_POST['role'];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {
        $fullname = mysqli_real_escape_string($conn, $fullname);
        $username = mysqli_real_escape_string($conn, $username);
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // check if username exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if (!$check) die("DB error: " . mysqli_error($conn));

        if (mysqli_num_rows($check) > 0) {
            $error = "Username already exists";
        } else {
            // Cashiers pending by default, Admins approved immediately
            $status = ($role === 'admin') ? 'approved' : 'pending';

            $insert = mysqli_query(
                $conn,
                "INSERT INTO users (fullname, username, password, role, status)
                 VALUES ('$fullname','$username','$hash','$role','$status')"
            );

            if ($insert) {
                if ($status === 'pending') {
                    $success = "Account created! Waiting for admin approval.";
                } else {
                    $success = "Account created and approved. You can log in now.";
                }
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<link rel="stylesheet" href="../assets/style.css">
<div class="auth-wrapper">
<div class="container">
  <h2>Create account</h2>

  <?php if ($error): ?><p class="msg-error"><?= $error ?></p><?php endif; ?>
  <?php if ($success): ?><p class="msg-success"><?= $success ?></p><?php endif; ?>

  <form method="POST">
    <input name="fullname" placeholder="Full name" required>
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm" placeholder="Confirm password" required>

    <select name="role" required>
      <option value="">Select role</option>
      <option value="admin">Admin</option>
      <option value="cashier">Cashier</option>
    </select>

    <button name="register">Register</button>
  </form>

  <div class="link">
    Already have an account? <a href="login.php">Login</a>
  </div>
</div>
</div>
