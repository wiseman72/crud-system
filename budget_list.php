


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget List</title>
    <style>

      /* General body and dashboard layout */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7f8;
    margin: 0;
    padding: 0;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Main content area */
.main-content {
    flex: 1;
    padding: 30px;
    background-color: #fff;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
}

.main-content h2 {
    margin-bottom: 20px;
    color: #333;
    font-size: 28px;
    font-weight: 600;
}

/* Table styles */
#budget-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

#budget-table th, #budget-table td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
}

#budget-table th {
    background-color: #4a90e2;
    color: white;
    font-weight: 600;
}

#budget-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

#budget-table tr:hover {
    background-color: #f1f1f1;
}

/* Buttons */
.btn {
    display: inline-block;
    padding: 10px 18px;
    margin: 5px 2px;
    background-color: green;
    color: white;
    font-weight: 500;
    text-decoration: none;
    border-radius: 6px;
    transition: background-color 0.3s ease, transform 0.2s ease;
    cursor: pointer;
    border: none;
}

.btn:hover {
    background-color: darkgreen;
    transform: translateY(-2px);
}

/* Finance summary message */
.finance-summary {
    padding: 15px;
    background-color: #e0ffe0;
    border: 1px solid #2e7d32;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    margin-bottom: 20px;
}

/* Chart container */
#budgetChart {
    margin-top: 30px;
    max-width: 100%;
    height: 400px;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }

    .main-content {
        padding: 20px;
    }

    #budget-table th, #budget-table td {
        padding: 10px 8px;
    }
}

    </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <?php include 'header.php'; ?>
            <h2>Budget List</h2>
            <?php require_once("connection.php"); ?>
            <?php 
            // Query all budget records
            $query = "SELECT * FROM budgets";
            $result = mysqli_query($conn, $query);

            // Arrays for chart data
            $categories = array();
            $allocated = array();
            $actual = array();

            // Check if query was successful and has rows
            if ($result && mysqli_num_rows($result) > 0) { ?>
                <table class="table table-striped" id="budget-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Allocated</th>
                            <th>Actual</th>
                            <th>Variance</th>
                            <th>Percentage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    // Populate table and chart arrays in same loop
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = $row['category'];
                        $allocated[] = $row['allocated'];
                        $actual[] = $row['actual'];
                        $variance = $row['allocated'] - $row['actual'];
                        $percentage = ($row['allocated'] != 0) ? (($variance / $row['allocated']) * 100) : 0;
                        ?>
                        <tr>
                            <td><?php echo $row['category']; ?></td>
                            <td><?php echo $row['allocated']; ?></td>
                            <td><?php echo $row['actual']; ?></td>
                            <td><?php echo $variance; ?></td>
                            <td><?php echo round($percentage, 2); ?>%</td>
                            <td>
                                <a class="btn" href="edit_budget.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a class="btn" href="delete_budget.php?id=<?php echo $row['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } else { 
                echo '<div class="finance-summary">No budgets found</div>';
            } ?>
            <!-- Add and Print buttons -->
            <a href="add_budget.php" class="btn">Add New Budget</a>
            <button class="btn" onclick="window.print()">Print Budget</button>
            <!-- Display chart only if there is data -->
            <?php if (!empty($categories)) { ?>
                <canvas id="budgetChart"></canvas>
            <?php } ?>
        </div>
    </div>
    <!-- Chart.js for budget visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    <?php if (!empty($categories)) { ?>
        // Prepare and render budget chart using PHP arrays
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Allocated',
                    data: <?php echo json_encode($allocated); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: 'Actual',
                    data: <?php echo json_encode($actual); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    <?php } ?>
    </script>
</body>
</html>


