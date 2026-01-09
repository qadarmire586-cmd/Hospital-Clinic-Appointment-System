<?php
// Authentication check for doctor pages
session_start();

// Check if doctor is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor' || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Optional: Check if session is still valid by querying the database
include_once '../config/db.php';

$doctor_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM doctors WHERE id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // Session is invalid, destroy session and redirect to login
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$stmt->close();
?>