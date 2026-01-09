<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin' || !isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

include '../config/db.php';

// Get patient ID from GET parameter
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id <= 0) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid patient ID']);
    exit();
}

// Get patient details
$stmt = $conn->prepare("SELECT id, username, full_name, email, phone, address, date_of_birth, gender, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['error' => 'Patient not found']);
    exit();
}

$patient = $result->fetch_assoc();
$stmt->close();

// Get appointment history
$stmt = $conn->prepare("SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.reason, d.name as doctor_name, d.specialization 
                       FROM appointments a 
                       JOIN doctors d ON a.doctor_id = d.id 
                       WHERE a.patient_id = ? 
                       ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$appointments = [];
while ($appointment = $appointments_result->fetch_assoc()) {
    $appointments[] = $appointment;
}
$stmt->close();

$conn->close();

// Return patient details and appointments as JSON
header('Content-Type: application/json');
echo json_encode([
    'patient' => $patient,
    'appointments' => $appointments
]);
?>