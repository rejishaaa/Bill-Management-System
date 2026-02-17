<?php
session_start();
include "../config/db.php";

if (!isset($_GET['id'])) {
    die("Bill ID missing");
}

$bill_id = (int) $_GET['id'];

// fetch bill + cashier
$bill_res = $conn->query("
    SELECT b.*, u.username AS cashier_name
    FROM bills b
    JOIN users u ON b.cashier_id = u.id
    WHERE b.id = $bill_id
");
if (!$bill_res) die("BILL SQL ERROR: ".$conn->error);

$bill = $bill_res->fetch_assoc();

// fetch bill items
$items_res = $conn->query("SELECT * FROM bill_items WHERE bill_id = $bill_id");
if (!$items_res) die("ITEM SQL ERROR: ".$conn->error);
?>

<!DOCTYPE html>
<html>
<head>
<title>Bill #<?= $bill_id ?></title>
<style>
body { font-family: monospace; }
.bill { width: 320px; margin:auto; }
h2, p { text-align:center; margin: 2px 0; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
td { padding: 3px 0; }
.total { border-top:1px dashed #000; font-weight:bold; }
</style>
</head>
<body onload="window.print()">

<div class="bill">
    <h2>🧾 BMS STORE</h2>
    <p>Customer: <?= htmlspecialchars($bill['customer_name']) ?></p>
    <p>Cashier: <?= htmlspecialchars($bill['cashier_name']) ?></p>
    <p><?= $bill['created_at'] ?></p>
    <p>Status: <?= strtoupper($bill['payment_status']) ?></p>

    <table>
        <?php while ($item = $items_res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($item['product_name']) ?> × <?= $item['qty'] ?></td>
            <td align="right">Rs. <?= number_format($item['subtotal'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr class="total">
            <td>Total</td>
            <td align="right">Rs. <?= number_format($bill['total'], 2) ?></td>
        </tr>
    </table>

    <p> Thank you, visit again!</p>
</div>

</body>
</html>
