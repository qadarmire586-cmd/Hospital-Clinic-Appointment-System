<?php
// Script to fix or create admin user
include 'config/db.php';

// Admin credentials
$username = 'admin';
$password = 'password';
$email = 'admin@hospital.com';
$full_name = 'System Administrator';

// Hash the password (this is the correct hash for 'password')
$hashed_password = '$2y$10$4w9TdQ5kmH6G5wOCPB5WzOaXTANUBfvKBKH/Rp/.C5lZh3Ea5fjuO';

echo "<h2>Admin User Fix Script</h2>";

// Check if admin already exists
$stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p>Admin user found:<br>";
    echo "Username: " . $admin['username'] . "<br>";
    
    // Verify password hash
    if (password_verify($password, $admin['password'])) {
        echo "Password verification: <span style='color:green'>SUCCESS</span><br>";
    } else {
        echo "Password verification: <span style='color:red'>FAILED</span><br>";
        echo "Fixing password...<br>";
        
        // Update password
        $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $hashed_password, $username);
        if ($update_stmt->execute()) {
            echo "Password updated successfully!<br>";
        } else {
            echo "Error updating password: " . $conn->error . "<br>";
        }
        $update_stmt->close();
    }
    echo "</p>";
} else {
    echo "<p>Admin user not found. Creating new admin user...<br>";
    
    // Insert admin user
    $insert_stmt = $conn->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
    
    if ($insert_stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: password<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
    $insert_stmt->close();
}

$stmt->close();

// Show all admins
echo "<h3>Current Admin Users:</h3>";
$all_stmt = $conn->prepare("SELECT id, username, email, full_name FROM admins");
$all_stmt->execute();
$all_result = $all_stmt->get_result();

if ($all_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th></tr>";
    while ($row = $all_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['full_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin users found in the database.</p>";
}

$all_stmt->close();
$conn->close();

echo "<p><a href='admin/login.php'>Try Admin Login Now</a></p>";
?>