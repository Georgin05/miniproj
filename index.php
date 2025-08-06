<?php
include 'conn.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Moveto WMS | Warehouse Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
    
    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
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
    
    .navbar-brand {
      font-weight: bold;
      color: var(--accent);
      font-size: 1.5rem;
      display: flex;
      align-items: center;
    }
    
    .navbar-brand i {
      margin-right: 0.5rem;
      font-size: 1.8rem;
    }
    
    .nav-link {
      color: var(--text);
      padding: 0.5rem 1rem;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .nav-link:hover {
      color: var(--accent);
    }
    
    .login-btn {
      background: var(--accent);
      color: #000;
      font-weight: 600;
      padding: 0.5rem 1.5rem;
      border-radius: 8px;
      transition: var(--transition);
      text-decoration: none;
    }
    
    .login-btn:hover {
      background: var(--accent-dark);
      transform: translateY(-2px);
      color: #000;
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                  url('https://prologisfreight.com/assets/images/DIGITALISATION/right-side-image.jpg') center/cover no-repeat;
      padding: 6rem 2rem;
      text-align: center;
      color: white;
      margin-bottom: 3rem;
      position: relative;
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1;
    }
    
    .hero .container {
      position: relative;
      z-index: 2;
    }
    
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
      margin-bottom: 1.5rem;
      color: var(--accent);
    }
    
    .hero p {
      font-size: 1.25rem;
      max-width: 800px;
      margin: 0 auto 2rem;
      color: var(--text);
    }
    
    /* Features Section */
    .features {
      padding: 0 2rem 4rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .features h2 {
      text-align: center;
      font-weight: bold;
      margin-bottom: 3rem;
      color: var(--accent);
      font-size: 2.5rem;
    }
    
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .feature-card {
      background: var(--surface);
      border-radius: 12px;
      padding: 2rem;
      transition: var(--transition);
      border: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow);
      border-color: var(--accent);
    }
    
    .feature-icon {
      font-size: 2.5rem;
      color: var(--accent);
      margin-bottom: 1.5rem;
      background: rgba(255, 215, 0, 0.1);
      width: 70px;
      height: 70px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .feature-card h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--text);
    }
    
    .feature-card p {
      color: var(--text-muted);
      margin-bottom: 0;
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
    
    /* Footer */
    footer {
      background: var(--surface);
      color: var(--text-muted);
      padding: 2rem 0;
      margin-top: auto;
      border-top: 1px solid var(--border);
    }
    
    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
      text-align: center;
    }
    
    .footer-links {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .footer-links a {
      color: var(--text-muted);
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .footer-links a:hover {
      color: var(--accent);
    }
    
    .copyright {
      font-size: 0.9rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
      .hero h1 {
        font-size: 2.5rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .features h2 {
        font-size: 2rem;
      }
    }
    
    @media (max-width: 768px) {
      .hero {
        padding: 4rem 1rem;
      }
      
      .hero h1 {
        font-size: 2rem;
      }
      
      .feature-grid {
        grid-template-columns: 1fr;
      }
      
      .feature-card {
        padding: 1.5rem;
      }
    }
    
    @media (max-width: 576px) {
      .hero h1 {
        font-size: 1.8rem;
      }
      
      .features h2 {
        font-size: 1.8rem;
        margin-bottom: 2rem;
      }
      
      .footer-links {
        flex-direction: column;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Header with Navigation -->
  <header>
    <div class="container">
      <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <i class="fas fa-warehouse"></i>WarehousePro
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
              <li class="nav-item">
                <a class="nav-link" href="features">Features</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Pricing</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Solutions</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Resources</a>
              </li>
            </ul>
            <a href="login.php" class="login-btn">Login</a>
          </div>
        </div>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1>Optimize Your Warehouse Operations</h1>
      <p>Moveto WMS provides real-time inventory tracking, automated workflows, and intelligent analytics to streamline your warehouse management.</p>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features">
    <h2>Powerful Warehouse Management Features</h2>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="bi bi-clipboard2-data"></i>
        </div>
        <h3>Real-time Inventory</h3>
        <p>Track inventory levels, locations, and movements across your warehouse in real-time with our advanced tracking system.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="bi bi-robot"></i>
        </div>
        <h3>Automated Workflows</h3>
        <p>Reduce manual work with automated receiving, put-away, picking, and shipping processes that save time and reduce errors.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <h3>Advanced Analytics</h3>
        <p>Gain valuable insights into your warehouse operations with customizable reports and data visualization tools.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="bi bi-phone"></i>
        </div>
        <h3>Mobile Ready</h3>
        <p>Manage your warehouse from anywhere with our fully responsive mobile interface and dedicated mobile apps.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-links">
        <a href="#">About Us</a>
        <a href="#">Contact</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
      </div>
      <div class="copyright">
        &copy; 2023 Moveto WMS. All rights reserved.
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>