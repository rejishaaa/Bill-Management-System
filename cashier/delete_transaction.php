<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'cashier') {
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_GET['id'])) die("Bill ID missing");

$bill_id = (int)$_GET['id'];

// 1️⃣ restore stock
$items = $conn->query("SELECT * FROM bill_items WHERE bill_id = $bill_id");
while($item = $items->fetch_assoc()){
    $qty = (int)$item['qty'];
    $product_name = $item['product_name'];
    // increase stock
    $conn->query("UPDATE products SET stock = stock + $qty WHERE name = '".$product_name."'");
}

// 2️⃣ delete bill items
$conn->query("DELETE FROM bill_items WHERE bill_id = $bill_id");

// 3️⃣ delete bill itself
$conn->query("DELETE FROM bills WHERE id = $bill_id");

header("Location: sales.php");
