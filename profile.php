<?php
session_start();
require_once("connection.php");

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Handle Profile Picture Upload
if (isset($_POST['upload'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = $username . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE name = ?");
                $stmt->bind_param("ss", $newFileName, $username);
                $stmt->execute();
                $stmt->close();
                $successMsg = "Profile picture updated successfully!";
            } else {
                $errorMsg = "Error moving the uploaded file.";
            }
        } else {
            $errorMsg = "Upload failed. Allowed file types: " . implode(", ", $allowedExts);
        }
    } else {
        $errorMsg = "No file selected or upload error.";
    }
}

// Handle Profile Picture Delete
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentPic = $row['profile_picture'];
    $stmt->close();

    if ($currentPic && file_exists('uploads/' . $currentPic)) {
        unlink('uploads/' . $currentPic);
    }

    $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    $successMsg = "Profile picture deleted successfully!";
}

// Fetch User Info
$stmt = $conn->prepare("SELECT id, name, role, branch, profile_picture FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 20px;
    color: #333;
}
.profile-container {
    max-width: 500px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
h2 { text-align: center; color: #007bff; }
img.profile-pic {
    display: block;
    max-width: 150px;
    max-height: 150px;
    width: auto;
    height: auto;
    margin: 15px auto;
    border-radius: 50%;
    border: 2px solid #007bff;
}
form { text-align: center; margin-top: 15px; }
input[type=file] { margin-bottom: 10px; }
button { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; background: #007bff; color: #fff; margin: 5px; }
button.delete { background: #dc3545; }
.msg { text-align: center; margin-bottom: 15px; }
.msg.success { color: green; }
.msg.error { color: red; }
.info { margin: 15px 0; }
.preview {
    display: block;
    margin: 10px auto;
    max-width: 150px;
    max-height: 150px;
    border-radius: 50%;
    border: 2px dashed #007bff;
}
</style>
</head>
<body>
<div class="profile-container">
<h2>My Profile</h2>

<?php if(isset($successMsg)) echo "<div class='msg success'>{$successMsg}</div>"; ?>
<?php if(isset($errorMsg)) echo "<div class='msg error'>{$errorMsg}</div>"; ?>

<!-- Display current profile picture -->
<img id="currentPic" src="<?php echo ($user['profile_picture'] && file_exists('uploads/' . $user['profile_picture'])) ? 'uploads/' . htmlspecialchars($user['profile_picture']) : 'uploads/default.png'; ?>" class="profile-pic">

<!-- Upload / Update Form -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)" required><br>
    <img id="preview" class="preview" style="display:none;">
    <br>
    <button type="submit" name="upload">Upload / Update</button>
    <button type="submit" name="delete" class="delete">Delete</button>
</form>

<div class="info">
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
    <p><strong>Branch:</strong> <?php echo htmlspecialchars($user['branch']); ?></p>
</div>
</div>

<script>
// Live preview of selected image
function previewImage(event) {
    const preview = document.getElementById('preview');
    const current = document.getElementById('currentPic');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
    current.style.display = 'none'; // hide current pic while previewing
}
</script>
</body>
</html>
