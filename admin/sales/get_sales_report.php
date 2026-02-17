<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

// Summarize sales by date and customer
$result = $conn->query("
    SELECT s.id, s.customer_id, c.name as customer_name, DATE(s.created_at) as sale_date, COUNT(*) as total_bills, SUM(s.total) as revenue
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    GROUP BY DATE(s.created_at), s.customer_id
    ORDER BY DATE(s.created_at) DESC
");

$sales = [];
while ($row = $result->fetch_assoc()) {
    $row['total_bills'] = (int)$row['total_bills'];
    $row['revenue'] = (float)$row['revenue'];
    $sales[] = $row;
}

header('Content-Type: application/json');
echo json_encode($sales);
?>
