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
    if (isset($_POST['update_patient'])) {
        // Update patient logic
        $patient_id = intval($_POST['patient_id']);
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        
        if (empty($full_name) || empty($username) || empty($email)) {
            $error = "Full name, username, and email are required.";
        } else {
            // Check if username or email already exists for another patient
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check_stmt->bind_param("ssi", $username, $email, $patient_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Username or email already exists for another patient.";
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Update patient information
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $full_name, $username, $email, $phone, $patient_id);
                
                if ($stmt->execute()) {
                    $success = "Patient updated successfully.";
                } else {
                    $error = "Failed to update patient. Please try again.";
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['delete_patient'])) {
        // Delete patient logic
        $patient_id = intval($_POST['patient_id']);
        
        // Check if patient has appointments
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
        $check_stmt->bind_param("i", $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $appointment_count = $check_result->fetch_assoc()['count'];
        $check_stmt->close();
        
        if ($appointment_count > 0) {
            $error = "Cannot delete patient with existing appointments. Please cancel appointments first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $patient_id);
            
            if ($stmt->execute()) {
                $success = "Patient deleted successfully.";
            } else {
                $error = "Failed to delete patient. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    // Search by username, full_name, email, or phone
    $stmt = $conn->prepare("SELECT id, username, full_name, email, phone, created_at FROM users 
                           WHERE username LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ? 
                           ORDER BY full_name");
    $searchTerm = "%{$search}%";
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    // Get all patients
    $stmt = $conn->prepare("SELECT id, username, full_name, email, phone, created_at FROM users ORDER BY full_name");
}
$stmt->execute();
$patients_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - Hospital & Clinic Appointment System</title>
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
                    <a href="doctors.php" class="nav-link"><i class="fas fa-user-md me-2"></i> Manage Doctors</a>
                    <a href="patients.php" class="nav-link active"><i class="fas fa-users me-2"></i> Manage Patients</a>
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
                        <div class="navbar-brand">Manage Patients</div>
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
                            <h2>Patients Management</h2>
                            <p>View and manage patients in the system.</p>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search patients by name, username, email, or phone..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="patients.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <?php if (!empty($search)): ?>
                            <div class="col-md-6 d-flex align-items-center">
                                <p class="text-muted mb-0">
                                    Found <?php echo $patients_result->num_rows; ?> patient(s) matching "<?php echo htmlspecialchars($search); ?>"
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h5>All Patients</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($patients_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Username</th>
                                                        <th>Full Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Registered Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($patient = $patients_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($patient['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></td>
                                                            <td><?php echo date("M j, Y", strtotime($patient['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary" 
                                                                        onclick="editPatient(<?php echo $patient['id']; ?>, '<?php echo addslashes($patient['username']); ?>', '<?php echo addslashes($patient['full_name']); ?>', '<?php echo addslashes($patient['email']); ?>', '<?php echo addslashes($patient['phone'] ?? ''); ?>')">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        onclick="deletePatient(<?php echo $patient['id']; ?>, '<?php echo addslashes($patient['full_name']); ?>')">
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
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h4>No patients found</h4>
                                            <p class="text-muted">
                                                <?php if (!empty($search)): ?>
                                                    No patients match your search criteria. Try different keywords or <a href="patients.php">view all patients</a>.
                                                <?php else: ?>
                                                    No patients have registered yet.
                                                <?php endif; ?>
                                            </p>
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

    <!-- View Patient Modal -->
    <div class="modal fade" id="viewPatientModal" tabindex="-1" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPatientModalLabel">Patient Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="patient-details">
                        <!-- Patient details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Patient Modal -->
    <div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPatientModalLabel">Edit Patient</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="update_patient" value="1">
                        <input type="hidden" id="edit_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Patient Confirmation Modal -->
    <div class="modal fade" id="deletePatientModal" tabindex="-1" aria-labelledby="deletePatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deletePatientModalLabel">Delete Patient</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="delete_patient" value="1">
                        <input type="hidden" id="delete_patient_id" name="patient_id">
                        <p>Are you sure you want to delete patient <strong id="delete_patient_name"></strong>? This action cannot be undone.</p>
                        <p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> Note: Patients with existing appointments cannot be deleted.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function viewPatient(patientId) {
            // Show loading indicator
            document.getElementById('patient-details').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading patient details...</p>
                </div>
            `;
            
            var viewModal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
            viewModal.show();
            
            // Fetch patient details via AJAX
            fetch('get_patient_details.php?id=' + patientId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('patient-details').innerHTML = `
                            <div class="alert alert-danger">
                                Error: ${data.error}
                            </div>
                        `;
                        return;
                    }
                    
                    const patient = data.patient;
                    const appointments = data.appointments;
                    
                    // Format patient details
                    let patientDetailsHTML = `
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="mb-4"><i class="fas fa-user me-2"></i>Patient Profile</h4>
                                
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5>Personal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Patient ID:</strong> ${patient.id}</p>
                                                <p><strong>Full Name:</strong> ${patient.full_name}</p>
                                                <p><strong>Username:</strong> ${patient.username}</p>
                                                <p><strong>Email:</strong> ${patient.email}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Phone:</strong> ${patient.phone || 'N/A'}</p>
                                                <p><strong>Gender:</strong> ${patient.gender || 'N/A'}</p>
                                                <p><strong>Date of Birth:</strong> ${patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'N/A'}</p>
                                                <p><strong>Member Since:</strong> ${new Date(patient.created_at).toLocaleDateString()}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    `;
                    
                    // Add appointment history
                    patientDetailsHTML += `
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Appointment History</h5>
                                    </div>
                                    <div class="card-body">
                    `;
                    
                    if (appointments.length > 0) {
                        patientDetailsHTML += `
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Doctor</th>
                                                        <th>Specialization</th>
                                                        <th>Status</th>
                                                        <th>Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                        `;
                        
                        appointments.forEach(appointment => {
                            patientDetailsHTML += `
                                                    <tr>
                                                        <td>${new Date(appointment.appointment_date).toLocaleDateString()}</td>
                                                        <td>${formatTime(appointment.appointment_time)}</td>
                                                        <td>${appointment.doctor_name}</td>
                                                        <td>${appointment.specialization}</td>
                                                        <td>
                                                            <span class="badge ${
                                                                appointment.status === 'Confirmed' ? 'bg-success' :
                                                                appointment.status === 'Pending' ? 'bg-warning' :
                                                                appointment.status === 'Cancelled' ? 'bg-danger' : 'bg-info'
                                                            }">${appointment.status}</span>
                                                        </td>
                                                        <td>${appointment.reason || 'N/A'}</td>
                                                    </tr>
                            `;
                        });
                        
                        patientDetailsHTML += `
                                                </tbody>
                                            </table>
                                        </div>
                        `;
                    } else {
                        patientDetailsHTML += `
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No appointment history found</p>
                                        </div>
                        `;
                    }
                    
                    patientDetailsHTML += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('patient-details').innerHTML = patientDetailsHTML;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('patient-details').innerHTML = `
                        <div class="alert alert-danger">
                            Error loading patient details. Please try again.
                        </div>
                    `;
                });
        }
        
        function editPatient(id, username, fullName, email, phone) {
            document.getElementById('edit_patient_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            var editModal = new bootstrap.Modal(document.getElementById('editPatientModal'));
            editModal.show();
        }
        
        function deletePatient(id, fullName) {
            document.getElementById('delete_patient_id').value = id;
            document.getElementById('delete_patient_name').textContent = fullName;
            var deleteModal = new bootstrap.Modal(document.getElementById('deletePatientModal'));
            deleteModal.show();
        }
        
        // Helper function to format time
        function formatTime(timeString) {
            const timeParts = timeString.split(':');
            const hours = parseInt(timeParts[0]);
            const minutes = timeParts[1];
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = hours % 12 || 12;
            return `${formattedHours}:${minutes} ${ampm}`;
        }
    </script>
</body>
</html>