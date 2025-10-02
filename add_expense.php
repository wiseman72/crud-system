<?php
require_once("connection.php");
session_start();

$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Branch logic: only allow normal users to add expenses for their own branch
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['expense_name'];
    $amount = $_POST['amount'];
    $branch = $_POST['branch'];

    // If not admin/manager, force branch to user's branch
    if (!in_array($user_role, ['Admin', 'Manager'])) {
        $branch = $user_branch;
    }

    $stmt = $conn->prepare("INSERT INTO expenses (expense_name, amount, branch) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $amount, $branch);

    if ($stmt->execute()) {
        header("Location: finance.php");
        exit();
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
}

// Only show allowed branches in dropdown
if (in_array($user_role, ['Admin', 'Manager'])) {
    $branches = $conn->query("SELECT DISTINCT branch FROM inventory");
} else {
    $branches = new ArrayObject([['branch' => $user_branch]]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f2f6fc; margin:20px; }
        h1 { text-align:center; color:#004085; }
        form { width:400px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.2); }
        label { display:block; margin-top:10px; color:#004085; }
        input, select { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
        button { background:#007BFF; color:white; border:none; padding:10px 15px; margin-top:15px; border-radius:5px; cursor:pointer; }
        button:hover { background:#0056b3; }
        a { display:inline-block; margin-top:15px; text-decoration:none; color:#007BFF; }
    </style>
</head>
<body>
    <h1>Add Expense</h1>
    <form method="post">
        <label>Expense Name:</label>
        <input type="text" name="expense_name" required>

        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" required>

        <label>Branch:</label>
        <select name="branch" required <?= (!in_array($user_role, ['Admin', 'Manager'])) ? 'readonly disabled' : '' ?>>
            <option value="">Select Branch</option>
            <?php foreach ($branches as $row): ?>
                <option value="<?= $row['branch'] ?>" <?= ($row['branch'] == $user_branch ? 'selected' : '') ?>><?= $row['branch'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Save Expense</button>
    </form>
    <a href="finance.php">⬅ Back to Finance</a>
</body>
</html>