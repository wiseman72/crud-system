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
    table th, table td { padding: 12px 10px; border: 1px solid #ddd; text-align: left; }
    table th { background: #007bff; color: #fff; }
    table tr:nth-child(even) { background: #f9f9f9; }
    table tr:hover { background: #f1f1f1; }

    img.profile-thumb { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer; }

    /* Lightbox */
    .lightbox {
      display: none;
      position: fixed;
      z-index: 999;
      padding-top: 80px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background: rgba(0,0,0,0.8);
    }
    .lightbox-content {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 80%;
    }
    .lightbox-close {
      position: absolute;
      top: 30px;
      right: 40px;
      color: #fff;
      font-size: 40px;
      font-weight: bold;
      cursor: pointer;
    }

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
        echo '<div style="color:red;">Connection is not established</div>';
      } else {
        if (isset($_POST['search'])) {
          $search_term = '%' . $_POST['search_term'] . '%';
          if (in_array($user_role, ['Admin', 'Manager'])) {
            $query = "SELECT * FROM `staff` WHERE Name LIKE ? OR NRC LIKE ? OR Phone LIKE ? OR Role LIKE ? OR Branch LIKE ? OR Gender LIKE ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
          } else {
            $query = "SELECT * FROM `staff` WHERE (Name LIKE ? OR NRC LIKE ? OR Phone LIKE ? OR Role LIKE ? OR Branch LIKE ? OR Gender LIKE ?) AND Branch=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $user_branch);
          }
          $stmt->execute();
          $result = $stmt->get_result();
        } else {
          if (in_array($user_role, ['Admin', 'Manager'])) {
            $result = $conn->query("SELECT * FROM `staff`");
          } else {
            $stmt = $conn->prepare("SELECT * FROM `staff` WHERE Branch=?");
            $stmt->bind_param("s", $user_branch);
            $stmt->execute();
            $result = $stmt->get_result();
          }
        }

        if ($result && $result->num_rows > 0) {
      ?>
      <table id="staff-table">
        <thead>
          <tr>
            <th>Profile</th>
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
            <td>
              <?php 
                $profile = !empty($row['profile_picture']) && file_exists('uploads/'.$row['profile_picture']) 
                  ? 'uploads/'.htmlspecialchars($row['profile_picture']) 
                  : 'uploads/default.png'; 
              ?>
              <img class="profile-thumb" src="<?= $profile ?>" alt="Profile" onclick="openLightbox('<?= $profile ?>')">
            </td>
            <td><?= htmlspecialchars($row['Name']) ?></td>
            <td><?= htmlspecialchars($row['NRC']) ?></td>
            <td><?= htmlspecialchars($row['Phone']) ?></td>
            <td><?= htmlspecialchars($row['Role']) ?></td>
            <td><?= htmlspecialchars($row['Branch']) ?></td>
            <td><?= htmlspecialchars($row['Gender']) ?></td>
            <td>
              <a class="btn btn-green" href="edit_staff.php?id=<?= $row['id'] ?>">Edit</a>
              <a class="btn btn-red" href="delete_staff.php?id=<?= $row['id'] ?>" onClick="return confirm('Are you sure you want to delete?')">Delete</a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php
        } else {
          echo '<div style="color:red;">' . (isset($_POST['search']) ? 'No search results found' : 'No records found') . '</div>';
        }
        if (isset($result) && method_exists($result, 'free_result')) $result->free_result();
        $conn->close();
      }
      ?>
    </div>
  </div>

  <!-- Lightbox Modal -->
  <div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img class="lightbox-content" id="lightbox-img">
  </div>

  <script>
    function printStaffList() {
      var table = document.getElementById('staff-table');
      var win = window.open('', '', 'height=500,width=800');
      win.document.write('<html><head><title>Staff List</title></head><body>');
      win.document.write(table.outerHTML);
      win.document.write('</body></html>');
      win.document.close();
      win.print();
      win.close();
    }

    function openLightbox(src) {
      document.getElementById('lightbox').style.display = 'block';
      document.getElementById('lightbox-img').src = src;
    }

    function closeLightbox() {
      document.getElementById('lightbox').style.display = 'none';
    }
  </script>
</body>
</html>
