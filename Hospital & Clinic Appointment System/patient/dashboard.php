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
$stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get upcoming appointments
$stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.name as doctor_name, d.specialization 
                       FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() 
                       ORDER BY a.appointment_date ASC, a.appointment_time ASC 
                       LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Hospital & Clinic Appointment System</title>
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
                    <a href="book_appointment.php" class="nav-link"><i class="fas fa-calendar-plus me-2"></i> Book Appointment</a>
                    <a href="appointments.php" class="nav-link"><i class="fas fa-calendar-check me-2"></i> My Appointments</a>
                    <a href="profile.php" class="nav-link"><i class="fas fa-user-edit me-2"></i> Profile</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">Patient Dashboard</div>
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
                            <h2>Dashboard Overview</h2>
                            <p>Welcome back! Here's what's happening with your appointments today.</p>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5>Total Appointments</h5>
                                            <h2>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
                                                $stmt->bind_param("i", $user_id);
                                                $stmt->execute();
                                                $count_result = $stmt->get_result();
                                                $count = $count_result->fetch_assoc()['count'];
                                                echo $count;
                                                $stmt->close();
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon text-primary">
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
                                            <h5>Upcoming</h5>
                                            <h2>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'Confirmed' AND appointment_date >= CURDATE()");
                                                $stmt->bind_param("i", $user_id);
                                                $stmt->execute();
                                                $count_result = $stmt->get_result();
                                                $count = $count_result->fetch_assoc()['count'];
                                                echo $count;
                                                $stmt->close();
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon text-success">
                                            <i class="fas fa-clock fa-2x"></i>
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
                                            <h5>Completed</h5>
                                            <h2>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'Completed'");
                                                $stmt->bind_param("i", $user_id);
                                                $stmt->execute();
                                                $count_result = $stmt->get_result();
                                                $count = $count_result->fetch_assoc()['count'];
                                                echo $count;
                                                $stmt->close();
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon text-info">
                                            <i class="fas fa-check-circle fa-2x"></i>
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
                                            <h5>Cancelled</h5>
                                            <h2>
                                                <?php
                                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'Cancelled'");
                                                $stmt->bind_param("i", $user_id);
                                                $stmt->execute();
                                                $count_result = $stmt->get_result();
                                                $count = $count_result->fetch_assoc()['count'];
                                                echo $count;
                                                $stmt->close();
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon text-danger">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Appointments -->
                    <div class="row">
                        <div class="col">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Upcoming Appointments</h5>
                                    <a href="appointments.php" class="btn btn-primary btn-sm">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if ($appointments_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Doctor</th>
                                                        <th>Specialization</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                                        <tr>
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
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">No upcoming appointments. <a href="book_appointment.php">Book one now</a>.</p>
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