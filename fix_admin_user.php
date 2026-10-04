<?php
require 'db.php';

// Generate a brand new, secure hash for admin123
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);

// Update the database
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$new_hash]);

echo "✅ Admin password reset to: admin123. You can now delete this file and log in!";
?>
