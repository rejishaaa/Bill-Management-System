<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

$result = $conn->query("SELECT * FROM users WHERE approved = 0");
$users = [];
while ($row = $result->fetch_assoc()) $users[] = $row;
echo json_encode($users);
?>
