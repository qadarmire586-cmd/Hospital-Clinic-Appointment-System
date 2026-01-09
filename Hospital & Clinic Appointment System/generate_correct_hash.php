<?php
// Generate correct password hash for ilyas8833
echo "Username: ilyaas\n";
echo "Password: ilyas8833\n";
$hash = password_hash('ilyas8833', PASSWORD_DEFAULT);
echo "Correct Hash: " . $hash . "\n";

// Create the correct SQL statement
echo "\nSQL Statement to run in phpMyAdmin:\n";
echo "USE hospital_clinic_appointment_system;\n";
echo "DELETE FROM admins WHERE username = 'ilyaas';\n";
echo "INSERT INTO admins (username, password, email, full_name) VALUES ('ilyaas', '" . $hash . "', 'ilyaas@hospital.com', 'Ilyaas Administrator');\n";
?>