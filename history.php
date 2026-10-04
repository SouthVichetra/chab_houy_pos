<?php
session_start();
require_once 'db.php';

// Protect the page - must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all transactions, newest first, and join with users to get the cashier's name
$stmt = $pdo->query("
    SELECT t.id, t.total_amount, t.created_at, u.username 
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="history.php"><b>Transaction History</b></a>
        <a href="inventory.php">Inventory</a>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            <a href="users.php">Manage Staff</a>
        <?php endif; ?>
    </sidebar>

    <main>
        <header>
            <h1>Transaction History</h1>
        </header>

        <div class="card">
            <h3>All Past Sales</h3>
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Cashier</th>
                        <th>Total Amount</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td>#<?= $txn['id'] ?></td>
                                <td><?= htmlspecialchars($txn['username'] ?? 'Unknown') ?></td>
                                <td style="color: #1abc9c; font-weight: bold;">$<?= number_format($txn['total_amount'], 2) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($txn['created_at'])) ?></td>
                                <td>
                                    <!-- We can build a receipt view later! -->
                                    <a href="receipt.php?id=<?= $txn['id'] ?>" class="btn" style="padding: 5px 10px; font-size: 12px; text-decoration: none; color: white;">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #7f8c8d; padding: 20px;">No transactions recorded yet. Go sell some snacks!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
