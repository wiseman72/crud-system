<?php
require_once("connection.php");
session_start();

$user_branch = $_SESSION['branch'] ?? '';
$user_role = $_SESSION['role'] ?? '';

// Validate expense ID
if (!isset($_GET['id'])) {
    die("Invalid expense ID.");
}
$id = intval($_GET['id']);

// Fetch expense
$stmt = $conn->prepare("SELECT * FROM expenses WHERE expense_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$expense = $stmt->get_result()->fetch_assoc();

if (!$expense) die("Expense not found.");

// Branch security: only admin/manager can edit other branches
if (!in_array($user_role, ['Admin', 'Manager']) && $expense['branch'] !== $user_branch) {
    die("You are not allowed to edit expenses from another branch.");
}

// Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['expense_name'];
    $amount = $_POST['amount'];
    $branch = $_POST['branch'];

    // Non-admin/manager: force branch to user's branch
    if (!in_array($user_role, ['Admin', 'Manager'])) {
        $branch = $user_branch;
    }

    $stmt = $conn->prepare("UPDATE expenses SET expense_name=?, amount=?, branch=? WHERE expense_id=?");
    $stmt->bind_param("sdsi", $name, $amount, $branch, $id);

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
    <title>Edit Expense</title>
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
    <h1>Edit Expense</h1>
    <form method="post">
        <label>Expense Name:</label>
        <input type="text" name="expense_name" value="<?= htmlspecialchars($expense['expense_name']) ?>" required>

        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($expense['amount']) ?>" required>

        <label>Branch:</label>
        <select name="branch" required <?= (!in_array($user_role, ['Admin', 'Manager'])) ? 'readonly disabled' : '' ?>>
            <?php foreach ($branches as $row): ?>
                <option value="<?= $row['branch'] ?>" <?= ($expense['branch'] == $row['branch']) ? 'selected' : '' ?>>
                    <?= $row['branch'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update Expense</button>
    </form>
    <a href="finance.php">⬅ Back to Finance</a>
</body>
</html>