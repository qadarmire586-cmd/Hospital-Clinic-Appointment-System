<?php
// Script to create admin user with custom credentials
include 'config/db.php';

// Admin credentials
$username = 'ilyaas';
$password = 'ilyas8833';
$email = 'ilyaas@hospital.com';
$full_name = 'Ilyaas Administrator';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Creating Custom Admin User</h2>";

// Check if this admin already exists
$stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color:orange'>Admin user 'ilyaas' already exists. Updating password...</p>";
    
    // Update password
    $update_stmt = $conn->prepare("UPDATE admins SET password = ?, email = ?, full_name = ? WHERE username = ?");
    $update_stmt->bind_param("ssss", $hashed_password, $email, $full_name, $username);
    if ($update_stmt->execute()) {
        echo "<p style='color:green'>Admin user 'ilyaas' updated successfully!</p>";
    } else {
        echo "<p style='color:red'>Error updating admin user: " . $conn->error . "</p>";
    }
    $update_stmt->close();
} else {
    echo "<p>Creating new admin user 'ilyaas'...<br>";
    
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

echo "<p><strong>Your Admin Credentials:</strong><br>";
echo "Username: ilyaas<br>";
echo "Password: ilyas8833</p>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>