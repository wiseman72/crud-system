<?php
require_once("connection.php");
session_start();

$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Fetch available branches for dropdown
if (in_array($user_role, ['Admin', 'Manager'])) {
    $branches_result = mysqli_query($conn, "SELECT DISTINCT branch FROM inventory");
} else {
    // Only user's branch
    $branches_result = new ArrayObject([['branch' => $user_branch]]);
}

if (isset($_POST['submit'])) {
    $order_made_by = $_POST['order_made_by'];
    $quantity = intval($_POST['quantity']);
    $product_name = $_POST['product_name'];
    $amount = floatval($_POST['amount']); // This is the total amount (not unit price)
    $branch = $_POST['branch'];
    $supplier_email = $_POST['supplier_email'];
    $date_ordered = $_POST['date_ordered'];

    // Force branch for normal users
    if (!in_array($user_role, ['Admin', 'Manager'])) {
        $branch = $user_branch;
    }

    // Insert order
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (order_made_by, quantity, product_name, date_ordered, amount, branch, supplier_email, goods_received) VALUES (?, ?, ?, ?, ?, ?, ?, 'No')");
    mysqli_stmt_bind_param($stmt, "sissdss", $order_made_by, $quantity, $product_name, $date_ordered, $amount, $branch, $supplier_email);

    if (mysqli_stmt_execute($stmt)) {
        // Update inventory
        $inv_stmt = mysqli_prepare($conn, "SELECT * FROM inventory WHERE product_name=? AND branch=?");
        mysqli_stmt_bind_param($inv_stmt, "ss", $product_name, $branch);
        mysqli_stmt_execute($inv_stmt);
        $inv_result = mysqli_stmt_get_result($inv_stmt);

        if ($inv_row = mysqli_fetch_assoc($inv_result)) {
            $new_quantity = $inv_row['quantity'] + $quantity;
            $update_inv = mysqli_prepare($conn, "UPDATE inventory SET quantity=? WHERE id=?");
            mysqli_stmt_bind_param($update_inv, "ii", $new_quantity, $inv_row['id']);
            mysqli_stmt_execute($update_inv);
        } else {
            $insert_inv = mysqli_prepare($conn, "INSERT INTO inventory (product_name, quantity, branch) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insert_inv, "sis", $product_name, $quantity, $branch);
            mysqli_stmt_execute($insert_inv);
        }

        // Email supplier notification
        $subject = "New Order Placed";
        $message = "Order Details:\nProduct: $product_name\nQuantity: $quantity\nAmount: $amount\nBranch: $branch";
        $headers = "From: your-email@example.com";
        mail($supplier_email, $subject, $message, $headers);

        header("Location: orders.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add New Order</title>
<style>
body { font-family: Arial,sans-serif; background:#f4f6f8; margin:0; padding:0; color:#333; }
h2 { text-align:center; margin-top:40px; color:#222; }
form { max-width:600px; margin:40px auto; background:#fff; padding:25px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
table { width:100%; border-collapse:collapse; }
td { padding:12px; vertical-align:top; }
td:first-child { font-weight:bold; width:35%; }
input, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; font-size:14px; }
input[type="submit"] { background:green; color:#fff; border:none; padding:12px 20px; cursor:pointer; border-radius:5px; font-size:15px; width:100%; margin-top:15px; }
input[type="submit"]:hover { background:darkgreen; }
</style>
</head>
<body>

<h2>Add New Order</h2>
<form method="post">
<table>
<tr>
<td>Order Made By:</td>
<td><input type="text" name="order_made_by" required></td>
</tr>
<tr>
<td>Quantity:</td>
<td><input type="number" name="quantity" min="1" required></td>
</tr>
<tr>
<td>Product Name:</td>
<td><input type="text" name="product_name" required></td>
</tr>
<tr>
<td>Amount (₵):</td>
<td>
  <input type="number" name="amount" step="0.01" min="0" required placeholder="Total value for all units">
  <div style="font-size:12px;color:#888;">Enter the total amount for the order (e.g., quantity × unit price)</div>
</td>
</tr>
<tr>
<td>Branch:</td>
<td>
<select name="branch" required <?= (!in_array($user_role, ['Admin', 'Manager'])) ? 'readonly disabled' : '' ?>>
<option value="">Select Branch</option>
<?php
foreach ($branches_result as $branch_row) {
    echo "<option value='{$branch_row['branch']}'" . 
         (($branch_row['branch'] == $user_branch) ? ' selected' : '') . ">{$branch_row['branch']}</option>";
}
?>
</select>
</td>
</tr>
<tr>
<td>Date Ordered:</td>
<td><input type="date" name="date_ordered" required value="<?= date('Y-m-d'); ?>"></td>
</tr>
<tr>
<td>Supplier Email:</td>
<td><input type="email" name="supplier_email" required></td>
</tr>
<tr>
<td></td>
<td><input type="submit" name="submit" value="Add Order"></td>
</tr>
</table>
</form>

</body>
</html>