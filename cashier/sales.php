<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'cashier') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$res = $conn->query("
  SELECT * FROM bills
  WHERE cashier_id = $user_id
  ORDER BY created_at DESC
");

if (!$res) die("SQL ERROR: " . $conn->error);
?>

<link rel="stylesheet" href="../assets/style.css">

<h2>My Sales</h2>

<div class="sales-container" style="display:flex; flex-wrap:wrap; gap:15px;">
<?php if($res->num_rows == 0): ?>
    <p>No transactions yet.</p>
<?php endif; ?>

<?php while($b = $res->fetch_assoc()): ?>
<div class="card" style="padding:15px; border:1px solid #ccc; border-radius:8px; width:220px;">
    <p><strong>Customer:</strong> <?= htmlspecialchars($b['customer_name']) ?></p>
    <p><strong>Total:</strong> Rs. <?= number_format($b['total'],2) ?></p>
    <p><strong>Status:</strong> <?= strtoupper($b['payment_status']) ?></p>
    <p><strong>Date:</strong> <?= $b['created_at'] ?></p>

    <a href="print_bill.php?id=<?= $b['id'] ?>" target="_blank">
        <button style="margin-top:5px;">Print</button>
    </a>

    <a href="delete_transaction.php?id=<?= $b['id'] ?>" onclick="return confirm('Are you sure?')">
        <button style="margin-top:5px; background:red; color:white;">Delete</button>
    </a>
</div>
<?php endwhile; ?>
</div>
