
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory - Warehouse Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      width: 250px;
      position: fixed;
      background-color: #212529;
      padding-top: 20px;
      color: white;
    }
    .sidebar a {
      padding: 12px 20px;
      display: block;
      color: white;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #343a40;
    }
    .topbar {
      margin-left: 250px;
      background-color: #ffffff;
      padding: 15px;
      border-bottom: 1px solid #dee2e6;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
    }
    .table-container {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .btn-move-stock {
      background-color: #0d6efd;
      color: white;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h4 class="text-center">WMS</h4>
  <a href="dashboard.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a>
  <a href="inventory.php"><i class="fas fa-boxes me-2"></i>Inventory</a>
  <a href="shipments.php"><i class="fas fa-truck-arrow-right me-2"></i>Inbound</a>
  <a href="shipments.php"><i class="fas fa-truck me-2"></i>Outbound</a>
  <a href="stock_transfers.php"><i class="fas fa-exchange-alt me-2"></i>Stock Transfers</a>
  <a href="returns.php"><i class="fas fa-undo-alt me-2"></i>Returns</a>
  <a href="reports.php"><i class="fas fa-file-alt me-2"></i>Reports</a>
  <a href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
</div>

<div class="topbar d-flex justify-content-between">
  <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo ucfirst($role); ?>)</div>
</div>

<div class="content">
  <h3>Inventory Overview</h3>
  <canvas id="stockChart" height="150"></canvas>

  <div class="mt-4 table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5>Recent Inbound Shipments</h5>
      <button class="btn btn-move-stock"><i class="fas fa-dolly me-1"></i>Move Stock</button>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Date</th>
          <th>Source</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Product A</td>
          <td>100</td>
          <td>2025-07-20</td>
          <td>Supplier X</td>
        </tr>
        <tr>
          <td>2</td>
          <td>Product B</td>
          <td>50</td>
          <td>2025-07-18</td>
          <td>Supplier Y</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('stockChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Product A', 'Product B', 'Product C', 'Product D'],
      datasets: [{
        label: 'Stock Level',
        data: [120, 90, 60, 30],
        backgroundColor: '#0d6efd'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
</script>

</body>
</html>
