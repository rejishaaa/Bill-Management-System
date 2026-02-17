<?php include "../../config/db.php"; ?>

<h2>Pending Users</h2>

<?php
$res = mysqli_query($conn,"SELECT * FROM users WHERE status='pending'");
while($u = mysqli_fetch_assoc($res)){
  echo "
  <div class='card'>
    {$u['fullname']} ({$u['role']})
    <a href='approve.php?id={$u['id']}'>Approve</a>
  </div>";
}
?>
