<?php
session_start();
require_once("connection.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'] ?? null;

if (!$user_role) {
    header("Location: login.php");
    exit;
}

// Handle form submission
$successMsg = "";
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_role = $_POST['recipient_role'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($recipient_role) || empty($subject) || empty($content)) {
        $errorMsg = "All fields are required.";
    } else {
        // Insert into messages table
        $stmt = $conn->prepare("INSERT INTO messages (sender_role, subject, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_role, $subject, $content);
        if ($stmt->execute()) {
            $message_id = $stmt->insert_id;

            // Insert recipient
            $stmt2 = $conn->prepare("INSERT INTO message_recipients (message_id, recipient_role) VALUES (?, ?)");
            $stmt2->bind_param("is", $message_id, $recipient_role);
            $stmt2->execute();

            $successMsg = "Message sent successfully!";
        } else {
            $errorMsg = "Error sending message: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        form { background: #fff; padding: 20px; border-radius: 8px; width: 400px; }
        input, textarea, select { width: 100%; margin-bottom: 15px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        input[type="submit"] { background: #1E3A8A; color: #fff; border: none; cursor: pointer; }
        .success { color: green; margin-bottom: 15px; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Send Message</h2>

    <?php if ($successMsg) echo "<div class='success'>{$successMsg}</div>"; ?>
    <?php if ($errorMsg) echo "<div class='error'>{$errorMsg}</div>"; ?>

    <form method="post">
        <label>Recipient Role</label>
        <select name="recipient_role">
            <option value="">Select Role</option>
            <option value="Admin">Admin</option>
            <option value="Manager">Manager</option>
            <option value="Finance">Finance</option>
            <option value="HR">HR</option>
            <option value="Sales">Sales</option>
        </select>

        <label>Subject</label>
        <input type="text" name="subject">

        <label>Content</label>
        <textarea name="content" rows="5"></textarea>

        <input type="submit" value="Send">
    </form>
</body>
</html>
