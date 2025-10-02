<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'] ?? null;

// Fetch messages sent or received by this role
$stmt = $conn->prepare("
    SELECT m.id, m.subject, m.content, m.sender_role, m.created_at, 
           mr.read_status
    FROM messages m
    LEFT JOIN message_recipients mr ON m.id = mr.message_id AND mr.recipient_role = ?
    WHERE m.sender_role = ? OR mr.recipient_role = ?
    ORDER BY m.created_at DESC
");
$stmt->bind_param("sss", $user_role, $user_role, $user_role);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .message { background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .unread { border-left: 4px solid #1E3A8A; }
        a { text-decoration: none; color: #1E3A8A; margin-right: 10px; }
    </style>
</head>
<body>
    <h2>Your Messages</h2>
    <a href="send_message.php">Send New Message</a>

    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="message <?php echo ($row['read_status'] == 0) ? 'unread' : ''; ?>">
            <strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?><br>
            <strong>From:</strong> <?php echo htmlspecialchars($row['sender_role']); ?><br>
            <strong>Content:</strong> <?php echo nl2br(htmlspecialchars($row['content'])); ?><br>
            <strong>Date:</strong> <?php echo $row['created_at']; ?><br>
            <a href="view_message.php?id=<?php echo $row['id']; ?>">View</a>
            <?php if ($row['sender_role'] == $user_role): ?>
                <a href="edit_message.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete_message.php?id=<?php echo $row['id']; ?>">Delete</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>
