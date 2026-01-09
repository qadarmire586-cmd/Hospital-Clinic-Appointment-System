<?php
// Generate password hash for ilyas8833
$password = 'ilyas8833';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Create the SQL insert statement
$sql = "INSERT INTO admins (username, password, email, full_name) VALUES ('ilyaas', '" . $hash . "', 'ilyaas@hospital.com', 'Ilyaas Administrator');";
echo "\nSQL Statement:\n" . $sql . "\n";
?>