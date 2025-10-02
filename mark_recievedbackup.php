<?php
require_once("connection.php");

if (isset($_POST['mark_received'], $_POST['id'])) {
    $order_id = intval($_POST['id']);

    mysqli_begin_transaction($conn);

    try {
        // Fetch order
        $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$order) throw new Exception("Order not found.");

        // Mark as received and set date_received
        $stmt = mysqli_prepare($conn, "UPDATE orders SET goods_received = 'Yes', date_received = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        // Inventory variables
        $product_name = $order['product_name'];
        $quantity = intval($order['quantity']);
        $amount = floatval($order['amount']); // total order value
        $branch = $order['branch'];
        $unit_price = $quantity > 0 ? ($amount / $quantity) : 0;

        // Check inventory
        $stmt = mysqli_prepare($conn, "SELECT * FROM inventory WHERE product_name=? AND branch=?");
        mysqli_stmt_bind_param($stmt, "ss", $product_name, $branch);
        mysqli_stmt_execute($stmt);
        $inv_result = mysqli_stmt_get_result($stmt);

        if ($inv_row = mysqli_fetch_assoc($inv_result)) {
            $new_quantity = $inv_row['quantity'] + $quantity;
            // Optionally update unit_price (if you want to always use the latest)
            $stmt = mysqli_prepare($conn, "UPDATE inventory SET quantity=?, unit_price=? WHERE product_id=?");
            mysqli_stmt_bind_param($stmt, "idi", $new_quantity, $unit_price, $inv_row['product_id']);
            mysqli_stmt_execute($stmt);
        } else {
            // Insert new inventory record
            $stmt = mysqli_prepare($conn, "INSERT INTO inventory (product_name, quantity, unit_price, branch) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sids", $product_name, $quantity, $unit_price, $branch);
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($conn);
        header("Location: orders.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: orders.php");
    exit;
}
?>