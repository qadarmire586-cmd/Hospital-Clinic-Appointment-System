<?php
// Redirect to unified login
session_start();

// If already logged in as admin, go to admin dashboard
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin' && isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Otherwise, redirect to unified login
header("Location: ../login.php");
exit();
?>