<?php
require_once("connection.php");
session_start();

$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Validate expense ID
if (!isset($_GET['id'])) {
    die("Invalid expense ID.");
}
$id = intval($_GET['id']);

// Fetch expense for branch safety
$stmt = $conn->prepare("SELECT branch FROM expenses WHERE expense_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$expense = $res->fetch_assoc();
if (!$expense) {
    die("Expense not found.");
}

// Only allow delete if user is admin/manager or the expense is for their branch
if (!in_array($user_role, ['Admin', 'Manager']) && $expense['branch'] !== $user_branch) {
    die("You are not allowed to delete expenses from another branch.");
}

// Delete
$stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: finance.php");
    exit();
} else {
    echo "<p style='color:red;'>Error deleting expense.</p>";
}
?>