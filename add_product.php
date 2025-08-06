<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';

// Fetch categories for the dropdown
$categories = [];
$cat_result = $conn->query("SELECT category_id, name FROM categories");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $reorder_level = $_POST['reorder_level'] ?? 10;
    $category_id = $_POST['category_id'] ?? '';

    if ($name && is_numeric($price) && is_numeric($stock) && is_numeric($reorder_level) && is_numeric($category_id)) {
        // Insert into products
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, reorder_level) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdii", $name, $description, $price, $stock, $reorder_level);

        if ($stmt->execute()) {
            $product_id = $conn->insert_id;

            // Insert into inventory
            $inv_stmt = $conn->prepare("INSERT INTO inventory (product_id, quantity, reorder_level) VALUES (?, ?, ?)");
            $inv_stmt->bind_param("iii", $product_id, $stock, $reorder_level);
            $inv_stmt->execute();

            echo "<script>alert('Product added successfully'); window.location.href='add_product.php';</script>";
            exit;
        } else {
            echo "Product insert failed: " . $stmt->error;
        }
    } else {
        echo "All fields must be filled and valid.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

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
            max-width: 500px;
            width: 100%;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.5s ease-in-out;
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

        input, textarea, select, button {
            width: 100%;
            padding: 12px 14px;
            margin: 10px 0 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            transition: border 0.2s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #1976d2;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background: #1976d2;
            color: #fff;
            border: none;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: background 0.3s, transform 0.2s;
        }

        button:hover {
            background: #0d47a1;
            transform: scale(1.02);
        }

        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Product</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Product Name" required>
            <textarea name="description" placeholder="Product Description (optional)"></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price (e.g., 99.99)" required>
            <input type="number" name="stock" placeholder="Stock Quantity" required>
            <input type="number" name="reorder_level" placeholder="Reorder Level (default 10)" value="10" required>

            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
