<?php
session_start();
require_once 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Get Total Products
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $stmt->fetch()['total'];

// 2. Get Today's Sales
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT SUM(total_amount) as today_sales FROM transactions WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$todaySales = $stmt->fetch()['today_sales'] ?? 0.00; // Default to 0 if null

// 3. Get Low Stock Items (Threshold: less than 10)
$stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity < 10");
$lowStock = $stmt->fetch()['low_stock'];

// 4. Get 5 Most Recent Transactions
$stmt = $pdo->query("
    SELECT t.id, t.total_amount, t.created_at, u.username 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$recentTxns = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .stats-grid { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { flex: 1; background: #fff; padding: 20px; border-radius: 8px; border-left: 5px solid #1abc9c; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 14px; color: #7f8c8d; margin-bottom: 8px; text-transform: uppercase; }
        .stat-card p { font-size: 28px; font-weight: bold; color: #2c3e50; }
        .alert-text { color: #e74c3c; }
    </style>
</head>
    <body>

        <sidebar>
            <h2>🏪 Chab Houy</h2>
            <!-- Added "active" class to highlight the current page -->
            <a href="index.php" class="active">📊 Dashboard</a>
            <a href="pos.php">🛒 POS Checkout</a>
            <a href="history.php">🧾 History</a>
            <a href="inventory.php">📦 Inventory</a>
            
            
            <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                <a href="users.php">👥 Staff</a>
            <?php endif; ?>
            
            <div style="margin-top: 50px; border-top: 1px solid #334155; padding-top: 20px;">
                <p style="color: #94a3b8; font-size: 13px; margin-bottom: 10px;">User: <span style="color:#fff; font-weight:bold;"><?= htmlspecialchars($_SESSION['username']) ?></span></p>
                <a href="logout.php" style="color: #ef4444; padding-left: 0;">🚪 Logout</a>
            </div>
        </sidebar>

        <main>
            <header>
                <h1>Overview</h1>
                <p style="color: #64748b; font-weight: 500;"><?= date('l, F j, Y') ?></p>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📦 Total Products</h3>
                    <p><?= $totalProducts ?></p>
                </div>
                <div class="stat-card sales">
                    <h3>💰 Today's Sales</h3>
                    <p>$<?= number_format($todaySales, 2) ?></p>
                </div>
                <div class="stat-card alerts">
                    <h3>⚠️️ Low Stock</h3>
                    <p class="<?= $lowStock > 0 ? 'alert-text' : '' ?>"><?= $lowStock ?></p>
                </div>
            </div>

            <div class="card">
                <h3>Recent Transactions</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cashier</th>
                            <th>Total</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentTxns) > 0): ?>
                            <?php foreach ($recentTxns as $txn): ?>
                                <tr>
                                    <td><span style="color: #64748b;">#<?= $txn['id'] ?></span></td>
                                    <td><?= htmlspecialchars($txn['username']) ?></td>
                                    <td style="font-weight: 600; color: #10b981;">$<?= number_format($txn['total_amount'], 2) ?></td>
                                    <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 13px;"><?= date('h:i A', strtotime($txn['created_at'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">No sales yet today. Time to open the doors!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

    </body>

</html>
