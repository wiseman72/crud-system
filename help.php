<?php
// Include database connection if needed
// require_once("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Base styles to match the dashboard */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f7f9;
      margin: 0;
      color: #333;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 30px;
      background-color: #fff;
      box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }

    h2 {
      color: #333;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .section {
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .section:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    h3 {
      color: #007bff;
      margin-bottom: 10px;
    }

    ul, dl {
      margin-left: 20px;
      margin-bottom: 10px;
    }

    li, dd {
      margin-bottom: 8px;
      font-size: 14px;
    }

    dt {
      font-weight: bold;
    }

    a {
      color: #4a90e2;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <?php include 'header.php'; ?>
      <h2>Help</h2>

      <div class="section">
        <h3>System Overview</h3>
        <p>Our sales system is designed to help you track and manage your sales data. You can record sales, view reports, and print reports.</p>
      </div>

      <div class="section">
        <h3>User Guide</h3>
        <ul>
          <li>To log in, enter your username and password in the login form.</li>
          <li>To record a sale, click on the "Record Sale" button and fill out the sales form.</li>
          <li>To view sales reports, click on the "View Sales" button.</li>
          <li>To print sales reports, click on the "Print Sales" button.</li>
        </ul>
      </div>

      <div class="section">
        <h3>Frequently Asked Questions (FAQs)</h3>
        <dl>
          <dt>Q: What happens if I enter incorrect data?</dt>
          <dd>A: You can edit the sales record by contacting the administrator.</dd>
          <dt>Q: How do I reset my password?</dt>
          <dd>A: Contact the administrator to reset your password.</dd>
        </dl>
      </div>

      <div class="section">
        <h3>Troubleshooting Tips</h3>
        <ul>
          <li>If you encounter an error message, try refreshing the page or contacting the administrator.</li>
          <li>If you have trouble logging in, ensure your username and password are correct.</li>
        </ul>
      </div>

      <div class="section">
        <h3>Contact Information</h3>
        <p>Email: <a href="mailto:wisemanmilimo@gmail.com">wisemanmilimo@gmail.com</a></p>
        <p>Phone: +260 972445452</p>
      </div>
    </div>
  </div>
</body>
</html>
