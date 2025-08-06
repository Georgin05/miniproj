<?php
require 'conn.php';  // Provides $conn = new mysqli(...)

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ─── Collect and sanitize input ────────────────────────────────
    $categoryName = trim($_POST['category_name'] ?? '');
    $categoryDesc = trim($_POST['category_desc'] ?? '');

    $supplierName    = trim($_POST['supplier_name'] ?? '');
    $contactPerson   = trim($_POST['contact_person'] ?? '');
    $supplierPhone   = trim($_POST['supplier_phone'] ?? '');
    $supplierEmail   = trim($_POST['supplier_email'] ?? '');
    $supplierAddress = trim($_POST['supplier_address'] ?? '');
    $supplierTaxId   = trim($_POST['supplier_tax_id'] ?? '');
    $paymentTerms    = trim($_POST['payment_terms'] ?? '');

    $productCode   = trim($_POST['product_code'] ?? '');
    $productName   = trim($_POST['product_name'] ?? '');
    $productSku    = trim($_POST['product_sku'] ?? '');
    $productDesc   = trim($_POST['product_desc'] ?? '');
    $unitOfMeasure = trim($_POST['unit_of_measure'] ?? '');
    $minStock      = intval($_POST['min_stock'] ?? 0);
    $maxStock      = $_POST['max_stock'] !== '' ? intval($_POST['max_stock']) : null;

    try {
        // Turn on MySQLi exceptions if not already set in conn.php
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // ─── Start transaction ────────────────────────────────────────
        $conn->begin_transaction();

        // ─── 1. Insert category ───────────────────────────────────────
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param('ss', $categoryName, $categoryDesc);
        $stmt->execute();
        $categoryId = $conn->insert_id;           // get AUTO_INCREMENT from main connection :contentReference[oaicite:2]{index=2}
        $stmt->close();

        // ─── 2. Insert supplier ───────────────────────────────────────
        $stmt = $conn->prepare("
            INSERT INTO suppliers
              (supplier_name, contact_person, phone, email, address, tax_id, payment_terms)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sssssss',
            $supplierName,
            $contactPerson,
            $supplierPhone,
            $supplierEmail,
            $supplierAddress,
            $supplierTaxId,
            $paymentTerms
        );
        $stmt->execute();
        $supplierId = $conn->insert_id;
        $stmt->close();

        // ─── 3. Insert product ────────────────────────────────────────
        $stmt = $conn->prepare("
            INSERT INTO products
              (product_code, product_name, sku, description,
               category_id, unit_of_measure, supplier_id,
               min_stock_level, max_stock_level)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sss si s iii',
            $productCode,
            $productName,
            $productSku,
            $productDesc,
            $categoryId,
            $unitOfMeasure,
            $supplierId,
            $minStock,
            $maxStock
        );
        $stmt->execute();
        $stmt->close();

        // ─── Commit all three inserts as a unit ───────────────────────
        $conn->commit();
        $message = 'Category, supplier, and product saved successfully.';
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        $message = 'Error saving data: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>New Product Entry</title></head>
<body>
    <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <form method="post">
      <fieldset>
        <legend>Category</legend>
        <label>Name*: <input name="category_name" required></label><br>
        <label>Description: <input name="category_desc"></label>
      </fieldset>

      <fieldset>
        <legend>Supplier</legend>
        <label>Supplier Name*: <input name="supplier_name" required></label><br>
        <label>Contact Person: <input name="contact_person"></label><br>
        <label>Phone: <input name="supplier_phone"></label><br>
        <label>Email: <input name="supplier_email" type="email"></label><br>
        <label>Address:<br><textarea name="supplier_address" rows="2" cols="40"></textarea></label><br>
        <label>Tax ID: <input name="supplier_tax_id"></label><br>
        <label>Payment Terms: <input name="payment_terms"></label>
      </fieldset>

      <fieldset>
        <legend>Product</legend>
        <label>Product Code*: <input name="product_code" required></label><br>
        <label>Product Name*: <input name="product_name" required></label><br>
        <label>SKU: <input name="product_sku"></label><br>
        <label>Description:<br><textarea name="product_desc" rows="3" cols="40"></textarea></label><br>
        <label>Unit of Measure: <input name="unit_of_measure"></label><br>
        <label>Min Stock Level: <input name="min_stock" type="number"></label><br>
        <label>Max Stock Level: <input name="max_stock" type="number"></label>
      </fieldset>

      <button type="submit">Save All</button>
    </form>
</body>
</html>
