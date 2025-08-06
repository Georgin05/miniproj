<?php
session_start();
include 'conn.php';

$error = "";

// Login Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $password_hash, $role);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role === 'admin') {
                header("Location: dashboard.php");
            } elseif ($role === 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                $error = "Unknown role.";
            }
            exit();
        } else {
            $error = "\u274C Incorrect password.";
        }
    } else {
        $error = "\u274C Invalid username or password.";
    }
    $stmt->close();
}

// Registration Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['reg_username'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "\u274C Passwords do not match.";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "\u274C Username or email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'staff'; // Change if needed

            $insert_stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $fullname, $email, $username, $password_hash, $role);

            if ($insert_stmt->execute()) {
                $error = "\u2705 Registration successful! Please login.";
                echo '<script>document.addEventListener("DOMContentLoaded", function() { 
                        document.getElementById("loginForm").classList.add("active"); 
                        document.getElementById("registerForm").classList.remove("active"); 
                      });</script>';
            } else {
                $error = "\u274C Registration failed. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | WMS</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Import Google Font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

/* Base reset */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
  list-style: none;
  text-decoration: none;
}

body {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(90deg, #e2e2e2, #c9d6ff);
  padding: 20px;
}

.container {
  width: 100%;
  max-width: 850px;
  background: #fff;
  border-radius: 30px;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
  display: flex;
  overflow: hidden;
  flex-direction: column;
}

@media (min-width: 768px) {
  .container {
    flex-direction: row;
    height: 550px;
  }
}

.form-box {
  flex: 1;
  padding: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-box h1,
.form-box h2 {
  font-size: 28px;
  color: #333;
  margin-bottom: 10px;
}

.form-box p {
  color: #777;
  margin-bottom: 20px;
}

.welcome {
  background-color: #cddafd;
  text-align: center;
  padding: 40px;
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
}

@media (min-width: 768px) {
  .welcome {
    border-top-right-radius: 0;
    border-bottom-left-radius: 30px;
  }
}

.input-box {
  position: relative;
  margin-bottom: 20px;
}

.input-box input {
  width: 100%;
  padding: 10px 40px 10px 15px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 8px;
}

.input-box i {
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  font-size: 18px;
  color: #555;
}

.forgot-link {
  text-align: right;
  margin-bottom: 20px;
}

.forgot-link a {
  font-size: 12px;
  color: #555;
}

.btn {
  padding: 10px 20px;
  background: #4b70e2;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  margin-bottom: 15px;
  transition: background 0.3s ease;
}

.btn:hover {
  background: #3557c3;
}

.or {
  text-align: center;
  font-size: 14px;
  color: #666;
  margin-bottom: 10px;
}

.social-icons {
  display: flex;
  justify-content: space-between;
  padding: 0 30px;
}

.social-icons a {
  font-size: 20px;
  color: #555;
  transition: color 0.3s;
}

.social-icons a:hover {
  color: #4b70e2;
}

/* Form toggle transitions */
.toggle-box {
  display: none;
}

.show-login .login-form {
  display: block;
}

.show-register .register-form {
  display: block;
}

.message {
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 6px;
  text-align: center;
  font-size: 14px;
}

.message.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

  </style></head>
<body>
  <div class="auth-container">
    <div class="form-box login-form active" id="loginForm">
      <h2>Login</h2>
      <?php if (!empty($error)): ?>
        <p class="<?= strpos($error, '✅') !== false ? 'success-message' : 'error-message' ?>">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>
      <form method="post">
        <label>Username<input type="text" name="username" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button type="submit" name="login">Login</button>
        <p><button type="button" class="toggle-btn" onclick="toggleForm()">Register here</button></p>
      </form>
    </div>

    <div class="form-box register-form" id="registerForm">
      <h2>Register</h2>
      <?php if (!empty($error)): ?>
        <p class="<?= strpos($error, '✅') !== false ? 'success-message' : 'error-message' ?>">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>
      <form method="post">
        <label>Full Name<input type="text" name="fullname" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Username<input type="text" name="reg_username" required></label>
        <label>Password<input type="password" name="reg_password" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" required></label>
        <button type="submit" name="register">Register</button>
        <p><button type="button" class="toggle-btn" onclick="toggleForm()">Login here</button></p>
      </form>
    </div>
  </div>

  <script>
    function toggleForm() {
      document.getElementById('loginForm').classList.toggle('active');
      document.getElementById('registerForm').classList.toggle('active');
      document.querySelectorAll('.error-message, .success-message').forEach(el => el.textContent = '');
    }
  </script>
</body>
</html>
