<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

$customer_id = $_POST['customer_id'] ?? '';
$total = $_POST['total'] ?? '';

if($customer_id && $total) {
    $stmt = $conn->prepare("INSERT INTO sales (customer_id, total, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $customer_id, $total); // i = int, d = double
    $stmt->execute();
}
?>
