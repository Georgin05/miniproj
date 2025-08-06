<?php
include 'config.php'; // DB connection

$sql = "SELECT product_id, name, description, price, stock, reorder_level FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Products</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 12px;
      max-width: 900px;
      width: 100%;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
      animation: fadeIn 0.5s ease-in-out;
      overflow-x: auto;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2 {
      text-align: center;
      color: #0d47a1;
      margin-bottom: 25px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
      font-size: 0.95rem;
    }

    th {
      background-color: #1976d2;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    tr:hover {
      background-color: #e3f2fd;
    }

    @media (max-width: 600px) {
      table {
        font-size: 0.85rem;
      }

      th, td {
        padding: 10px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Product List</h2>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price (₹)</th>
        <th>Stock</th>
        <th>Reorder Level</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['product_id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= number_format($row['price'], 2) ?></td>
            <td><?= $row['stock'] ?></td>
            <td><?= $row['reorder_level'] ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align: center;">No products found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
