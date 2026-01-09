<?php
session_start();
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit();
}

echo "<h2>Doctor Schedules Test</h2>";

// Get all doctors
$stmt = $conn->prepare("SELECT id, name, specialization FROM doctors ORDER BY name");
$stmt->execute();
$doctors_result = $stmt->get_result();
$stmt->close();

if ($doctors_result->num_rows == 0) {
    echo "<p>No doctors found in the database.</p>";
    exit();
}

echo "<h3>Doctors:</h3>";
echo "<ul>";
while ($doctor = $doctors_result->fetch_assoc()) {
    echo "<li><strong>" . htmlspecialchars($doctor['name']) . "</strong> - " . htmlspecialchars($doctor['specialization']) . " (ID: " . $doctor['id'] . ")</li>";
    
    // Check schedules for this doctor
    $stmt = $conn->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY day_of_week, start_time");
    $stmt->bind_param("i", $doctor['id']);
    $stmt->execute();
    $schedules_result = $stmt->get_result();
    
    if ($schedules_result->num_rows > 0) {
        echo "<ul>";
        while ($schedule = $schedules_result->fetch_assoc()) {
            echo "<li>" . $schedule['day_of_week'] . " " . 
                 date('g:i A', strtotime($schedule['start_time'])) . " - " . 
                 date('g:i A', strtotime($schedule['end_time'])) . 
                 " (Duration: " . $schedule['slot_duration'] . " min, Max Patients: " . $schedule['max_patients'] . 
                 ", " . ($schedule['is_available'] ? 'Available' : 'Not Available') . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<ul><li>No schedules found for this doctor</li></ul>";
    }
    
    $stmt->close();
}
echo "</ul>";

$conn->close();
?>