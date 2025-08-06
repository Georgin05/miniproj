<?php
include 'config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $bin_id = $_POST['bin_id'];
    $quantity = $_POST['quantity'];

    // Check if enough stock exists
    $check = $conn->prepare("SELECT quantity FROM inventory WHERE product_id = ? AND bin_id = ?");
    $check->bind_param("ii", $product_id, $bin_id);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['quantity'] >= $quantity) {
        // Record outbound entry
        $stmt = $conn->prepare("INSERT INTO outbound (product_id, quantity, bin_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $product_id, $quantity, $bin_id);
        $stmt->execute();

        // Subtract from inventory
        $update = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND bin_id = ?");
        $update->bind_param("iii", $quantity, $product_id, $bin_id);
        $update->execute();

        $msg = "Outbound shipment recorded successfully!";
    } else {
        $msg = "Not enough stock in selected bin.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Outbound Shipments</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>🚚 Outbound Shipments</h2>

    <?php if (isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <form method="POST" class="card p-3">
        <div class="mb-2">
            <label>Product</label>
            <select name="product_id" class="form-select" required>
                <option value="">-- Select Product --</option>
                <?php
                $res = $conn->query("SELECT * FROM products");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['product_id']}'>{$row['name']} ({$row['sku']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label>Bin Location</label>
            <select name="bin_id" class="form-select" required>
                <option value="">-- Select Bin --</option>
                <?php
                $res = $conn->query("SELECT * FROM bins");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['bin_id']}'>{$row['bin_code']} (Zone: {$row['zone']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-danger">Record Outbound</button>
    </form>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</body>
</html>
<?php
include 'config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $bin_id = $_POST['bin_id'];
    $quantity = $_POST['quantity'];

    // Check if enough stock exists
    $check = $conn->prepare("SELECT quantity FROM inventory WHERE product_id = ? AND bin_id = ?");
    $check->bind_param("ii", $product_id, $bin_id);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['quantity'] >= $quantity) {
        // Record outbound entry
        $stmt = $conn->prepare("INSERT INTO outbound (product_id, quantity, bin_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $product_id, $quantity, $bin_id);
        $stmt->execute();

        // Subtract from inventory
        $update = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND bin_id = ?");
        $update->bind_param("iii", $quantity, $product_id, $bin_id);
        $update->execute();

        $msg = "Outbound shipment recorded successfully!";
    } else {
        $msg = "Not enough stock in selected bin.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Outbound Shipments</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>🚚 Outbound Shipments</h2>

    <?php if (isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <form method="POST" class="card p-3">
        <div class="mb-2">
            <label>Product</label>
            <select name="product_id" class="form-select" required>
                <option value="">-- Select Product --</option>
                <?php
                $res = $conn->query("SELECT * FROM products");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['product_id']}'>{$row['name']} ({$row['sku']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label>Bin Location</label>
            <select name="bin_id" class="form-select" required>
                <option value="">-- Select Bin --</option>
                <?php
                $res = $conn->query("SELECT * FROM bins");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['bin_id']}'>{$row['bin_code']} (Zone: {$row['zone']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-danger">Record Outbound</button>
    </form>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</body>
</html>
