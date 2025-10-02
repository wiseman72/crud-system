<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once("connection.php");

/**
 * Function to fetch the logged-in user’s role
 */
function get_user_role($conn) {
    if (isset($_SESSION['username']) && isset($_SESSION['branch'])) {
        $username = $_SESSION['username'];
        $branch = $_SESSION['branch'];
        $query = "SELECT role FROM users WHERE name = ? AND branch = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $branch);
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

// Only Admin and Manager see the badge
$pending_count = in_array($user_role, ['Admin','Manager']) ? get_pending_leave_count($conn) : 0;
?>

<div class="sidebar">
    <h2>Menu</h2>

    <ul class="menu">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>

        <?php if (in_array($user_role, ['Admin','Manager'])): ?>
            <li><a href="create_user.php"><i class="fa fa-user-plus"></i> Create User</a></li>
            <li><a href="approvals.php"><i class="fa fa-bell"></i> Approvals</a></li>
            <li><a href="audit_log.php"><i class="fa fa-file-text"></i> Audit Log</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Finance'])): ?>
            <li><a href="finance.php"><i class="fa fa-money"></i> Finance</a></li>
            <li><a href="budget_list.php"><i class="fa fa-line-chart"></i> Budgets</a></li>
            <li><a href="payroll.php"><i class="fa fa-credit-card"></i> Payrolls</a></li>
            <li><a href="orders.php"><i class="fa fa-shopping-bag"></i> Orders</a></li>
            <li><a href="expenses.php"><i class="fa fa-money"></i> Expenses</a></li>
            <li><a href="sales_reports.php"><i class="fa fa-bar-chart"></i> Reports</a></li>

        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','Sales'])): ?>
            <li><a href="record_sale.php"><i class="fa fa-plus"></i> Record Sale</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart"></i> Sales</a></li>
        <?php endif; ?>

        <?php if (in_array($user_role, ['Admin','Manager','HR'])): ?>
            <li><a href="staff.php"><i class="fa fa-users"></i> Staff</a></li>
        <?php endif; ?>

        <!-- Leave Requests link for ALL roles -->
        <li>
            <a href="leave_requests.php">
                <i class="fa fa-calendar"></i> Leave Requests
                <?php if ($pending_count > 0): ?>
                    <span class="badge"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </li>

        <?php if (in_array($user_role, ['Admin','Manager','Finance','HR'])): ?>
            <li><a href="inventory.php"><i class="fa fa-cubes"></i> Inventory</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart"></i> Sales</a></li>
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

<!-- Sidebar Toggle Button (place this inside header.php or top of main-content) -->
<button id="sidebarToggle" class="sidebar-toggle">
  <i class="fa fa-bars"></i>
</button>

<!-- Sidebar CSS -->
<style>
/* ================= Sidebar Base ================= */
.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  width: 250px;
  height: 100%;
  background: #007bff;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  overflow-y: auto;
  transition: all 0.3s ease-in-out;
  border-top-right-radius: 15px;
  border-bottom-right-radius: 15px;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
  z-index: 1000;
}

/* Sidebar Header */
.sidebar h2 {
  text-align: center;
  font-size: 1.8rem;
  margin-bottom: 20px;
  color: #fff;
  animation: slideDown 0.5s ease;
}

/* Lists */
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

/* ================= Links & Hover ================= */
.sidebar ul li a {
  display: flex;
  align-items: center;
  justify-content: space-between;
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

/* ================= Dropdowns ================= */
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

/* ================= Icons ================= */
.sidebar ul li a i {
  display: inline-block !important;
  min-width: 20px;
  text-align: center;
  color: inherit;
}

/* ================= Badges ================= */
.sidebar .badge {
  background: #dc3545;
  color: #fff;
  font-size: 0.8rem;
  padding: 3px 8px;
  border-radius: 12px;
  margin-left: 10px;
  animation: pulseBadge 1.5s infinite;
}

/* ================= Animations ================= */
@keyframes pulseBadge {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

@keyframes slideDown {
  from { transform: translateY(-20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ================= Collapsed / Hidden Sidebar ================= */
/* Desktop fully hidden */
.sidebar.hidden {
  left: -260px;
  opacity: 0;
}

/* Collapsed sidebar for icon-only state */
.sidebar.collapsed {
  width: 60px;
  overflow: hidden;
}

.sidebar.collapsed ul li a {
  justify-content: center;
  padding-left: 0;
}

.sidebar.collapsed ul li a span,
.sidebar.collapsed ul li a .badge {
  display: none;
}

/* Main content adjustments */
.main-content {
  margin-left: 250px;
  padding: 20px;
  transition: margin-left 0.3s ease-in-out;
}

.main-content.fullwidth {
  margin-left: 0;
}

/* ================= Toggle Button ================= */
.sidebar-toggle {
  position: fixed;
  top: 15px;
  left: 15px;
  background: #007bff;
  color: #fff;
  border: none;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 18px;
  cursor: pointer;
  z-index: 2000; /* above sidebar */
  transition: background 0.3s ease;
}

.sidebar-toggle:hover {
  background: #0056b3;
}

/* ================= Mobile ================= */
@media (max-width: 768px) {
  .sidebar {
    width: 200px;
    left: -200px;
  }

  .sidebar.open {
    left: 0;
  }

  .main-content {
    margin-left: 0;
  }

  .sidebar ul li a {
    padding: 10px 20px;
    font-size: 0.95rem;
  }
}



</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    toggleBtn.addEventListener("click", () => {
        if (window.innerWidth > 768) {
            // Desktop: fully hide/show sidebar
            sidebar.classList.toggle("hidden");
            mainContent.classList.toggle("fullwidth");
        } else {
            // Mobile: overlay sidebar
            sidebar.classList.toggle("open");
        }
    });

    // Close mobile sidebar when clicking outside
    mainContent.addEventListener("click", () => {
        if (window.innerWidth <= 768 && sidebar.classList.contains("open")) {
            sidebar.classList.remove("open");
        }
    });
});



</script>

