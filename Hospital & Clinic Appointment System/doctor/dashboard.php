<?php
include 'auth_check.php';
include '../config/db.php';

// Get doctor's appointments with patient details
$doctor_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason, a.created_at,
                       u.full_name as patient_name, u.email as patient_email
                       FROM appointments a
                       JOIN users u ON a.patient_id = u.id
                       WHERE a.doctor_id = ?
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$stmt->close();

// Count total appointments
$total_appointments = $appointments_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - Hospital & Clinic Appointment System</title>
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
                    <a href="dashboard.php" class="nav-link active"><i class="fas fa-home me-2"></i> Dashboard</a>
                    <a href="profile.php" class="nav-link"><i class="fas fa-user me-2"></i> Profile</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">Doctor Dashboard</div>
                        <div class="d-flex align-items-center">
                            <span class="me-3">Welcome, Doctor <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                            <i class="fas fa-user-md fa-2x text-primary"></i>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid p-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>My Appointments</h2>
                            <p>View details of all appointments booked with you.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>All My Appointments</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($total_appointments > 0): ?>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p><strong>Total Appointments:</strong> <?php echo $total_appointments; ?></p>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Patient Name</th>
                                                        <th>Illness/Reason</th>
                                                        <th>Appointment Date</th>
                                                        <th>Appointment Time</th>
                                                        <th>Booking Time</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($appointment['reason'] ?? 'N/A'); ?></td>
                                                            <td><?php echo date("M j, Y", strtotime($appointment['appointment_date'])); ?></td>
                                                            <td><?php echo date("g:i A", strtotime($appointment['appointment_time'])); ?></td>
                                                            <td><?php echo date("M j, Y g:i A", strtotime($appointment['created_at'])); ?></td>
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
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                                            <h4>No appointments found</h4>
                                            <p class="text-muted">You don't have any appointments yet.</p>
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