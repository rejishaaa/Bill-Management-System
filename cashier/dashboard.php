<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'cashier') {
    header("Location: ../auth/login.php");
    exit;
}
?>

<link rel="stylesheet" href="../assets/style.css">

<div class="layout">
  <aside class="sidebar">
    <h2>Cashier · BMS</h2>
    <nav class="nav">
      <a href="billing.php"> Create Bill</a>
      <a href="sales.php">My Sales</a>
      <a href="../auth/logout.php"> Logout</a>
    </nav>
  </aside>

  <main class="content">
    <div class="card">
      <h3>Welcome, <?= $_SESSION['username'] ?></h3>
      <p>Select an action from the sidebar to get started.</p>
    </div>
  </main>
</div>
