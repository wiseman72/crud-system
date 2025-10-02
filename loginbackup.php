<?php
session_start();
require_once("connection.php");

$errorMsg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $branch   = trim($_POST["branch"] ?? "");

    $errors = [];

    if (empty($username)) $errors[] = "Username is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($branch))   $errors[] = "Please select a branch.";

    if (!empty($errors)) {
        $errorMsg = implode('<br>', $errors);
    } else {
        // Check username, branch, and password
        $query = "SELECT * FROM users WHERE name = ? AND branch = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            $errorMsg = "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $username, $branch);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    // Set session variables including role
                    $_SESSION["username"] = $username;
                    $_SESSION["branch"]   = $branch;
                    $_SESSION["role"]     = $row['role']; // <-- crucial for messages

                    // Handle "remember me" with cookies
                    if (!empty($_POST['remember'])) {
                        setcookie('username', $username, time() + (86400 * 30), "/");
                        setcookie('branch', $branch, time() + (86400 * 30), "/");
                    } else {
                        setcookie('username', '', time() - 3600, "/");
                        setcookie('branch', '', time() - 3600, "/");
                    }

                    // Redirect to dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $errorMsg = "Invalid username, password, or branch.";
                }
            } else {
                $errorMsg = "Invalid username, password, or branch.";
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
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="login-container">
    <form class="login-form" action="login.php" method="post" autocomplete="off">
        <h2 class="login-title">Login</h2>

        <?php if (!empty($errorMsg)) { echo '<div class="error-message">' . $errorMsg . '</div>'; } ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus
                   value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>">
        </div>

        <div class="form-group password-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword()">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="branch">Branch</label>
            <select id="branch" name="branch" required>
                <option value="">Select Branch</option>
                <option value="Branch 1" <?php if(isset($_COOKIE['branch']) && $_COOKIE['branch'] == 'Branch 1') echo 'selected'; ?>>Branch 1</option>
                <option value="Branch 2" <?php if(isset($_COOKIE['branch']) && $_COOKIE['branch'] == 'Branch 2') echo 'selected'; ?>>Branch 2</option>
                <option value="Branch 3" <?php if(isset($_COOKIE['branch']) && $_COOKIE['branch'] == 'Branch 3') echo 'selected'; ?>>Branch 3</option>
            </select>
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>

    <script>
        function togglePassword() {
            var pwd = document.getElementById('password');
            var eye = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.innerHTML = '<circle cx="12" cy="12" r="3"></circle><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.81 21.81 0 0 1 5.06-6.06M1 1l22 22" stroke="#1976d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>';
            } else {
                pwd.type = 'password';
                eye.innerHTML = '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>

    <footer class="login-footer">
        <p>&copy; 2025</p>
    </footer>
</div>
</body>
</html>
