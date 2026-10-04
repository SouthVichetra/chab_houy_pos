<?php
session_start();
require_once 'db.php';

// BRUTAL SECURITY CHECK: Must be logged in AND must be an Admin (role_id 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access Denied. You are not an Admin.");
}

$message = '';

// Handle creating a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];

    if (!empty($username) && !empty($password)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $role_id]);
            $message = "User added successfully!";
        } catch (PDOException $e) {
            $message = "Error: Username might already exist.";
        }
    }
}

// Fetch all users and their roles
$stmt = $pdo->query("
    SELECT u.id, u.username, u.created_at, r.role_name 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.id DESC
");
$staff = $stmt->fetchAll();

// Fetch roles for the dropdown
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff - Chab Houy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .layout { display: flex; gap: 20px; }
        .form-panel { flex: 1; background: #fff; padding: 20px; border-radius: 8px; height: fit-content;}
        .table-panel { flex: 2; background: #fff; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .alert { background: #e8f8f5; color: #1abc9c; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>

    <sidebar>
        <h2>🏪 Chab Houy POS</h2>
        <a href="index.php">Dashboard</a>
        <a href="pos.php">POS Checkout</a>
        <a href="inventory.php">Inventory</a>
        <a href="history.php">Transaction History</a>
        <a href="users.php"><b>Manage Staff</b></a>
    </sidebar>

    <main>
        <header>
            <h1>Staff Management</h1>
        </header>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="layout">
            <!-- Add New Staff Form -->
            <div class="form-panel">
                <h3>Add New User</h3>
                <form action="users.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Create User</button>
                </form>
            </div>

            <!-- List of Current Staff -->
            <div class="table-panel">
                <h3>Current Staff</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['role_name']) ?></td>
                                <td>
                                    <!-- Edit Button (Everyone gets an edit button) -->
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn" style="background: #3498db; padding: 5px 10px; text-decoration: none; font-size: 12px; margin-right: 5px;">Edit</a>

                                    <!-- Delete Button (Still can't delete yourself) -->
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn" style="background: #e74c3c; padding: 5px 10px; text-decoration: none; font-size: 12px;" onclick="return confirm('Are you sure you want to fire this user?');">Delete</a>
                                    <?php else: ?>
                                        <span style="color: #7f8c8d; font-size: 12px;">(You)</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
