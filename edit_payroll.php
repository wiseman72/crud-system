<?php
session_start();
require_once("connection.php");

if (!$conn) {
    die("Connection is not established");
}

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        die("Invalid ID");
    }

    $query = "SELECT * FROM `payrolls` WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $row = $result->fetch_assoc();
}

if (isset($_POST['submit'])) {
    $staff = $_POST['staff'];
    $pay_period = $_POST['pay_period'];
    $gross_pay = floatval($_POST['gross_pay']);
    $deduction = floatval($_POST['deduction']);
    $net_pay = $gross_pay - $deduction;
    $status = $_POST['status'];
    $branch = $_POST['branch'];
    $id = $_POST['id'];

    $query = "UPDATE `payrolls` SET staff = ?, pay_period = ?, gross_pay = ?, deduction = ?, net_pay = ?, status = ?, branch = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $staff, $pay_period, $gross_pay, $deduction, $net_pay, $status, $branch, $id);

    if ($stmt->execute()) {
        echo '<div class="success-message">Payroll updated successfully.</div>';
        echo "<p><a class='btn-link' href='payroll.php'>Back to payroll list</a></p>";
        echo "<p><a class='btn-link' href='index.php'>Back to Menu</a></p>";
    } else {
        echo '<div class="error-message">Error: ' . $conn->error . '</div>';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Payroll</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }
    form {
        background-color: #fff;
        max-width: 600px;
        margin: 40px auto;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
    }
    input[type="text"], input[type="number"], input[type="date"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }
    input[type="submit"] {
        margin-top: 20px;
        padding: 12px 25px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 7px;
        cursor: pointer;
        font-size: 16px;
    }
    input[type="submit"]:hover {
        background-color: #218838;
    }
    .success-message, .error-message {
        max-width: 600px;
        margin: 20px auto;
        padding: 15px;
        border-radius: 7px;
        font-weight: bold;
    }
    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .btn-link {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }
    .btn-link:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<form action="" method="post">
    <h2 style="text-align:center;">Edit Payroll</h2>
    <input type="hidden" name="id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>">

    <label for="staff">Staff:</label>
    <input type="text" id="staff" name="staff" required value="<?php echo isset($row['staff']) ? htmlspecialchars($row['staff']) : ''; ?>">

    <label for="pay_period">Pay Period:</label>
    <input type="text" id="pay_period" name="pay_period" required value="<?php echo isset($row['pay_period']) ? htmlspecialchars($row['pay_period']) : ''; ?>">

    <label for="gross_pay">Gross Pay:</label>
    <input type="number" step="0.01" id="gross_pay" name="gross_pay" required value="<?php echo isset($row['gross_pay']) ? $row['gross_pay'] : ''; ?>">

    <label for="deduction">Deduction:</label>
    <input type="number" step="0.01" id="deduction" name="deduction" required value="<?php echo isset($row['deduction']) ? $row['deduction'] : ''; ?>">

    <label for="status">Status:</label>
    <input type="text" id="status" name="status" required value="<?php echo isset($row['status']) ? htmlspecialchars($row['status']) : ''; ?>">

    <label for="branch">Branch:</label>
    <input type="text" id="branch" name="branch" required value="<?php echo isset($row['branch']) ? htmlspecialchars($row['branch']) : ''; ?>">

    <input type="submit" name="submit" value="Update Payroll">
</form>
</body>
</html>

<?php
$conn->close();
?>
