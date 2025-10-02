<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:0; color:#333; }
h2 { text-align:center; margin:30px 0; color:#222; }
.main-content { max-width:95%; margin:20px auto; padding:0 20px; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
th, td { padding:12px 15px; text-align:left; }
th { background:#007bff; color:#fff; font-weight:bold; }
tr:nth-child(even) { background:#f9f9f9; }
tr:hover { background:#e0f7fa; }
.btn { display:inline-block; padding:8px 12px; margin:3px 0; border-radius:5px; text-decoration:none; color:#fff; font-size:14px; cursor:pointer; border:none; transition:background 0.3s ease; }
.btn-edit { background:green; } .btn-edit:hover { background:darkgreen; }
.btn-delete { background:red; } .btn-delete:hover { background:darkred; }
.btn-receive { background:#28a745; } .btn-receive:hover { background:#007bff; }
.btn-add { background:#007bff; display:inline-block; padding:10px 20px; margin:15px 0; border-radius:5px; color:#fff; text-decoration:none; font-weight:bold; }
.low-stock { color:red; font-weight:bold; }
.received { background:#d4edda; }
@media (max-width:768px) {
  table, thead, tbody, th, td, tr { display:block; }
  th, td { padding:10px; text-align:right; position:relative; }
  td::before { position:absolute; left:10px; width:45%; text-align:left; font-weight:bold; content:attr(data-label); }
  tr { margin-bottom:15px; border-bottom:2px solid #ddd; }
  td:last-child { text-align:center; }
}
</style>
</head>
<body>
<div class="main-content">
<h2>Orders</h2>
<?php
require_once("connection.php");
session_start();
$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Restrict order view by branch (only admin/manager can view all branches)
if (in_array($user_role, ['Admin', 'Manager'])) {
  $query = "SELECT o.*, i.quantity as inventory_quantity
            FROM orders o
            LEFT JOIN inventory i ON o.product_name = i.product_name AND o.branch = i.branch
            ORDER BY o.date_ordered DESC";
  $result = mysqli_query($conn, $query);
} else {
  $query = "SELECT o.*, i.quantity as inventory_quantity
            FROM orders o
            LEFT JOIN inventory i ON o.product_name = i.product_name AND o.branch = i.branch
            WHERE o.branch = ?
            ORDER BY o.date_ordered DESC";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $user_branch);
  $stmt->execute();
  $result = $stmt->get_result();
}

if ($result && mysqli_num_rows($result) > 0) {
?>
<table>
<thead>
<tr>
<th>Order Made By</th>
<th>Product</th>
<th>Quantity Ordered</th>
<th>Amount (₵)</th>
<th>Goods Received</th>
<th>Date Ordered</th>
<th>Date Received</th>
<th>Inventory Stock</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($result)) { 
    $lowStock = ($row['inventory_quantity'] !== null && $row['inventory_quantity'] < 10);
    $receivedClass = (strtolower($row['goods_received']) == 'yes') ? 'received' : '';
?>
<tr class="<?= $receivedClass; ?>">
<td data-label="Order Made By"><?= htmlspecialchars($row['order_made_by']); ?></td>
<td data-label="Product"><?= htmlspecialchars($row['product_name']); ?></td>
<td data-label="Quantity Ordered"><?= $row['quantity']; ?></td>
<td data-label="Amount (₵)"><?= number_format($row['amount'], 2); ?></td>
<td data-label="Goods Received"><?= htmlspecialchars($row['goods_received']); ?></td>
<td data-label="Date Ordered"><?= htmlspecialchars($row['date_ordered']); ?></td>
<td data-label="Date Received"><?= $row['date_received'] ? htmlspecialchars($row['date_received']) : '-'; ?></td>
<td data-label="Inventory Stock">
    <?= ($row['inventory_quantity'] !== null) ? $row['inventory_quantity'] : 'N/A'; ?>
    <?php if($lowStock) echo '<span class="low-stock"> (Low!)</span>'; ?>
</td>
<td data-label="Actions">
<?php if (strtolower($row['goods_received']) != 'yes') { ?>
<form action="mark_received.php" method="post" style="display:inline;">
<input type="hidden" name="id" value="<?= $row['id']; ?>">
<input type="submit" name="mark_received" class="btn btn-receive" value="Mark as Received">
</form>
<?php } ?>
<a class="btn btn-edit" href="edit_order.php?id=<?= $row['id']; ?>">Edit</a>
<a class="btn btn-delete" href="delete_order.php?id=<?= $row['id']; ?>">Delete</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
<?php } else { echo '<div>No orders found.</div>'; } ?>
<a href="add_order.php" class="btn-add">Add New Order</a>
</div>
</body>
</html>