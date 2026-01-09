<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Custom Admin User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Create Custom Admin User</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            include 'config/db.php';
                            
                            // Custom admin credentials
                            $username = 'ilyas';
                            $password = 'ilyas3388';
                            $email = 'ilyas@hospital.com';
                            $full_name = 'Ilyas Administrator';
                            
                            // Hash the password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Check if admin already exists
                            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                echo '<div class="alert alert-warning">Admin user with username "ilyas" already exists.</div>';
                            } else {
                                // Insert admin user
                                $stmt = $conn->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
                                
                                if ($stmt->execute()) {
                                    echo '<div class="alert alert-success">Admin user created successfully!</div>';
                                    echo '<p><strong>Username:</strong> ' . $username . '</p>';
                                    echo '<p><strong>Password:</strong> ' . $password . '</p>';
                                    echo '<a href="admin/login.php" class="btn btn-primary">Go to Admin Login</a>';
                                } else {
                                    echo '<div class="alert alert-danger">Error creating admin user: ' . $conn->error . '</div>';
                                }
                                $stmt->close();
                            }
                            
                            $conn->close();
                        } else {
                        ?>
                            <p>Click the button below to create the custom admin user:</p>
                            <p><strong>Username:</strong> ilyas</p>
                            <p><strong>Password:</strong> ilyas3388</p>
                            <form method="POST">
                                <button type="submit" class="btn btn-success">Create Admin User (ilyas)</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>