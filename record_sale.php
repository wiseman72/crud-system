<?php
session_start();
require_once("connection.php");

// Get user's branch
$user_branch = $_SESSION['branch'] ?? '';

// Fetch available products for sale (WITH STOCK, ONLY FOR THIS BRANCH)
$products = [];
$stmt = $conn->prepare("SELECT product_id, product_name, unit_price, quantity, branch FROM inventory WHERE quantity > 0 AND branch=? ORDER BY product_name ASC");
$stmt->bind_param("s", $user_branch);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $products[] = $row;

$errors = [];
$success = false;
$receipt = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Customer info
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $sold_by = $_SESSION['username'] ?? "Staff";

    // Product selections
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $sale_items = [];
    $grand_total = 0;

    // Validate customer info
    if (empty($customer_name)) $errors[] = "Customer name is required.";
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";

    // Validate and prepare sale items
    foreach ($product_ids as $idx => $pid) {
        $qty = intval($quantities[$idx]);
        if ($qty < 1) continue;
        // Get product info (ONLY FOR THIS BRANCH)
        $stmt = $conn->prepare("SELECT product_id, product_name, unit_price, quantity, branch FROM inventory WHERE product_id=? AND branch=?");
        $stmt->bind_param("is", $pid, $user_branch);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();
        if (!$prod) {
            $errors[] = "Product not found or not available in your branch.";
            continue;
        }
        if ($qty > $prod['quantity']) {
            $errors[] = "Not enough stock for {$prod['product_name']}.";
            continue;
        }
        $sale_items[] = [
            'product_id' => $prod['product_id'],
            'product_name' => $prod['product_name'],
            'unit_price' => $prod['unit_price'],
            'quantity' => $qty,
            'subtotal' => $prod['unit_price'] * $qty,
            'branch' => $prod['branch']
        ];
        $grand_total += $prod['unit_price'] * $qty;
    }

    if (empty($sale_items)) $errors[] = "No valid products or quantities selected.";

    // Record sale + update inventory
    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            foreach ($sale_items as $item) {
                // Insert sale record (branch always user's branch)
                $stmt = $conn->prepare(
                    "INSERT INTO sales
                     (sale_date, product_id, product_name, quantity_sold, sale_price, total_price, sold_by, customer_name, customer_email, customer_phone, branch)
                     VALUES (NOW(),?,?,?,?,?,?,?,?,?,?)"
                );
                $stmt->bind_param(
                    "isiddsssss",
                    $item['product_id'],
                    $item['product_name'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['subtotal'],
                    $sold_by,
                    $customer_name,
                    $customer_email,
                    $customer_phone,
                    $user_branch
                );
                if (!$stmt->execute()) throw new Exception($stmt->error);

                // Update inventory (only for user's branch)
                $stmt2 = $conn->prepare("UPDATE inventory SET quantity=quantity-? WHERE product_id=? AND branch=?");
                $stmt2->bind_param("iis", $item['quantity'], $item['product_id'], $user_branch);
                if (!$stmt2->execute()) throw new Exception($stmt2->error);
            }
            $conn->commit();
            $success = true;
            $receipt = [
                'date' => date("Y-m-d H:i"),
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'sold_by' => $sold_by,
                'items' => $sale_items,
                'grand_total' => $grand_total
            ];

            // Email receipt
            if (isset($_POST['send_email'])) {
                $subject = "Sales Receipt";
                $body = "Thank you for your purchase!\n\n";
                $body .= "Date: " . $receipt['date'] . "\n";
                $body .= "Customer: " . $receipt['customer_name'] . "\n";
                $body .= "Items:\n";
                foreach ($sale_items as $item) {
                    $body .= "- {$item['product_name']} x{$item['quantity']} @ K₵" . number_format($item['unit_price'],2) . " = K₵" . number_format($item['subtotal'],2) . "\n";
                }
                $body .= "\nTotal: K₵" . number_format($grand_total,2) . "\n";
                $body .= "\nRegards,\nYour Store Team";
                @mail($customer_email, $subject, $body);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Sale could not be recorded. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Record Sale</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family:Arial,sans-serif; background:#f9f9f9; margin:30px; }
        h1 { text-align:center; color:#004aad; }
        form { background:#fff; max-width:700px; margin:auto; padding:20px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08);}
        label { font-weight:bold; margin-top:10px; }
        input, select { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
        .btn { background:#004aad; color:white; padding:10px 18px; border:none; border-radius:4px; cursor:pointer; margin-top:15px; }
        .btn:hover { background:#003580; }
        .add-row { background:#28a745; margin-left:10px; }
        .remove-row { background:#dc3545; }
        .error { color:red; margin-bottom:10px; }
        .success { color:green; margin-bottom:10px; }
        .receipt { background:#fff; margin:30px auto; padding:20px; max-width:500px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08);}
        .receipt-table { width:100%; border-collapse:collapse; margin-top:10px;}
        .receipt-table th, .receipt-table td { border:1px solid #ddd; padding:8px; text-align:left; }
        .receipt-table th { background:#004aad; color:white;}
        .print-btn { background:#007bff; color:white; padding:8px 14px; border-radius:4px; border:none; margin-top:15px;}
        .row-flex { display:flex; gap:10px; }
    </style>
    <script>
    function addRow() {
        var row = document.querySelector('.product-row').cloneNode(true);
        row.querySelectorAll('input').forEach(function(inp){inp.value='';});
        row.querySelector('select').selectedIndex = 0;
        document.getElementById('products-list').appendChild(row);
    }
    function removeRow(el) {
        var rows = document.querySelectorAll('.product-row');
        if (rows.length > 1) el.closest('.product-row').remove();
    }
    </script>
</head>
<body>
<h1>Record Sale</h1>

<?php if ($success): ?>
    <div class="success">Sale recorded successfully!</div>
    <div class="receipt" id="receipt">
        <h2>Receipt</h2>
        <p>Date: <?= htmlspecialchars($receipt['date']) ?></p>
        <p>Customer: <?= htmlspecialchars($receipt['customer_name']) ?> <br>
           Email: <?= htmlspecialchars($receipt['customer_email']) ?> <br>
           Phone: <?= htmlspecialchars($receipt['customer_phone']) ?></p>
        <table class="receipt-table">
            <tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th><th>Branch</th></tr>
            <?php foreach($receipt['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>K₵<?= number_format($item['unit_price'],2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>K₵<?= number_format($item['subtotal'],2) ?></td>
                    <td><?= htmlspecialchars($item['branch']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr><th colspan="4">Total</th><th>K₵<?= number_format($receipt['grand_total'],2) ?></th></tr>
        </table>
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="error"><?= implode("<br>", $errors) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Customer Name:</label>
        <input type="text" name="customer_name" required>
        <label>Customer Email:</label>
        <input type="email" name="customer_email" required>
        <label>Customer Phone:</label>
        <input type="text" name="customer_phone">

        <label>Products Sold:</label>
        <div id="products-list">
            <div class="product-row row-flex">
                <select name="product_id[]" required>
                    <option value="">Select product</option>
                    <?php foreach($products as $prod): ?>
                        <option value="<?= $prod['product_id'] ?>">
                            <?= htmlspecialchars($prod['product_name']) ?> (K₵<?= number_format($prod['unit_price'],2) ?>, Qty: <?= $prod['quantity'] ?>, Branch: <?= htmlspecialchars($prod['branch']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantity[]" min="1" placeholder="Qty" required style="width:80px;">
                <button type="button" class="btn add-row" onclick="addRow()">+</button>
                <button type="button" class="btn remove-row" onclick="removeRow(this)">-</button>
            </div>
        </div>
        <input type="checkbox" name="send_email" value="1"> <label>Send receipt by email</label><br>
        <button type="submit" class="btn">Record Sale</button>
    </form>
<?php endif; ?>
</body>
</html>