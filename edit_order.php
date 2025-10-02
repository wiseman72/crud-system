<?php
require_once("connection.php");

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Fetch current order
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    echo "Order not found!";
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    $order_made_by = $_POST['order_made_by'];
    $quantity = intval($_POST['quantity']);
    $product_name = $_POST['product_name'];
    $amount = floatval($_POST['amount']); // Total order value (not per unit)
    $branch = $_POST['branch'];
    $supplier_email = $_POST['supplier_email'];
    $date_ordered = $_POST['date_ordered'];

    $update_stmt = mysqli_prepare(
        $conn,
        "UPDATE orders SET order_made_by=?, quantity=?, product_name=?, amount=?, branch=?, supplier_email=?, date_ordered=? WHERE id=?"
    );
    mysqli_stmt_bind_param(
        $update_stmt,
        "sisdsssi",
        $order_made_by,
        $quantity,
        $product_name,
        $amount,
        $branch,
        $supplier_email,
        $date_ordered,
        $order_id
    );
    if (mysqli_stmt_execute($update_stmt)) {
        header("Location: orders.php");
        exit;
    } else {
        echo "Error updating order: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Order</title>
<style>
body { font-family: Arial,sans-serif; background:#f4f6f8; margin:0; padding:0; color:#333; }
h2 { text-align:center; margin-top:40px; color:#222; }
form { max-width:600px; margin:40px auto; background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
table { width:100%; border-collapse:collapse; }
td { padding:12px; vertical-align:top; }
td:first-child { font-weight:bold; width:35%; }
input[type="text"], input[type="number"], input[type="email"], input[type="date"] { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; font-size:14px; }
input[type="submit"] { background-color: green; color: white; border:none; padding:12px 20px; cursor:pointer; border-radius:5px; font-size:15px; width:100%; margin-top:15px; }
input[type="submit"]:hover { background-color: darkgreen; }
</style>
</head>
<body>

<h2>Edit Order</h2>
<form method="post">
<table>
<tr>
<td>Order Made By:</td>
<td><input type="text" name="order_made_by" value="<?= htmlspecialchars($order['order_made_by']); ?>" required></td>
</tr>
<tr>
<td>Quantity:</td>
<td><input type="number" name="quantity" value="<?= $order['quantity']; ?>" required min="1"></td>
</tr>
<tr>
<td>Product Name:</td>
<td><input type="text" name="product_name" value="<?= htmlspecialchars($order['product_name']); ?>" required></td>
</tr>
<tr>
<td>Amount (₵):</td>
<td>
  <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($order['amount']); ?>" required min="0">
  <div style="font-size:12px;color:#888;">Total amount for the order (quantity × unit price)</div>
</td>
</tr>
<tr>
<td>Branch:</td>
<td><input type="text" name="branch" value="<?= htmlspecialchars($order['branch']); ?>" required></td>
</tr>
<tr>
<td>Date Ordered:</td>
<td><input type="date" name="date_ordered" value="<?= date('Y-m-d', strtotime($order['date_ordered'])); ?>" required></td>
</tr>
<tr>
<td>Supplier Email:</td>
<td><input type="email" name="supplier_email" value="<?= htmlspecialchars($order['supplier_email']); ?>" required></td>
</tr>
<tr>
<td></td>
<td><input type="submit" name="submit" value="Update Order"></td>
</tr>
</table>
</form>

</body>
</html>