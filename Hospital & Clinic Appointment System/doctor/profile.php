<?php
include 'auth_check.php';
include '../config/db.php';

// Get doctor information
$doctor_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, username, name, specialization, email, phone, address, qualification, experience_years, bio, created_at FROM doctors WHERE id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

// Handle form submission for updating profile
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $qualification = trim($_POST['qualification']);
    $experience = intval($_POST['experience']);
    $bio = trim($_POST['bio']);
    
    // Validation
    if (empty($name)) {
        $error = "Name is required.";
    } else {
        // Update doctor information
        $stmt = $conn->prepare("UPDATE doctors SET name = ?, email = ?, phone = ?, address = ?, qualification = ?, experience_years = ?, bio = ? WHERE id = ?");
        $stmt->bind_param("sssssiii", $name, $email, $phone, $address, $qualification, $experience, $bio, $doctor_id);
        
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            // Refresh doctor data
            $stmt->close(); // Close the update statement first
            $stmt = $conn->prepare("SELECT id, username, name, specialization, email, phone, address, qualification, experience_years, bio, created_at FROM doctors WHERE id = ?");
            $stmt->bind_param("i", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctor = $result->fetch_assoc();
            $stmt->close(); // Close the select statement
        } else {
            $error = "Failed to update profile. Please try again.";
            $stmt->close(); // Close the update statement in case of error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - Hospital & Clinic Appointment System</title>
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
                    <a href="profile.php" class="nav-link active"><i class="fas fa-user me-2"></i> Profile</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">Doctor Profile</div>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, Dr. <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-user-md fa-2x text-primary"></i>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid p-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>My Profile</h2>
                            <p>Manage your personal and professional information.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Update Profile Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="update_profile" value="1">
                                        
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($doctor['username']); ?>" disabled>
                                            <div class="form-text">Username cannot be changed.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="specialization" class="form-label">Specialization</label>
                                            <input type="text" class="form-control" id="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" disabled>
                                            <div class="form-text">Specialization cannot be changed. Contact admin to update.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($doctor['address'] ?? ''); ?></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="qualification" class="form-label">Qualification</label>
                                            <input type="text" class="form-control" id="qualification" name="qualification" value="<?php echo htmlspecialchars($doctor['qualification'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="experience" class="form-label">Years of Experience</label>
                                            <input type="number" class="form-control" id="experience" name="experience" min="0" value="<?php echo htmlspecialchars($doctor['experience_years'] ?? 0); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio/Description</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                                            <div class="form-text">A brief description about yourself and your expertise.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="created_at" class="form-label">Member Since</label>
                                            <input type="text" class="form-control" id="created_at" value="<?php echo date("F j, Y", strtotime($doctor['created_at'])); ?>" disabled>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Profile Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-user-md fa-5x text-primary mb-3"></i>
                                        <h4><?php echo htmlspecialchars($doctor['name']); ?></h4>
                                        <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold">Contact Information</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> <?php echo htmlspecialchars($doctor['email'] ?? 'N/A'); ?></li>
                                        <li class="mb-2"><i class="fas fa-phone me-2 text-primary"></i> <?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></li>
                                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <?php echo htmlspecialchars($doctor['address'] ?? 'N/A'); ?></li>
                                    </ul>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold">Professional Details</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-graduation-cap me-2 text-primary"></i> <?php echo htmlspecialchars($doctor['qualification'] ?? 'N/A'); ?></li>
                                        <li class="mb-2"><i class="fas fa-briefcase me-2 text-primary"></i> <?php echo htmlspecialchars($doctor['experience_years'] ?? 0); ?> years of experience</li>
                                    </ul>
                                    
                                    <hr>
                                    
                                    <h6 class="fw-bold">About</h6>
                                    <p><?php echo htmlspecialchars($doctor['bio'] ?? 'No bio available.'); ?></p>
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
</body>
</html>