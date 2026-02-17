<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

$result = $conn->query("SELECT * FROM products");
$products = [];
while ($row = $result->fetch_assoc()) $products[] = $row;
echo json_encode($products);
?>
