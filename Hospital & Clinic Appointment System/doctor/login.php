<?php
// Redirect to unified login
session_start();

// If already logged in as doctor, go to doctor dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'doctor' && isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Otherwise, redirect to unified login
header("Location: ../login.php");
exit();
?>