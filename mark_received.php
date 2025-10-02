<?php
session_start();
require_once("connection.php");

if (isset($_POST['mark_received'], $_POST['id'])) {
    $order_id = intval($_POST['id']);

    mysqli_begin_transaction($conn);

    try {
        // Fetch order
        $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $order_result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($order_result);

        if (!$order) throw new Exception("Order not found.");

        $quantity = intval($order['quantity']);
        if ($quantity <= 0) throw new Exception("Invalid order quantity.");

        $product_name = $order['product_name'];
        $amount = floatval($order['amount']);
        $branch = $order['branch'];
        $unit_price = round($amount / $quantity, 2);

        // Mark order as received
        $stmt = mysqli_prepare($conn, "UPDATE orders SET goods_received = 'Yes', date_received = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        // Check inventory
        $stmt = mysqli_prepare($conn, "SELECT * FROM inventory WHERE product_name=? AND branch=?");
        mysqli_stmt_bind_param($stmt, "ss", $product_name, $branch);
        mysqli_stmt_execute($stmt);
        $inv_result = mysqli_stmt_get_result($stmt);

        if ($inv_row = mysqli_fetch_assoc($inv_result)) {
            $new_quantity = $inv_row['quantity'] + $quantity;
            $stmt = mysqli_prepare($conn, "UPDATE inventory SET quantity=?, unit_price=?, cost_price=? WHERE product_id=?");
            mysqli_stmt_bind_param($stmt, "iddi", $new_quantity, $unit_price, $unit_price, $inv_row['product_id']);
            mysqli_stmt_execute($stmt);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO inventory (product_name, quantity, unit_price, cost_price, branch) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sidss", $product_name, $quantity, $unit_price, $unit_price, $branch);
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($conn);
        $_SESSION['success'] = "Order marked as received and inventory updated successfully.";
        header("Location: orders.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: orders.php");
        exit;
    }

} else {
    header("Location: orders.php");
    exit;
}
?>
