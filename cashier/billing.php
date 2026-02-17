<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'cashier') {
    header("Location: ../auth/login.php");
    exit;
}

// fetch products for Create Bill
$productsQuery = $conn->query("SELECT * FROM products WHERE stock > 0");
$products = [];
if ($productsQuery) {
    while ($p = $productsQuery->fetch_assoc()) {
        $products[] = $p;
    }
}

// get page to show: billing or sales
$page = $_GET['page'] ?? 'billing';
?>

<link rel="stylesheet" href="../assets/style.css">

<div class="layout">
  <aside class="sidebar">
    <h2>Cashier · BMS</h2>
    <nav class="nav">
      <a href="?page=billing" class="<?= $page=='billing'?'active':'' ?>">Create Bill</a>
      <a href="?page=sales" class="<?= $page=='sales'?'active':'' ?>">My Sales</a>
      <a href="../auth/logout.php">Logout</a>
    </nav>
  </aside>

  <main class="content">
    <div class="card">

      <?php if($page=='billing'): ?>
      
      <h3>Create Bill</h3>
      <form method="POST">
        <label>Customer Name:</label>
        <input type="text" name="customer" required>

        <label>Payment Method:</label>
        <select name="payment" required>
          <option value="cash">Cash</option>
          <option value="esewa">eSewa</option>
          <option value="bank">Bank Transfer</option>
        </select>

        <label>Select Products:</label>
        <div style="display:grid; grid-template-columns: 30px 1fr 70px 70px; gap:10px; align-items:center;">
          <strong></strong>
          <strong>Product</strong>
          <strong>Price</strong>
          <strong>Qty</strong>

          <?php foreach ($products as $p): ?>
            <input type="checkbox" name="products[]" value="<?= $p['id'] ?>">
            <span><?= htmlspecialchars($p['name']) ?></span>
            <span>Rs. <?= number_format($p['price'],2) ?></span>
            <input type="number" name="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $p['stock'] ?>" style="width:60px">
          <?php endforeach; ?>
        </div>

        <button type="submit" name="checkout">Create Bill</button>
      </form>

      <?php
      if(isset($_POST['checkout'])){
          $customer = mysqli_real_escape_string($conn, $_POST['customer']);
          $payment_method = $_POST['payment'];
          $productsSelected = $_POST['products'] ?? [];

          if(empty($productsSelected)){
              echo "<p style='color:red;'>Select at least one product.</p>";
              exit;
          }

          // Calculate next bill_number
          $result = $conn->query("SELECT MAX(bill_number) as max_bill FROM bills");
          $row = $result->fetch_assoc();
          $next_bill_number = $row['max_bill'] + 1;

          // CREATE BILL PENDING
          $conn->query("INSERT INTO bills (bill_number, customer_name, payment_method, payment_status, cashier_id, created_at)
                        VALUES ($next_bill_number,'$customer','$payment_method','pending',{$_SESSION['user_id']},NOW())");
          $bill_id = $conn->insert_id;

          $total=0;
          foreach($productsSelected as $pid){
              $pid = (int)$pid;
              $qty = (int)($_POST['qty_'.$pid] ?? 1);

              $res = $conn->query("SELECT * FROM products WHERE id=$pid");
              $p = $res->fetch_assoc();

              if($qty > $p['stock']) die("Not enough stock for {$p['name']}");

              $subtotal = $p['price'] * $qty;
              $total += $subtotal;

              $conn->query("INSERT INTO bill_items (bill_id, product_name, qty, price, subtotal)
                            VALUES ('$bill_id','{$p['name']}','$qty','{$p['price']}','$subtotal')");

              $conn->query("UPDATE products SET stock = stock - $qty WHERE id=$pid");
          }

          // 3️⃣ PAYMENT HANDLING
if($payment_method==='esewa'){
    // check if on localhost
    $host = $_SERVER['HTTP_HOST'];
    if(strpos($host,'localhost') !== false || strpos($host,'127.0.0.1') !== false){
        // skip eSewa on localhost
        $payment_method = 'cash';
    } else {
        $_SESSION['bill_id'] = $bill_id;
        echo "
        <form id='esewa_form' action='https://esewa.com.np/epay/main' method='POST'>
            <input type='hidden' name='tAmt' value='$total'>
            <input type='hidden' name='amt' value='$total'>
            <input type='hidden' name='txAmt' value='0'>
            <input type='hidden' name='psc' value='0'>
            <input type='hidden' name='pdc' value='0'>
            <input type='hidden' name='pid' value='BILL$next_bill_number'>
            <input type='hidden' name='scd' value='EPAYTEST'>
            <input type='hidden' name='su' value='http://yourdomain.com/cashier/esewa_success.php'>
            <input type='hidden' name='fu' value='http://yourdomain.com/cashier/esewa_fail.php'>
        </form>
        <script>document.getElementById('esewa_form').submit();</script>
        ";
        exit;
    }
}


          if($payment_method==='bank'){
              $conn->query("UPDATE bills SET total='$total' WHERE id='$bill_id'");
              echo "<div class='card'><h3>Bank Transfer</h3><p>Amount: Rs. $total</p><p>Reference ID: BILL$next_bill_number</p></div>";
              exit;
          }

          // CASH = paid
          $conn->query("UPDATE bills SET total='$total', payment_status='paid' WHERE id='$bill_id'");

          echo "<div class='card'>
                  <p>Bill Created Successfully</p>
                  <p>Bill #: $next_bill_number</p>
                  <p>Total: Rs. $total</p>
                  <a href='print_bill.php?id=$bill_id' target='_blank'><button>Print Bill</button></a>
                </div>";
      }
      ?>

      <?php elseif($page=='sales'): ?>
      
      <h3>My Sales</h3>
      <?php
      $user_id = (int)$_SESSION['user_id'];
      $res = $conn->query("SELECT * FROM bills WHERE cashier_id=$user_id ORDER BY created_at DESC");
      ?>
      <div class="sales-container" style="display:flex; flex-wrap:wrap; gap:15px;">
      <?php if($res->num_rows==0) echo "<p>No transactions yet.</p>"; ?>

      <?php while($b = $res->fetch_assoc()): ?>
      <div class="card" style="padding:10px; border:1px solid #ccc; border-radius:8px; width:220px;">
          <p><strong>Bill #:</strong> <?= $b['bill_number'] ?></p>
          <p><strong>Customer:</strong> <?= htmlspecialchars($b['customer_name']) ?></p>
          <p><strong>Total:</strong> Rs. <?= number_format($b['total'],2) ?></p>
          <p><strong>Status:</strong> <?= strtoupper($b['payment_status']) ?></p>
          <p><strong>Date:</strong> <?= $b['created_at'] ?></p>

          <a href="print_bill.php?id=<?= $b['id'] ?>" target="_blank"><button>Print</button></a>
          <a href="delete_transaction.php?id=<?= $b['id'] ?>" onclick="return confirm('Are you sure?')">
              <button style="background:red; color:white;">Delete</button>
          </a>
      </div>
      <?php endwhile; ?>
      </div>

      <?php endif; ?>
    </div>
  </main>
</div>
