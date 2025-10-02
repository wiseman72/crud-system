<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
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
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <?php include 'header.php'; ?>
            <h2>Payroll</h2>

            <form action="" method="post" class="search-bar-container">
                <input type="text" name="search_term" class="search-bar" placeholder="Search payroll...">
                <input type="submit" name="search" class="btn" value="Search">
            </form>

            <a href="add_payroll.php" class="btn">Add new payrolls</a>
            <button class="btn" onclick="printPayrollList()">Print payroll List</button>

            <?php
            require_once("connection.php");

            if (!$conn) {
                echo '<div class="finance-summary">Connection is not established</div>';
            } else {
                if (isset($_POST['search'])) {
                    $search_term = '%' . $_POST['search_term'] . '%';
                    $query = "SELECT id, staff, pay_period, gross_pay, deduction, net_pay, status, branch 
                              FROM `payrolls` 
                              WHERE staff LIKE ? OR pay_period LIKE ? OR gross_pay LIKE ? OR deduction LIKE ? 
                              OR net_pay LIKE ? OR status LIKE ? OR branch LIKE ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $query = "SELECT id, staff, pay_period, gross_pay, deduction, net_pay, status, branch FROM `payrolls`";
                    $result = $conn->query($query);
                }

                if (!$result) {
                    echo '<div class="finance-summary">Query failed: ' . $conn->error . '</div>';
                } else if ($result->num_rows > 0) {
            ?>
                <table class="table table-striped" id="payroll-table">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Pay Period</th>
                            <th>Gross Pay</th>
                            <th>Deduction</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th>Branch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['staff']); ?></td>
                                <td><?php echo htmlspecialchars($row['pay_period']); ?></td>
                                <td><?php echo number_format($row['gross_pay'],2); ?></td>
                                <td><?php echo number_format($row['deduction'],2); ?></td>
                                <td><?php echo number_format($row['net_pay'],2); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['branch']); ?></td>
                                <td>
                                    <a class="btn" href="edit_payroll.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                                    <a class="btn" href="delete_payroll.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php
                } else {
                    echo '<div class="finance-summary">No records found</div>';
                }

                if (isset($stmt)) $stmt->close();
                $conn->close();
            }
            ?>
        </div>
    </div>

    <script>
        function printPayrollList() {
            var table = document.getElementById('payroll-table');
            var win = window.open('', '', 'height=500,width=700');
            win.document.write('<html><head><title>Payroll List</title></head><body>');
            win.document.write(table.outerHTML);
            win.document.write('</body></html>');
            win.document.close();
            win.print();
            win.close();
        }
    </script>
</body>
</html>
