<?php
session_start();
require_once "conn.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$full_name = $_SESSION['full_name'] ?? 'User';

// Get recent receiving activity
function getRecentReceiving($limit = 5) {
    global $conn;
    $recent = [];
    $query = "SELECT r.*, p.product_name, s.supplier_name, l.location_name 
              FROM receiving r
              JOIN products p ON r.product_id = p.product_id
              JOIN suppliers s ON r.supplier_id = s.supplier_id
              JOIN locations l ON r.location_id = l.location_id
              ORDER BY r.received_at DESC LIMIT $limit";
    $result = $conn->query($query);
    if ($result) {
        $recent = $result->fetch_all(MYSQLI_ASSOC);
    }
    return $recent;
}

$recent_receiving = getRecentReceiving(5);

// Get dashboard stats
function getDashboardStats() {
    global $conn;
    $stats = [
        'total_inventory' => 0,
        'orders_today' => 0,
        'pending_receipts' => 0,
        'low_stock' => 0
    ];
    
    // Get total inventory count
    $result = $conn->query("SELECT SUM(quantity) as total FROM inventory");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['total_inventory'] = $row['total'] ?? 0;
    }
    
    // Get today's orders - MODIFIED to use order_date instead of created_at
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['orders_today'] = $row['count'] ?? 0;
    }
    
    // Get pending receipts - MODIFIED to check if status column exists
    $result = $conn->query("SHOW COLUMNS FROM receiving LIKE 'status'");
    if ($result && $result->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM receiving WHERE status = 'pending'");
    } else {
        $result = $conn->query("SELECT COUNT(*) as count FROM receiving");
    }
    if ($result && $row = $result->fetch_assoc()) {
        $stats['pending_receipts'] = $row['count'] ?? 0;
    }
    
    // Get low stock items - MODIFIED to handle case where min_stock_level doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM inventory LIKE 'min_stock_level'");
    if ($result && $result->num_rows > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM inventory WHERE quantity < min_stock_level");
    } else {
        $result = $conn->query("SELECT COUNT(*) as count FROM inventory WHERE quantity < 10"); // Default threshold
    }
    if ($result && $row = $result->fetch_assoc()) {
        $stats['low_stock'] = $row['count'] ?? 0;
    }
    
    return $stats;
}

$stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern WMS | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FFD700;         /* Gold */
            --primary-dark: #d4b000;
            --secondary: #2a2a2a;       /* Light surface */
            --dark: #f5f5f5;            /* Light text */
            --light: #121212;           /* Dark background */
            --surface: #1e1e1e;
            --surface-light: #2a2a2a;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #aaaaaa;
            --gray-light: #333333;
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

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: var(--surface-light);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            color: var(--primary);
        }

        .sidebar-menu {
            padding: 1rem 0;
            flex: 1;
        }

        .menu-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--gray);
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s;
        }

        .menu-item:hover {
            background-color: var(--primary);
            color: #000;
        }

        .menu-item.active {
            background-color: rgba(255, 215, 0, 0.1);
            color: var(--primary);
            border-left: 3px solid var(--primary);
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--danger);
            text-decoration: none;
            transition: all 0.2s;
            border-top: 1px solid var(--gray-light);
        }

        .logout-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            background-color: var(--light);
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--gray-light);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .stat-title {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .stat-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
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
            font-size: 1.2rem;
        }

        .stat-icon.blue { background-color: var(--primary-dark); }
        .stat-icon.green { background-color: var(--success); }
        .stat-icon.orange { background-color: var(--warning); }
        .stat-icon.red { background-color: var(--danger); }

        /* Charts Section */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--surface);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--gray-light);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-title {
            font-weight: 600;
            color: var(--primary);
        }

        .chart-actions {
            display: flex;
            gap: 0.5rem;
        }

        .chart-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .chart-btn:hover, .chart-btn.active {
            background-color: var(--primary);
            color: #000;
        }

        .chart-placeholder {
            height: 200px;
            background-color: var(--surface-light);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            flex-direction: column;
        }

        .chart-placeholder i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        /* Activity Section */
        .activity-card {
            background: var(--surface);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--gray-light);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 215, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content h4 {
            margin-bottom: 0.25rem;
        }

        .activity-content p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .activity-time {
            color: var(--gray);
            font-size: 0.8rem;
            margin-left: 1rem;
            white-space: nowrap;
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            color: var(--gray);
            transition: color 0.2s;
        }

        .notification-icon:hover {
            color: var(--primary);
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -10px;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 50%;
        }

        .user-info {
            text-align: right;
        }

        .user-info h4 {
            margin-bottom: 0.25rem;
        }

        .user-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            transition: transform 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                bottom: 0;
                height: 60px;
                overflow-y: auto;
                z-index: 100;
            }

            .sidebar-header {
                display: none;
            }

            .sidebar-menu {
                display: flex;
                padding: 0;
            }

            .menu-title {
                display: none;
            }

            .menu-item {
                flex-direction: column;
                padding: 0.5rem;
                font-size: 0.7rem;
                text-align: center;
                flex: 1;
            }

            .menu-item i {
                margin-right: 0;
                margin-bottom: 5px;
            }

            .logout-btn {
                display: none;
            }

            .main-content {
                padding-bottom: 80px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-menu {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
            }

            .stats-container,
            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stat-card {
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-warehouse"></i>
                <h2>Modern WMS</h2>
            </div>
            <div class="sidebar-menu">
                <div class="menu-title">Main</div>
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="inventory.php" class="menu-item">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="inbound.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Receiving</span>
                </a>
                <a href="outbound.php" class="menu-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Shipping</span>
                </a>
                <a href="transfers.php" class="menu-item">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transfers</span>
                </a>

                <div class="menu-title">Management</div>
                <a href="users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="locations.php" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Locations</span>
                </a>
                <a href="products.php" class="menu-item">
                    <i class="fas fa-barcode"></i>
                    <span>Products</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
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
                <h1>Dashboard Overview</h1>
                <div class="user-menu">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($full_name); ?></h4>
                        <p><?php echo htmlspecialchars(ucfirst($user_type)); ?></p>
                    </div>
                    <img src="assets/images/avatars/<?php echo $user_id; ?>.jpg" class="user-avatar" alt="User Avatar" 
                         onerror="this.src='assets/images/avatars/default.jpg'">
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Inventory</div>
                            <div class="stat-value"><?php echo number_format($stats['total_inventory']); ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>12% from last month</span>
                            </div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Orders Today</div>
                            <div class="stat-value"><?php echo number_format($stats['orders_today']); ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>5% from yesterday</span>
                            </div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Pending Receipts</div>
                            <div class="stat-value"><?php echo number_format($stats['pending_receipts']); ?></div>
                            <div class="stat-change negative">
                                <i class="fas fa-arrow-down"></i>
                                <span>3% from last week</span>
                            </div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="fas fa-dolly"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Low Stock Items</div>
                            <div class="stat-value"><?php echo number_format($stats['low_stock']); ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>8% resolved</span>
                            </div>
                        </div>
                        <div class="stat-icon red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Inventory Movement</div>
                        <div class="chart-actions">
                            <button class="chart-btn active">Daily</button>
                            <button class="chart-btn">Weekly</button>
                            <button class="chart-btn">Monthly</button>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-line"></i>
                        <span>Inventory movement chart</span>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Storage Utilization</div>
                        <div class="chart-actions">
                            <button class="chart-btn active">Zones</button>
                            <button class="chart-btn">Categories</button>
                        </div>
                    </div>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-pie"></i>
                        <span>Storage utilization chart</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-card">
                <div class="chart-header">
                    <div class="chart-title">Recent Receiving Activity</div>
                    <div class="chart-actions">
                        <a href="inbound.php" class="chart-btn">View All</a>
                    </div>
                </div>
                <ul class="activity-list">
                    <?php if (!empty($recent_receiving)): ?>
                        <?php foreach ($recent_receiving as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($activity['product_name']); ?></h4>
                                <p>
                                    <?php echo htmlspecialchars($activity['quantity']); ?> units received from 
                                    <?php echo htmlspecialchars($activity['supplier_name']); ?>
                                </p>
                            </div>
                            <div class="activity-time">
                                <?php echo date('H:i', strtotime($activity['received_at'])); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="activity-item">
                            <div class="activity-content">
                                <p>No recent receiving activity</p>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle active state for menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Toggle chart periods
            const chartBtns = document.querySelectorAll('.chart-btn');
            chartBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const parent = this.parentElement;
                    parent.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    // Here you would typically update the chart data
                });
            });

            // Simulate loading data
            setTimeout(() => {
                const placeholders = document.querySelectorAll('.chart-placeholder');
                placeholders.forEach(p => {
                    p.innerHTML = '<canvas id="chart' + Math.floor(Math.random() * 100) + '"></canvas>';
                    // In a real app, you would initialize charts here with Chart.js or similar
                });
            }, 1500);

            // Notification bell click
            const notificationIcon = document.querySelector('.notification-icon');
            if (notificationIcon) {
                notificationIcon.addEventListener('click', function() {
                    alert('You have 3 new notifications');
                    this.querySelector('.notification-badge').style.display = 'none';
                });
            }

            // Responsive sidebar toggle for mobile
            function handleResize() {
                if (window.innerWidth <= 768) {
                    document.querySelector('.sidebar').classList.add('mobile');
                } else {
                    document.querySelector('.sidebar').classList.remove('mobile');
                }
            }

            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
</body>
</html>