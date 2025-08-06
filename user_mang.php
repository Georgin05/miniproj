<?php
include "conn.php";
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: loginx.php");
    exit;
}

$message = '';
$error = '';

// Handle user approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_user'])) {
    $userId = $_POST['user_id'];
    $userType = $_POST['user_type'];
    
    try {
        $stmt = $conn->prepare("UPDATE users SET is_approved = 1, user_type = ? WHERE user_id = ?");
        $stmt->execute([$userType, $userId]);
        
        // Assign default role based on user type
        $roleId = ($userType === 'admin') ? 1 : 2;
        $stmt = $conn->prepare("INSERT INTO user_role_mapping (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $roleId]);
        
        $message = "User approved successfully!";
    } catch (PDOException $e) {
        $error = "Error approving user: " . $e->getMessage();
    }
}

// Handle role assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_role'])) {
    $userId = $_POST['user_id'];
    $roleId = $_POST['role_id'];
    
    try {
        // First remove all roles
        $conn->prepare("DELETE FROM user_role_mapping WHERE user_id = ?")->execute([$userId]);
        
        // Add selected role
        $stmt = $conn->prepare("INSERT INTO user_role_mapping (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $roleId]);
        
        $message = "Role assigned successfully!";
    } catch (PDOException $e) {
        $error = "Error assigning role: " . $e->getMessage();
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY is_approved, created_at DESC")->fetchAll();

// Fetch all roles
$roles = $conn->query("SELECT * FROM user_roles")->fetchAll();

// Fetch roles for each user
foreach ($users as &$user) {
    $stmt = $conn->prepare("SELECT r.role_id, r.role_name FROM user_role_mapping m 
                           JOIN user_roles r ON m.role_id = r.role_id 
                           WHERE m.user_id = ?");
    $stmt->execute([$user['user_id']]);
    $user['roles'] = $stmt->fetchAll();
}
unset($user);

// Safe to access session data
$current_user = $_SESSION['users'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern WMS | User Management</title>
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

/* Global Reset */
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

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background: var(--surface-light);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
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
}
.container {
    display: flex;
    min-height: 100vh;
}

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

/* Cards */
.card {
    background: var(--surface);
    color: var(--dark);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    border: 1px solid var(--gray-light);
    margin-bottom: 1.5rem;
}

.card-header {
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
}

.card-header h3 {
    font-size: 1.15rem;
    color: var(--primary);
}

/* Tables */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.data-table th, .data-table td {
    padding: 0.8rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-light);
}

.data-table th {
    background-color: var(--surface-light);
    color: var(--primary);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
}

.data-table tr:hover td {
    background-color: rgba(255, 215, 0, 0.05);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 0.25em 0.5em;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.badge-primary {
    background-color: var(--primary);
    color: #000;
}

.badge-success {
    background-color: var(--success);
    color: white;
}

.badge-warning {
    background-color: var(--warning);
    color: #000;
}

.badge-danger {
    background-color: var(--danger);
    color: white;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    border: none;
    margin-right: 0.5rem;
}

.btn-primary {
    background: var(--primary);
    color: #000;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-secondary {
    background: var(--gray-light);
    color: var(--dark);
}

.btn-secondary:hover {
    background: #444;
}

/* Form Elements */
select {
    background-color: var(--surface-light);
    color: var(--dark);
    border: 1px solid var(--gray-light);
    padding: 0.5rem;
    border-radius: 4px;
    margin-right: 0.5rem;
}

/* Alerts */
.alert {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
    border-left: 3px solid transparent;
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border-left-color: var(--success);
    color: var(--success);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border-left-color: var(--danger);
    color: var(--danger);
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
    }

    .sidebar-header {
        display: none;
    }

    .sidebar-menu {
        display: flex;
        padding: 0;
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
    
    .data-table {
        display: block;
        overflow-x: auto;
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
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="inbound.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Receiving</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Shipping</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transfers</span>
                </a>

                <div class="menu-title">Management</div>
                <a href="user_mang.php" class="menu-item active">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Locations</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-barcode"></i>
                    <span>Products</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>

                <div class="menu-title">System</div>
                <a href="#" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>User Management</h1>
                <div class="user-menu">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($current_user['full_name']); ?></h4>
                        <p><?php echo htmlspecialchars($current_user['user_type']); ?></p>
                    </div>
                    <img src="LUFFY.jpg" alt="User" class="zoom-img">
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> User List</h3>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Roles</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_approved']): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(ucfirst($user['user_type'])); ?>
                                </td>
                                <td>
                                    <?php foreach ($user['roles'] as $role): ?>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($role['role_name']); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if (!$user['is_approved']): ?>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="user_type" required class="form-control" style="width: auto; display: inline-block;">
                                                <option value="admin">Admin</option>
                                                <option value="staff">Staff</option>
                                            </select>
                                            <button type="submit" name="approve_user" class="btn btn-primary">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="role_id" required class="form-control" style="width: auto; display: inline-block;">
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?php echo $role['role_id']; ?>"
                                                        <?php foreach ($user['roles'] as $userRole): ?>
                                                            <?php if ($userRole['role_id'] == $role['role_id']) echo 'selected'; ?>
                                                        <?php endforeach; ?>>
                                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_role" class="btn btn-primary">
                                                <i class="fas fa-user-tag"></i> Assign Role
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle active state for menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
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