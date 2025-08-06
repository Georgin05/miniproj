

<?php
session_start();
include 'config.php'; // Make sure it connects to your DB

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']) ?? '';
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

            // Redirect by role
            if ($role === 'admin') {
                header("Location: dashboard.php");
            } elseif ($role === 'staff') {
                header("Location: staff_dashboard.php");
            } else {
                $error = "Unknown role.";
            }
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ Invalid username or password.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | WMS</title>
  <style>
    /* Reset */
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
    input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      font-size: 16px;
      border: 1.8px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
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

    .register-link {
      margin-top: 25px;
      font-size: 14px;
      color: #444;
    }

    .register-link a {
      color: #0d5ea6;
      text-decoration: none;
      font-weight: 600;
      transition: text-decoration 0.3s ease;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    .error-message {
      margin-bottom: 18px;
      color: #d32f2f;
      font-weight: 600;
      font-size: 14px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
      <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="login.php" method="post" autocomplete="off">
      <div class="input-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus />
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />
      </div>

      <button type="submit">Login</button>
    </form>

    <p class="register-link">
      First time here? <a href="register.php">Register an account</a>
    </p>
  </div>
</body>
</html>