<?php
    session_start();

    // If they are not logged in, kick them back to the login page
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>


<?php
// Include database connection
require_once 'db.php';

$message = '';

// Handle form submission to add a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock_quantity'] ?? 0;

    if (!empty($name) && !empty($category) && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock_quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $stock]);
        $message = "Product added successfully!";
    } else {
        $message = "Please fill in all fields correctly.";
    }
}

// Fetch all products from MySQL
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .layout { display: flex; gap: 20px; }
        .add-product-form { flex: 1; background: #fff; padding: 20px; border-radius: 8px; }
        .stock-table { flex: 2; background: #fff; padding: 20px; border-radius: 8px; }
        .alert { background: #e8f8f5; color: #1abc9c; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>

    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="history.php">Transaction History</a>
        <a href="inventory.php"><b>Inventory</b></a>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            <a href="users.php">Manage Staff</a>
        <?php endif; ?>
    </sidebar>

    <main>
        <header>
            <h1>Inventory & Stock Management</h1>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="layout">
            <!-- Add Product Form -->
            <div class="add-product-form">
                <h3>Add New Product</h3>
                <form action="inventory.php" method="POST" style="margin-top: 15px;">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" placeholder="e.g. Tiger Beer" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" placeholder="e.g. Beverages" required>
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" name="price" step="0.01" placeholder="1.25" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" placeholder="50" required>
                    </div>
                    <button type="submit" class="btn">Save Product</button>
                </form>
            </div>

            <!-- Stock Table View (Dynamic from MySQL) -->
            <div class="stock-table">
                <h3>Current Stock</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                                    <td>
                                        
                                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn" style="background: #3498db; padding: 5px 10px; text-decoration: none;">Edit</a>
                                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn" style="background: #e74c3c; padding: 5px 10px; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #7f8c8d;">No products found in inventory.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
