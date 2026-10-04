<?php
session_start();
require_once 'db.php';

// BRUTAL SECURITY CHECK: Must be logged in AND an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access Denied. You are not an Admin.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("User ID required.");
}

// Fetch current user details
$stmt = $pdo->prepare("SELECT id, username, role_id FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Fetch roles for the dropdown
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role_id = (int)$_POST['role_id'];
    $new_password = $_POST['new_password'];

    if (!empty($new_password)) {
        // If a new password was typed, hash it and update everything
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role_id = ? WHERE id = ?");
        $updateStmt->execute([$username, $hashed_password, $role_id, $id]);
    } else {
        // If password field is empty, just update username and role
        $updateStmt = $pdo->prepare("UPDATE users SET username = ?, role_id = ? WHERE id = ?");
        $updateStmt->execute([$username, $role_id, $id]);
    }
    
    header("Location: users.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 8px; max-width: 400px; margin: 20px auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .help-text { font-size: 12px; color: #7f8c8d; margin-top: 5px; display: block; }
    </style>
</head>
<body>
    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="history.php">Transaction History</a>
        <a href="inventory.php">Inventory</a>
        <a href="users.php"><b>Manage Staff</b></a>
    </sidebar>

    <main>
        <header>
            <h1><a href="users.php" style="text-decoration: none; color: #2c3e50;">⬅ Back to Staff Management</a></h1>
        </header>

        <div class="form-container">
            <h2>Edit Staff: <?= htmlspecialchars($user['username']) ?></h2>
            <form action="edit_user.php?id=<?= $user['id'] ?>" method="POST" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reset Password (Optional)</label>
                    <input type="password" name="new_password" placeholder="Enter new password">
                    <span class="help-text">Leave blank to keep the current password.</span>
                </div>
                <button type="submit" class="btn" style="width: 100%; background: #3498db;">Update User</button>
            </form>
        </div>
    </main>
</body>
</html>
