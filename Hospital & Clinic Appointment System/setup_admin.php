<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Setup Admin User</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                                echo '<div class="alert alert-warning">Admin user already exists.</div>';
                            } else {
                                // Insert admin user
                                $stmt = $conn->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
                                
                                if ($stmt->execute()) {
                                    echo '<div class="alert alert-success">Admin user created successfully!</div>';
                                    echo '<p><strong>Username:</strong> admin</p>';
                                    echo '<p><strong>Password:</strong> password</p>';
                                    echo '<a href="admin/login.php" class="btn btn-primary">Go to Admin Login</a>';
                                } else {
                                    echo '<div class="alert alert-danger">Error creating admin user: ' . $conn->error . '</div>';
                                }
                                $stmt->close();
                            }
                            
                            $conn->close();
                        } else {
                        ?>
                            <p>Click the button below to create the default admin user:</p>
                            <form method="POST">
                                <button type="submit" class="btn btn-success">Create Admin User</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>