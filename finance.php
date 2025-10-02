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

// Fetch distinct branches into array
$branches_result = $conn->query("SELECT DISTINCT branch FROM inventory");
$branches_arr = [];
while($row = $branches_result->fetch_assoc()) {
    $branches_arr[] = $row['branch'];
}

// --- Revenue Query based on period ---
switch($period) {
    case 'weekly':
        $rev_query = "SELECT YEAR(sale_date) AS yr, WEEK(sale_date) AS wk, SUM(quantity_sold * sale_price) AS revenue 
                      FROM sales WHERE 1";
        break;
    case 'monthly':
        $rev_query = "SELECT YEAR(sale_date) AS yr, MONTH(sale_date) AS mon, SUM(quantity_sold * sale_price) AS revenue 
                      FROM sales WHERE 1";
        break;
    case 'yearly':
        $rev_query = "SELECT YEAR(sale_date) AS yr, SUM(quantity_sold * sale_price) AS revenue 
                      FROM sales WHERE 1";
        break;
    case 'daily':
    default:
        $rev_query = "SELECT DATE(sale_date) AS day, SUM(quantity_sold * sale_price) AS revenue 
                      FROM sales WHERE 1";
        break;
}

if($branch != '') $rev_query .= " AND branch='".$conn->real_escape_string($branch)."'";
if($from != '' && $to != '') $rev_query .= " AND DATE(sale_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";

switch($period) {
    case 'weekly': $rev_query .= " GROUP BY yr, wk ORDER BY yr, wk"; break;
    case 'monthly': $rev_query .= " GROUP BY yr, mon ORDER BY yr, mon"; break;
    case 'yearly': $rev_query .= " GROUP BY yr ORDER BY yr"; break;
    case 'daily': default: $rev_query .= " GROUP BY day ORDER BY day"; break;
}

$rev_result = $conn->query($rev_query);
$labels = []; $revenues = [];
while($row = $rev_result->fetch_assoc()) {
    switch($period) {
        case 'weekly': $labels[] = 'Week '.$row['wk'].'-'.$row['yr']; break;
        case 'monthly': $labels[] = date("F Y", mktime(0,0,0,$row['mon'],1,$row['yr'])); break;
        case 'yearly': $labels[] = $row['yr']; break;
        case 'daily': default: $labels[] = $row['day']; break;
    }
    $revenues[] = $row['revenue'];
}

// --- Expenses ---
$exp_query = "SELECT * FROM expenses WHERE 1";
if($branch != '') $exp_query .= " AND branch='".$conn->real_escape_string($branch)."'";
if($from != '' && $to != '') $exp_query .= " AND DATE(expense_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";

$expenses_result = $conn->query($exp_query);

// Total expenses
$exp_total_query = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE 1";
if($branch != '') $exp_total_query .= " AND branch='".$conn->real_escape_string($branch)."'";
if($from != '' && $to != '') $exp_total_query .= " AND DATE(expense_date) BETWEEN '".$conn->real_escape_string($from)."' AND '".$conn->real_escape_string($to)."'";
$total_expenses = $conn->query($exp_total_query)->fetch_assoc()['total_expenses'] ?? 0;

// --- Profit ---
$profit = array_sum($revenues) - $total_expenses;

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
    <title>Finance Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial,sans-serif; background:#f9f9f9; margin:20px; }
        h1 { text-align:center; color:#004085; }
        .summary { display:flex; justify-content:space-around; margin:20px 0; flex-wrap:wrap; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); width:30%; text-align:center; margin:10px; }
        .revenue { border-top:5px solid green; }
        .expenses { border-top:5px solid red; }
        .profit { border-top:5px solid blue; }
        table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; }
        th, td { border:1px solid #ddd; padding:10px; }
        th { background:#007BFF; color:#fff; text-align:left; }
        td.amount, .card p { text-align:right; }
        .btn { padding:8px 12px; text-decoration:none; border-radius:5px; margin:2px; display:inline-block; }
        .btn-blue { background:#007BFF; color:white; }
        .btn-red { background:red; color:white; }
        .btn-green { background:green; color:white; }
        .btn-print { background:purple; color:white; }
        form { margin-bottom:20px; }
        label { margin-right:5px; }
        select,input { padding:5px; margin-right:5px; }
    </style>
</head>
<body>
<h1>Finance Dashboard</h1>

<form method="get">
    <label>Branch:</label>
    <select name="branch">
        <option value="">All</option>
        <?php foreach($branches_arr as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>" <?= ($branch==$b)?'selected':'' ?>><?= htmlspecialchars($b) ?></option>
        <?php endforeach; ?>
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
    <button type="button" onclick="window.print()" class="btn btn-print">Print</button>
    <a href="add_expense.php" class="btn btn-green">Add Expense</a>
    <a href="export_expenses.php?branch=<?= $branch ?>&from=<?= $from ?>&to=<?= $to ?>" class="btn btn-blue">Export to Excel</a>
    <a href="import_expenses.php" class="btn btn-blue">Import from Excel</a>
</form>

<div class="summary">
    <div class="card revenue"><h2>Revenue</h2><p><b><?= format_kwacha(array_sum($revenues)) ?></b></p></div>
    <div class="card expenses"><h2>Expenses</h2><p><b><?= format_kwacha($total_expenses) ?></b></p></div>
    <div class="card profit"><h2>Profit</h2><p><b><?= format_kwacha($profit) ?></b></p></div>
</div>

<h2>Revenue Chart <?= ($branch != '' ? "for " . htmlspecialchars($branch) : "(All Branches)") ?></h2>
<canvas id="revenueChart" style="max-width:90%; margin:auto;"></canvas>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx,{
    type:'bar',
    data:{
        labels: <?= json_encode($labels) ?>,
        datasets:[{
            label:'Revenue <?= ($branch != '' ? "(".addslashes($branch).")" : "(All)") ?>',
            data: <?= json_encode($revenues) ?>,
            backgroundColor:'rgba(0,123,255,0.7)',
            borderColor:'blue',
            borderWidth:1
        }]
    },
    options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
});
</script>

<h2>Per-Branch Financial Breakdown</h2>
<table>
    <tr>
        <th>Branch</th>
        <th>Revenue</th>
        <th>Expenses</th>
        <th>Gross Profit</th>
        <th>Net Profit</th>
    </tr>
    <?php if($breakdown_result && $breakdown_result->num_rows>0): ?>
        <?php while($row = $breakdown_result->fetch_assoc()): 
            $net = $row['gross_profit'] - $row['total_expenses'];
        ?>
            <tr>
                <td><?= htmlspecialchars($row['branch']) ?></td>
                <td class="amount"><?= format_kwacha($row['revenue']) ?></td>
                <td class="amount"><?= format_kwacha($row['total_expenses']) ?></td>
                <td class="amount"><?= format_kwacha($row['gross_profit']) ?></td>
                <td class="amount"><?= format_kwacha($net) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No data available for selected filters.</td></tr>
    <?php endif; ?>
</table>

<h2>Expenses</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Expense Name</th>
        <th>Amount</th>
        <th>Branch</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    <?php if($expenses_result->num_rows>0): ?>
        <?php while($row = $expenses_result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['expense_id'] ?></td>
                <td><?= $row['expense_name'] ?></td>
                <td class="amount"><?= format_kwacha($row['amount']) ?></td>
                <td><?= $row['branch'] ?></td>
                <td><?= $row['expense_date'] ?></td>
                <td>
                    <a href="edit_expense.php?id=<?= $row['expense_id'] ?>" class="btn btn-blue">Edit</a>
                    <a href="delete_expense.php?id=<?= $row['expense_id'] ?>" class="btn btn-red" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No expenses found.</td></tr>
    <?php endif; ?>
</table>
</body>
</html>
