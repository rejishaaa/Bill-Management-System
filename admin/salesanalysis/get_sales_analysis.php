<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') die("Unauthorized");

$result = $conn->query("
    SELECT 
        DATE(created_at) AS sale_date,
        COUNT(*) AS total_sales,
        SUM(total) AS revenue
    FROM sales
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");


$analysis = [];
while ($row = $result->fetch_assoc()) $analysis[] = $row;
echo json_encode($analysis);
?>
