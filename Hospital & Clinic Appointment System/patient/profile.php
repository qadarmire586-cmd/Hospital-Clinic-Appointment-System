<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'patient' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, full_name, email, phone, address, date_of_birth, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = "Full name and email are required.";
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email is already taken by another user.";
        } else {
            // Update user information
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?, gender = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $full_name, $email, $phone, $address, $date_of_birth, $gender, $user_id);
            
            if ($stmt->execute()) {
                $success = "Profile updated successfully.";
                // Update session name if changed
                $_SESSION['full_name'] = $full_name;
                
                // Refresh user data
                $stmt->close(); // Close the UPDATE statement first
                $stmt = $conn->prepare("SELECT username, full_name, email, phone, address, date_of_birth, gender FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = "Failed to update profile. Please try again.";
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Hospital & Clinic Appointment System</title>
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
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
                    <a href="book_appointment.php" class="nav-link"><i class="fas fa-calendar-plus me-2"></i> Book Appointment</a>
                    <a href="appointments.php" class="nav-link"><i class="fas fa-calendar-check me-2"></i> My Appointments</a>
                    <a href="profile.php" class="nav-link active"><i class="fas fa-user-edit me-2"></i> Profile</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">My Profile</div>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, Patient <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid p-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Profile Information</h2>
                            <p>Manage your personal information and account settings.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <img src="https://via.placeholder.com/150" alt="Profile Image" class="profile-img mb-3">
                                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                    <p class="text-muted">Patient</p>
                                    <p class="text-muted">
                                        <i class="fas fa-envelope me-2"></i>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                    <p class="text-muted">
                                        <i class="fas fa-user me-2"></i>
                                        Member since <?php echo date("F Y"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Edit Profile</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                            <div class="form-text">Username cannot be changed.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="gender" class="form-label">Gender</label>
                                                <select class="form-select" id="gender" name="gender">
                                                    <option value="">Select Gender</option>
                                                    <option value="Male" <?php echo ($user['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option value="Female" <?php echo ($user['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                    <option value="Other" <?php echo ($user['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </form>
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
        // Prevent non-numeric input in phone field
        document.getElementById('phone').addEventListener('keypress', function(e) {
            // Allow only digits (0-9)
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        });
        
        // Also prevent paste of non-numeric content
        document.getElementById('phone').addEventListener('paste', function(e) {
            setTimeout(function() {
                var phoneValue = e.target.value;
                // Remove any non-digit characters
                var cleanedValue = phoneValue.replace(/\D/g, '');
                e.target.value = cleanedValue;
            }, 10);
        });
    </script>
</body>
</html>