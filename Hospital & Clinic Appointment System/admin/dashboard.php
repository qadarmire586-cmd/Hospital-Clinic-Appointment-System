<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

// Get admin information
$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Get statistics
$stats = [
    'total_patients' => 0,
    'total_doctors' => 0,
    'total_appointments' => 0,
    'pending_appointments' => 0
];

// Total patients
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_patients'] = $result->fetch_assoc()['count'];
$stmt->close();

// Total doctors
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM doctors");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_doctors'] = $result->fetch_assoc()['count'];
$stmt->close();

// Total appointments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_appointments'] = $result->fetch_assoc()['count'];
$stmt->close();

// Pending appointments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'");
$stmt->execute();
$result = $stmt->get_result();
$stats['pending_appointments'] = $result->fetch_assoc()['count'];
$stmt->close();

// Recent appointments
$stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, u.full_name as patient_name, d.name as doctor_name 
                       FROM appointments a 
                       JOIN users u ON a.patient_id = u.id 
                       JOIN doctors d ON a.doctor_id = d.id 
                       ORDER BY a.created_at DESC 
                       LIMIT 5");
$stmt->execute();
$recent_appointments = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hospital & Clinic Appointment System</title>
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
        .card-stats {
            border-left: 4px solid #2575fc;
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
                    <a href="admins.php" class="nav-link"><i class="fas fa-user-shield me-2"></i> Manage Admins</a>
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
                        <div class="navbar-brand">Admin Dashboard</div>
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
                            <h2>Dashboard Overview</h2>
                            <p>Welcome back! Here's what's happening in your hospital management system.</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>Patients</h5>
                                            <h2><?php echo $stats['total_patients']; ?></h2>
                                        </div>
                                        <div class="icon text-primary">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>Doctors</h5>
                                            <h2><?php echo $stats['total_doctors']; ?></h2>
                                        </div>
                                        <div class="icon text-success">
                                            <i class="fas fa-user-md fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>Total Appointments</h5>
                                            <h2><?php echo $stats['total_appointments']; ?></h2>
                                        </div>
                                        <div class="icon text-info">
                                            <i class="fas fa-calendar fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>Pending Approval</h5>
                                            <h2><?php echo $stats['pending_appointments']; ?></h2>
                                        </div>
                                        <div class="icon text-warning">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Recent Appointments</h5>
                                    <a href="appointments.php" class="btn btn-primary btn-sm">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if ($recent_appointments->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Patient</th>
                                                        <th>Doctor</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($appointment = $recent_appointments->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
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
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">No appointments found.</p>
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