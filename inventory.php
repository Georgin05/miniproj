<?php
include 'config/db.php';
session_start();

$sql = "SELECT 
            p.name AS product_name, 
            p.sku, 
            b.bin_code, 
            b.zone, 
            i.quantity
        FROM inventory i
        JOIN products p ON i.product_id = p.product_id
        JOIN bins b ON i.bin_id = b.bin_id
        ORDER BY p.name, b.bin_code";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bin Inventory</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>📍 Bin-Level Inventory</h2>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Bin Code</th>
                <th>Zone</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['sku'] ?></td>
                    <td><?= $row['bin_code'] ?></td>
                    <td><?= $row['zone'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</body>
</html>
