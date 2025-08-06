<?php
require 'conn.php'; // DB connection
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Grab inputs
    $fullName   = trim($_POST['fullName'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $userType   = $_POST['userType'] ?? '';
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirmPassword'] ?? '';

    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($userType) || empty($password) || empty($confirm)) {
        $message = "❌ All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
    } elseif ($password !== $confirm) {
        $message = "❌ Passwords do not match.";
    } elseif (!in_array($userType, ['admin', 'staff'])) {
        $message = "❌ Invalid user type selected.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Email already registered.";
        } else {
            $check->close();

            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, user_type, password_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullName, $email, $phone, $userType, $hashed);

            if ($stmt->execute()) {
                header("Location: login.php?register=success");
                exit();
            } else {
                $message = "❌ Registration failed. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WarehousePro - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #121212;
            --surface: #1e1e1e;
            --surface-light: #2a2a2a;
            --border: #333333;
            --text: #f5f5f5;
            --text-muted: #aaaaaa;
            --accent: #FFD700;
            --accent-dark: #d4b000;
            --danger: #e76f51;
            --success: #2a9d8f;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: var(--surface);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 32px;
            color: var(--accent);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: 1px;
        }

        .logo-text span {
            color: var(--accent);
        }

        .theme-toggle {
            background: transparent;
            border: none;
            color: var(--text);
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
            padding: 8px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Register Section */
        .register-section {
            display: flex;
            flex: 1;
            align-items: center;
            padding: 4rem 0;
        }

        .register-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .register-image {
            flex: 1;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('') center/cover no-repeat;
            padding: 3rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-image h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: var(--accent);
        }

        .register-image p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .register-image ul {
            list-style: none;
            margin-bottom: 2.5rem;
        }

        .register-image li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .register-image i {
            color: var(--accent);
        }

        .register-form {
            flex: 1;
            padding: 3rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--accent);
        }

        .form-header p {
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            background: var(--surface-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text);
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }

        .select-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: var(--surface-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--text);
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23aaaaaa' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }

        .select-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }

        .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .radio-option input {
            accent-color: var(--accent);
        }

        .btn {
            display: inline-block;
            padding: 0.875rem 2rem;
            background: var(--accent);
            color: #000;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: var(--transition);
        }

        .btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
        }

        .form-footer p {
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-danger {
            background-color: var(--danger);
            color: white;
        }

        /* Footer */
        footer {
            background: var(--surface);
            color: var(--text-muted);
            padding: 2rem 0;
            margin-top: auto;
            border-top: 1px solid var(--border);
        }

        .copyright {
            font-size: 0.9rem;
            text-align: center;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .register-container {
                flex-direction: column;
                max-width: 600px;
            }
            
            .register-image {
                padding: 2rem;
            }
            
            .register-form {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .register-image h2, .form-header h2 {
                font-size: 1.75rem;
            }
            
            .form-control {
                padding: 0.75rem 1rem 0.75rem 3rem;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="logo-text">Warehouse<span>Pro</span></div>
            </div>
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </header>

    <!-- Register Section -->
    <section class="register-section">
        <div class="container">
            <div class="register-container">
                <div class="register-image">
                    <h2>Join WarehousePro</h2>
                    <p>Register your account to access our comprehensive warehouse management system and optimize your inventory operations.</p>
                    
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Streamline your warehouse operations</li>
                        <li><i class="fas fa-check-circle"></i> Track inventory in real-time</li>
                        <li><i class="fas fa-check-circle"></i> Generate detailed reports</li>
                        <li><i class="fas fa-check-circle"></i> Manage multiple warehouse locations</li>
                    </ul>
                    
                    <p>Already have an account? <a href="login.php" style="color: var(--accent); font-weight: 500;">Sign in here</a></p>
                </div>
                
                <div class="register-form">
                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>Fill in your details to register</p>
                    </div>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="registrationForm" method="POST" action="">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="fullName" name="fullName" class="form-control" placeholder="John Smith" 
                                       value="<?php echo htmlspecialchars($_POST['fullName'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (555) 123-4567" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>User Type</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="admin" name="userType" value="admin" 
                                           <?php echo ($_POST['userType'] ?? '') === 'admin' ? 'checked' : ''; ?> required>
                                    <label for="admin">Administrator</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="staff" name="userType" value="staff" 
                                           <?php echo ($_POST['userType'] ?? '') === 'staff' ? 'checked' : ''; ?> required>
                                    <label for="staff">Staff Member</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirm your password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">
                                <i class="fas fa-user-plus"></i> Register Account
                            </button>
                        </div>
                        
                        <div class="form-footer">
                            <p>By registering, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> WarehousePro Management System. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');
        
        function toggleTheme() {
            document.body.classList.toggle('light-mode');
            
            const isLightMode = document.body.classList.contains('light-mode');
            localStorage.setItem('lightMode', isLightMode);
            
            // Change icon based on mode
            if (isLightMode) {
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        }
        
        themeToggle.addEventListener('click', toggleTheme);

        // Check for saved theme preference
        if (localStorage.getItem('lightMode') === 'true') {
            document.body.classList.add('light-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }
    </script>
</body>
</html>