<?php
require_once 'config.php';

$conn = getDB();
if (!$conn) {
    die('Database connection failed. Check config.php');
}

$email = 'demo@example.com';
$name = 'Demo Customer';
$phone = '9876543210';
$password = 'demo123';

// Generate correct hash
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insert or update customer
$stmt = $conn->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name)");
$stmt->bind_param("ssss", $name, $email, $phone, $hash);

if ($stmt->execute()) {
    echo "✅ Customer created/updated successfully!<br>";
    echo "Email: <strong>$email</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "❌ Error: " . $stmt->error;
}
$stmt->close();
?>