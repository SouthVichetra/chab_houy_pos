<?php
session_start();
require_once 'db.php';

// Must be logged in (and ideally an Admin, but we'll keep it simple for now)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

// Send them back to the inventory page
header("Location: inventory.php?msg=deleted");
exit;
?>
