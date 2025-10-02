<?php
session_start();
require_once("connection.php");

// Get current user and branch
$username = $_SESSION['username'] ?? '';
$branch = $_SESSION['branch'] ?? '';

// Get current user role for this branch
$stmt = $conn->prepare("SELECT role FROM users WHERE name = ? AND branch = ?");
$stmt->bind_param("ss", $username, $branch);
$stmt->execute();
$result = $stmt->get_result();
$role = ($row = $result->fetch_assoc()) ? $row['role'] : '';

if (!in_array($role, ['Admin', 'Manager'])) {
    die("Access denied.");
}

// Handle approve/deny actions (only for requests in the same branch)
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    // Check request branch before updating
    $stmt = $conn->prepare("SELECT branch FROM leave_requests WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $req_result = $stmt->get_result();
    $req_branch = ($row = $req_result->fetch_assoc()) ? $row['branch'] : '';
    if ($req_branch === $branch) {
        if ($_GET['action'] === 'approve') {
            $stmt = $conn->prepare("UPDATE leave_requests SET status='approved' WHERE id=?");
        } else if ($_GET['action'] === 'deny') {
            $stmt = $conn->prepare("UPDATE leave_requests SET status='denied' WHERE id=?");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: approvals.php");
    exit();
}

// Fetch pending leave requests for current branch
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE status='pending' AND branch=? ORDER BY created_at DESC");
$stmt->bind_param("s", $branch);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leave Approvals</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body { font-family: Arial,sans-serif; background:#f4f6f9; margin:0; padding:0; }
.main-content { margin-left: 270px; padding: 20px; animation: fadeIn 0.7s ease-in-out; }
h2 { color:#444; text-align:center; animation: slideDown 0.6s ease; }
/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    animation: fadeInUp 0.8s ease;
}
th, td { padding:14px 18px; text-align:left; font-size:15px; }
th { background:#007bff; color:white; text-transform:uppercase; }
tr { transition: background 0.3s ease, transform 0.3s ease; }
tr:nth-child(even){ background:#f9f9f9; }
tr:hover { background:#eaf2ff; transform:scale(1.02); }
.btn { padding:6px 12px; border-radius:5px; text-decoration:none; color:#fff; margin-right:5px; transition:0.3s; }
.btn-approve { background:#28a745; }
.btn-approve:hover { background:#218838; }
.btn-deny { background:#dc3545; }
.btn-deny:hover { background:#c82333; }
/* Animations */
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
@keyframes slideDown { from {transform:translateY(-20px); opacity:0;} to {transform:translateY(0); opacity:1;} }
@keyframes fadeInUp { from {transform:translateY(20px); opacity:0;} to {transform:translateY(0); opacity:1;} }
/* Responsive */
@media(max-width:768px){
    .main-content{ margin-left:0; padding:10px; }
    table, thead, tbody, th, td, tr{ display:block; }
    thead tr{ display:none; }
    td{ padding-left:50%; position:relative; margin-bottom:10px; }
    td::before{ content:attr(data-label); position:absolute; left:10px; font-weight:bold; color:#007bff; }
}
</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
<h2>Pending Leave Approvals</h2>
<?php if ($result->num_rows > 0): ?>
<table>
<thead>
<tr>
<th>Leave Type</th>
<th>Requested By</th>
<th>Number of Days</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td data-label="Leave Type"><?php echo htmlspecialchars($row['leave_type']); ?></td>
<td data-label="Requested By"><?php echo htmlspecialchars($row['requested_by']); ?></td>
<td data-label="Number of Days"><?php echo (int)$row['number_of_days']; ?></td>
<td data-label="Action">
<a class="btn btn-approve" href="?action=approve&id=<?php echo $row['id']; ?>">Approve</a>
<a class="btn btn-deny" href="?action=deny&id=<?php echo $row['id']; ?>">Deny</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<p style="text-align:center; color:#555; margin-top:20px;">No pending leave requests.</p>
<?php endif; ?>
</div>
</body>
</html>