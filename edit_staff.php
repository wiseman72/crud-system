<?php
session_start();
require_once("connection.php");

if (!$conn) {
  die("Connection is not established");
}

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $query = "SELECT * FROM staff WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!$result) {
    die("Query failed: " . $conn->error);
  }
  $row = $result->fetch_assoc();
}

if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $nrc = $_POST['nrc'];
  $phone = $_POST['phone'];
  $role = $_POST['role'];
  $branch = $_POST['branch'];
  $gender = $_POST['gender'];
  $id = $_POST['id'];

  $query = "UPDATE staff SET Name = ?, NRC = ?, Phone = ?, Role = ?, Branch = ?, Gender = ? WHERE id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ssssssi", $name, $nrc, $phone, $role, $branch, $gender, $id);

  if ($stmt->execute()) {
    echo "Staff member updated successfully";
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
  <title>Edit Staff</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
    }

    form {
      background: #fff;
      padding: 25px;
      margin: 30px 15px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 500px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      color: #444;
    }

    input[type="text"],
    select {
      width: 100%;
      padding: 12px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    input[type="text"]:focus,
    select:focus {
      border-color: #007bff;
      outline: none;
    }

    input[type="submit"] {
      background: #28a745;
      color: white;
      border: none;
      padding: 12px 18px;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s;
    }

    input[type="submit"]:hover {
      background: #218838;
    }

    @media (max-width: 600px) {
      form {
        padding: 20px;
        margin: 20px;
      }
      input[type="submit"] {
        font-size: 15px;
        padding: 10px;
      }
    }
  </style>
</head>
<body>

  <form action="" method="post">
    <h2>Edit Staff</h2>
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required value="<?php echo $row['Name']; ?>">

    <label for="nrc">NRC:</label>
    <input type="text" id="nrc" name="nrc" required value="<?php echo $row['NRC']; ?>">

    <label for="phone">Phone:</label>
    <input type="text" id="phone" name="phone" required value="<?php echo $row['Phone']; ?>">

    <label for="role">Role:</label>
    <input type="text" id="role" name="role" required value="<?php echo $row['Role']; ?>">

    <label for="branch">Branch:</label>
    <input type="text" id="branch" name="branch" required value="<?php echo $row['Branch']; ?>">

    <label for="gender">Gender:</label>
    <select id="gender" name="gender" required>
      <option value="Male" <?php if ($row['Gender'] == 'Male') echo 'selected'; ?>>Male</option>
      <option value="Female" <?php if ($row['Gender'] == 'Female') echo 'selected'; ?>>Female</option>
      <option value="Other" <?php if ($row['Gender'] == 'Other') echo 'selected'; ?>>Other</option>
    </select>

    <input type="submit" name="submit" value="Update Staff">
  </form>

</body>
</html>
