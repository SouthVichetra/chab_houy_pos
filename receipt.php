<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if an ID was passed in the URL
if (!isset($_GET['id'])) {
    die("Error: No transaction ID provided.");
}

$txn_id = (int)$_GET['id'];

// 1. Get the main transaction and cashier details
$stmt = $pdo->prepare("
    SELECT t.id, t.total_amount, t.created_at, u.username 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$txn_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die("Error: Transaction not found.");
}

// 2. Get the specific line items for this transaction
$stmt_items = $pdo->prepare("
    SELECT ti.quantity, ti.subtotal, p.name, p.price 
    FROM transaction_items ti
    JOIN products p ON ti.product_id = p.id
    WHERE ti.transaction_id = ?
");
$stmt_items->execute([$txn_id]);
$items = $stmt_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?= $transaction['id'] ?> - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .receipt-container { display: flex; justify-content: center; margin-top: 30px; }
        .receipt-card { background: #fff; padding: 30px; width: 400px; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #2c3e50; }
        .receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #ddd; padding-bottom: 15px; }
        .receipt-header h2 { color: #2c3e50; margin-bottom: 5px; }
        .receipt-info { font-size: 14px; color: #7f8c8d; margin-bottom: 20px; }
        .receipt-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .receipt-table th, .receipt-table td { padding: 8px 0; text-align: left; border-bottom: 1px dashed #eee; font-size: 15px; }
        .receipt-table th { color: #2c3e50; }
        .receipt-table td.text-right, .receipt-table th.text-right { text-align: right; }
        .receipt-total { font-size: 18px; font-weight: bold; text-align: right; color: #1abc9c; border-top: 2px dashed #ddd; padding-top: 15px; }
    </style>
</head>
<body>

    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="history.php"><b>Transaction History</b></a>
        <a href="inventory.php">Inventory</a>
        
    </sidebar>

    <main>
        <header>
            <h1><a href="history.php" style="text-decoration: none; color: #2c3e50;">⬅ Back to History</a></h1>
        </header>

        <div class="receipt-container">
            <div class="receipt-card">
                <div class="receipt-header">
                    <h2>🏪 Chab Houy</h2>
                    <p>Official Receipt</p>
                </div>
                
                <div class="receipt-info">
                    <p><b>Order ID:</b> #<?= $transaction['id'] ?></p>
                    <p><b>Date:</b> <?= date('M d, Y h:i A', strtotime($transaction['created_at'])) ?></p>
                    <p><b>Cashier:</b> <?= htmlspecialchars($transaction['username']) ?></p>
                </div>

                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?> <br><small style="color: #7f8c8d;">@ $<?= number_format($item['price'], 2) ?></small></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="text-right">$<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="receipt-total">
                    Total: $<?= number_format($transaction['total_amount'], 2) ?>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn" onclick="window.print()">🖨️ Print Receipt</button>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
