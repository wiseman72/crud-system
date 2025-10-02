<?php
require_once("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['product_name'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $branch = $_POST['branch'];
    $price = $_POST['unit_price'];
    $cost = $_POST['cost_price'];
    $quantity = $_POST['quantity'];
    $reorder = $_POST['reorder_level'];

    $stmt = $conn->prepare(
        "INSERT INTO inventory (product_name, category, supplier, branch, unit_price, cost_price, quantity, reorder_level) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssddis", $name, $category, $supplier, $branch, $price, $cost, $quantity, $reorder);
    $stmt->execute();

    header("Location: inventory.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
    <style>
        body { font-family: Arial,sans-serif; margin:20px; background:#f4f9ff; color:#333; }
        h2 { color:#004aad; text-align:center; }
        form { max-width:500px; margin:auto; background:white; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        label { display:block; margin-top:10px; font-weight:bold; }
        input { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
        button, .btn-back { margin-top:15px; padding:8px 14px; border:none; border-radius:5px; cursor:pointer; text-decoration:none; color:white; }
        button { background:#004aad; }
        .btn-back { background:#6c757d; display:inline-block; }
    </style>
</head>
<body>
<h2>Add New Item</h2>
<form method="post">
    <label>Product Name:</label>
    <input type="text" name="product_name" required>

    <label>Category:</label>
    <input type="text" name="category">

    <label>Supplier:</label>
    <input type="text" name="supplier">

    <label>Branch:</label>
    <input type="text" name="branch" required>

    <label>Unit Price (Sale Price):</label>
    <input type="number" step="0.01" name="unit_price" required>

    <label>Cost Price:</label> <!-- NEW -->
    <input type="number" step="0.01" name="cost_price" required>

    <label>Quantity:</label>
    <input type="number" name="quantity" required>

    <label>Reorder Level:</label>
    <input type="number" name="reorder_level" value="10">

    <button type="submit">Add Product</button>
    <a href="inventory.php" class="btn-back">Back</a>
</form>
</body>
</html>
