<?php
require_once("connection.php");

if (isset($_POST['import'])) {
    $file = $_FILES['file']['tmp_name'];

    if ($file) {
        $handle = fopen($file, 'r');
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($row == 0) { $row++; continue; } // Skip header row
            $name = $data[0];
            $amount = floatval($data[1]);
            $branch = $data[2];
            $date = $data[3] ?: date('Y-m-d H:i:s');

            $stmt = $conn->prepare("INSERT INTO expenses (expense_name, amount, branch, expense_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $name, $amount, $branch, $date);
            $stmt->execute();
            $row++;
        }
        fclose($handle);
        header("Location: finance.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Expenses</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f2f6fc; margin:20px; }
        h1 { text-align:center; color:#004085; }
        form { width:400px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.2); }
        input[type=file] { width:100%; margin-top:10px; }
        button { background:#007BFF; color:white; border:none; padding:10px 15px; margin-top:15px; border-radius:5px; cursor:pointer; }
        button:hover { background:#0056b3; }
        a { display:block; margin-top:15px; text-align:center; color:#007BFF; text-decoration:none; }
    </style>
</head>
<body>
<h1>Import Expenses</h1>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit" name="import">Import</button>
</form>
<a href="finance.php">⬅ Back to Finance</a>
</body>
</html>
