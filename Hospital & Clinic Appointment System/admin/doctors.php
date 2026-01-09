<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_doctor'])) {
        // Add doctor logic
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $specialization = trim($_POST['specialization']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $qualification = trim($_POST['qualification']);
        $experience = intval($_POST['experience']);
        
        if (empty($name) || empty($specialization) || empty($username) || empty($password)) {
            $error = "Name, specialization, username, and password are required.";
        } else {
            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT id FROM doctors WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO doctors (username, password, name, specialization, email, phone, qualification, experience_years) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssi", $username, $hashed_password, $name, $specialization, $email, $phone, $qualification, $experience);
                
                if ($stmt->execute()) {
                    $success = "Doctor added successfully.";
                } else {
                    $error = "Failed to add doctor. Please try again.";
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    } elseif (isset($_POST['update_doctor'])) {
        // Update doctor logic
        $doctor_id = intval($_POST['doctor_id']);
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $specialization = trim($_POST['specialization']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $qualification = trim($_POST['qualification']);
        $experience = intval($_POST['experience']);
        
        // Check if password is being updated
        $password = trim($_POST['password']);
        
        if (empty($name) || empty($specialization) || empty($username)) {
            $error = "Name, specialization, and username are required.";
        } else {
            // Check if username already exists for another doctor
            $check_stmt = $conn->prepare("SELECT id FROM doctors WHERE username = ? AND id != ?");
            $check_stmt->bind_param("si", $username, $doctor_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Username already exists. Please choose a different username.";
            } else {
                $check_stmt->close();
                
                // Prepare update statement based on whether password is being changed
                if (!empty($password)) {
                    // Hash the new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE doctors SET username = ?, password = ?, name = ?, specialization = ?, email = ?, phone = ?, qualification = ?, experience_years = ? WHERE id = ?");
                    $stmt->bind_param("sssssssii", $username, $hashed_password, $name, $specialization, $email, $phone, $qualification, $experience, $doctor_id);
                } else {
                    $stmt = $conn->prepare("UPDATE doctors SET username = ?, name = ?, specialization = ?, email = ?, phone = ?, qualification = ?, experience_years = ? WHERE id = ?");
                    $stmt->bind_param("ssssssii", $username, $name, $specialization, $email, $phone, $qualification, $experience, $doctor_id);
                }
                
                if ($stmt->execute()) {
                    $success = "Doctor updated successfully.";
                } else {
                    $error = "Failed to update doctor. Please try again.";
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['delete_doctor'])) {
        // Delete doctor logic
        $doctor_id = intval($_POST['doctor_id']);
        
        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->bind_param("i", $doctor_id);
        
        if ($stmt->execute()) {
            $success = "Doctor deleted successfully.";
        } else {
            $error = "Failed to delete doctor. Please try again.";
        }
        $stmt->close();
    }
}

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    // Search by name or specialization
    $stmt = $conn->prepare("SELECT id, username, name, specialization, email, phone, qualification, experience_years FROM doctors 
                           WHERE name LIKE ? OR specialization LIKE ? 
                           ORDER BY name");
    $searchTerm = "%{$search}%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    // Get all doctors
    $stmt = $conn->prepare("SELECT id, username, name, specialization, email, phone, qualification, experience_years FROM doctors ORDER BY name");
}
$stmt->execute();
$doctors_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Hospital & Clinic Appointment System</title>
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
                    <a href="doctors.php" class="nav-link active"><i class="fas fa-user-md me-2"></i> Manage Doctors</a>
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
                        <div class="navbar-brand">Manage Doctors</div>
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
                            <h2>Doctors Management</h2>
                            <p>Add, edit, or remove doctors from the system.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search doctors by name or specialization..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="doctors.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <?php if (!empty($search)): ?>
                            <div class="col-md-6 d-flex align-items-center">
                                <p class="text-muted mb-0">
                                    Found <?php echo $doctors_result->num_rows; ?> doctor(s) matching "<?php echo htmlspecialchars($search); ?>"
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>All Doctors</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                                        <i class="fas fa-plus me-2"></i>Add Doctor
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?php if ($doctors_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Username</th>
                                                        <th>Name</th>
                                                        <th>Specialization</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Experience</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                                            <td><?php echo htmlspecialchars($doctor['email'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($doctor['experience_years'] ?? 0); ?> years</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary" 
                                                                        onclick="editDoctor(<?php echo $doctor['id']; ?>, '<?php echo addslashes($doctor['username']); ?>', '<?php echo addslashes($doctor['name']); ?>', '<?php echo addslashes($doctor['specialization']); ?>', '<?php echo addslashes($doctor['email'] ?? ''); ?>', '<?php echo addslashes($doctor['phone'] ?? ''); ?>', '<?php echo addslashes($doctor['qualification'] ?? ''); ?>', <?php echo $doctor['experience_years'] ?? 0; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        onclick="deleteDoctor(<?php echo $doctor['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                            <h4>No doctors found</h4>
                                            <p class="text-muted">
                                                <?php if (!empty($search)): ?>
                                                    No doctors match your search criteria. Try different keywords or <a href="doctors.php">view all doctors</a>.
                                                <?php else: ?>
                                                    Add your first doctor to get started.
                                                <?php endif; ?>
                                            </p>
                                            <?php if (empty($search)): ?>
                                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                                                    <i class="fas fa-plus me-2"></i>Add Doctor
                                                </button>
                                            <?php endif; ?>
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

    <!-- Add Doctor Modal -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDoctorModalLabel">Add New Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add_doctor" value="1">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="qualification" class="form-label">Qualification</label>
                            <input type="text" class="form-control" id="qualification" name="qualification">
                        </div>
                        <div class="mb-3">
                            <label for="experience" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="experience" name="experience" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDoctorModalLabel">Edit Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="update_doctor" value="1">
                        <input type="hidden" id="edit_doctor_id" name="doctor_id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="edit_specialization" name="specialization" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="edit_qualification" class="form-label">Qualification</label>
                            <input type="text" class="form-control" id="edit_qualification" name="qualification">
                        </div>
                        <div class="mb-3">
                            <label for="edit_experience" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="edit_experience" name="experience" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Doctor Confirmation Modal -->
    <div class="modal fade" id="deleteDoctorModal" tabindex="-1" aria-labelledby="deleteDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDoctorModalLabel">Delete Doctor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="delete_doctor" value="1">
                        <input type="hidden" id="delete_doctor_id" name="doctor_id">
                        <p>Are you sure you want to delete this doctor? This action cannot be undone and will remove all associated schedules and appointments.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editDoctor(id, username, name, specialization, email, phone, qualification, experience) {
            document.getElementById('edit_doctor_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_specialization').value = specialization;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_qualification').value = qualification;
            document.getElementById('edit_experience').value = experience;
            var editModal = new bootstrap.Modal(document.getElementById('editDoctorModal'));
            editModal.show();
        }
        
        function deleteDoctor(id) {
            document.getElementById('delete_doctor_id').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteDoctorModal'));
            deleteModal.show();
        }
        
        // Prevent non-numeric input in phone fields
        document.addEventListener('DOMContentLoaded', function() {
            // Add doctor modal phone field
            var addPhoneField = document.getElementById('phone');
            if (addPhoneField) {
                addPhoneField.addEventListener('keypress', function(e) {
                    // Allow only digits (0-9)
                    if (e.which < 48 || e.which > 57) {
                        e.preventDefault();
                    }
                });
                
                addPhoneField.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        var phoneValue = e.target.value;
                        // Remove any non-digit characters
                        var cleanedValue = phoneValue.replace(/\D/g, '');
                        e.target.value = cleanedValue;
                    }, 10);
                });
            }
            
            // Edit doctor modal phone field
            var editPhoneField = document.getElementById('edit_phone');
            if (editPhoneField) {
                editPhoneField.addEventListener('keypress', function(e) {
                    // Allow only digits (0-9)
                    if (e.which < 48 || e.which > 57) {
                        e.preventDefault();
                    }
                });
                
                editPhoneField.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        var phoneValue = e.target.value;
                        // Remove any non-digit characters
                        var cleanedValue = phoneValue.replace(/\D/g, '');
                        e.target.value = cleanedValue;
                    }, 10);
                });
            }
        });
    </script>
</body>
</html>