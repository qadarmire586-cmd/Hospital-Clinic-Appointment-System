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

$error = '';
$success = '';

// Handle appointment cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $appointment_id = intval($_GET['cancel']);
    
    // Check if the appointment belongs to the current user and is cancellable
    $stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update appointment status to cancelled
        $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        if ($stmt->execute()) {
            $success = "Appointment cancelled successfully.";
        } else {
            $error = "Failed to cancel appointment. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Invalid appointment or appointment cannot be cancelled.";
    }
    $stmt->close();
}

// Handle search
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    // Search by doctor name or specialization
    $stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason, a.created_at,
                           d.name as doctor_name, d.specialization
                           FROM appointments a
                           JOIN doctors d ON a.doctor_id = d.id
                           WHERE a.patient_id = ? AND (d.name LIKE ? OR d.specialization LIKE ?)
                           ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $searchTerm = "%{$search}%";
    $stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm);
} else {
    // Get all appointments for the patient
    $stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason, a.created_at,
                           d.name as doctor_name, d.specialization
                           FROM appointments a
                           JOIN doctors d ON a.doctor_id = d.id
                           WHERE a.patient_id = ?
                           ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$appointments_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Hospital & Clinic Appointment System</title>
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
        .status-pending {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }
        .status-confirmed {
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .status-cancelled {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .status-completed {
            background-color: #d4edda;
            border-color: #c3e6cb;
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
                    <a href="appointments.php" class="nav-link active"><i class="fas fa-calendar-check me-2"></i> My Appointments</a>
                    <a href="profile.php" class="nav-link"><i class="fas fa-user-edit me-2"></i> Profile</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">My Appointments</div>
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
                            <h2>My Appointments</h2>
                            <p>View and manage your appointment history.</p>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search appointments by doctor or specialization..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="appointments.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <?php if (!empty($search)): ?>
                            <div class="col-md-6 d-flex align-items-center">
                                <p class="text-muted mb-0">
                                    Found <?php echo $appointments_result->num_rows; ?> appointment(s) matching "<?php echo htmlspecialchars($search); ?>"
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <?php if ($appointments_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Doctor</th>
                                                        <th>Specialization</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                        <th>Reason</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                                        <tr class="<?php 
                                                            switch ($appointment['status']) {
                                                                case 'Pending': echo 'status-pending'; break;
                                                                case 'Confirmed': echo 'status-confirmed'; break;
                                                                case 'Cancelled': echo 'status-cancelled'; break;
                                                                case 'Completed': echo 'status-completed'; break;
                                                            }
                                                        ?>">
                                                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                                                            <td><?php echo date("M j, Y", strtotime($appointment['appointment_date'])); ?></td>
                                                            <td><?php echo date("g:i A", strtotime($appointment['appointment_time'])); ?></td>
                                                            <td>
                                                                <?php
                                                                switch ($appointment['status']) {
                                                                    case 'Pending':
                                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                                        break;
                                                                    case 'Confirmed':
                                                                        echo '<span class="badge bg-success">Confirmed</span>';
                                                                        break;
                                                                    case 'Cancelled':
                                                                        echo '<span class="badge bg-danger">Cancelled</span>';
                                                                        break;
                                                                    case 'Completed':
                                                                        echo '<span class="badge bg-info">Completed</span>';
                                                                        break;
                                                                }
                                                                ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($appointment['reason'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <?php if ($appointment['status'] == 'Pending'): ?>
                                                                    <a href="?cancel=<?php echo $appointment['id']; ?>" 
                                                                       class="btn btn-sm btn-outline-danger" 
                                                                       onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                                        Cancel
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No actions</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <h4>No appointments found</h4>
                                            <p class="text-muted">
                                                <?php if (!empty($search)): ?>
                                                    No appointments match your search criteria. Try different keywords or <a href="appointments.php">view all appointments</a>.
                                                <?php else: ?>
                                                    You haven't booked any appointments yet.
                                                <?php endif; ?>
                                            </p>
                                            <?php if (empty($search)): ?>
                                                <a href="book_appointment.php" class="btn btn-primary">Book Appointment</a>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>