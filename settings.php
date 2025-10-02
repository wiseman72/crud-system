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

        .settings-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.8s ease-in-out;
        }

        .settings-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333333;
            font-size: 28px;
            font-weight: 600;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 500px) {
            .settings-container {
                padding: 30px 20px;
            }

            .settings-container h2 {
                font-size: 24px;
            }
        }

        .toggle-password {
            margin-top: 5px;
            font-size: 14px;
            color: #4a90e2;
            cursor: pointer;
            user-select: none;
        }

        .password-strength {
            height: 8px;
            width: 100%;
            background-color: #eee;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-inner {
            height: 100%;
            width: 0%;
            background-color: red;
            transition: width 0.3s ease;
        }

        .password-match {
            margin-top: 5px;
            font-size: 14px;
        }

        .password-match.valid { color: green; }
        .password-match.invalid { color: red; }
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
                <span class="toggle-password" onclick="togglePassword('currentPassword')">Show</span>
            </div> 

            <div class="form-group"> 
                <label for="newPassword">New Password</label> 
                <input type="password" id="newPassword" name="newPassword" required> 
                <span class="toggle-password" onclick="togglePassword('newPassword')">Show</span>
                <div class="password-strength">
                    <div class="password-strength-inner" id="strengthBar"></div>
                </div>
            </div> 

            <div class="form-group"> 
                <label for="confirmPassword">Confirm Password</label> 
                <input type="password" id="confirmPassword" name="confirmPassword" required> 
                <span class="toggle-password" onclick="togglePassword('confirmPassword')">Show</span>
                <div class="password-match" id="passwordMatchMsg"></div>
            </div> 

            <button type="submit" class="settings-btn">Change Password</button> 
        </form> 
    </div> 

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = (field.type === "password") ? "text" : "password";
        }

        const newPassword = document.getElementById("newPassword");
        const confirmPassword = document.getElementById("confirmPassword");
        const strengthBar = document.getElementById("strengthBar");
        const matchMsg = document.getElementById("passwordMatchMsg");

        newPassword.addEventListener("input", function() {
            const val = newPassword.value;
            let strength = 0;
            if (val.length >= 6) strength += 1;
            if (/[A-Z]/.test(val)) strength += 1;
            if (/[0-9]/.test(val)) strength += 1;
            if (/[\W]/.test(val)) strength += 1;

            switch(strength) {
                case 0: strengthBar.style.width = "0%"; strengthBar.style.backgroundColor = "red"; break;
                case 1: strengthBar.style.width = "25%"; strengthBar.style.backgroundColor = "red"; break;
                case 2: strengthBar.style.width = "50%"; strengthBar.style.backgroundColor = "orange"; break;
                case 3: strengthBar.style.width = "75%"; strengthBar.style.backgroundColor = "yellowgreen"; break;
                case 4: strengthBar.style.width = "100%"; strengthBar.style.backgroundColor = "green"; break;
            }
        });

        confirmPassword.addEventListener("input", function() {
            if (confirmPassword.value === newPassword.value) {
                matchMsg.textContent = "Passwords match";
                matchMsg.className = "password-match valid";
            } else {
                matchMsg.textContent = "Passwords do not match";
                matchMsg.className = "password-match invalid";
            }
        });
    </script>
</body> 
</html>
