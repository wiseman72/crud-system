<?php
session_start();
require_once("connection.php");

if (!$conn) {
    die("Connection is not established");
}

if (isset($_POST['submit'])) {
    $staff = trim($_POST['staff']);
    $pay_period = trim($_POST['pay_period']);
    $gross_pay = floatval($_POST['gross_pay']);
    $deduction = floatval($_POST['deduction']);
    $net_pay = $gross_pay - $deduction;
    $status = trim($_POST['status']); 
    $branch = trim($_POST['branch']);

    if ($gross_pay < 0 || $deduction < 0) {
        echo '<div class="error-message">Invalid pay amount</div>';
    } else {
        $query = "INSERT INTO `payrolls` (staff, pay_period, gross_pay, deduction, net_pay, status, branch) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssddsss", $staff, $pay_period, $gross_pay, $deduction, $net_pay, $status, $branch);

        if ($stmt->execute()) {
            echo '<div class="success-message">New payroll added successfully.</div>';
            echo "<p><a class='btn-link' href='payroll.php'>Back to Payroll List</a></p>";
            echo "<p><a class='btn-link' href='index.php'>Back to Menu</a></p>";
        } else {
            echo '<div class="error-message">Error adding payroll: ' . $conn->error . '</div>';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Payroll</title>
<style>
/* ---------- Shared Styles ---------- */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9f9f9;
    color: #333;
    margin: 0;
    padding: 0;
}

h2 {
    color: #1976d2;
    margin-bottom: 20px;
}
    margin: 0;
    padding: 0;
}

h2 {
    color: #1976d2;
    margin-bottom: 20px;
}

/* ---------- Buttons ---------- */
.btn, .btn-link {
    display: inline-block;
    background: #1976d2;
    color: #fff;
    padding: 8px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
    margin: 5px 0;
}

.btn:hover, .btn-link:hover {
    background: #135cb0;
}

/* ---------- Payroll Table ---------- */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 3px 10px rgba(0,0,0,0.1);
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
}

.table th {
    background: #1976d2;
    color: #fff;
}

.table tr:nth-child(even) {
    background: #f1f1f1;
}

.table tr:hover {
    background: #e0f0ff;
}

/* ---------- Search Bar ---------- */
.search-bar-container {
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-bar {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
}

/* ---------- Forms (Add Payroll) ---------- */
form {
    max-width: 500px;
    margin: 20px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0px 3px 15px rgba(0,0,0,0.1);
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: 600;
}

form input[type="text"],
form input[type="number"],
form select {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1rem;
}

form input[type="submit"] {
    width: 100%;
    margin-top: 20px;
}

/* ---------- Messages ---------- */
.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

/* ---------- Print Button ---------- */
@media print {
    .btn, .search-bar-container {
        display: none;
    }
}
</style>
</head>
<body>
<form action="" method="post">
    <h2 style="text-align:center;">Add Payroll</h2>
    <label for="staff">Staff:</label>
    <input type="text" id="staff" name="staff" required>

    <label for="pay_period">Pay Period:</label>
    <input type="text" id="pay_period" name="pay_period" required>

    <label for="gross_pay">Gross Pay:</label>
    <input type="number" step="0.01" id="gross_pay" name="gross_pay" required>

    <label for="deduction">Deduction:</label>
    <input type="number" step="0.01" id="deduction" name="deduction" required>

    <label for="status">Status:</label>
    <select id="status" name="status" required>
        <option value="Pending">Pending</option>
        <option value="Paid">Paid</option>
        <option value="On Hold">On Hold</option>
    </select>

    <label for="branch">Branch:</label>
    <input type="text" id="branch" name="branch" required>

    <input type="submit" name="submit" value="Add Payroll">
</form>
</body>
</html>
