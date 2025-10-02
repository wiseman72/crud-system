<?php
session_start();
require_once("connection.php");

$username = $_SESSION['username'] ?? '';
$branch = $_SESSION['branch'] ?? '';
$user_role = '';
if ($username && $branch) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE name=? AND branch=?");
    $stmt->bind_param("ss", $username, $branch);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_role = $row['role'];
    }
}

// Handle form submission
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = trim($_POST['leave_type']);
    $number_of_days = (int)$_POST['number_of_days'];
    
    if ($leave_type && $username && $number_of_days > 0 && $branch) {
        // Include branch when inserting
        $stmt = $conn->prepare("INSERT INTO leave_requests (leave_type, requested_by, number_of_days, branch, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ssis", $leave_type, $username, $number_of_days, $branch);
        if ($stmt->execute()) {
            $success = "Leave request submitted successfully!";
        } else {
            $error = "Failed to submit request.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch leave requests
if (in_array($user_role, ['Admin','Manager'])) {
    // Admin/Manager see only requests for their branch
    $query = "SELECT * FROM leave_requests WHERE branch=? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $branch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Other users see only their own requests (in their branch)
    $query = "SELECT * FROM leave_requests WHERE requested_by=? AND branch=? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $branch);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Requests</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body { margin:0; font-family: Arial, sans-serif; background:#f4f6f9; }
.dashboard-container { display:flex; }
.main-content { flex:1; padding:20px; }
h2 { text-align:center; color:#333; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; border:1px solid #ccc; text-align:left; }
th { background:#007bff; color:#fff; }
tr:nth-child(even){background:#f9f9f9;}
tr:hover{background:#eaf2ff; transition:0.3s;}
.btn { padding:5px 10px; text-decoration:none; color:#fff; border-radius:5px; }
.btn-approve { background:#28a745; }
.btn-deny { background:#dc3545; }
.form-container { background:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); max-width:500px; margin:auto; }
label{display:block;margin:10px 0 5px;font-weight:bold;}
input, select{width:100%;padding:10px;margin-bottom:15px;border-radius:6px;border:1px solid #ccc;}
input[type="submit"]{background:#007bff;color:#fff;border:none;cursor:pointer;transition:0.3s;}
input[type="submit"]:hover{background:#0056b3;}
.success{color:green;}
.error{color:red;}
@media(max-width:768px){.main-content{padding:10px;}table,thead,tbody,th,td,tr{display:block;}thead tr{display:none;}tr{margin-bottom:15px;border:1px solid #ddd; border-radius:10px;padding:10px;}td{text-align:right;padding-left:50%;position:relative;}td::before{content:attr(data-label);position:absolute;left:15px;font-weight:bold;color:#007bff;}}
</style>
</head>
<body>
<div class="dashboard-container">
<?php include 'sidebar.php'; ?>
<div class="main-content">
<h2>Leave Requests</h2>

<!-- Show form for non-admin/manager users -->
<?php if(!in_array($user_role, ['Admin','Manager'])): ?>
<div class="form-container">
    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <form method="post">
        <label for="leave_type">Leave Type</label>
        <select name="leave_type" id="leave_type" required>
            <option value="">Select Type</option>
            <option value="annual_leave">Annual Leave</option>
            <option value="sick_leave">Sick Leave</option>
            <option value="maternity_leave">Maternity Leave</option>
            <option value="paternity_leave">Paternity Leave</option>
        </select>
        <label for="number_of_days">Number of Days</label>
        <input type="number" name="number_of_days" id="number_of_days" min="1" required>
        <input type="submit" value="Request Approval">
    </form>
</div>
<?php endif; ?>

<!-- Leave requests table -->
<?php if($result && $result->num_rows>0): ?>
<table>
<thead>
<tr>
<th>Leave Type</th>
<th>Requested By</th>
<th>Number of Days</th>
<th>Status</th>
<?php if(in_array($user_role,['Admin','Manager'])): ?><th>Action</th><?php endif; ?>
</tr>
</thead>
<tbody>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td data-label="Leave Type"><?php echo htmlspecialchars($row['leave_type']); ?></td>
<td data-label="Requested By"><?php echo htmlspecialchars($row['requested_by']); ?></td>
<td data-label="Number of Days"><?php echo htmlspecialchars($row['number_of_days']); ?></td>
<td data-label="Status">
<?php
if($row['status']=='approved') echo "<span style='color:green;font-weight:bold;'>Approved</span>";
elseif($row['status']=='denied') echo "<span style='color:red;font-weight:bold;'>Denied</span>";
else echo "<span style='color:orange;font-weight:bold;'>Pending</span>";
?>
</td>
<?php if(in_array($user_role,['Admin','Manager'])): ?>
<td data-label="Action">
<a class="btn btn-approve" href="approve_leave.php?id=<?php echo $row['id']; ?>">Approve</a>
<a class="btn btn-deny" href="deny_leave.php?id=<?php echo $row['id']; ?>">Deny</a>
</td>
<?php endif; ?>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<p style="text-align:center; margin-top:20px;">No leave requests found.</p>
<?php endif; ?>

</div>
</div>
</body>
</html>

<?php $conn->close(); ?>