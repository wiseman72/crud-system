<?php
require_once("connection.php");

if (isset($_GET['barcode'])) {
    $barcode = $conn->real_escape_string($_GET['barcode']);
    $result = $conn->query("SELECT * FROM inventory WHERE barcode='$barcode' LIMIT 1");

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "product_name" => $row['product_name'],
            "unit_price" => $row['unit_price'],
            "cost_price" => $row['cost_price'],
            "quantity_available" => $row['quantity']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
