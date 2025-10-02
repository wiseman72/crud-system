<?php
session_start();
require_once("connection.php");

// Only Admin or Manager for their branch can approve
$username = $_SESSION['username'] ?? '';
$branch = $_SESSION['branch'] ?? '';
$role = $_SESSION['role'] ?? '';

// Confirm role with branch
$stmt = $conn->prepare("SELECT role FROM users WHERE name=? AND branch=?");
$stmt->bind_param("ss", $username, $branch);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if (!$user || !in_array($user['role'], ['Admin', 'Manager'])) {
    header("Location: index.php");
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Invalid leave request ID.");
}

$id = (int)$_GET['id'];

// Make sure the leave request is for this branch!
$stmt = $conn->prepare("SELECT branch FROM leave_requests WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$req_res = $stmt->get_result();
$leave = $req_res->fetch_assoc();
if (!$leave || $leave['branch'] !== $branch) {
    die("You are not allowed to approve requests from another branch.");
}

// Update status to approved
$stmt = $conn->prepare("UPDATE leave_requests SET status='approved' WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: leave_approvals.php"); // Or approvals.php, or leave_requests.php
    exit;
} else {
    echo "<p style='color:red;'>Error approving leave: " . $conn->error . "</p>";
}
?>