<?php
require_once("connection.php");

// ---- SESSION & ACCESS CONTROL ----
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$user_branch = $_SESSION['branch'] ?? '';

// ---- SEARCH & FILTER ----
$search = trim($_GET['search'] ?? '');
$where  = "WHERE branch = ?";
$params = [$user_branch];
$types  = "s";

if ($search !== "") {
    $where .= " AND (action LIKE ? OR changed_by LIKE ? OR ip_address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

// ---- PAGINATION ----
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// ---- COUNT FOR PAGINATION ----
$count_sql = "SELECT COUNT(*) FROM audit_logs $where";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();
$total_pages = ceil($total_rows / $limit);

// ---- MAIN QUERY ----
$sql = "SELECT * FROM audit_logs $where ORDER BY changed_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f7f7f7; font-family: Arial, sans-serif; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.07);}
        h2 { text-align: center; }
        .search-box { text-align: right; margin-bottom: 16px; }
        .search-box input[type="text"] { padding: 6px; font-size: 15px; width: 250px; border: 1px solid #aaa; border-radius: 4px; }
        .search-box input[type="submit"] { padding: 6px 16px; font-size: 15px; border: none; border-radius: 4px; background: #337ab7; color: #fff; cursor: pointer; }
        .search-box input[type="submit"]:hover { background: #22588c; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d1d1; padding: 9px 12px; text-align: left; }
        th { background: #f2f2f2; }
        tr:nth-child(even) { background: #fafbfc; }
        .details { max-width: 320px; word-break: break-all; font-size: 13px; color: #555; }
        .pagination { margin-top: 25px; text-align: right; }
        .pagination a, .pagination span { display: inline-block; padding: 7px 13px; margin: 0 1px; border-radius: 4px; text-decoration: none; background: #eaeaea; color: #333; }
        .pagination .current { background: #337ab7; color: #fff; font-weight: bold; }
        .pagination a:hover { background: #b6d0ee; }
        .ip { font-size: 12px; color: #888; }
        .user-agent { font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
<div class="container">
    <h2>Audit Logs</h2>
    <div class="search-box">
        <form method="get">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search action, user, or IP...">
            <input type="submit" value="Search">
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date & Time</th>
                <th>Action</th>
                <th>User</th>
                <th>Order ID</th>
                <th>IP Address</th>
                <th>Old Data</th>
                <th>New Data</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="9" style="text-align:center;color:#888;">No audit logs found.</td></tr>
        <?php else: $n = $offset + 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $n++ ?></td>
                <td><?= htmlspecialchars($row['changed_at']) ?></td>
                <td><?= htmlspecialchars($row['action']) ?></td>
                <td><?= htmlspecialchars($row['changed_by']) ?></td>
                <td><?= htmlspecialchars($row['order_id']) ?></td>
                <td class="ip"><?= htmlspecialchars($row['ip_address']) ?></td>
                <td class="details"><?= nl2br(htmlspecialchars($row['old_data'])) ?></td>
                <td class="details"><?= nl2br(htmlspecialchars($row['new_data'])) ?></td>
                <td class="user-agent"><?= htmlspecialchars($row['user_agent']) ?></td>
            </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page-1 ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($p = max(1, $page-3); $p <= min($total_pages, $page+3); $p++): ?>
                <?php if ($p == $page): ?>
                    <span class="current"><?= $p ?></span>
                <?php else: ?>
                    <a href="?search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page+1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();