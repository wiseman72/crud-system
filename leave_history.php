<?php
session_start();
require_once("connection.php");

// Get current user
$username = $_SESSION['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave History</title>
<style>
body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f9;
    color: #333;
}
.dashboard-container { display: flex; }
.main-content { padding: 20px; flex: 1; }
h2 { text-align: center; color: #444; margin-bottom: 20px; }

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
th, td { padding: 14px 18px; text-align: left; font-size: 15px; }
th { background: #007bff; color: white; font-weight: bold; text-transform: uppercase; }
tr:nth-child(even) { background: #f9f9f9; }
tr:hover { background: #eaf2ff; transform: scale(1.01); transition: 0.3s; }

td span { font-weight: bold; padding: 5px 10px; border-radius: 8px; }
td span[style*="green"] { background: #d4edda; color: #155724; }
td span[style*="red"] { background: #f8d7da; color: #721c24; }
td span[style*="orange"] { background: #fff3cd; color: #856404; }

/* No history message */
.no-history {
    margin: 20px auto;
    background: #fff3cd;
    padding: 15px;
    border-left: 5px solid #ffc107;
    border-radius: 8px;
    max-width: 500px;
    text-align: center;
    font-weight: bold;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; }
    thead tr { display: none; }
    tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 10px; padding: 10px; }
    td { text-align: right; padding-left: 50%; position: relative; }
    td::before { content: attr(data-label); position: absolute; left: 15px; font-weight: bold; color: #007bff; text-transform: uppercase; }
}
</style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <h2>Leave History</h2>

        <?php
        // Fetch leave requests for current user
        $stmt = $conn->prepare("SELECT leave_type, number_of_days, status FROM leave_requests WHERE requested_by = ? ORDER BY created_at DESC");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo '<table>';
            echo '<thead><tr><th>Leave Type</th><th>Number of Days</th><th>Status</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                $statusColor = match($row['status']) {
                    'approved' => 'green',
                    'denied' => 'red',
                    default => 'orange',
                };
                echo '<tr>';
                echo '<td data-label="Leave Type">'.htmlspecialchars($row['leave_type']).'</td>';
                echo '<td data-label="Number of Days">'.htmlspecialchars($row['number_of_days']).'</td>';
                echo '<td data-label="Status"><span style="color: '.$statusColor.';">'.ucfirst($row['status']).'</span></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="no-history">No leave history found</div>';
        }

        $conn->close();
        ?>
    </div>
</div>
</body>
</html>
