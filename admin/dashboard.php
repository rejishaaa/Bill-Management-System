<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "../config/db.php";

// allow only admin
if (!isset($_SESSION['username']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['approve_user'])) {
    $user_id = intval($_POST['approve_id']);
    mysqli_query($conn, "UPDATE users SET status='approved' WHERE id=$user_id");
    // reload page to refresh table
    header("Location: " . $_SERVER['PHP_SELF'] . "#users");
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="../assets/style.css">
  <title>BMS Admin Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <h2>Admin · BMS</h2>
    <nav class="nav">
      <a href="#users">Approve Users</a>
      <a href="#customers">Customers</a>
      <a href="#products">Products</a>
      <a href="#suppliers">Suppliers</a>
      <a href="#sales">Sales Report</a>
      <a href="#analysis">Sales Analysis</a>
      <a href="../auth/logout.php"> Logout</a>
    </nav>
  </aside>

<main class="content">
    
  <section id="users" class="card">
  <h3>Approve Users</h3>

  <?php
  // fetch all pending users
  $pending_res = mysqli_query($conn, "SELECT id, fullname, username, role FROM users WHERE status='pending'");
  if (!$pending_res) die("DB Error: " . mysqli_error($conn));
  ?>

  <?php if (mysqli_num_rows($pending_res) > 0): ?>
    <table>
      <tr><th>Name</th><th>Username</th><th>Role</th><th>Action</th></tr>
      <?php while($user = mysqli_fetch_assoc($pending_res)): ?>
        <tr>
          <td><?= htmlspecialchars($user['fullname']) ?></td>
          <td><?= htmlspecialchars($user['username']) ?></td>
          <td><?= htmlspecialchars($user['role']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="approve_id" value="<?= $user['id'] ?>">
              <button name="approve_user">Approve</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No users pending approval.</p>
  <?php endif; ?>
  </section>


  <section id="customers" class="card">
  <h3>Add Customer</h3>

    <input id="name" placeholder="Customer Name" />
    <input id="phone" placeholder="Phone" />
    <button id="addCustomerBtn">Add Customer</button>
    
  <table id="customer-table">
    <tr><th>Id</th><th>Name</th><th>Phone</th><th>Action</th></tr>
  </table>

    <script>
    function loadCustomers() {
        fetch('customer/get_customer.php')
            .then(res => res.json())
            .then(data => {
                const table = document.getElementById("customer-table");

                table.innerHTML = `
                  <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Phone</th>
                  </tr>
                `;

            data.forEach(c => {
        if (!c) return; // 👈 this line saves your sanity

        table.innerHTML += `
          <tr>
            <td>${c.id}</td>
            <td>${c.name || ""}</td>
            <td>${c.phone || ""}</td>
          </tr>
        `;
    });

            });
    }

    document.getElementById("addCustomerBtn").addEventListener("click", () => {
        const name = document.getElementById("name").value;
        const phone = document.getElementById("phone").value;

        fetch("customer/add_customer.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`
        }).then(() => {
            loadCustomers();
            document.getElementById("name").value = "";
            document.getElementById("phone").value = "";
        });
    });


    loadCustomers();

    function loadCustomers() {
    fetch('customer/get_customer.php')
    .then(res => res.json())
    .then(data => {
        const table = document.getElementById("customer-table");
        table.innerHTML = `<tr>
            <th>Id</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>`;
        data.forEach(c => {
            if(!c.id) return; // skip null
            const row = table.insertRow();
            row.insertCell(0).textContent = c.id;
            row.insertCell(1).textContent = c.name;
            row.insertCell(2).textContent = c.phone;
            row.insertCell(3).innerHTML = `<button onclick="deleteCustomer(${c.id})">Delete</button>`;
        });
    });
}

    function deleteCustomer(id) {
        if(!confirm("Delete this customer?")) return;
        fetch("customer/delete_customer.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(id)}`
        }).then(() => loadCustomers());
    }

    document.getElementById("addCustomerBtn").addEventListener("click", () => {
        const name = document.getElementById("cust-name").value;
        const phone = document.getElementById("cust-phone").value;
        if(!name || !phone){ alert("Enter name and phone!"); return; }

        fetch("customer/add_customer.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`
        }).then(() => {
            document.getElementById("cust-name").value = "";
            document.getElementById("cust-phone").value = "";
            loadCustomers();
        });
    });

    loadCustomers();

    </script>
  </section>

  <section id="products" class="card">
      <h3>Add Product</h3>
          <input placeholder="Product Name" id="prod-name" />
          <input placeholder="Price" id="prod-price" />
          <input placeholder="Stock" id="prod-stock" />
          <button id="add-product">Add Product</button>

      <h3>View Products</h3>
      <table id="product-table">
        <tr><th>Name</th><th>Price</th><th>Stock</th><th>Action</th></tr>
      </table>

      <script>
      function loadProducts() {
    fetch('product/get_products.php')
    .then(res => res.json())
    .then(data => {
        const table = document.getElementById('product-table');
        table.innerHTML = `<tr>
            <th>Id</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>`;
        data.forEach(p => {
            if(!p.id) return;
            const row = table.insertRow();
            row.insertCell(0).textContent = p.id;
            row.insertCell(1).textContent = p.name;
            row.insertCell(2).textContent = p.price;
            row.insertCell(3).textContent = p.stock;
            row.insertCell(4).innerHTML = `<button onclick="deleteProduct(${p.id})">Delete</button>`;
        });
    });
}

function deleteProduct(id){
    if(!confirm("Delete this product?")) return;
    fetch('product/delete_product.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id=${encodeURIComponent(id)}`
    }).then(()=>loadProducts());
}

document.getElementById('add-product').addEventListener('click', ()=>{
    const name = document.getElementById('prod-name').value;
    const price = document.getElementById('prod-price').value;
    const stock = document.getElementById('prod-stock').value;
    if(!name || !price || !stock){ alert("Enter all fields!"); return; }

    fetch('product/add_product.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&stock=${encodeURIComponent(stock)}`
    }).then(()=>{
        document.getElementById('prod-name').value="";
        document.getElementById('prod-price').value="";
        document.getElementById('prod-stock').value="";
        loadProducts();
    });
});

loadProducts();

      </script>

  </section>

  <section id="suppliers" class="card">
    <h3>Add Supplier</h3>
    <input placeholder="Supplier Name" id="sup-name" />
    <input placeholder="Company" id="sup-company" />
    <input placeholder="Contact" id="sup-contact" />
    <button id="add-supplier">Add Supplier</button>


    <h3>View Suppliers</h3>
    <table id="supplier-table">
    <tr>
      <th>Id</th>
      <th>Name</th>
      <th>Company</th>
      <th>Contact</th>
      <th>Action</th>
    </tr>
  </table>


    <script>
  function loadSuppliers() {
      fetch('supplier/get_suppliers.php')
          .then(res => res.json())
          .then(data => {
              const table = document.getElementById('supplier-table');
              table.innerHTML = `
                <tr>
                  <th>Id</th>
                  <th>Name</th>
                  <th>Company</th>
                  <th>Contact</th>
                </tr>
              `;

              data.forEach(s => {
                const row = table.insertRow();

                row.insertCell(0).textContent = s.id;       // Id
                row.insertCell(1).textContent = s.name;     // Name
                row.insertCell(2).textContent = s.company;  // Company
                row.insertCell(3).textContent = s.contact;  // Contact
  });

          });
  }

  document.getElementById('add-supplier').addEventListener('click', () => {
      fetch('supplier/add_supplier.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `name=${encodeURIComponent(
              document.getElementById('sup-name').value
          )}&company=${encodeURIComponent(
              document.getElementById('sup-company').value
          )}&contact=${encodeURIComponent(
              document.getElementById('sup-contact').value
          )}`
      }).then(() => {
          loadSuppliers();
          document.getElementById('sup-name').value = "";
          document.getElementById('sup-company').value = "";
          document.getElementById('sup-contact').value = "";
      });
  });

  loadSuppliers();
  function loadSuppliers() {
    fetch('supplier/get_suppliers.php')
    .then(res => res.json())
    .then(data => {
        const table = document.getElementById('supplier-table');
        table.innerHTML = `<tr>
            <th>Id</th>
            <th>Name</th>
            <th>Company</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>`;
        data.forEach(s => {
            if(!s.id) return;
            const row = table.insertRow();
            row.insertCell(0).textContent = s.id;
            row.insertCell(1).textContent = s.name;
            row.insertCell(2).textContent = s.company;
            row.insertCell(3).textContent = s.contact;
            row.insertCell(4).innerHTML = `<button onclick="deleteSupplier(${s.id})">Delete</button>`;
        });
    });
}

function deleteSupplier(id) {
    if(!confirm("Delete this supplier?")) return;
    fetch("supplier/delete_supplier.php", {
        method: "POST",
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${encodeURIComponent(id)}`
    }).then(() => loadSuppliers());
}

document.getElementById('add-supplier').addEventListener('click', () => {
    const name = document.getElementById('sup-name').value;
    const company = document.getElementById('sup-company').value;
    const contact = document.getElementById('sup-contact').value;
    if(!name || !company || !contact){ alert("Enter all fields!"); return; }

    fetch('supplier/add_supplier.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`name=${encodeURIComponent(name)}&company=${encodeURIComponent(company)}&contact=${encodeURIComponent(contact)}`
    }).then(() => {
        document.getElementById('sup-name').value="";
        document.getElementById('sup-company').value="";
        document.getElementById('sup-contact').value="";
        loadSuppliers();
    });
});

loadSuppliers();


    </script>
</section>


   <section id="sales" class="card">
    <h3>Add Sale</h3>

  <select id="sale-customer">
    <option value="">Select Customer</option>
  </select>
  <input type="number" id="sale-total" placeholder="Total Amount" />
  <button id="add-sale">Add Sale</button>

  <h3>Sales Report</h3>
  <table id="sales-table">
    <tr><th>Date</th><th>Customer</th><th>Total Bills</th><th>Revenue</th><th>Action</th></tr>
  </table>

  <script>
    // Load Customers for dropdown
    function loadCustomersForSale() {
      fetch('customer/get_customer.php')
        .then(res => res.json())
        .then(data => {
          const select = document.getElementById('sale-customer');
          select.innerHTML = `<option value="">Select Customer</option>`;
          data.forEach(c => {
            if(c.id && c.name)
              select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
          });
        });
    }

    // Add a sale
    document.getElementById('add-sale').addEventListener('click', () => {
      const customer_id = document.getElementById('sale-customer').value;
      const total = document.getElementById('sale-total').value;

      if(!customer_id || !total) {
        alert("Select customer and enter total amount!");
        return;
      }

      fetch('sales/add_sale.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `customer_id=${encodeURIComponent(customer_id)}&total=${encodeURIComponent(total)}`
      }).then(() => {
        document.getElementById('sale-total').value = '';
        loadSalesReport();
      });
    });

    // Load Sales Report
    function loadSalesReport() {
      fetch('sales/get_sales_report.php')
        .then(res => res.json())
        .then(data => {
          const table = document.getElementById('sales-table');
          table.innerHTML = `<tr><th>Date</th><th>Customer</th><th>Total Bills</th><th>Revenue</th></tr>`;
          data.forEach(s => {
            const row = table.insertRow();
            row.insertCell(0).textContent = s.sale_date;
            row.insertCell(1).textContent = s.customer_name; // we will join this in PHP
            row.insertCell(2).textContent = s.total_bills;
            row.insertCell(3).textContent = 'Rs. ' + s.revenue;
          });
        });
    }

    // Initialize
    loadCustomersForSale();
    loadSalesReport();

    function deleteSale(id) {
    if(!confirm("Delete this sale?")) return;

    fetch('sales/delete_sale.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${encodeURIComponent(id)}`
    }).then(() => loadSalesReport());
}

// Update loadSalesReport to add delete buttons
function loadSalesReport() {
    fetch('sales/get_sales_report.php')
    .then(res => res.json())
    .then(data => {
        const table = document.getElementById('sales-table');
        table.innerHTML = `
          <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Total Bills</th>
            <th>Revenue</th>
            <th>Action</th>
          </tr>
        `;
        data.forEach(s => {
            const row = table.insertRow();
            row.insertCell(0).textContent = s.sale_date;
            row.insertCell(1).textContent = s.customer_name;
            row.insertCell(2).textContent = s.total_bills;
            row.insertCell(3).textContent = 'Rs. ' + s.revenue;
            row.insertCell(4).innerHTML = `<button onclick="deleteSale(${s.id})">Delete</button>`;
        });
    });
}

  </script>
</section>


    <section id="analysis" class="card">
      <h3>Sales Analysis</h3>
      <div class="stats">
      <p><strong>Total Revenue:</strong> Rs. <span id="totalRevenue">0</span></p>
      <p><strong>Total Sales:</strong> <span id="totalSales">0</span></p>
      </div>

      <canvas id="sales-chart" width="600" height="300"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    fetch('salesanalysis/get_sales_analysis.php')
    .then(res => res.json())
    .then(data => {
        let totalRevenue = 0;
        let totalSales = 0;

        data.forEach(d => {
            totalRevenue += parseFloat(d.revenue);
            totalSales += parseInt(d.total_sales);
        });

        document.getElementById('totalRevenue').textContent = totalRevenue;
        document.getElementById('totalSales').textContent = totalSales;

        const ctx = document.getElementById('sales-chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.sale_date),
                datasets: [{
                    label: 'Revenue',
                    data: data.map(d => d.revenue),
                    tension: 0.3
                }]
            }
        });
    });

    </script>

    </section>

  </main>
</div>
</body>
</html>
