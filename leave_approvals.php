<?php
session_start();
require_once("connection.php");

// Optional: Only allow Admin or Manager to access
if (!in_array($_SESSION['role'], ['Admin', 'Manager'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Approvals</title>
<style>
body { font-family: Arial, sans-serif; background:#f7f9fc; margin:0; padding:0; }
.dashboard-container { display:flex; }
.main-content { flex:1; padding:20px; }
h2 { color:#333; margin-bottom:20px; text-align:center; }

/* Table styling */
table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);}
th, td { padding:12px 15px; border:1px solid #ccc; text-align:left; }
th { background:#007bff; color:#fff; font-weight:600; text-transform:uppercase; }
tr:hover { background:#eaf2ff; }

/* Buttons */
.btn { padding:6px 12px; border:none; border-radius:5px; color:#fff; text-decoration:none; cursor:pointer; font-size:14px; transition:all 0.3s; }
.btn-approve { background:#28a745; }
.btn-approve:hover { background:#218838; }
.btn-deny { background:#dc3545; }
.btn-deny:hover { background:#c82333; }

/* No pending requests */
.no-pending { margin:20px auto; background:#fff3cd; padding:15px; border-left:5px solid #ffc107; border-radius:8px; max-width:500px; text-align:center; font-weight:bold; }

/* Mobile responsiveness */
@media (max-width:768px) {
    table, thead, tbody, th, td, tr { display:block; }
    thead tr { display:none; }
    tr { margin-bottom:15px; border:1px solid #ddd; border-radius:10px; padding:10px; background:#fff; }
    td { text-align:right; padding-left:50%; position:relative; }
    td::before { content: attr(data-label); position:absolute; left:15px; font-weight:bold; color:#007bff; text-transform:uppercase; }
    .btn { width:48%; margin-bottom:5px; display:inline-block; text-align:center; }
}
</style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <h2>Leave Approvals</h2>

        <?php
        // Fetch pending leave requests
        $query = "SELECT id, leave_type, requested_by, number_of_days, status FROM leave_requests WHERE status='pending' ORDER BY created_at DESC";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            echo '<table>';
            echo '<thead><tr><th>Leave Type</th><th>Requested By</th><th>Number of Days</th><th>Status</th><th>Action</th></tr></thead><tbody>';
            while ($row = $result->fetch_assoc()) {
                $id = (int)$row['id'];
                echo '<tr>';
                echo '<td data-label="Leave Type">'.htmlspecialchars($row['leave_type']).'</td>';
                echo '<td data-label="Requested By">'.htmlspecialchars($row['requested_by']).'</td>';
                echo '<td data-label="Number of Days">'.htmlspecialchars($row['number_of_days']).'</td>';
                echo '<td data-label="Status"><span style="color:orange;">Pending</span></td>';
                echo '<td data-label="Action">
                        <a class="btn btn-approve" href="approve_leave.php?id='.$id.'">Approve</a>
                        <a class="btn btn-deny" href="deny_leave.php?id='.$id.'">Deny</a>
                      </td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="no-pending">No pending leave requests.</div>';
        }
        $conn->close();
        ?>
    </div>
</div>
</body>
</html>
