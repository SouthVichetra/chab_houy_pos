<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// Make sure a cashier is actually logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $total_amount = 0;
    foreach ($data['cart'] as $item) {
        $total_amount += ($item['price'] * $item['qty']);
    }

    // 1. Save the transaction using the actual logged-in user's ID
    $cashier_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$cashier_id, $total_amount]);
    $transaction_id = $pdo->lastInsertId();

    // 2. Save items and deduct stock
    $stmt_item = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

    foreach ($data['cart'] as $item) {
        $subtotal = $item['price'] * $item['qty'];
        $stmt_item->execute([$transaction_id, $item['id'], $item['qty'], $subtotal]);
        $stmt_stock->execute([$item['qty'], $item['id']]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Sale completed! Cashier recorded.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
