<?php
session_start();
require 'conn.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "❌ Please fill in both fields.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $full_name, $password_hash, $user_type);
            $stmt->fetch();

            if (password_verify($password, $password_hash)) {
                // Set session values
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user_type
                if ($user_type === 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } elseif ($user_type === 'staff') {
                    header("Location: staff_dash.php");
                    exit();
                } else {
                    $message = "❌ Unknown user type.";
                }
            } else {
                $message = "❌ Incorrect password.";
            }
        } else {
            $message = "❌ Email not found.";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WarehousePro - Login</title>
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
            --danger: #ef4444;
            --success: #10b981;
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
        }

        /* Header Styles */
        header {
            background: var(--surface);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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

        /* Login Section */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 4rem 0;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('') center/cover no-repeat;
        }

        .login-container {
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

        .login-image {
            flex: 1;
            padding: 40px;
            color: var(--text);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-image h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--accent);
        }

        .login-image p {
            margin-bottom: 30px;
            color: var(--text-muted);
        }

        .login-image ul {
            list-style: none;
            margin-bottom: 40px;
        }

        .login-image li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
        }

        .login-image i {
            color: var(--accent);
        }

        .login-form {
            flex: 1;
            padding: 60px 40px;
            background: var(--surface-light);
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 32px;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
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
            padding: 14px 20px 14px 45px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 16px;
            transition: var(--transition);
            background: var(--surface);
            color: var(--text);
        }

        .form-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }

        .forgot-password {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            background-color: var(--accent);
            color: #000;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 16px;
            box-shadow: var(--shadow);
            width: 100%;
        }

        .btn:hover {
            background-color: var(--accent-dark);
            transform: translateY(-2px);
        }

        .form-footer {
            margin-top: 30px;
            text-align: center;
        }

        .form-footer p {
            margin-top: 20px;
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

        /* Social Login */
        .social-login {
            margin: 30px 0;
            text-align: center;
        }

        .social-login p {
            position: relative;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            height: 1px;
            width: 30%;
            background-color: var(--border);
            top: 50%;
        }

        .social-login p::before {
            left: 0;
        }

        .social-login p::after {
            right: 0;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: var(--transition);
            cursor: pointer;
        }

        .social-icon:hover {
            transform: translateY(-3px);
        }

        .google {
            background-color: #DB4437;
        }

        .microsoft {
            background-color: #00A1F1;
        }

        .linkedin {
            background-color: #0077B5;
        }

        /* Footer */
        footer {
            background: var(--surface);
            color: var(--text-muted);
            padding: 2rem 0;
            border-top: 1px solid var(--border);
            text-align: center;
        }

        .copyright {
            font-size: 14px;
        }

        /* Message Styles */
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            border-left: 3px solid transparent;
        }

        .message-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left-color: var(--danger);
            color: var(--danger);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 600px;
            }
            
            .login-image {
                padding: 30px;
            }
            
            .login-form {
                padding: 40px 30px;
            }
        }

        @media (max-width: 576px) {
            .login-image h2 {
                font-size: 26px;
            }
            
            .form-header h2 {
                font-size: 26px;
            }
            
            .form-control {
                padding: 12px 15px 12px 40px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
        </div>
    </header>

    <!-- Login Section -->
    <section class="login-section">
        <div class="container">
            <div class="login-container">
                <div class="login-image">
                    <h2>Welcome to WarehousePro</h2>
                    <p>Log in to access your warehouse management dashboard and streamline your inventory operations.</p>
                    
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Real-time inventory tracking</li>
                        <li><i class="fas fa-check-circle"></i> Automated stock management</li>
                        <li><i class="fas fa-check-circle"></i> Advanced reporting tools</li>
                        <li><i class="fas fa-check-circle"></i> Multi-location support</li>
                    </ul>
                    
                    <p>Don't have an account? <a href="register.php" style="color: var(--accent); font-weight: 500;">click here</a></p>
                </div>
                
                <div class="login-form">
                    <div class="form-header">
                        <h2>Sign In</h2>
                        <p>Enter your credentials to access your account</p>
                        <?php if (!empty($message)): ?>
                            <div class="message message-error">
                                <i class="fas fa-exclamation-circle"></i> <?= $message ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form id="loginForm" method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                        </div>
                        
                        <div class="remember-forgot">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </div>
                        
                        <div class="social-login">
                            <p>Or sign in with</p>
                            <div class="social-icons">
                                <div class="social-icon google">
                                    <i class="fab fa-google"></i>
                                </div>
                                <div class="social-icon microsoft">
                                    <i class="fab fa-microsoft"></i>
                                </div>
                                <div class="social-icon linkedin">
                                    <i class="fab fa-linkedin-in"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-footer">
                            <p>By signing in, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
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
                &copy; 2023 WarehousePro Management System. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Social login buttons
        document.querySelectorAll('.social-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const provider = this.classList.contains('google') ? 'Google' : 
                               this.classList.contains('microsoft') ? 'Microsoft' : 'LinkedIn';
                alert(`Redirecting to ${provider} login...`);
                // In a real app, this would redirect to the OAuth provider
            });
        });

        // Forgot password functionality
        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            const email = prompt('Please enter your email address to reset your password:');
            if (email) {
                alert(`Password reset link has been sent to ${email}`);
            }
        } );
    </script>
</body>
</html>