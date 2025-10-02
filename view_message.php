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

// Fetch the message
$stmt = $conn->prepare("
    SELECT m.*, mr.read_status
    FROM messages m
    LEFT JOIN message_recipients mr ON m.id = mr.message_id AND mr.recipient_role = ?
    WHERE m.id = ?
");
$stmt->bind_param("si", $user_role, $message_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    header("Location: messages.php");
    exit;
}

// Mark as read if the current user is the recipient
if ($row['sender_role'] != $user_role && $row['read_status'] == 0) {
    $stmt2 = $conn->prepare("UPDATE message_recipients SET read_status = 1 WHERE message_id = ? AND recipient_role = ?");
    $stmt2->bind_param("is", $message_id, $user_role);
    $stmt2->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Message</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .message { background: #fff; padding: 20px; border-radius: 8px; width: 500px; }
        a { text-decoration: none; color: #1E3A8A; margin-right: 10px; }
    </style>
</head>
<body>
    <h2>Message Details</h2>
    <div class="message">
        <strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?><br>
        <strong>From:</strong> <?php echo htmlspecialchars($row['sender_role']); ?><br>
        <strong>Content:</strong> <?php echo nl2br(htmlspecialchars($row['content'])); ?><br>
        <strong>Date:</strong> <?php echo $row['created_at']; ?><br><br>
        <a href="messages.php">Back</a>
        <?php if ($row['sender_role'] == $user_role): ?>
            <a href="edit_message.php?id=<?php echo $row['id']; ?>">Edit</a>
            <a href="delete_message.php?id=<?php echo $row['id']; ?>">Delete</a>
        <?php endif; ?>
    </div>
</body>
</html>
