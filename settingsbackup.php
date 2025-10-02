
<?php 
session_start(); 
require_once("connection.php"); 

if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
} 

$errorMsg = ""; 
$successMsg = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $currentPassword = trim($_POST["currentPassword"] ?? ""); 
    $newPassword = trim($_POST["newPassword"] ?? ""); 
    $confirmPassword = trim($_POST["confirmPassword"] ?? ""); 

    $errors = []; 

    if (empty($currentPassword)) { 
        $errors[] = "Current password is required."; 
    } 

    if (empty($newPassword)) { 
        $errors[] = "New password is required."; 
    } 

    if (empty($confirmPassword)) { 
        $errors[] = "Confirm password is required."; 
    } 

    if ($newPassword !== $confirmPassword) { 
        $errors[] = "New password and confirm password do not match."; 
    } 

    if (!empty($errors)) { 
        $errorMsg = implode("<br>", $errors);
    } else { 
        $query = "SELECT * FROM users WHERE name = ?"; 
        $stmt = $conn->prepare($query); 
        if (!$stmt) {
            $errorMsg = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("s", $_SESSION['username']); 
            $stmt->execute(); 
            $result = $stmt->get_result(); 

            if ($result && $row = $result->fetch_assoc()) { 
                if (password_verify($currentPassword, $row['password'])) { 
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); 
                    $query = "UPDATE users SET password = ? WHERE name = ?"; 
                    $stmt = $conn->prepare($query); 
                    if (!$stmt) {
                        $errorMsg = "Error preparing update statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("ss", $hashedPassword, $_SESSION['username']); 
                        if ($stmt->execute()) {
                            $successMsg = "Password changed successfully.";
                        } else {
                            $errorMsg = "Error changing password: " . $stmt->error;
                        }
                    }
                } else { 
                    $errorMsg = "Current password is incorrect.";
                } 
            } else { 
                $errorMsg = "User not found.";
            } 
        }
    } 
} 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Settings</title> 
        <style>
        /* General body styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Container for the settings form */
        .settings-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.8s ease-in-out;
        }

        /* Heading */
        .settings-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333333;
            font-size: 28px;
            font-weight: 600;
        }

        /* Form groups */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #555555;
        }

        .form-group input {
            padding: 12px 15px;
            border: 1px solid #cccccc;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
        }

        /* Button */
        .settings-btn {
            width: 100%;
            padding: 14px;
            background-color: #4a90e2;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .settings-btn:hover {
            background-color: #357ab8;
        }

        /* Error and success messages */
        .error-message, .success-message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }

        .error-message {
            background-color: #ffe0e0;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }

        .success-message {
            background-color: #e0ffe0;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 500px) {
            .settings-container {
                padding: 30px 20px;
            }

            .settings-container h2 {
                font-size: 24px;
            }
        }
    </style>

</head>


<body> 
    <div class="settings-container"> 
        <h2>Settings</h2> 
        <form action="settings.php" method="post"> 
            <?php if (!empty($errorMsg)) { echo '<div class="error-message">' . $errorMsg . '</div>'; } ?> 
            <?php if (!empty($successMsg)) { echo '<div class="success-message">' . $successMsg . '</div>'; } ?> 

            <div class="form-group"> 
                <label for="currentPassword">Current Password</label> 
                <input type="password" id="currentPassword" name="currentPassword" required> 
            </div> 

            <div class="form-group"> 
                <label for="newPassword">New Password</label> 
                <input type="password" id="newPassword" name="newPassword" required> 
            </div> 

            <div class="form-group"> 
                <label for="confirmPassword">Confirm Password</label> 
                <input type="password" id="confirmPassword" name="confirmPassword" required> 
            </div> 

            <button type="submit" class="settings-btn">Change Password</button> 
        </form> 
    </div> 
</body> 
</html>