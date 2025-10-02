<?php 
require_once("connection.php"); 
//working*
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $username = trim($_POST["name"]); 
    $password = trim($_POST["password"]); 
    $role = trim($_POST["role"]); 
    $branch = trim($_POST["branch"]); 

    $errors = []; 

    // Validate fields
    if (empty($username)) $errors[] = "<font color='red'>The name field is blank</font>"; 
    if (empty($password)) $errors[] = "<font color='red'>The password field is blank</font>"; 
    if (empty($role)) $errors[] = "<font color='red'>Please select a role</font>"; 
    if (empty($branch)) $errors[] = "<font color='red'>Please select a branch</font>"; 

    // Handle profile picture upload
    $profile_filename = null;
    if (!empty($_FILES['profile']['name'])) {
        $target_dir = "uploads/"; // Make sure this folder exists and is writable
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $file_ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg','jpeg','png','gif'];

        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "<font color='red'>Invalid file type. Only jpg, jpeg, png, gif allowed.</font>";
        } else {
            $profile_filename = uniqid() . "." . $file_ext;
            $target_file = $target_dir . $profile_filename;
            if (!move_uploaded_file($_FILES['profile']['tmp_name'], $target_file)) {
                $errors[] = "<font color='red'>Error uploading file.</font>";
            }
        }
    }

    // Show errors if any
    if (!empty($errors)) { 
        foreach ($errors as $error) echo $error . "<br/>"; 
        echo "<br/><a href='javascript:self.history.back();'>Go back</a>"; 
    } else { 
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); 
        // Insert into database including profile_picture
        $query = "INSERT INTO users (`name`, `password`, `role`, `branch`, `profile_picture`) VALUES (?, ?, ?, ?, ?)"; 
        $stmt = mysqli_prepare($conn, $query); 

        if (!$stmt) { 
            echo "<p><font color='red'>Error preparing statement: " . mysqli_error($conn) . "</font></p>"; 
            exit(); 
        } 

        mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $role, $branch, $profile_filename); 
        $result = mysqli_stmt_execute($stmt); 

        if ($result) { 
            echo "<p><font color='green'>User created successfully!</font></p>"; 
            echo "<a href='index.php'>View dashboard</a>"; 
        } else { 
            echo "<p><font color='red'>Error creating user: " . mysqli_error($conn) . "</font></p>"; 
        } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>Create User</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin:0; padding:0; }
    .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    h2 { text-align: center; margin-bottom: 20px; color: #007bff; }
    input[type="text"], input[type="password"], select, input[type="file"] {
        width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
    }
    button { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; width: 100%; }
    button:hover { background: #0056b3; }
    img#preview { display: block; margin: 10px auto; max-width: 150px; border-radius: 50%; border: 2px solid #ccc; }
    .error-message { color: red; margin-bottom: 10px; }
  </style>
  <script>
    // Preview selected profile picture
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('preview');
        output.src = reader.result;
      }
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head> 
<body> 
  <div class="container">
    <h2>Create User</h2> 
    <form action="create_user.php" method="post" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Username" required><br>
      <input type="password" name="password" placeholder="Password" required><br>
      <select name="role" required>
        <option value="">Select Role</option> 
        <option value="Admin">Admin</option> 
        <option value="Manager">Manager</option> 
        <option value="Finance">Finance</option> 
        <option value="Sales">Sales</option> 
        <option value="HR">HR</option> 
      </select><br>
      <select name="branch" required>
        <option value="">Select Branch</option> 
        <option value="Branch 1">Branch 1</option> 
        <option value="Branch 2">Branch 2</option> 
        <option value="Branch 3">Branch 3</option> 
      </select><br>
      <label>Profile Picture:</label>
      <input type="file" name="profile" accept="image/*" onchange="previewImage(event)"><br>
      <img id="preview" src="#" alt="Profile Preview" style="display:none;"><br>
      <button type="submit">Create User</button> 
    </form>
  </div>
  <script>
    // Hide preview image initially
    const preview = document.getElementById('preview');
    const fileInput = document.querySelector('input[name="profile"]');
    fileInput.addEventListener('change', () => { preview.style.display = 'block'; });
  </script>
</body> 
</html>


<?php 
require_once("connection.php"); 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $username = trim($_POST["name"]); 
    $password = trim($_POST["password"]); 
    $role = trim($_POST["role"]); 
    $branch = trim($_POST["branch"]); 

    $errors = []; 

    // Validate fields
    if (empty($username)) $errors[] = "The name field is blank"; 
    if (empty($password)) $errors[] = "The password field is blank"; 
    if (empty($role)) $errors[] = "Please select a role"; 
    if (empty($branch)) $errors[] = "Please select a branch"; 

    // Handle profile picture upload
    $profile_filename = null;
    if (!empty($_FILES['profile']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $file_ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg','jpeg','png','gif'];

        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Invalid file type. Only jpg, jpeg, png, gif allowed.";
        } else {
            $profile_filename = uniqid() . "." . $file_ext;
            $target_file = $target_dir . $profile_filename;
            if (!move_uploaded_file($_FILES['profile']['tmp_name'], $target_file)) {
                $errors[] = "Error uploading file.";
            }
        }
    }

    // Show errors if any
    if (!empty($errors)) { 
        foreach ($errors as $error) echo "<p style='color:red;'>$error</p>"; 
    } else { 
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

        // Insert into database including profile_picture
        $query = "INSERT INTO users (`name`, `password`, `role`, `branch`, `profile_picture`) VALUES (?, ?, ?, ?, ?)"; 
        $stmt = mysqli_prepare($conn, $query); 

        if (!$stmt) { 
            echo "<p style='color:red;'>Error preparing statement: " . mysqli_error($conn) . "</p>"; 
            exit(); 
        } 

        mysqli_stmt_bind_param($stmt, "sssss", $username, $hashed_password, $role, $branch, $profile_filename); 
        $result = mysqli_stmt_execute($stmt); 

        if ($result) { 
            echo "<p style='color:green;'>User created successfully!</p>"; 
            echo "<a href='staff.php'>Go to Staff List</a>"; 
        } else { 
            echo "<p style='color:red;'>Error creating user: " . mysqli_error($conn) . "</p>"; 
        } 
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="en"> 
  //working*too
<head> 
  <meta charset="UTF-8"> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>Create User</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; margin:0; padding:0; }
    .container { max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    h2 { text-align: center; margin-bottom: 20px; color: #007bff; }
    input[type="text"], input[type="password"], select, input[type="file"] {
        width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
    }
    button { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; width: 100%; }
    button:hover { background: #0056b3; }
    img#preview { display: block; margin: 10px auto; max-width: 150px; border-radius: 50%; border: 2px solid #ccc; }
  </style>
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block';
      }
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head> 
<body> 
  <div class="container">
    <h2>Create User</h2> 
    <form action="create_user.php" method="post" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" required>
        <option value="">Select Role</option> 
        <option value="Admin">Admin</option> 
        <option value="Manager">Manager</option> 
        <option value="Finance">Finance</option> 
        <option value="Sales">Sales</option> 
        <option value="HR">HR</option> 
      </select>
      <select name="branch" required>
        <option value="">Select Branch</option> 
        <option value="Branch 1">Branch 1</option> 
        <option value="Branch 2">Branch 2</option> 
        <option value="Branch 3">Branch 3</option> 
      </select>
      <label>Profile Picture:</label>
      <input type="file" name="profile" accept="image/*" onchange="previewImage(event)">
      <img id="preview" src="#" alt="Profile Preview" style="display:none;">
      <button type="submit">Create User</button> 
    </form>
  </div>
</body> 
</html>
