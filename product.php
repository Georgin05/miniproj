<?php
include 'conn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO products (name, sku, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $sku, $desc);
    $stmt->execute();
    $msg = "✅ Product added!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>➕ Add Product</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(to right, #e0f7fa, #fff3e0);
            font-family: 'Segoe UI', sans-serif;
            padding-top: 40px;
        }

        .form-container {
            background-color: white;
            max-width: 600px;
            margin: auto;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        h3 {
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #007bff;
            font-weight: 600;
            font-size: 16px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        .back-link:hover {
            color: #343a40;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h3><i class="bi bi-box-seam"></i> Add New Product</h3>
        <?php if (isset($msg)) echo "<div class='alert alert-success mt-3 text-center'>$msg</div>"; ?>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-type"></i> Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-upc-scan"></i> SKU</label>
                <input type="text" name="sku" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-card-text"></i> Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-circle me-1"></i> Add Product
            </button>
        </form>
        <a href="dashboard.php" class="back-link"><i class="bi bi-arrow-left-circle"></i> Back to Dashboard</a>
    </div>

</body>
</html>
