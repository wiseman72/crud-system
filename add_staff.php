<?php
session_start();
require_once("connection.php");

if (!$conn) {
  die("Connection is not established");
}

if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $nrc = $_POST['nrc'];
  $phone = $_POST['phone'];
  $role = $_POST['role'];
  $branch = $_POST['branch'];
  $gender = $_POST['gender'];

  $query = "INSERT INTO staff (Name, NRC, Phone, Role, Branch, Gender) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ssssss", $name, $nrc, $phone, $role, $branch, $gender);

  if ($stmt->execute()) {
    echo "New staff member added successfully";
    echo "<p><a href='staff.php'>Back to Staff List</a></p>";
    echo "<p><a href='index.php'>Back to Menu</a></p>";
  } else {
    echo "Error: " . $conn->error;
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Staff</title>
  <style>
    /* General page styling */
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #2c3e50;
    }

    form {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
    }

    input[type="text"],
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    input[type="text"]:focus,
    select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 5px rgba(52,152,219,0.4);
    }

    input[type="submit"] {
      width: 100%;
      background-color: #27ae60;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #219150;
    }

    /* Links after submit */
    p a {
      display: inline-block;
      margin-top: 10px;
      color: #3498db;
      text-decoration: none;
      font-weight: bold;
    }

    p a:hover {
      text-decoration: underline;
    }

    /* Mobile responsiveness */
    @media (max-width: 600px) {
      body {
        padding: 10px;
      }

      form {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <h2>Add Staff Member</h2>
  <form action="" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>

    <label for="nrc">NRC:</label>
    <input type="text" id="nrc" name="nrc" required>

    <label for="phone">Phone:</label>
    <input type="text" id="phone" name="phone" required>

    <label for="role">Role:</label>
    <input type="text" id="role" name="role" required>

    <label for="branch">Branch:</label>
    <input type="text" id="branch" name="branch" required>

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
      <option value="">Select Gender</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
      <option value="Other">Other</option>
    </select>

    <input type="submit" name="submit" value="Add Staff">
  </form>
</body>
</html>
