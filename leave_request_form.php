<?php
session_start();
require_once("connection.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $leave_type = trim($_POST['leave_type']);
    $requested_by = $_SESSION['username'];
    $number_of_days = (int)$_POST['number_of_days'];

    // Simple validation
    if (empty($leave_type) || $number_of_days <= 0) {
        $errorMsg = "Please select a valid leave type and number of days.";
    } else {
        $stmt = $conn->prepare("INSERT INTO leave_requests (leave_type, requested_by, number_of_days, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("ssi", $leave_type, $requested_by, $number_of_days);

        if ($stmt->execute()) {
            $successMsg = "Leave request submitted successfully.";
        } else {
            $errorMsg = "Error submitting request: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Request Form</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .form-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
    }
    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
        color: #555;
    }
    select, input[type="number"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 14px;
    }
    input[type="submit"] {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        background-color: #007bff;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    input[type="submit"]:hover {
        background-color: #0056b3;
    }
    .message {
        text-align: center;
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 6px;
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }

    @media (max-width: 480px) {
        .form-container { padding: 20px; }
        h1 { font-size: 18px; }
    }
</style>
</head>
<body>
<div class="form-container">
    <h1>Leave Request Form</h1>

    <?php if ($successMsg) echo "<div class='message success'>{$successMsg}</div>"; ?>
    <?php if ($errorMsg) echo "<div class='message error'>{$errorMsg}</div>"; ?>

    <form action="" method="post">
        <label for="leave_type">Leave Type:</label>
        <select name="leave_type" id="leave_type" required>
            <option value="">Select Leave Type</option>
            <option value="Annual Leave">Annual Leave</option>
            <option value="Sick Leave">Sick Leave</option>
            <option value="Maternity Leave">Maternity Leave</option>
            <option value="Paternity Leave">Paternity Leave</option>
        </select>

        <label for="number_of_days">Number of Days:</label>
        <input type="number" name="number_of_days" id="number_of_days" min="1" required>

        <input type="submit" name="submit_request" value="Submit Request">
    </form>
</div>
</body>
</html>
