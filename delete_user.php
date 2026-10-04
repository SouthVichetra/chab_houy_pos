<?php
session_start();
require_once 'db.php';

// Only Admins can delete users
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access Denied.");
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Prevent the admin from accidentally deleting themselves
    if ($id === (int)$_SESSION['user_id']) {
        die("You cannot delete your own account!");
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: users.php?msg=deleted");
exit;
?>
