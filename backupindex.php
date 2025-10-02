<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        /* Reset defaults */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f7f9;
            color: #333;
            line-height: 1.6;
        }

        /* Stats cards container */
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        /* Individual stat card */
        .stat {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            flex: 1 1 200px;
            min-width: 180px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .stat h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #555;
        }

        .stat h3 i {
            margin-right: 8px;
            color: #007bff;
        }

        .stat p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #222;
        }

        /* Dashboard sections container */
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        /* Individual dashboard section */
        .dashboard-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dashboard-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .dashboard-section h4 {
            margin-bottom: 12px;
            color: #333;
            font-size: 1.1rem;
        }

        .dashboard-section h4 i {
            margin-right: 6px;
            color: #007bff;
        }

        .dashboard-section ul {
            list-style: none;
            padding-left: 0;
        }

        .dashboard-section ul li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        .dashboard-section ul li:last-child {
            border-bottom: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
                align-items: center;
            }

            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php 
include 'sidebar.php'; 
include 'header.php';

// Define the variables
$total_sales = mysqli_query($conn, "SELECT SUM(total_value) AS total_sales FROM sales")->fetch_assoc()['total_sales'];
$total_payrolls = mysqli_query($conn, "SELECT SUM(gross_pay) AS total_payrolls FROM payrolls")->fetch_assoc()['total_payrolls'];
$total_inventory = mysqli_query($conn, "SELECT COUNT(*) AS total_inventory FROM products")->fetch_assoc()['total_inventory'];
$total_staff = mysqli_query($conn, "SELECT COUNT(*) AS total_staff FROM staff")->fetch_assoc()['total_staff'];

$user_role = get_user_role($conn); 
?> 

<div class="stats"> 
    <?php if ($user_role == 'Admin' || $user_role == 'Manager') { ?> 
        <div class="stat"> 
            <h3><i class="fa fa-shopping-cart"></i> Total Sales</h3> 
            <p><?php echo $total_sales; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-money"></i> Total Payrolls</h3> 
            <p><?php echo $total_payrolls; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-cubes"></i> Total Inventory</h3> 
            <p><?php echo $total_inventory; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-users"></i> Total Staff</h3> 
            <p><?php echo $total_staff; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-dollar"></i> Total Revenue</h3> 
            <p>$<?= $total_revenue ?? '0.00'; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-shopping-cart"></i> Total Orders</h3> 
            <p><?php echo isset($total_orders) ? $total_orders : '0'; ?></p> 
        </div> 
    <?php } elseif ($user_role == 'Finance') { ?> 
        <div class="stat"> 
            <h3><i class="fa fa-money"></i> Total Payrolls</h3> 
            <p><?php echo $total_payrolls; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-dollar"></i> Total Revenue</h3> 
            <p>$<?= $total_revenue ?? '0.00'; ?></p> 
        </div> 
    <?php } elseif ($user_role == 'Sales') { ?> 
        <div class="stat"> 
            <h3><i class="fa fa-shopping-cart"></i> Total Sales</h3> 
            <p><?php echo $total_sales; ?></p> 
        </div> 
        <div class="stat"> 
            <h3><i class="fa fa-shopping-cart"></i> Total Orders</h3> 
            <p><?php echo isset($total_orders) ? $total_orders : '0'; ?></p> 
        </div> 
    <?php } ?> 
</div> 

<div class="dashboard-sections"> 
    <div class="dashboard-section announcements"> 
        <h4><i class="fa fa-bullhorn"></i> Announcements</h4> 
        <ul> 
            <li>Welcome to the new dashboard!</li> 
            <li>Remember to check your messages regularly.</li> 
        </ul> 
    </div> 
    <div class="dashboard-section recent-activity"> 
        <h4><i class="fa fa-clock-o"></i> Recent Activity</h4> 
        <ul> 
            <li>No recent activity to show.</li> 
        </ul> 
    </div> 
    <div class="dashboard-section recent-sales"> 
        <h4><i class="fa fa-clock-o"></i> Recent Sales</h4> 
        <ul> 
            <li>Sale 1: $100</li> 
            <li>Sale 2: $200</li> 
            <li>Sale 3: $300</li> 
        </ul> 
    </div> 
    <div class="dashboard-section top-selling-products">
        <h4><i class="fa fa-star"></i> Top Selling Products</h4>
        <ul>
            <li>Product A - 150 units</li>
            <li>Product B - 120 units</li>
            <li>Product C - 90 units</li>
        </ul>
    </div>
</div>
</body>
</html>
