<?php
require 'config.php'; // Database connection
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($confirm) || empty($role)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (!in_array($role, ['admin', 'staff'])) {
        $message = "Invalid role selected.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Username already taken.";
        } else {
            $check->close();

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed, $role);

            if ($stmt->execute()) {
                header("Location: login.php?register=success");
                exit();
            } else {
                $message = "❌ Registration failed. Try again.";
            }
            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <title>Register User</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #0D5EA6;
      background-image: url('https://images.unsplash.com/photo-1581093588401-2688df26daba?auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      padding: 20px;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px 35px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      width: 350px;
      max-width: 100%;
      text-align: center;
    }

    h2 {
      margin-bottom: 30px;
      color: #222;
      font-weight: 700;
      font-size: 28px;
    }

    .input-group {
      margin-bottom: 22px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      font-size: 15px;
      color: #555;
    }

    input[type="text"],
    input[type="password"],
    select {
      width: 100%;
      padding: 12px 15px;
      font-size: 16px;
      border: 1.8px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    select:focus {
      border-color: #0d5ea6;
      outline: none;
    }

    button[type="submit"] {
      width: 100%;
      padding: 14px;
      font-size: 18px;
      font-weight: 700;
      background-color: #0d5ea6;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
      background-color: #094a82;
    }

    .error-message {
      margin-top: 15px;
      color: #d32f2f;
      font-weight: 600;
      font-size: 14px;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h2>Register</h2>
  <form method="POST" action="">
    <div class="input-group">
      <label>Username</label>
      <input type="text" name="username" required>
    </div>

    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <div class="input-group">
      <label>Re-enter Password</label>
      <input type="password" name="confirm_password" required>
    </div>

    <div class="input-group">
      <label>User Role</label>
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
      </select>
    </div>

    <button type="submit">Register</button>

    <?php if (!empty($message)): ?>
      <div class="error-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
  </form>
</div>

</body>
</html>
