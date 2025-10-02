<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once("connection.php");

/**
 * Function to fetch the logged-in user’s role
 */
function get_user_role($conn) {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $query = "SELECT role FROM users WHERE name = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['role'];
        }
    }
    return null;
}

$user_role = get_user_role($conn);

/**
 * Function to get pending leave requests count
 */
function get_pending_leave_count($conn) {
    $count = 0;
    $query = "SELECT COUNT(*) as total FROM leave_requests WHERE status='pending'";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $count = (int)$row['total'];
    }
    return $count;
}

$pending_count = get_pending_leave_count($conn);
?>

<!-- Sidebar starts -->
<div class="sidebar">
    <h2>Menu</h2>

    <ul class="menu">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <?php if (in_array($user_role, ['Admin','Manager'])): ?>
            <li><a href="create_user.php"><i class="fa fa-user-plus"></i> Create User</a></li>
            <li><a href="approvals.php"><i class="fa fa-bell"></i> Approvals</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Finance'])): ?>
            <li><a href="finance.php"><i class="fa fa-money"></i> Finance</a></li>
            <li><a href="budget_list.php"><i class="fa fa-line-chart"></i> Budgets</a></li>
            <li><a href="payroll.php"><i class="fa fa-credit-card"></i> Payrolls</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Sales'])): ?>
            <li><a href="record_sale.php"><i class="fa fa-plus"></i> Record Sale</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart"></i> Sales</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','HR'])): ?>
            <li><a href="staff.php"><i class="fa fa-users"></i> Staff</a></li>
            <li>
                <a href="leave_requests.php">
                    <i class="fa fa-calendar"></i> Leave Requests
                    <?php if ($pending_count > 0): ?>
                        <span class="badge"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Finance','HR'])): ?>
            <li><a href="inventory.php"><i class="fa fa-cubes"></i> Inventory</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Finance','HR','Sales'])): ?>
            <li>
                <a href="#"><i class="fa fa-envelope"></i> Messages <i class="fa fa-caret-down"></i></a>
                <ul class="dropdown">
                    <li><a href="send_message.php">Send Message</a></li>
                    <li><a href="messages.php">View Messages</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <li><a href="profile.php"><i class="fa fa-user"></i> Profile</a></li>
    </ul>

    <ul class="bottom-menu">
        <li><a href="help.php"><i class="fa fa-question-circle"></i> Help</a></li>
        <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
    </ul>
</div>

<!-- Sidebar CSS -->
<style>
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 250px;
  height: 100%;
  background: #007bff; /* Light blue */
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  overflow-y: auto; /* Makes sidebar scrollable */
  transition: all 0.3s ease;
}

.sidebar h2 {
    text-align: center;
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: #fff;
    animation: slideDown 0.5s ease;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar .menu {
    flex-grow: 1;
}

.sidebar .bottom-menu {
    border-top: 1px solid rgba(255,255,255,0.2);
    padding-top: 10px;
}

.sidebar ul li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 25px;
    color: #fff;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar ul li a:hover {
    background: rgba(255, 255, 255, 0.2);
    padding-left: 30px;
    border-left: 3px solid #38BDF8;
}

.sidebar ul li ul {
    display: none;
    background: rgba(255,255,255,0.05);
    padding-left: 15px;
}

.sidebar ul li:hover > ul {
    display: block;
}

.sidebar ul li a i.fa-caret-down {
    transition: transform 0.3s ease;
}

.sidebar ul li:hover > a i.fa-caret-down {
    transform: rotate(180deg);
}

/* Pending leave badge */
.sidebar .badge {
    background: #dc3545; /* Red */
    color: #fff;
    font-size: 0.8rem;
    padding: 3px 8px;
    border-radius: 12px;
    margin-left: 10px;
    animation: pulseBadge 1.5s infinite;
}

/* Badge pulse animation */
@keyframes pulseBadge {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Animations */
@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .sidebar {
    width: 200px;
  }

  .sidebar ul li a {
    padding: 10px 20px;
    font-size: 0.95rem;
  }
}
</style>
