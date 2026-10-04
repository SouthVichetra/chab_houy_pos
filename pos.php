<?php
    session_start();

    // If they are not logged in, kick them back to the login page
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>


<?php
require_once 'db.php';

// Fetch products from database for the POS grid
$stmt =$pdo->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY name ASC");
$products =$stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Checkout - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .pos-container { display: flex; gap: 20px; }
        .product-grid { flex: 2; display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; align-content: start; }
        .product-card { background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #e1e1e1; cursor: pointer; text-align: center; transition: 0.2s; }
        .product-card:hover { border-color: #1abc9c; background: #f9f9f9; }
        .product-card h3 { font-size: 16px; margin-bottom: 5px; color: #2c3e50; }
        .product-card p { color: #1abc9c; font-weight: bold; }
        .stock-badge { font-size: 12px; color: #7f8c8d; margin-top: 5px; }
        .cart-panel { flex: 1; background: #fff; padding: 20px; border-radius: 6px; border: 1px solid #e1e1e1; height: fit-content; }
    </style>
</head>
<body>

    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php"><b>POS Checkout</b></a>
        <a href="history.php">Transaction History</a>
        <a href="inventory.php">Inventory</a>
        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
            <a href="users.php">Manage Staff</a>
        <?php endif; ?>
    </sidebar>

    <main>
        <header>
            <h1>Point of Sale Register</h1>
        </header>

        <div class="pos-container">
            <!-- Available Products Grid -->
            <div class="product-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as$product): ?>
                        <div class="product-card" onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, <?=$product['stock_quantity'] ?>)">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p>$<?= number_format($product['price'], 2) ?></p>
                            <div class="stock-badge">Stock: <?= $product['stock_quantity'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #7f8c8d;">No products available in stock.</p>
                <?php endif; ?>
            </div>

            <!-- Cart Sidebar Panel -->
            <div class="cart-panel">
                <h3>Current Sale</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <!-- Javascript will dynamically add items here -->
                        <tr>
                            <td colspan="3" style="text-align: center; color: #7f8c8d;">Cart is empty</td>
                        </tr>
                    </tbody>
                </table>
                <hr style="margin: 15px 0;">
                <h3>Total: $<span id="cart-total">0.00</span></h3>
                <button class="btn" style="width: 100%; margin-top: 15px;" onclick="checkout()">Complete Sale</button>
            </div>
        </div>
    </main>

    <script>
        let cart = [];

        function addToCart(id, name, price, maxStock) {
            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                if (existingItem.qty < maxStock) {
                    existingItem.qty++;
                } else {
                    alert('Not enough stock available!');
                    return;
                }
            } else {
                cart.push({ id: id, name: name, price: price, qty: 1, maxStock: maxStock });
            }
            updateCartDisplay();
        }

        function updateCartDisplay() {
            let cartTableBody = document.getElementById('cart-items');
            let totalSpan = document.getElementById('cart-total');
            cartTableBody.innerHTML = '';

            if (cart.length === 0) {
                cartTableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #7f8c8d;">Cart is empty</td></tr>';
                totalSpan.textContent = '0.00';
                return;
            }

            let total = 0;
            cart.forEach(item => {
                let subtotal = item.price * item.qty;
                total += subtotal;
                cartTableBody.innerHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.qty}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                    </tr>
                `;
            });
            totalSpan.textContent = total.toFixed(2);
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }
            
            // Send the cart data to our PHP script securely
            fetch('process_sale.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message); // Tell the cashier it worked
                    cart = [];           // Empty the cart
                    updateCartDisplay(); // Clear the screen
                    location.reload();   // Refresh page to load new stock quantities from MySQL
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to connect to the server.');
            });
        }

    </script>

</body>
</html>
