<?php
// Script to create admin user
include 'config/db.php';

// Admin credentials
$username = 'admin';
$password = 'password';
$email = 'admin@hospital.com';
$full_name = 'System Administrator';

// Hash the password
$hashed_password = '$2y$10$4w9TdQ5kmH6G5wOCPB5WzOaXTANUBfvKBKH/Rp/.C5lZh3Ea5fjuO'; // Hash for 'password'

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Admin user already exists.";
} else {
    // Insert admin user
    $stmt = $conn->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!";
        echo "<br>Username: admin";
        echo "<br>Password: password";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>