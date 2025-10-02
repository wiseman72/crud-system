<?php
include 'sidebar.php';
include 'header.php';
require_once("connection.php");

$user_role = get_user_role($conn);

// Updated table names and columns to match your workflow
// Total Sales (from sales table)
$total_sales = mysqli_query($conn, "SELECT SUM(total_price) AS total_sales FROM sales")->fetch_assoc()['total_sales'] ?? 0;
// Total Orders (count of rows in sales table)
$total_orders = mysqli_query($conn, "SELECT COUNT(*) AS total_orders FROM sales")->fetch_assoc()['total_orders'] ?? 0;
// Total Payrolls (if payrolls table exists)
$total_payrolls = mysqli_query($conn, "SELECT SUM(gross_pay) AS total_payrolls FROM payrolls")->fetch_assoc()['total_payrolls'] ?? 0;
// Total Staff
$total_staff = mysqli_query($conn, "SELECT COUNT(*) AS total_staff FROM staff")->fetch_assoc()['total_staff'] ?? 0;
// Total Inventory (count of products in inventory)
$total_inventory = mysqli_query($conn, "SELECT COUNT(*) AS total_inventory FROM inventory")->fetch_assoc()['total_inventory'] ?? 0;
// Total Revenue (from sales)
$total_revenue = $total_sales;

// Fetch recent sales
$recent_sales_res = mysqli_query($conn, "SELECT product_name, quantity_sold, total_price, sale_date FROM sales ORDER BY sale_date DESC LIMIT 5");
$recent_sales = [];
while ($row = mysqli_fetch_assoc($recent_sales_res)) {
    $recent_sales[] = $row;
}

// Top selling products (by quantity_sold)
$top_products_res = mysqli_query($conn, "SELECT product_name, SUM(quantity_sold) as total_qty FROM sales GROUP BY product_name ORDER BY total_qty DESC LIMIT 5");
$top_products = [];
while ($row = mysqli_fetch_assoc($top_products_res)) {
    $top_products[] = $row;
}

// Recent activity placeholder (update with real activity as needed)
$recent_activity = [
    "Logged in",
    "Updated profile",
    "Created a sale"
];
?>

<!--<div style="margin:30px 0 20px 0;">
    <nav style="display:flex;gap:18px;flex-wrap:wrap;">
        <a href="inventory.php" class="btn-nav"><i class="fa fa-cubes"></i> Inventory</a>
        <a href="record_sale.php" class="btn-nav"><i class="fa fa-shopping-cart"></i> Record Sale</a>
        <a href="sales.php" class="btn-nav"><i class="fa fa-list"></i> Sales</a>
        <a href="finance.php" class="btn-nav"><i class="fa fa-money"></i> Finance</a>
        <a href="expenses.php" class="btn-nav"><i class="fa fa-credit-card"></i> Expenses</a>
         Add more navigation links as needed // add comments
    </nav>
</div> -->

<div class="stats">
    <?php if (in_array($user_role, ['Admin', 'Manager'])): ?>
        <div class="stat">
            <h3><i class="fa fa-shopping-cart"></i> Total Sales</h3>
            <p><?= number_format($total_sales,2) ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-money"></i> Total Payrolls</h3>
            <p><?= number_format($total_payrolls,2) ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-cubes"></i> Total Inventory</h3>
            <p><?= $total_inventory ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-users"></i> Total Staff</h3>
            <p><?= $total_staff ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-dollar"></i> Total Revenue</h3>
            <p><?= number_format($total_revenue,2) ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-shopping-cart"></i> Total Orders</h3>
            <p><?= $total_orders ?></p>
        </div>
    <?php elseif ($user_role == 'Finance'): ?>
        <div class="stat">
            <h3><i class="fa fa-money"></i> Total Payrolls</h3>
            <p><?= number_format($total_payrolls,2) ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-dollar"></i> Total Revenue</h3>
            <p><?= number_format($total_revenue,2) ?></p>
        </div>
    <?php elseif ($user_role == 'Sales'): ?>
        <div class="stat">
            <h3><i class="fa fa-shopping-cart"></i> Total Sales</h3>
            <p><?= number_format($total_sales,2) ?></p>
        </div>
        <div class="stat">
            <h3><i class="fa fa-shopping-cart"></i> Total Orders</h3>
            <p><?= $total_orders ?></p>
        </div>
    <?php endif; ?>
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
            <?php foreach ($recent_activity as $activity): ?>
                <li><?= htmlspecialchars($activity) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="dashboard-section recent-sales">
        <h4><i class="fa fa-clock-o"></i> Recent Sales</h4>
        <ul>
            <?php foreach ($recent_sales as $sale): ?>
                <li>
                    <?= htmlspecialchars($sale['product_name']) ?> - Qty: <?= $sale['quantity_sold'] ?> - K<?= number_format($sale['total_price'],2) ?> (<?= htmlspecialchars($sale['sale_date']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="dashboard-section top-selling-products">
        <h4><i class="fa fa-star"></i> Top Selling Products</h4>
        <ul>
            <?php foreach ($top_products as $prod): ?>
                <li><?= htmlspecialchars($prod['product_name']) ?> - <?= $prod['total_qty'] ?> units</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<style>
.btn-nav {
    background: #007bff;
    color: #fff;
    padding: 10px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.07);
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-nav:hover { background: #0056b3; }

.stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 20px 0;
}
.stat {
    flex: 1 1 200px;
    background: #f0f8ff;
    border-left: 5px solid #007bff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.stat h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #007bff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.stat p {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}
.stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}
.dashboard-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.dashboard-section {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.dashboard-section h4 {
    margin-bottom: 15px;
    font-size: 1.1rem;
    color: #007bff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dashboard-section ul {
    list-style: disc inside;
    padding-left: 0;
    margin: 0;
}
.dashboard-section ul li {
    padding: 6px 0;
    font-size: 0.95rem;
    color: #333;
}
.dashboard-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.08);
}
@media (max-width: 768px) {
    .stats {
        flex-direction: column;
    }
    .dashboard-sections {
        grid-template-columns: 1fr;
    }
}
</style>