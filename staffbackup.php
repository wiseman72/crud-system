<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      margin: 0;
      padding: 0;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 20px;
      background: #fff;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
    }

    /* Search bar */
    .search-bar-container {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
    .search-bar {
      flex: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    /* Buttons */
    .btn {
      display: inline-block;
      padding: 8px 14px;
      margin: 4px 2px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s ease;
      color: #fff;
    }
    .btn:hover { opacity: 0.9; }

    .btn-blue { background: #007bff; }
    .btn-green { background: #28a745; }
    .btn-red { background: #dc3545; }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #fff;
      border-radius: 6px;
      overflow: hidden;
    }
    table th, table td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }
    table th {
      background: #007bff;
      color: #fff;
    }
    table tr:nth-child(even) {
      background: #f9f9f9;
    }
    table tr:hover {
      background: #f1f1f1;
    }

    /* Messages */
    .finance-summary {
      padding: 12px;
      background: #ffefc1;
      border: 1px solid #ffd56b;
      border-radius: 6px;
      margin: 15px 0;
      color: #7a5c00;
    }

    /* Mobile */
    @media (max-width: 768px) {
      .search-bar-container {
        flex-direction: column;
      }
      table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <?php include 'header.php'; ?>
      <h2>Staff</h2>

      <form action="" method="post" class="search-bar-container">
        <input type="text" name="search_term" class="search-bar" placeholder="Search staff...">
        <input type="submit" name="search" class="btn btn-blue" value="Search">
      </form>

      <a href="add_staff.php" class="btn btn-blue">Add New Staff</a>
      <button class="btn btn-blue" onclick="printStaffList()">Print Staff List</button>

      <?php
      require_once("connection.php");
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      $user_branch = $_SESSION['branch'] ?? '';
      $user_role = $_SESSION['role'] ?? '';
      if (!$conn) {
        echo '<div class="finance-summary">Connection is not established</div>';
      } else {
        // Branch restriction: Only Admin/Manager can view all branches, others see only their branch
        if (isset($_POST['search'])) {
          $search_term = '%' . $_POST['search_term'] . '%';
          if (in_array($user_role, ['Admin', 'Manager'])) {
            $query = "SELECT id, Name, NRC, Phone, Role, Branch, Gender FROM `staff` WHERE (Name LIKE ? OR NRC LIKE ? OR Phone LIKE ? OR Role LIKE ? OR Branch LIKE ? OR Gender LIKE ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
          } else {
            // Restrict staff view to user's branch
            $query = "SELECT id, Name, NRC, Phone, Role, Branch, Gender FROM `staff` WHERE (Name LIKE ? OR NRC LIKE ? OR Phone LIKE ? OR Role LIKE ? OR Branch LIKE ? OR Gender LIKE ?) AND Branch=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $user_branch);
          }
          $stmt->execute();
          $result = $stmt->get_result();
        } else {
          if (in_array($user_role, ['Admin', 'Manager'])) {
            $query = "SELECT id, Name, NRC, Phone, Role, Branch, Gender FROM `staff`";
            $result = $conn->query($query);
          } else {
            $query = "SELECT id, Name, NRC, Phone, Role, Branch, Gender FROM `staff` WHERE Branch=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $user_branch);
            $stmt->execute();
            $result = $stmt->get_result();
          }
        }
        if (!$result) {
          echo '<div class="finance-summary">Query failed: ' . $conn->error . '</div>';
        } else if ($result->num_rows > 0) {
      ?>
      <table id="staff-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>NRC</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Branch</th>
            <th>Gender</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['Name']); ?></td>
            <td><?php echo htmlspecialchars($row['NRC']); ?></td>
            <td><?php echo htmlspecialchars($row['Phone']); ?></td>
            <td><?php echo htmlspecialchars($row['Role']); ?></td>
            <td><?php echo htmlspecialchars($row['Branch']); ?></td>
            <td><?php echo htmlspecialchars($row['Gender']); ?></td>
            <td>
              <a class="btn btn-green" href="edit_staff.php?id=<?php echo $row['id']; ?>">Edit</a>
              <a class="btn btn-red" href="delete_staff.php?id=<?php echo $row['id']; ?>" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else {
          if (isset($_POST['search'])) {
            echo '<div class="finance-summary">No search results found</div>';
          } else {
            echo '<div class="finance-summary">No records found</div>';
          }
        }
        if (isset($result) && method_exists($result, 'free_result')) $result->free_result();
        $conn->close();
      }
      ?>
    </div>
  </div>
  <script>
    function printStaffList() {
      var table = document.getElementById('staff-table');
      var win = window.open('', '', 'height=500,width=700');
      win.document.write(table.outerHTML);
      win.document.close();
      win.print();
      win.close();
    }
  </script>
</body>
</html>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
    .dashboard-container { display: flex; min-height: 100vh; }
    .main-content { flex: 1; padding: 20px; background: #fff; }
    h2 { margin-bottom: 20px; color: #333; }
    .search-bar-container { display: flex; gap: 10px; margin-bottom: 20px; }
    .search-bar { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    .btn { display: inline-block; padding: 8px 14px; margin: 4px 2px; border: none; border-radius: 6px; text-decoration: none; font-size: 14px; cursor: pointer; transition: background 0.3s ease; color: #fff; }
    .btn:hover { opacity: 0.9; }
    .btn-blue { background: #007bff; }
    .btn-green { background: #28a745; }
    .btn-red { background: #dc3545; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 6px; overflow: hidden; }
    table th, table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
    table th { background: #007bff; color: #fff; }
    table tr:nth-child(even) { background: #f9f9f9; }
    table tr:hover { background: #f1f1f1; }
    .profile-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; }
    .finance-summary { padding: 12px; background: #ffefc1; border: 1px solid #ffd56b; border-radius: 6px; margin: 15px 0; color: #7a5c00; }
    @media (max-width: 768px) {
      .search-bar-container { flex-direction: column; }
      table { display: block; overflow-x: auto; white-space: nowrap; }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <?php include 'header.php'; ?>
      <h2>Staff</h2>

      <form action="" method="post" class="search-bar-container">
        <input type="text" name="search_term" class="search-bar" placeholder="Search staff...">
        <input type="submit" name="search" class="btn btn-blue" value="Search">
      </form>

      <a href="add_staff.php" class="btn btn-blue">Add New Staff</a>
      <button class="btn btn-blue" onclick="printStaffList()">Print Staff List</button>

      <?php
      require_once("connection.php");
      if (session_status() === PHP_SESSION_NONE) session_start();

      $user_branch = $_SESSION['branch'] ?? '';
      $user_role = $_SESSION['role'] ?? '';

      if (!$conn) {
          echo '<div class="finance-summary">Connection is not established</div>';
      } else {
          if (isset($_POST['search'])) {
              $search_term = '%' . $_POST['search_term'] . '%';
              if (in_array($user_role, ['Admin', 'Manager'])) {
                  $query = "SELECT id, name, role, branch, profile_picture FROM `users` WHERE name LIKE ? OR role LIKE ? OR branch LIKE ?";
                  $stmt = $conn->prepare($query);
                  $stmt->bind_param("sss", $search_term, $search_term, $search_term);
              } else {
                  $query = "SELECT id, name, role, branch, profile_picture FROM `users` WHERE (name LIKE ? OR role LIKE ? OR branch LIKE ?) AND branch=?";
                  $stmt = $conn->prepare($query);
                  $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $user_branch);
              }
              $stmt->execute();
              $result = $stmt->get_result();
          } else {
              if (in_array($user_role, ['Admin', 'Manager'])) {
                  $result = $conn->query("SELECT id, name, role, branch, profile_picture FROM `users`");
              } else {
                  $stmt = $conn->prepare("SELECT id, name, role, branch, profile_picture FROM `users` WHERE branch=?");
                  $stmt->bind_param("s", $user_branch);
                  $stmt->execute();
                  $result = $stmt->get_result();
              }
          }

          if (!$result) {
              echo '<div class="finance-summary">Query failed: ' . $conn->error . '</div>';
          } else if ($result->num_rows > 0) {
      ?>
      <table id="staff-table">
        <thead>
          <tr>
            <th>Profile</th>
            <th>Name</th>
            <th>Role</th>
            <th>Branch</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td>
              <?php if (!empty($row['profile_picture']) && file_exists('uploads/' . $row['profile_picture'])): ?>
                <img class="profile-img" src="uploads/<?= htmlspecialchars($row['profile_picture']); ?>" alt="Profile">
              <?php else: ?>
                <img class="profile-img" src="uploads/default.png" alt="Profile">
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['role']); ?></td>
            <td><?= htmlspecialchars($row['branch']); ?></td>
            <td>
              <a class="btn btn-green" href="edit_staff.php?id=<?= $row['id']; ?>">Edit</a>
              <a class="btn btn-red" href="delete_staff.php?id=<?= $row['id']; ?>" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php
          } else {
              echo '<div class="finance-summary">' . (isset($_POST['search']) ? 'No search results found' : 'No records found') . '</div>';
          }

          if (isset($result) && method_exists($result, 'free_result')) $result->free_result();
          $conn->close();
      }
      ?>
    </div>
  </div>
  <script>
    function printStaffList() {
      var table = document.getElementById('staff-table');
      var win = window.open('', '', 'height=500,width=700');
      win.document.write(table.outerHTML);
      win.document.close();
      win.print();
      win.close();
    }
  </script>
</body>
</html>
