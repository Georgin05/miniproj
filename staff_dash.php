<?php
include "conn.php";
session_start();

// Check if session data exists
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Safe to access
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Staff User';

// Get recent receiving activity for this staff member
function getStaffRecentActivity($user_id, $limit = 5) {
    global $conn;
    $activity = [];
    
    // Get receiving activities
    $query = "SELECT r.*, p.product_name, s.supplier_name, l.location_name 
              FROM receiving r
              JOIN products p ON r.product_id = p.product_id
              JOIN suppliers s ON r.supplier_id = s.supplier_id
              JOIN locations l ON r.location_id = l.location_id
              WHERE r.received_by = ?
              ORDER BY r.received_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['activity_type'] = 'receiving';
            $activity[] = $row;
        }
    }
    
    return $activity;
}

$recent_activity = getStaffRecentActivity($user_id, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern WMS | Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles remain unchanged */
        :root {
            --primary: #FFD700;
            --primary-dark: #d4b000;
            --secondary: #2a2a2a;
            --dark: #f5f5f5;
            --light: #121212;
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

        /* Logout Button */
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--danger);
            text-decoration: none;
            transition: all 0.2s;
            border-top: 1px solid var(--gray-light);
            margin-top: auto;
        }

        .logout-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .logout-btn i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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

        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .zoom-img {
            transition: transform 0.3s ease;
        }

        .zoom-img:hover {
            transform: scale(1.2);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: var(--surface);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            transition: transform 0.2s;
            cursor: pointer;
            border-top: 4px solid var(--primary);
            color: var(--dark);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .action-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .action-card h3 {
            margin-bottom: 0.5rem;
        }

        .action-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Activity Log */
        .activity-card {
            background: var(--surface);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            color: var(--dark);
        }

        .activity-card h2 {
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .activity-item {
            display: flex;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--gray-light);
            align-items: center;
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
        }

        .activity-content {
            flex: 1;
        }

        .activity-content h4 {
            margin-bottom: 0.3rem;
        }

        .activity-content p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .activity-time {
            color: var(--gray);
            font-size: 0.8rem;
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
                background: var(--surface);
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

            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        /* Sidebar styles remain unchanged */
        .sidebar {
            width: 250px;
            background: var(--surface-light);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }

        /* ... rest of your CSS ... */
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar remains unchanged -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-warehouse"></i>
                <h2>Modern WMS</h2>
            </div>
            <div class="sidebar-menu">
                <div class="menu-title">Main</div>
                <a href="#" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="check_inventory.php" class="menu-item">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="Inbound.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Receiving</span>
                </a>
                <a href="ship.php" class="menu-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Shipping</span>
                </a>
                <a href="transfer.php" class="menu-item">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transfers</span>
                </a>

                <div class="menu-title">Operations</div>
                <a href="pick_order.php" class="menu-item">
                    <i class="fas fa-box-open"></i>
                    <span>Pick Order</span>
                </a>
                <a href="pack_order.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    <span>Pack Order</span>
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
                <h1>Staff Dashboard</h1>
                <div class="user-menu">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($full_name); ?></h4>
                        <p>Warehouse Staff</p>
                    </div>
                    <img src="assets/images/avatars/<?php echo $user_id; ?>.jpg" class="zoom-img" alt="User" 
                         onerror="this.src='assets/images/avatars/default.jpg'">
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="location.href='Inbound.php'">
                    <i class="fas fa-truck"></i>
                    <h3>Receiving</h3>
                    <p>Check In Items</p>
                </div>
                
                <div class="action-card" onclick="location.href='pick_order.php'">
                    <i class="fas fa-box-open"></i>
                    <h3>Pick Order</h3>
                    <p>Prepare shipments</p>
                </div>
                
                <div class="action-card" onclick="location.href='pack_order.php'">
                    <i class="fas fa-box"></i>
                    <h3>Pack Order</h3>
                    <p>Package items</p>
                </div>
                
                <div class="action-card" onclick="location.href='ship.php'">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Ship</h3>
                    <p>Process shipments</p>
                </div>
            </div>

            <!-- Recent Activity - Simplified to only show receiving activities -->
            <div class="activity-card">
                <h2>Your Recent Receiving Activity</h2>
                
                <?php if (!empty($recent_activity)): ?>
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Received <?php echo htmlspecialchars($activity['quantity']); ?> <?php echo htmlspecialchars($activity['product_name']); ?></h4>
                            <p>From <?php echo htmlspecialchars($activity['supplier_name']); ?> at <?php echo htmlspecialchars($activity['location_name']); ?></p>
                        </div>
                        <div class="activity-time">
                            <?php echo htmlspecialchars(date('H:i', strtotime($activity['received_at']))); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-content">
                            <p>No recent receiving activity found</p>
                        </div>
                    </div>
                <?php endif; ?>
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

            // Action cards click
            const actionCards = document.querySelectorAll('.action-card');
            actionCards.forEach(card => {
                card.addEventListener('click', function() {
                    const action = this.querySelector('h3').textContent;
                    console.log('Action:', action);
                });
            });

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