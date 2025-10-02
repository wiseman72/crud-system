<?php
require_once("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['product_name'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $branch = $_POST['branch'];
    $price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];
    $reorder = $_POST['reorder_level'];

    $stmt = $conn->prepare("INSERT INTO inventory (product_name, category, supplier, branch, unit_price, quantity, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdis", $name, $category, $supplier, $branch, $price, $quantity, $reorder);
    $stmt->execute();

    header("Location: inventory.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Item</title>
    
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
 

    <label>Unit Price:</label>
    <input type="number" step="0.01" name="unit_price" required>

    <label>Quantity:</label>
    <input type="number" name="quantity" required>

    <label>Reorder Level:</label>
    <input type="number" name="reorder_level" value="10">

    <button type="submit">Add Product</button>
    <a href="inventory.php" class="btn btn-back">Back</a>
</form>
</body>
</html>
