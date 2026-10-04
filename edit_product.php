<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Product ID required.");
}

// Fetch current product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

// Handle form submission to save changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];

    $updateStmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock_quantity = ? WHERE id = ?");
    $updateStmt->execute([$name, $category, $price, $stock, $id]);
    
    header("Location: inventory.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; margin: 20px auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="inventory.php"><b>Inventory</b></a>
        <a href="history.php">Transaction History</a>
    </sidebar>

    <main>
        <header>
            <h1><a href="inventory.php" style="text-decoration: none; color: #2c3e50;">⬅ Back to Inventory</a></h1>
        </header>

        <div class="form-container">
            <h2>Edit: <?= htmlspecialchars($product['name']) ?></h2>
            <form action="edit_product.php?id=<?= $product['id'] ?>" method="POST" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" required>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Update Product</button>
            </form>
        </div>
    </main>
</body>
</html>
