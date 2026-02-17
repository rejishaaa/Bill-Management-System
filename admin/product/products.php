<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit;
}
include "../../config/db.php"; ?>

<form method="POST">
  <input name="name" placeholder="Product Name">
  <input name="price" placeholder="Price">
  <button name="add">Add</button>
</form>

<?php
if (isset($_POST['add'])) {
  $conn->query("INSERT INTO products VALUES (NULL,'$_POST[name]','$_POST[price]')");
}

$res = $conn->query("SELECT * FROM products");
while ($p = $res->fetch_assoc()) {
  echo $p['name']." - ₹".$p['price']."<br>";
}
?>
