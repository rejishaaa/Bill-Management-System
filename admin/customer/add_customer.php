<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = $_POST['name'];
$phone = $_POST['phone'];

$stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $phone);
echo $stmt->execute() ? "success" : "error";
}
?>
