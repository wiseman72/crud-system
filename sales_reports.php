<?php
require_once("connection.php");

// Helper function for Kwacha formatting
function format_kwacha($amount) {
    return "K " . number_format($amount, 2);
}

// Fetch filters
$branch = isset($_GET['branch']) ? $_GET['branch'] : '';
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$period = isset($_GET['period']) ? $_GET['period'] : 'daily';

// Fetch distinct branches
$branches = $conn->query("SELECT DISTINCT branch FROM sales");

// --- Sales Query based on period ---
switch($period) {
    case 'weekly':
        $sales_query = "SELECT YEAR(sale_date) AS yr, WEEK(sale_date) AS wk, SUM(quantity_sold * sale_price) AS total_sales 
                        FROM sales WHERE 1";
        break;
    case 'monthly':
        $sales_query = "SELECT YEAR(sale_date) AS yr, MONTH(sale_date) AS mon, SUM(quantity_sold * sale_price) AS total_sales 
                        FROM sales WHERE 1";
        break;
    case 'yearly':
        $sales_query = "SELECT YEAR(sale_date) AS yr, SUM(quantity_sold * sale_price) AS total_sales 
                        FROM sales WHERE 1";
        break;
    case 'daily':
    default:
        $sales_query = "SELECT DATE(sale_date) AS day, SUM(quantity_sold * sale_price) AS total_sales 
                        FROM sales WHERE 1";
        break;
}

if($branch != '') $sales_query .= " AND branch='".$conn->real_escape_string($branch)."'";
if($from != '' && $to != '') $sales_query .= " AND DATE(sale_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";

switch($period) {
    case 'weekly': $sales_query .= " GROUP BY yr, wk ORDER BY yr, wk"; break;
    case 'monthly': $sales_query .= " GROUP BY yr, mon ORDER BY yr, mon"; break;
    case 'yearly': $sales_query .= " GROUP BY yr ORDER BY yr"; break;
    case 'daily': default: $sales_query .= " GROUP BY day ORDER BY day"; break;
}

$sales_result = $conn->query($sales_query);
$labels = []; $totals = [];
while($row = $sales_result->fetch_assoc()) {
    switch($period) {
        case 'weekly': $labels[] = 'Week '.$row['wk'].'-'.$row['yr']; break;
        case 'monthly': $labels[] = date("F Y", mktime(0,0,0,$row['mon'],1,$row['yr'])); break;
        case 'yearly': $labels[] = $row['yr']; break;
        case 'daily': default: $labels[] = $row['day']; break;
    }
    $totals[] = $row['total_sales'];
}

// --- Per-branch financial breakdown ---
$branch_filter_sql = ($branch != '') ? " AND s.branch='".$conn->real_escape_string($branch)."'" : "";

$branch_breakdown_query = "
    SELECT 
        s.branch,
        SUM(s.quantity_sold * s.sale_price) AS revenue,
        SUM((s.sale_price - i.cost_price) * s.quantity_sold) AS gross_profit,
        (SELECT COALESCE(SUM(amount),0) FROM expenses e 
         WHERE e.branch = s.branch " . 
         (($from != '' && $to != '') ? " AND DATE(e.expense_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'" : "") . "
        ) AS total_expenses
    FROM sales s
    JOIN inventory i ON s.product_id = i.product_id
    WHERE 1
    " . (($from != '' && $to != '') ? " AND DATE(s.sale_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'" : "") . "
    $branch_filter_sql
    GROUP BY s.branch
    ORDER BY s.branch ASC
";

$breakdown_result = $conn->query($branch_breakdown_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial,sans-serif; background:#f9f9f9; margin:20px; }
        h1 { text-align:center; color:#004085; }
        table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; }
        th, td { border:1px solid #ddd; padding:10px; }
        th { background:#007BFF; color:#fff; text-align:left; }
        td.amount { text-align:right; }
        .btn { padding:8px 12px; text-decoration:none; border-radius:5px; margin:2px; display:inline-block; }
        .btn-blue { background:#007BFF; color:white; }
        .btn-print { background:purple; color:white; }
        form { margin-bottom:20px; }
        label { margin-right:5px; }
        select,input { padding:5px; margin-right:5px; }

        /* Hide elements when printing */
        @media print {
            form, .btn-print { display:none; }
        }
    </style>
</head>
<body>
<h1>Sales Reports</h1>

<form method="get">
    <label>Branch:</label>
    <select name="branch">
        <option value="">All</option>
        <?php while($row = $branches->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['branch']) ?>" <?= ($branch==$row['branch'])?'selected':'' ?>><?= htmlspecialchars($row['branch']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>From:</label>
    <input type="date" name="from" value="<?= $from ?>">
    <label>To:</label>
    <input type="date" name="to" value="<?= $to ?>">

    <label>Period:</label>
    <select name="period">
        <option value="daily" <?= ($period=='daily')?'selected':'' ?>>Daily</option>
        <option value="weekly" <?= ($period=='weekly')?'selected':'' ?>>Weekly</option>
        <option value="monthly" <?= ($period=='monthly')?'selected':'' ?>>Monthly</option>
        <option value="yearly" <?= ($period=='yearly')?'selected':'' ?>>Yearly</option>
    </select>

    <button type="submit" class="btn btn-blue">Filter</button>
    <button type="button" onclick="window.print()" class="btn btn-print">Print Report</button>
</form>

<h2>Sales Chart</h2>
<canvas id="salesChart" style="max-width:90%; margin:auto;"></canvas>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx,{
    type:'bar',
    data:{
        labels: <?= json_encode($labels) ?>,
        datasets:[{
            label:'Sales',
            data: <?= json_encode($totals) ?>,
            backgroundColor:'rgba(0,123,255,0.7)',
            borderColor:'blue',
            borderWidth:1
        }]
    },
    options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
});
</script>

<h2>Sales Details</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Quantity Sold</th>
        <th>Sale Price</th>
        <th>Total</th>
        <th>Branch</th>
        <th>Date</th>
    </tr>
    <?php
    $details_query = "SELECT * FROM sales WHERE 1";
    if($branch != '') $details_query .= " AND branch='".$conn->real_escape_string($branch)."'";
    if($from != '' && $to != '') $details_query .= " AND DATE(sale_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";
    $details_result = $conn->query($details_query);

    if($details_result->num_rows > 0):
        while($row = $details_result->fetch_assoc()):
            $total_amount = $row['quantity_sold'] * $row['sale_price'];
    ?>
    <tr>
        <td><?= $row['sale_id'] ?></td>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= $row['quantity_sold'] ?></td>
        <td class="amount"><?= format_kwacha($row['sale_price']) ?></td>
        <td class="amount"><?= format_kwacha($total_amount) ?></td>
        <td><?= htmlspecialchars($row['branch']) ?></td>
        <td><?= $row['sale_date'] ?></td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="7">No sales found.</td></tr>
    <?php endif; ?>
</table>

<h2>Per-Branch Financial Breakdown</h2>
<table>
    <tr>
        <th>Branch</th>
        <th>Revenue</th>
        <th>Expenses</th>
        <th>Gross Profit</th>
        <th>Net Profit</th>
    </tr>
    <?php if($breakdown_result && $breakdown_result->num_rows > 0): ?>
        <?php while($row = $breakdown_result->fetch_assoc()): 
            $net_profit = $row['gross_profit'] - $row['total_expenses'];
        ?>
        <tr>
            <td><?= htmlspecialchars($row['branch']) ?></td>
            <td class="amount"><?= format_kwacha($row['revenue']) ?></td>
            <td class="amount"><?= format_kwacha($row['total_expenses']) ?></td>
            <td class="amount"><?= format_kwacha($row['gross_profit']) ?></td>
            <td class="amount"><?= format_kwacha($net_profit) ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No data available.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
