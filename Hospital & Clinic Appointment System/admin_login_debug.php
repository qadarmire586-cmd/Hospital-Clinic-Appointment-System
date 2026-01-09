<?php
// Admin Login Debug Script
include '../config/db.php';

echo "<h2>Admin Login Debug</h2>";

// Check database connection
if ($conn->connect_error) {
    die("<p style='color:red'>Database connection failed: " . $conn->connect_error . "</p>");
} else {
    echo "<p style='color:green'>Database connection successful</p>";
}

// Check if database exists and is selected
$db_check = $conn->query("SELECT DATABASE() as db_name");
if ($db_check) {
    $db_result = $db_check->fetch_assoc();
    echo "<p>Current database: " . ($db_result['db_name'] ?? 'None') . "</p>";
} else {
    echo "<p style='color:red'>Could not determine current database</p>";
}

// List all databases
echo "<h3>Databases:</h3>";
$dbs = $conn->query("SHOW DATABASES");
if ($dbs) {
    echo "<ul>";
    while ($db = $dbs->fetch_assoc()) {
        echo "<li>" . $db['Database'] . "</li>";
    }
    echo "</ul>";
}

// Check if hospital_clinic_appointment_system database exists
$db_exists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'hospital_clinic_appointment_system'");
if ($db_exists->num_rows > 0) {
    echo "<p style='color:green'>hospital_clinic_appointment_system database exists</p>";
    
    // Select the database
    $conn->select_db('hospital_clinic_appointment_system');
    
    // Check if admins table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admins'");
    if ($table_check->num_rows > 0) {
        echo "<p style='color:green'>admins table exists</p>";
        
        // Count admins
        $admin_count = $conn->query("SELECT COUNT(*) as count FROM admins");
        if ($admin_count) {
            $count_result = $admin_count->fetch_assoc();
            echo "<p>Total admin users: " . $count_result['count'] . "</p>";
            
            if ($count_result['count'] > 0) {
                // Show all admins
                echo "<h3>Admin Users:</h3>";
                $admins = $conn->query("SELECT id, username, password, email, full_name FROM admins");
                if ($admins) {
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Email</th><th>Full Name</th></tr>";
                    while ($admin = $admins->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $admin['id'] . "</td>";
                        echo "<td>" . $admin['username'] . "</td>";
                        echo "<td>" . substr($admin['password'], 0, 20) . "...</td>";
                        echo "<td>" . $admin['email'] . "</td>";
                        echo "<td>" . $admin['full_name'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Test specific login
                    echo "<h3>Login Test:</h3>";
                    $test_username = 'ilyaas';
                    $test_password = 'ilyas8833';
                    
                    echo "<p>Testing login with Username: <strong>$test_username</strong> and Password: <strong>$test_password</strong></p>";
                    
                    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admins WHERE username = ?");
                    if ($stmt) {
                        $stmt->bind_param("s", $test_username);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows == 1) {
                            echo "<p style='color:green'>User found in database</p>";
                            $admin = $result->fetch_assoc();
                            
                            if (password_verify($test_password, $admin['password'])) {
                                echo "<p style='color:green;font-weight:bold'>PASSWORD VERIFICATION SUCCESSFUL!</p>";
                                echo "<p>You should be able to log in now.</p>";
                            } else {
                                echo "<p style='color:red;font-weight:bold'>PASSWORD VERIFICATION FAILED!</p>";
                                echo "<p>Stored hash: " . $admin['password'] . "</p>";
                                echo "<p>Expected password: $test_password</p>";
                            }
                        } else {
                            echo "<p style='color:red'>User '$test_username' not found in database</p>";
                        }
                        $stmt->close();
                    } else {
                        echo "<p style='color:red'>Prepare statement failed: " . $conn->error . "</p>";
                    }
                }
            } else {
                echo "<p style='color:orange'>No admin users found in the database</p>";
            }
        }
    } else {
        echo "<p style='color:red'>admins table does not exist</p>";
        echo "<p>Creating admins table...</p>";
        
        $create_table = $conn->query("
            CREATE TABLE admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        if ($create_table) {
            echo "<p style='color:green'>admins table created successfully</p>";
        } else {
            echo "<p style='color:red'>Failed to create admins table: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color:red'>hospital_clinic_appointment_system database does not exist</p>";
    echo "<p>Creating database...</p>";
    
    $create_db = $conn->query("CREATE DATABASE hospital_clinic_appointment_system");
    if ($create_db) {
        echo "<p style='color:green'>Database created successfully</p>";
        $conn->select_db('hospital_clinic_appointment_system');
    } else {
        echo "<p style='color:red'>Failed to create database: " . $conn->error . "</p>";
    }
}

$conn->close();

echo "<p><a href='login.php'>Try Admin Login Again</a></p>";
?>