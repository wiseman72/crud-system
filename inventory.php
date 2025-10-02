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
    
    <style>
        /* --- Body & Typography --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 100px;
            background: #f4f9ff;
            color: #333;
        }
        h1 {
            color: #004aad;
            text-align: center;
            margin-bottom: 25px;
        }

        /* --- Search & Buttons --- */
        .search-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .search-box input[type="text"] {
            padding: 8px;
            flex: 1;
            min-width: 200px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 7px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
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
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 10px;
            text-align: center;
        }
        table th {
            background: #004aad;
            color: white;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #e6f2ff;
        }
        .low-stock {
            color: red;
            font-weight: bold;
        }

        /* --- Responsive --- */
        @media (max-width: 1000px) {
            table th, table td {
                padding: 8px;
                font-size: 14px;
            }
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box input, .search-box .btn, .search-box a {
                width: 100%;
                margin: 3px 0;
            }
        }
    </style>
</head>
<body>
<h1>Inventory Management</h1>

<div class="search-box">
    <form method="get" style="flex:1; display:flex; gap:5px; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Search product..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-import">Search</button>
        <button type="button" class="btn btn-print" onclick="window.print()">Print</button>
        <a href="add_item.php" class="btn btn-add">+ Add Item</a>
    </form>
</div>

<table>
    <thead>
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
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = ($row['quantity'] <= $row['reorder_level']) ? "<span class='low-stock'>Low Stock</span>" : "In Stock"; ?>
            <tr>
                <td><?= $row['product_id'] ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= htmlspecialchars($row['supplier']) ?></td>
                <td><?= htmlspecialchars($row['branch']) ?></td>
                <td>K<?= number_format($row['unit_price'], 2) ?></td>
                <td>K<?= number_format($row['cost_price'], 2) ?></td>
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
    </tbody>
</table>
</body>
</html>
