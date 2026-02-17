<?php
session_start();

// redirect logged-in users
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'admin') header("Location: ../admin/dashboard.php");
    else header("Location: ../cashier/dashboard.php");
    exit;
}
?>

<link rel="stylesheet" href="../assets/style.css">
<div class="auth-wrapper">
<div class="container">
  <h2>Login</h2>

  <?php if (isset($_GET['error'])): ?>
    <p class="msg-error">
      Invalid credentials or account not approved yet
    </p>
  <?php endif; ?>

  <form method="POST" action="login_check.php">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role" required>
      <option value="cashier">Cashier</option>
      <option value="admin">Admin</option>
    </select>
    <button>Login</button>
  </form>

  <div class="link">
    New here? <a href="register.php">Create account</a>
  </div>
</div>
</div>
