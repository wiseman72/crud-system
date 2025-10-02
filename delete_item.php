<?php
require_once("connection.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID");
}
$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM inventory WHERE product_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: inventory.php");
exit();
