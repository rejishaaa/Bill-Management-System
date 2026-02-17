<?php
include "../config/db.php";

$bill_id = (int) str_replace("BILL", "", $_GET['oid']);
$refId = $_GET['refId'] ?? '';

$conn->query("
  UPDATE bills 
  SET payment_status='paid', transaction_id='$refId'
  WHERE id=$bill_id
");

header("Location: print_bill.php?id=$bill_id");
