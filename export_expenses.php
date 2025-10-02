<?php
require_once("connection.php");

// Filters (optional)
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';

// Fetch expenses
$query = "SELECT expense_name, amount, branch, expense_date FROM expenses WHERE 1";
if ($branch != '') $query .= " AND branch='".$conn->real_escape_string($branch)."'";
if ($from != '' && $to != '') $query .= " AND DATE(expense_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";

$result = $conn->query($query);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=expenses.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Expense Name', 'Amount', 'Branch', 'Date']);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}
fclose($output);
exit();
