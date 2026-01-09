<?php
// Complete Admin User Fix Script
include 'config/db.php';

echo "<h2>Complete Admin User Fix</h2>";

// Function to test admin login
function testAdminLogin($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            return true; // Login successful
        }
    }
    return false; // Login failed
}

// Admin credentials
$username = 'ilyaas';
$password = 'ilyas8833';
$email = 'ilyaas@hospital.com';
$full_name = 'Ilyaas Administrator';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p>Admin user 'ilyaas' found. Updating credentials...</p>";
    
    // Update admin user
    $update_stmt = $conn->prepare("UPDATE admins SET password = ?, email = ?, full_name = ? WHERE username = ?");
    $update_stmt->bind_param("ssss", $hashed_password, $email, $full_name, $username);
    if ($update_stmt->execute()) {
        echo "<p style='color:green'>Admin user 'ilyaas' updated successfully!</p>";
    } else {
        echo "<p style='color:red'>Error updating admin user: " . $conn->error . "</p>";
    }
    $update_stmt->close();
} else {
    echo "<p>Admin user 'ilyaas' not found. Creating new admin user...</p>";
    
    // Insert admin user
    $insert_stmt = $conn->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
    
    if ($insert_stmt->execute()) {
        echo "<p style='color:green'>Admin user 'ilyaas' created successfully!</p>";
    } else {
        echo "<p style='color:red'>Error creating admin user: " . $conn->error . "</p>";
    }
    $insert_stmt->close();
}

$stmt->close();

// Test the login
echo "<h3>Testing Login Credentials:</h3>";
if (testAdminLogin($conn, $username, $password)) {
    echo "<p style='color:green;font-weight:bold'>SUCCESS! Login test passed. You can now log in with:</p>";
    echo "<p><strong>Username:</strong> " . $username . "<br>";
    echo "<strong>Password:</strong> " . $password . "</p>";
} else {
    echo "<p style='color:red;font-weight:bold'>FAILED! Login test failed. There may be an issue with the database.</p>";
}

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

echo "<p><a href='admin/login.php' style='font-size:18px;font-weight:bold;'>Try Admin Login Now</a></p>";
echo "<p><em>Note: Make sure you're using Username: ilyaas and Password: ilyas8833</em></p>";
?>