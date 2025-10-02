<?php
session_start();
require_once("connection.php");

$branch = $_SESSION['branch'] ?? '';

// Handle search
$search = "";
if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM inventory WHERE branch = '$branch' AND (product_name LIKE '%$search%' OR category LIKE '%$search%' OR supplier LIKE '%$search%')";
} else {
    $query = "SELECT * FROM inventory WHERE branch = '$branch'";
}
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* --- Body & Typography --- */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f4f9ff;
            color: #333;
        }
        h1 {
            color: #004aad;
            text-align: center;
            margin-bottom: 20px;
        }

        /* --- Search & Buttons --- */
        .search-box {
            margin-bottom: 15px;
            text-align: right;
        }
        .search-box input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            margin-left: 3px;
        }
        .btn-add { background: #004aad; }
        .btn-edit { background: #28a745; }
        .btn-delete { background: #dc3545; }
        .btn-print { background: #007bff; }
        .btn-import { background: #17a2b8; }

        /* --- Table --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background: #004aad;
            color: white;
        }
        .low-stock {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Inventory Management</h1>

<div class="search-box">
    <form method="get">
        <input type="text" name="search" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-import">Search</button>
        <button type="button" class="btn btn-print" onclick="window.print()">Print</button>
        <a href="add_item.php" class="btn btn-add">+ Add Item</a>
    </form>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Category</th>
        <th>Supplier</th>
        <th>Branch</th>
        <th>Unit Price</th>
        <th>Cost Price</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = ($row['quantity'] <= $row['reorder_level']) ? "<span class='low-stock'>Low Stock</span>" : "In Stock"; ?>
            <tr>
                <td><?= $row['product_id'] ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['supplier']) ?></td>
                <td><?= htmlspecialchars($row['branch']) ?></td>
                <td>K₵<?= number_format($row['unit_price'], 2) ?></td>
                <td>K₵<?= number_format($row['cost_price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $status ?></td>
                <td>
                    <a href="edit_item.php?id=<?= $row['product_id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="delete_item.php?id=<?= $row['product_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="10">No products found.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>
