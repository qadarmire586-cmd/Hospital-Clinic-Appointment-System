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

// Handle form submission for adding/updating schedules
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_schedule'])) {
        // Add new schedule
        $doctor_id = intval($_POST['doctor_id']);
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $slot_duration = intval($_POST['slot_duration']);
        $max_patients = intval($_POST['max_patients']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        if (empty($doctor_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            $error = "All fields are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, max_patients, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiii", $doctor_id, $day_of_week, $start_time, $end_time, $slot_duration, $max_patients, $is_available);
            
            if ($stmt->execute()) {
                $success = "Schedule added successfully.";
            } else {
                $error = "Failed to add schedule. Please try again.";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['update_schedule'])) {
        // Update schedule
        $schedule_id = intval($_POST['schedule_id']);
        $doctor_id = intval($_POST['doctor_id']);
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $slot_duration = intval($_POST['slot_duration']);
        $max_patients = intval($_POST['max_patients']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        if (empty($schedule_id) || empty($doctor_id) || empty($day_of_week) || empty($start_time) || empty($end_time)) {
            $error = "All fields are required.";
        } else {
            $stmt = $conn->prepare("UPDATE doctor_schedules SET doctor_id = ?, day_of_week = ?, start_time = ?, end_time = ?, slot_duration = ?, max_patients = ?, is_available = ? WHERE id = ?");
            $stmt->bind_param("isssiiii", $doctor_id, $day_of_week, $start_time, $end_time, $slot_duration, $max_patients, $is_available, $schedule_id);
            
            if ($stmt->execute()) {
                $success = "Schedule updated successfully.";
            } else {
                $error = "Failed to update schedule. Please try again.";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_schedule'])) {
        // Delete schedule
        $schedule_id = intval($_POST['schedule_id']);
        
        $stmt = $conn->prepare("DELETE FROM doctor_schedules WHERE id = ?");
        $stmt->bind_param("i", $schedule_id);
        
        if ($stmt->execute()) {
            $success = "Schedule deleted successfully.";
        } else {
            $error = "Failed to delete schedule. Please try again.";
        }
        $stmt->close();
    }
}

// Get all doctors
$stmt = $conn->prepare("SELECT id, name, specialization FROM doctors ORDER BY name");
$stmt->execute();
$doctors_result = $stmt->get_result();
$stmt->close();

// Get all schedules with doctor information
$stmt = $conn->prepare("SELECT ds.*, d.name as doctor_name, d.specialization 
                       FROM doctor_schedules ds 
                       JOIN doctors d ON ds.doctor_id = d.id 
                       ORDER BY d.name, ds.day_of_week, ds.start_time");
$stmt->execute();
$schedules_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedules - Hospital & Clinic Appointment System</title>
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
                    <a href="patients.php" class="nav-link"><i class="fas fa-users me-2"></i> Manage Patients</a>
                    <a href="appointments.php" class="nav-link"><i class="fas fa-calendar-check me-2"></i> Appointments</a>
                    <a href="schedules.php" class="nav-link active"><i class="fas fa-clock me-2"></i> Doctor Schedules</a>
                    <a href="logout.php" class="nav-link mt-auto"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 p-0">
                <!-- Header -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <div class="navbar-brand">Doctor Schedules</div>
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
                            <h2>Doctor Schedules Management</h2>
                            <p>Add, edit, or remove doctor schedules.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>All Doctor Schedules</h5>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                        <i class="fas fa-plus me-2"></i>Add Schedule
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?php if ($schedules_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Doctor</th>
                                                        <th>Specialization</th>
                                                        <th>Day</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Duration (min)</th>
                                                        <th>Max Patients</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($schedule = $schedules_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td class="text-nowrap"><?php echo htmlspecialchars($schedule['doctor_name']); ?></td>
                                                            <td class="text-nowrap"><?php echo htmlspecialchars($schedule['specialization']); ?></td>
                                                            <td class="text-nowrap"><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                                            <td class="text-nowrap"><?php echo date("g:i A", strtotime($schedule['start_time'])); ?></td>
                                                            <td class="text-nowrap"><?php echo date("g:i A", strtotime($schedule['end_time'])); ?></td>
                                                            <td><?php echo htmlspecialchars($schedule['slot_duration']); ?></td>
                                                            <td><?php echo htmlspecialchars($schedule['max_patients']); ?></td>
                                                            <td>
                                                                <?php if ($schedule['is_available']): ?>
                                                                    <span class="badge bg-success">Available</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Unavailable</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary" 
                                                                        onclick="editSchedule(<?php echo $schedule['id']; ?>, <?php echo $schedule['doctor_id']; ?>, '<?php echo $schedule['day_of_week']; ?>', '<?php echo $schedule['start_time']; ?>', '<?php echo $schedule['end_time']; ?>', <?php echo $schedule['slot_duration']; ?>, <?php echo $schedule['max_patients']; ?>, <?php echo $schedule['is_available']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        onclick="deleteSchedule(<?php echo $schedule['id']; ?>)">
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
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h4>No schedules found</h4>
                                            <p class="text-muted">Add your first schedule to get started.</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                                                <i class="fas fa-plus me-2"></i>Add Schedule
                                            </button>
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

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addScheduleModalLabel">Add New Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add_schedule" value="1">
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Doctor</label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php 
                                $doctors_result->data_seek(0); // Reset pointer
                                while ($doctor = $doctors_result->fetch_assoc()): ?>
                                    <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="day_of_week" class="form-label">Day of Week</label>
                            <select class="form-select" id="day_of_week" name="day_of_week" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="slot_duration" class="form-label">Slot Duration (minutes)</label>
                            <input type="number" class="form-control" id="slot_duration" name="slot_duration" value="30" min="10" max="120">
                        </div>
                        <div class="mb-3">
                            <label for="max_patients" class="form-label">Maximum Patients</label>
                            <input type="number" class="form-control" id="max_patients" name="max_patients" value="10" min="1" max="50">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_available" name="is_available" checked>
                            <label class="form-check-label" for="is_available">Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="update_schedule" value="1">
                        <input type="hidden" id="edit_schedule_id" name="schedule_id">
                        <div class="mb-3">
                            <label for="edit_doctor_id" class="form-label">Doctor</label>
                            <select class="form-select" id="edit_doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php 
                                $doctors_result->data_seek(0); // Reset pointer
                                while ($doctor = $doctors_result->fetch_assoc()): ?>
                                    <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_day_of_week" class="form-label">Day of Week</label>
                            <select class="form-select" id="edit_day_of_week" name="day_of_week" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_slot_duration" class="form-label">Slot Duration (minutes)</label>
                            <input type="number" class="form-control" id="edit_slot_duration" name="slot_duration" min="10" max="120">
                        </div>
                        <div class="mb-3">
                            <label for="edit_max_patients" class="form-label">Maximum Patients</label>
                            <input type="number" class="form-control" id="edit_max_patients" name="max_patients" min="1" max="50">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_available" name="is_available">
                            <label class="form-check-label" for="edit_is_available">Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Schedule Confirmation Modal -->
    <div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-labelledby="deleteScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteScheduleModalLabel">Delete Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="delete_schedule" value="1">
                        <input type="hidden" id="delete_schedule_id" name="schedule_id">
                        <p>Are you sure you want to delete this schedule? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editSchedule(id, doctorId, dayOfWeek, startTime, endTime, slotDuration, maxPatients, isAvailable) {
            document.getElementById('edit_schedule_id').value = id;
            document.getElementById('edit_doctor_id').value = doctorId;
            document.getElementById('edit_day_of_week').value = dayOfWeek;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_slot_duration').value = slotDuration;
            document.getElementById('edit_max_patients').value = maxPatients;
            document.getElementById('edit_is_available').checked = isAvailable == 1;
            var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            editModal.show();
        }
        
        function deleteSchedule(id) {
            document.getElementById('delete_schedule_id').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteScheduleModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>