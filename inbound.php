<?php
session_start();
require_once "conn.php"; // Database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$user_id = $_SESSION['user_id'];

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Initialize database connection
    $conn=new mysqli("localhost","root","","warehousez");
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    try {
        // Validate inputs
        $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $location_id = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT);
        $batch_number = !empty($_POST['batch_number']) ? trim($_POST['batch_number']) : null;
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        // Validate all required fields
        if (!$supplier_id || !$product_id || !$quantity || !$location_id) {
            throw new Exception("Please fill all required fields with valid values");
        }

        // Validate expiry date format if provided
        if ($expiry_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)) {
            throw new Exception("Invalid expiry date format");
        }

        // Start transaction
        $conn->begin_transaction();
        
        // Generate reference number
        $reference_number = 'REC-' . date('Ymd-His');
        
        // Insert into receiving table
        $stmt = $conn->prepare("
            INSERT INTO receiving (
                reference_number, supplier_id, product_id, quantity, 
                location_id, batch_number, expiry_date, notes, received_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiiisssi", 
            $reference_number, $supplier_id, $product_id, $quantity, 
            $location_id, $batch_number, $expiry_date, $notes, $user_id
        );
        $stmt->execute();
        
        // Update or insert into inventory
        $stmt = $conn->prepare("
            INSERT INTO inventory (
                product_id, location_id, quantity, batch_number, expiry_date
            ) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                quantity = quantity + VALUES(quantity),
                batch_number = COALESCE(VALUES(batch_number), batch_number),
                expiry_date = COALESCE(VALUES(expiry_date), expiry_date)
        ");
        $stmt->bind_param("iiiss", $product_id, $location_id, $quantity, $batch_number, $expiry_date);
        $stmt->execute();
        
        $conn->commit();
        $success_message = "Successfully received $quantity items! Reference: $reference_number";
        
        // Clear form values on success
        $_POST = [];
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        if ($e->getCode() == 1062) { // Duplicate entry
            $error_message = "This product/batch combination already exists at this location";
        } else {
            $error_message = "Database error: " . $e->getMessage();
        }
        error_log("Receiving Error: " . $e->getMessage());
    } catch (Exception $e) {
        if (isset($conn) && method_exists($conn, 'rollback')) {
            $conn->rollback();
        }
        $error_message = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Fetch data for dropdowns
try {
   $conn=new mysqli("localhost","root","","warehousez");
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get suppliers - REMOVED STATUS FILTER
    $suppliers = [];
    $result = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name");
    if ($result) {
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    // Get products - REMOVED STATUS FILTER
    $products = [];
    $result = $conn->query("SELECT * FROM products ORDER BY product_name");
    if ($result) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    // Get locations - REMOVED STATUS FILTER
    $locations = [];
    $result = $conn->query("SELECT * FROM locations ORDER BY location_name");
    if ($result) {
        $locations = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    // Get recent receiving activity
    $recent_receiving = [];
    $result = $conn->query("
        SELECT r.*, p.product_code, p.product_name, s.supplier_name, 
               l.location_code, u.full_name as received_by_name
        FROM receiving r
        JOIN products p ON r.product_id = p.product_id
        JOIN suppliers s ON r.supplier_id = s.supplier_id
        JOIN locations l ON r.location_id = l.location_id
        JOIN users u ON r.received_by = u.user_id
        ORDER BY r.received_at DESC
        LIMIT 10
    ");
    if ($result) {
        $recent_receiving = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    $conn->close();
} catch (Exception $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
    error_log("Data Fetch Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management System | Inbound Receiving</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

    :root {
        --primary: #FFD700;         /* Gold */
        --primary-dark: #d4b000;
        --secondary: #2a2a2a;       /* Light surface */
        --dark: #f5f5f5;           /* Light text */
        --light: #121212;           /* Dark background */
        --surface: #1e1e1e;
        --surface-light: #2a2a2a;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --gray: #aaaaaa;
        --gray-light: #333333;
        --white: #fff;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: var(--light);
        color: var(--dark);
        line-height: 1.6;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar styles */
    .sidebar {
        width: 250px;
        background-color: var(--surface-light);
        color: var(--dark);
        padding: 20px 0;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--gray-light);
        color: var(--primary);
        display: flex;
        align-items: center;
    }

    .sidebar-menu {
        padding: 20px 0;
        flex: 1;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
    }

    .menu-item:hover {
        background-color: var(--primary);
        color: #000;
    }

    .menu-item i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .menu-item.active {
        background-color: rgba(255, 215, 0, 0.1);
        color: var(--primary);
        border-left: 3px solid var(--primary);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: var(--danger);
        text-decoration: none;
        margin-top: auto;
        border-top: 1px solid var(--gray-light);
    }

    .logout-btn:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    /* Main content styles */
    .main-content {
        flex: 1;
        padding: 20px;
        background-color: var(--light);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--gray-light);
    }

    .header h1 {
        color: var(--primary);
    }

    .card {
        background-color: var(--surface);
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        margin-bottom: 20px;
        border: 1px solid var(--gray-light);
    }

    .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--gray-light);
        color: var(--primary);
    }

    .card-body {
        padding: 20px;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px 15px;
    }

    .form-group {
        flex: 1;
        min-width: 250px;
        padding: 0 10px;
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: var(--dark);
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--gray-light);
        border-radius: 4px;
        font-size: 16px;
        background-color: var(--surface-light);
        color: var(--dark);
    }

    select.form-control {
        height: 40px;
    }

    textarea.form-control {
        min-height: 100px;
    }

    .btn {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: var(--primary);
        color: #000;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
    }

    .btn-secondary {
        background-color: var(--gray);
        color: white;
    }

    .btn-secondary:hover {
        background-color: #7f8c8d;
    }

    .form-actions {
        display: flex;
        gap: 10px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid var(--success);
    }

    .alert-error {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--dark);
    }

    table.data-table th, table.data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--gray-light);
    }

    table.data-table th {
        background-color: var(--surface-light);
        font-weight: 600;
    }

    table.data-table tr:hover {
        background-color: var(--surface-light);
    }

    /* Dashboard specific elements */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: var(--surface);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        border: 1px solid var(--gray-light);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .stat-title {
        color: var(--gray);
        font-size: 14px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 600;
        margin: 10px 0;
        color: var(--dark);
    }

    .stat-change {
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-change.positive {
        color: var(--success);
    }

    .stat-change.negative {
        color: var(--danger);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #000;
    }

    .stat-icon.blue { background-color: var(--primary-dark); }
    .stat-icon.green { background-color: var(--success); }
    .stat-icon.orange { background-color: var(--warning); }
    .stat-icon.red { background-color: var(--danger); }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            height: auto;
        }

        .form-group {
            min-width: 100%;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }
    }

    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>WMS</h2>
        </div>
        <div class="sidebar-menu">
            <a href="staff_dash.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="inventory.php" class="menu-item">
                <i class="fas fa-boxes"></i>
                <span>Inventory</span>
            </a>
            <a href="receiving.php" class="menu-item active">
                <i class="fas fa-truck"></i>
                <span>Inbound</span>
            </a>
            <a href="shipping.php" class="menu-item">
                <i class="fas fa-shipping-fast"></i>
                <span>Outbound</span>
            </a>
            <a href="reports.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Inbound Receiving</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Receiving Form -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-truck-loading"></i> Receive Products</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="inbound.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="supplier_id"><i class="fas fa-parachute-box"></i> Supplier *</label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo htmlspecialchars($supplier['supplier_id']); ?>"
                                    <?php echo (isset($_POST['supplier_id']) && $_POST['supplier_id'] == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_id"><i class="fas fa-box-open"></i> Product *</label>
                            <select id="product_id" name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['product_id']); ?>"
                                    <?php echo (isset($_POST['product_id']) && $_POST['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['product_code'] . ' - ' . $product['product_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity"><i class="fas fa-cubes"></i> Quantity *</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" required
                                   value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="location_id"><i class="fas fa-warehouse"></i> Storage Location *</label>
                            <select id="location_id" name="location_id" class="form-control" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location['location_id']); ?>"
                                    <?php echo (isset($_POST['location_id']) && $_POST['location_id'] == $location['location_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['location_code'] . ' - ' . $location['location_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="batch_number"><i class="fas fa-barcode"></i> Batch/Lot Number</label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['batch_number'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="expiry_date"><i class="fas fa-calendar-times"></i> Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes"><i class="fas fa-sticky-note"></i> Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"><?php 
                            echo htmlspecialchars($_POST['notes'] ?? ''); 
                        ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Receive Products
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Receiving Activity -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Receiving Activity</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_receiving)): ?>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="far fa-calendar-alt"></i> Date</th>
                                <th><i class="fas fa-hashtag"></i> Reference</th>
                                <th><i class="fas fa-box"></i> Product</th>
                                <th><i class="fas fa-cubes"></i> Quantity</th>
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <th><i class="fas fa-parachute-box"></i> Supplier</th>
                                <th><i class="fas fa-user"></i> Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_receiving as $receiving): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($receiving['received_at']))); ?></td>
                                <td><?php echo htmlspecialchars($receiving['reference_number']); ?></td>
                                <td><?php echo htmlspecialchars($receiving['product_code'] . ' - ' . $receiving['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($receiving['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($receiving['location_code']); ?></td>
                                <td><?php echo htmlspecialchars($receiving['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($receiving['received_by_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p>No recent receiving activity found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });

    // Auto-focus first field
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('input, select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    });
</script>
</body>
</html>