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

// Only sender can delete
$stmt = $conn->prepare("SELECT * FROM messages WHERE id = ? AND sender_role = ?");
$stmt->bind_param("is", $message_id, $user_role);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if ($row) {
    // Delete from message_recipients first
    $stmt2 = $conn->prepare("DELETE FROM message_recipients WHERE message_id = ?");
    $stmt2->bind_param("i", $message_id);
    $stmt2->execute();

    // Delete from messages table
    $stmt3 = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt3->bind_param("i", $message_id);
    $stmt3->execute();
}

header("Location: messages.php");
exit;
?>
