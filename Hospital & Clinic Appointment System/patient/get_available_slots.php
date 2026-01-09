<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../config/db.php';

// Get parameters
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (empty($doctor_id) || empty($date)) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Get the day of week for the selected date
$day_of_week = date('l', strtotime($date));

// Get doctor's schedule for the selected day
$stmt = $conn->prepare("SELECT id, start_time, end_time, slot_duration FROM doctor_schedules 
                       WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1");
$stmt->bind_param("is", $doctor_id, $day_of_week);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'No schedule available for this doctor on the selected day']);
    exit();
}

$schedule = $result->fetch_assoc();
$stmt->close();

// Get already booked appointments for this doctor on this date
$stmt = $conn->prepare("SELECT appointment_time FROM appointments 
                       WHERE doctor_id = ? AND appointment_date = ? AND status != 'Cancelled'");
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$booked_result = $stmt->get_result();

$booked_slots = [];
while ($row = $booked_result->fetch_assoc()) {
    $booked_slots[] = $row['appointment_time'];
}
$stmt->close();

// Generate available time slots
$start_time = strtotime($schedule['start_time']);
$end_time = strtotime($schedule['end_time']);
$slot_duration = $schedule['slot_duration'] * 60; // Convert minutes to seconds

$available_slots = [];
for ($time = $start_time; $time < $end_time; $time += $slot_duration) {
    $time_formatted = date('H:i:s', $time);
    
    // Check if this slot is already booked
    if (!in_array($time_formatted, $booked_slots)) {
        $available_slots[] = [
            'time' => $time_formatted,
            'display' => date('g:i A', $time)
        ];
    }
} // This was missing

header("Content-Type: application/json");
echo json_encode([
    'success' => true,
    'slots' => $available_slots
]);
?>