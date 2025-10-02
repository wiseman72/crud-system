<?php
require_once("connection.php");

$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=sales_report_" . date('Y-m-d') . ".xls");

$query = "SELECT s.sale_id, i.product_name, s.branch, s.quantity_sold, s.sale_price, 
                 (s.quantity_sold * s.sale_price) as total, s.sale_date
          FROM sales s
          JOIN inventory i ON s.product_id = i.product_id
          WHERE 1";

if ($branch != '') {
    $query .= " AND s.branch = '".$conn->real_escape_string($branch)."'";
}
if ($from != '' && $to != '') {
    $query .= " AND DATE(s.sale_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";
}

$query .= " ORDER BY s.sale_date DESC";
$result = $conn->query($query);

// Output Excel headers
echo "Sale ID\tProduct\tBranch\tQuantity\tSale Price\tTotal\tDate\n";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo $row['sale_id'] . "\t" .
             $row['product_name'] . "\t" .
             $row['branch'] . "\t" .
             $row['quantity_sold'] . "\t" .
             number_format($row['sale_price'], 2) . "\t" .
             number_format($row['total'], 2) . "\t" .
             $row['sale_date'] . "\n";
    }
}
?>
