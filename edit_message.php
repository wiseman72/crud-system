<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'] ?? null;
$message_id = $_GET['id'] ?? null;

if (!$message_id) {
    header("Location: messages.php");
    exit;
}

// Fetch the message only if the logged-in user is the sender
$stmt = $conn->prepare("SELECT * FROM messages WHERE id = ? AND sender_role = ?");
$stmt->bind_param("is", $message_id, $user_role);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    header("Location: messages.php");
    exit;
}

// Handle form submission
$errorMsg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($subject) || empty($content)) {
        $errorMsg = "Both subject and content are required.";
    } else {
        $stmt2 = $conn->prepare("UPDATE messages SET subject = ?, content = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $subject, $content, $message_id);
        $stmt2->execute();
        header("Location: messages.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Message</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        form { background: #fff; padding: 20px; border-radius: 8px; width: 400px; }
        input, textarea { width: 100%; margin-bottom: 15px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        input[type="submit"] { background: #1E3A8A; color: #fff; border: none; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Edit Message</h2>
    <?php if ($errorMsg) echo "<div class='error'>{$errorMsg}</div>"; ?>
    <form method="post">
        <label>Subject</label>
        <input type="text" name="subject" value="<?php echo htmlspecialchars($row['subject']); ?>">

        <label>Content</label>
        <textarea name="content" rows="5"><?php echo htmlspecialchars($row['content']); ?></textarea>

        <input type="submit" value="Update">
    </form>
</body>
</html>
