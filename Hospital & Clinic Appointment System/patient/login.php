<?php
// Redirect to unified login
session_start();

// If already logged in as patient, go to patient dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'patient' && isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Otherwise, redirect to unified login
header("Location: ../login.php");
exit();
?>