<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// Get user information
$user_id = $_SESSION['user_id'];

// Configure the number of divisions (occurrences) - between 7 and 14
$number_of_divisions = 10; // Default value
// You can adjust this value between 7 and 14
if ($number_of_divisions < 7) $number_of_divisions = 7;
if ($number_of_divisions > 14) $number_of_divisions = 14;

$error = '';
$success = '';

// Get all doctors
$stmt = $conn->prepare("SELECT id, name, specialization FROM doctors ORDER BY name");
$stmt->execute();
$doctors_result = $stmt->get_result();
$stmt->close();

$selected_doctor = null;
$available_dates = [];

// Check if a doctor has been selected
if (isset($_GET['doctor']) && !empty($_GET['doctor'])) {
    $selected_doctor = intval($_GET['doctor']);
} elseif (isset($_POST['doctor_id']) && !empty($_POST['doctor_id'])) {
    $selected_doctor = intval($_POST['doctor_id']);
}

if ($selected_doctor) {
    // Get available dates for the selected doctor
    $stmt = $conn->prepare("SELECT DISTINCT ds.day_of_week, ds.start_time, ds.end_time, ds.slot_duration
                           FROM doctor_schedules ds
                           WHERE ds.doctor_id = ? AND ds.is_available = 1
                           ORDER BY FIELD(ds.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $stmt->bind_param("i", $selected_doctor);
    $stmt->execute();
    $schedules_result = $stmt->get_result();
    
    // Generate available dates based on schedules
    while ($schedule = $schedules_result->fetch_assoc()) {
        $day_of_week = $schedule['day_of_week'];
        // Calculate next occurrence of this day
        $current_day = date('l');
        $days_until = (array_search($day_of_week, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']) - 
                      array_search($current_day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']) + 7) % 7;
        
        if ($days_until == 0 && date('H:i:s') > $schedule['end_time']) {
            $days_until = 7; // If today but time has passed, get next week
        }
        
        // Generate occurrences based on the configurable number of divisions
        for ($i = 0; $i < $number_of_divisions; $i++) {
            $date = date('Y-m-d', strtotime("+$days_until days", strtotime(date('Y-m-d'))) + ($i * 7 * 24 * 60 * 60));
            $available_dates[] = [
                'date' => $date,
                'day' => date('l, F j, Y', strtotime($date)),
                'schedule' => $schedule
            ];
        }
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_date'])) {
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    // Validate inputs
    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error = "Please select a doctor, date, and time.";
    } else {
        // Check if the selected time slot is available
        $stmt = $conn->prepare("SELECT ds.id as schedule_id
                               FROM doctor_schedules ds
                               LEFT JOIN appointments a ON ds.id = a.schedule_id 
                               AND a.appointment_date = ? AND a.appointment_time = ?
                               WHERE ds.doctor_id = ? AND ds.is_available = 1
                               AND a.id IS NULL");
        $stmt->bind_param("ssi", $appointment_date, $appointment_time, $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $schedule = $result->fetch_assoc();
            $schedule_id = $schedule['schedule_id'];
            $stmt->close(); // Close the SELECT statement first
            
            // Book the appointment
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, schedule_id, appointment_date, appointment_time, reason) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiiss", $user_id, $doctor_id, $schedule_id, $appointment_date, $appointment_time, $reason);
            
            try {
                if ($stmt->execute()) {
                    $success = "Appointment booked successfully! It is pending approval by the admin.";
                }
            } catch (mysqli_sql_exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'unique_appointment') !== false) {
                    $error = "Sorry, this appointment slot is already taken. Please select a different date or time.";
                } else {
                    $error = "Failed to book appointment. Please try again.";
                }
            }
            $stmt->close();
        } else {
            $stmt->close(); // Close the SELECT statement
            $error = "Selected time slot is not available. Please choose another time.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Hospital & Clinic Appointment System</title>
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
                    <a href="book_appointment.php" class="nav-link active"><i class="fas fa-calendar-plus me-2"></i> Book Appointment</a>
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
                        <div class="navbar-brand">Book Appointment</div>
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
                            <h2>Book New Appointment</h2>
                            <p>Select a doctor and available time slot to book your appointment.</p>
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
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="doctor_id" class="form-label">Select Doctor</label>
                                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                                <option value="">Choose a doctor</option>
                                                <?php 
                                                // Reset the result pointer to the beginning
                                                $doctors_result->data_seek(0);
                                                while ($doctor = $doctors_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $doctor['id']; ?>" <?php echo ($selected_doctor == $doctor['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <?php if ($selected_doctor && !empty($available_dates)): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Available Dates</label>
                                                <div class="row">
                                                    <?php foreach ($available_dates as $date_info): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h6><?php echo $date_info['day']; ?></h6>
                                                                    <p class="text-muted small">
                                                                        <?php echo date('g:i A', strtotime($date_info['schedule']['start_time'])); ?> - 
                                                                        <?php echo date('g:i A', strtotime($date_info['schedule']['end_time'])); ?>
                                                                    </p>
                                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                            onclick="showTimeSlots('<?php echo $date_info['date']; ?>', <?php echo $selected_doctor; ?>)">
                                                                        Select Time Slots
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <div id="time-slots-container" class="mb-3" style="display: none;">
                                                <label class="form-label">Available Time Slots</label>
                                                <div id="time-slots" class="d-flex flex-wrap gap-2">
                                                    <!-- Time slots will be loaded here -->
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" id="appointment_date" name="appointment_date">
                                            <input type="hidden" id="appointment_time" name="appointment_time">
                                            
                                            <div class="mb-3">
                                                <label for="reason" class="form-label">Reason for Appointment</label>
                                                <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary" id="book-btn" disabled>Book Appointment</button>
                                        <?php elseif ($selected_doctor): ?>
                                            <div class="alert alert-info">
                                                No available schedules for this doctor. Please select another doctor.
                                            </div>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Instructions</h5>
                                </div>
                                <div class="card-body">
                                    <ol>
                                        <li>Select a doctor from the dropdown list</li>
                                        <li>Choose an available date from the options</li>
                                        <li>Select a time slot that works for you</li>
                                        <li>Add a reason for your appointment (optional)</li>
                                        <li>Click "Book Appointment" to submit</li>
                                    </ol>
                                    <p class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Your appointment will be pending approval by the admin.
                                    </p>
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
        // Handle doctor selection
        document.addEventListener('DOMContentLoaded', function() {
            const doctorSelect = document.getElementById('doctor_id');
            if (doctorSelect) {
                doctorSelect.addEventListener('change', function() {
                    const selectedDoctor = this.value;
                    if (selectedDoctor) {
                        // Redirect to the same page with the selected doctor
                        window.location.href = '?doctor=' + selectedDoctor;
                    } else {
                        // If no doctor selected, remove the parameter
                        window.location.href = window.location.pathname;
                    }
                });
            }
        });
        
        function showTimeSlots(date, doctorId) {
            // Fetch available time slots from the server
            const timeSlotsContainer = document.getElementById('time-slots-container');
            const timeSlots = document.getElementById('time-slots');
            const appointmentDateInput = document.getElementById('appointment_date');
            
            // Clear previous slots
            timeSlots.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            // Set the selected date
            appointmentDateInput.value = date;
            
            // Make AJAX request to get available slots
            fetch('get_available_slots.php?doctor_id=' + doctorId + '&date=' + date)
                .then(response => response.json())
                .then(data => {
                    timeSlots.innerHTML = '';
                    
                    if (data.error) {
                        timeSlots.innerHTML = '<div class="alert alert-warning">' + data.error + '</div>';
                        return;
                    }
                    
                    if (data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'btn btn-outline-primary';
                            button.textContent = slot.display;
                            button.onclick = function() {
                                selectTimeSlot(date, slot.time, slot.display);
                            };
                            timeSlots.appendChild(button);
                        });
                    } else {
                        timeSlots.innerHTML = '<div class="alert alert-info">No available time slots for this date.</div>';
                    }
                    
                    // Show the container
                    timeSlotsContainer.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching time slots:', error);
                    timeSlots.innerHTML = '<div class="alert alert-danger">Error loading time slots. Please try again.</div>';
                });
        }
        
        function selectTimeSlot(date, time, displayTime) {
            // Set the selected time
            document.getElementById('appointment_time').value = time;
            
            // Enable the book button
            document.getElementById('book-btn').disabled = false;
            
            // Show confirmation
            alert('Selected: ' + displayTime + ' on ' + date);
        }
    </script>
</body>
</html>