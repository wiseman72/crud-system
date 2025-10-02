
<?php
session_start();
require_once("connection.php");

if (!$conn) {
    die("Connection is not established");
}

if (!isset($_GET['id'])) {
    die("No ID specified");
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false) {
    die("Invalid ID");
}

$stmt = $conn->prepare("DELETE FROM `payrolls` WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: payrolls.php?msg=Payroll deleted successfully");
} else {
    die("Error deleting payroll: " . $conn->error);
}

$stmt->close();
$conn->close();
exit;
?>