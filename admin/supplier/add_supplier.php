<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

$name    = $_POST['name'] ?? '';
$company = $_POST['company'] ?? '';
$contact = $_POST['contact'] ?? '';

$stmt = $conn->prepare(
    "INSERT INTO suppliers (name, company, contact) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $name, $company, $contact);
$stmt->execute();
