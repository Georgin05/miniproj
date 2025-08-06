<?php
include 'config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $from_bin = $_POST['from_bin'];
    $to_bin = $_POST['to_bin'];
    $quantity = $_POST['quantity'];

    // Prevent same bin transfer
    if ($from_bin == $to_bin) {
        $msg = "❌ Source and destination bins cannot be the same.";
    } else {
        // Check stock in source bin
        $check = $conn->prepare("SELECT quantity FROM inventory WHERE product_id = ? AND bin_id = ?");
        $check->bind_param("ii", $product_id, $from_bin);
        $check->execute();
        $res = $check->get_result();
        $row = $res->fetch_assoc();

        if ($row && $row['quantity'] >= $quantity) {
            // Insert transfer record
            $insert = $conn->prepare("INSERT INTO transfers (product_id, from_bin, to_bin, quantity) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiii", $product_id, $from_bin, $to_bin, $quantity);
            $insert->execute();

            // Reduce from source bin
            $reduce = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND bin_id = ?");
            $reduce->bind_param("iii", $quantity, $product_id, $from_bin);
            $reduce->execute();

            // Increase in destination bin
            $checkTo = $conn->prepare("SELECT quantity FROM inventory WHERE product_id = ? AND bin_id = ?");
            $checkTo->bind_param("ii", $product_id, $to_bin);
            $checkTo->execute();
            $resTo = $checkTo->get_result();

            if ($resTo->num_rows > 0) {
                $add = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE product_id = ? AND bin_id = ?");
                $add->bind_param("iii", $quantity, $product_id, $to_bin);
                $add->execute();
            } else {
                $add = $conn->prepare("INSERT INTO inventory (product_id, bin_id, quantity) VALUES (?, ?, ?)");
                $add->bind_param("iii", $product_id, $to_bin, $quantity);
                $add->execute();
            }

            $msg = "✅ Stock transferred successfully.";
        } else {
            $msg = "❌ Not enough stock in the source bin.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Transfer</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>🔁 Stock Transfer</h2>

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
            <label>From Bin</label>
            <select name="from_bin" class="form-select" required>
                <option value="">-- Select Source Bin --</option>
                <?php
                $res = $conn->query("SELECT * FROM bins");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['bin_id']}'>{$row['bin_code']} (Zone: {$row['zone']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-2">
            <label>To Bin</label>
            <select name="to_bin" class="form-select" required>
                <option value="">-- Select Destination Bin --</option>
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

        <button type="submit" class="btn btn-primary">Transfer Stock</button>
    </form>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</body>
</html>
