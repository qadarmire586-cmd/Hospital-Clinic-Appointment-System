<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' || !isset($_SESSION['user_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

include '../config/db.php';

$error = '';
$success = '';

// Handle form submission for adding new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
            $stmt->close();
        } else {
            $stmt->close();
            
            // Hash password and insert admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, email, full_name, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Admin added successfully!";
            } else {
                $error = "Failed to add admin. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Handle admin deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $admin_id = intval($_GET['delete']);
    
    // Prevent deletion of current logged-in admin
    if ($admin_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            $success = "Admin deleted successfully!";
        } else {
            $error = "Failed to delete admin. Please try again.";
        }
        $stmt->close();
    }
}

// Get all admins
$stmt = $conn->prepare("SELECT id, username, email, full_name, created_at FROM admins ORDER BY created_at DESC");
$stmt->execute();
$admins_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Hospital & Clinic Appointment System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
            color: white;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h2 class="text-white"><i class="fas fa-hospital"></i> HMS</h2>
                    <p class="text-white-50">Hospital Management System</p>
                </div>
                <div class="d-flex flex-column">
                    <a href="dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i> Dashboard</a>
                    <a href="admins.php" class="nav-link active"><i class="fas fa-user-shield me-2"></i> Manage Admins</a>
                    <a href="doctors.php" class="nav-link"><i class="fas fa-user-md me-2"></i> Manage Doctors</a>
                    <a href="patients.php" class="nav-link"><i class="fas fa-users me-2"></i> Manage Patients</a>
                    <a href="appointments.php" class="nav-link"><i class="fas fa-calendar-check me-2"></i> Appointments</a>
                    <a href="schedules.php" class="nav-link"><i class="fas fa-clock me-2"></i> Doctor Schedules</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">Manage Admins</div>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, Admin <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-user-shield fa-2x text-primary"></i>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid p-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Manage Admins</h2>
                            <p>Add new admins or manage existing admin accounts.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Add Admin Form -->
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Add New Admin</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                                <input type="text" class="form-control" id="username" name="username" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Password must be at least 6 characters long.</div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Admins List -->
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Admins List</h5>
                                    <span class="badge bg-primary"><?php echo $admins_result->num_rows; ?> admins</span>
                                </div>
                                <div class="card-body">
                                    <?php if ($admins_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Full Name</th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Created</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($admin = $admins_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($admin['full_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                                            <td><?php echo date("M j, Y", strtotime($admin['created_at'])); ?></td>
                                                            <td>
                                                                <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                                                    <a href="?delete=<?php echo $admin['id']; ?>" 
                                                                       class="btn btn-sm btn-outline-danger" 
                                                                       onclick="return confirm('Are you sure you want to delete this admin?')">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">You</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                                            <h4>No admins found</h4>
                                            <p class="text-muted">No additional admins have been created yet.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            var passwordField = document.getElementById('password');
            var icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Toggle confirm password visibility
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            var confirmPasswordField = document.getElementById('confirm_password');
            var icon = this.querySelector('i');
            
            if (confirmPasswordField.type === 'password') {
                confirmPasswordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>