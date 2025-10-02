<?php
require_once("connection.php");

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Fetch order details
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if ($order && strtolower($order['goods_received']) === 'yes') {
    $product_name = $order['product_name'];
    $quantity = intval($order['quantity']);
    $branch = $order['branch'];

    // Adjust inventory only if the order was marked as received
    $stmt = mysqli_prepare($conn, "SELECT * FROM inventory WHERE product_name=? AND branch=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $product_name, $branch);
    mysqli_stmt_execute($stmt);
    $inv_result = mysqli_stmt_get_result($stmt);

    if ($inv_row = mysqli_fetch_assoc($inv_result)) {
        $new_quantity = max(0, $inv_row['quantity'] - $quantity); // Prevent negative stock
        $stmt = mysqli_prepare($conn, "UPDATE inventory SET quantity=? WHERE product_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $inv_row['product_id']);
        mysqli_stmt_execute($stmt);
    }
}

// Delete the order
$stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

header("Location: orders.php");
exit;
?>