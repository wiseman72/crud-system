<?php
session_start();
require_once("connection.php");

$branch = $_SESSION['branch'] ?? '';

// Simple search/filter
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'product_name';

// Allowed fields
$allowed = ['product_name', 'customer_name', 'sale_date', 'sold_by', 'branch'];
if (!in_array($search_by, $allowed)) $search_by = 'product_name';

// Build query
if ($search_term !== '') {
    $search_like = "%" . $conn->real_escape_string($search_term) . "%";
    if ($search_by === 'branch' && isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Manager'])) {
        $query = "SELECT * FROM sales WHERE branch LIKE ? ORDER BY sale_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $search_like);
    } else {
        $query = "SELECT * FROM sales WHERE branch = ? AND $search_by LIKE ? ORDER BY sale_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $branch, $search_like);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM sales WHERE branch = ? ORDER BY sale_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branch);
    $stmt->execute();
    $result = $stmt->get_result();
}

$grand_total = 0;
$total_gross_profit = 0;
$sales = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
        $grand_total += $row['total_price'];
        $total_gross_profit += $row['gross_profit'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Records</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0;}
        .main-content { max-width:1200px; margin:auto; padding:30px;}
        h2 { margin-bottom: 20px; }
        .search-bar-container { margin-bottom: 20px; display:flex; gap:10px; }
        .search-bar, select { padding: 10px; border: 1px solid #ccc; border-radius: 5px;}
        .btn { padding: 10px 18px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor:pointer;}
        .btn:hover { background: darkblue;}
        .btn-print { background: purple; margin-left:10px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);}
        th, td { padding: 12px 15px; border-bottom: 1px solid #eee;}
        th { background: #007bff; color: white; font-weight: 600;}
        tr:hover { background: #f1f1f1;}
        tfoot th { text-align:right; font-weight:600; background:#f0f0f0;}
        @media print {
            .search-bar-container, .btn-print { display: none; }
            body, .main-content { background: white; color: black; }
            table { box-shadow:none; }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Sales Records</h2>
    <form method="get" class="search-bar-container">
        <input type="text" name="search" class="search-bar" placeholder="Search sales..." value="<?= htmlspecialchars($search_term) ?>">
        <select name="search_by" class="search-bar">
            <option value="product_name" <?php if($search_by=='product_name') echo 'selected'; ?>>Product Name</option>
            <option value="customer_name" <?php if($search_by=='customer_name') echo 'selected'; ?>>Customer Name</option>
            <option value="sale_date" <?php if($search_by=='sale_date') echo 'selected'; ?>>Date</option>
            <option value="sold_by" <?php if($search_by=='sold_by') echo 'selected'; ?>>Sold By</option>
            <option value="branch" <?php if($search_by=='branch') echo 'selected'; ?>>Branch</option>
        </select>
        <input type="submit" class="btn" value="Search">
        <button type="button" class="btn btn-print" onclick="window.print()">Print Sales</button>
    </form>
    <?php if(count($sales) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
                <th>Cost Price</th>
                <th>Gross Profit</th>
                <th>Sold By</th>
                <th>Customer Name</th>
                <th>Customer Email</th>
                <th>Customer Phone</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($sales as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['sale_date']) ?></td>
                <td><?= htmlspecialchars($s['product_name']) ?></td>
                <td><?= htmlspecialchars($s['quantity_sold']) ?></td>
                <td>K<?= number_format($s['sale_price'],2) ?></td>
                <td>K<?= number_format($s['total_price'],2) ?></td>
                <td>K<?= number_format($s['cost_price'],2) ?></td>
                <td>K<?= number_format($s['gross_profit'],2) ?></td>
                <td><?= htmlspecialchars($s['sold_by']) ?></td>
                <td><?= htmlspecialchars($s['customer_name']) ?></td>
                <td><?= htmlspecialchars($s['customer_email']) ?></td>
                <td><?= htmlspecialchars($s['customer_phone']) ?></td>
                <td><?= htmlspecialchars($s['branch']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Grand Total:</th>
                <th>K<?= number_format($grand_total,2) ?></th>
                <th></th>
                <th>K<?= number_format($total_gross_profit,2) ?></th>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
        <p style="color:red;">No sales found.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$conn->close();