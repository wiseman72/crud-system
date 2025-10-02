<?php
require_once("connection.php");
session_start();

$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Branch filter: only admin/manager can select all branches
if (in_array($user_role, ['Admin', 'Manager'])) {
    $branch = isset($_GET['branch']) ? $_GET['branch'] : '';
} else {
    $branch = $user_branch; // force user's branch
}

// Fetch distinct branches
if (in_array($user_role, ['Admin', 'Manager'])) {
    $branches = $conn->query("SELECT DISTINCT branch FROM inventory");
} else {
    // Only user's branch for dropdown
    $branches = new ArrayObject([['branch' => $user_branch]]);
}

// Fetch expenses
$params = [];
$query = "SELECT * FROM expenses WHERE branch=?";
$params[] = $branch;
if ($branch == '') {
    // For admin/manager requesting all branches
    $query = "SELECT * FROM expenses ORDER BY expense_date DESC, expense_id DESC";
    $result = $conn->query($query);
} else {
    $query .= " ORDER BY expense_date DESC, expense_id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branch);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Calculate total expenses
if ($branch == '') {
    $total_query = "SELECT SUM(amount) AS total FROM expenses";
    $total_expenses = $conn->query($total_query)->fetch_assoc()['total'] ?? 0;
} else {
    $total_query = "SELECT SUM(amount) AS total FROM expenses WHERE branch=?";
    $stmt2 = $conn->prepare($total_query);
    $stmt2->bind_param("s", $branch);
    $stmt2->execute();
    $stmt2->bind_result($total_expenses);
    $stmt2->fetch();
    $stmt2->close();
    $total_expenses = $total_expenses ?? 0;
}

// Helper for currency
function format_kwacha($amt) { return "K₵ " . number_format($amt,2); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Expenses</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6fa; margin:0; }
        .container { max-width:1100px; margin:30px auto; background:#fff; padding:30px; border-radius:12px; box-shadow: 0 6px 22px rgba(0,0,0,0.07);}
        h1 { color:#004085; margin-bottom:24px; text-align:center;}
        .top-bar { display:flex; gap:14px; align-items:center; justify-content:space-between; margin-bottom:24px;}
        .btn, button { padding:8px 18px; text-decoration:none; border-radius:6px; background:#007bff; color:#fff; border:none; font-weight:600; cursor:pointer;}
        .btn:hover, button:hover { background:#0056b3;}
        .btn-red { background:red; }
        .btn-red:hover { background:darkred; }
        .btn-green { background:green; }
        .btn-green:hover { background:darkgreen; }
        table { width:100%; border-collapse:collapse; margin-top:16px; background:#fff;}
        th, td { padding:10px 12px; border-bottom:1px solid #eee;}
        th { background:#007bff; color:white; }
        td.amount { text-align:right; }
        tfoot th { text-align:right; background:#f0f0f0;}
        .actions a { margin-right:7px; }
        form.filter-form { display:inline; }
        select { padding:7px; border-radius:4px; border:1px solid #ccc;}
        @media (max-width: 700px) {
            .container { padding:10px;}
            table, th, td { font-size:0.95rem;}
            .top-bar { flex-direction:column; gap:8px;}
        }
        @media print {
            .top-bar, .actions, .btn, .btn-green, .btn-red { display:none !important; }
            body, .container { background:white; color:black; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Expenses</h1>
    <div class="top-bar">
        <form method="get" class="filter-form">
            <label for="branch">Branch:</label>
            <select name="branch" id="branch" onchange="this.form.submit()" <?= (in_array($user_role, ['Admin', 'Manager'])) ? "" : "readonly disabled" ?>>
                <?php if(in_array($user_role, ['Admin', 'Manager'])): ?>
                    <option value="">All Branches</option>
                <?php endif; ?>
                <?php foreach($branches as $row): ?>
                    <option value="<?= $row['branch'] ?>" <?= ($branch==$row['branch'])?'selected':'' ?>><?= $row['branch'] ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <div>
            <a href="add_expense.php" class="btn btn-green">Add Expense</a>
            <button onclick="window.print()" class="btn">Print</button>
            <a href="finance.php" class="btn">Back to Finance</a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Expense Name</th>
                <th>Amount</th>
                <th>Branch</th>
                <th>Date</th>
                <th class="actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result->num_rows>0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['expense_id'] ?></td>
                        <td><?= htmlspecialchars($row['expense_name']) ?></td>
                        <td class="amount"><?= format_kwacha($row['amount']) ?></td>
                        <td><?= htmlspecialchars($row['branch']) ?></td>
                        <td><?= htmlspecialchars($row['expense_date'] ?? '') ?></td>
                        <td class="actions">
                            <a href="edit_expense.php?id=<?= $row['expense_id'] ?>" class="btn">Edit</a>
                            <a href="delete_expense.php?id=<?= $row['expense_id'] ?>" class="btn btn-red" onclick="return confirm('Delete this expense?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="color:red;text-align:center;">No expenses found.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total Expenses:</th>
                <th class="amount"><?= format_kwacha($total_expenses) ?></th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>