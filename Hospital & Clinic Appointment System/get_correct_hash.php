<!DOCTYPE html>
<html>
<head>
    <title>Get Correct Password Hash</title>
</head>
<body>
    <h2>Correct Admin Credentials</h2>
    <?php
    // Generate correct password hash for ilyas8833
    echo "<p><strong>Username:</strong> ilyaas</p>";
    echo "<p><strong>Password:</strong> ilyas8833</p>";
    
    $hash = password_hash('ilyas8833', PASSWORD_DEFAULT);
    echo "<p><strong>Correct Hash:</strong> " . $hash . "</p>";

    // Create the correct SQL statement
    echo "<h3>SQL Statements to run in phpMyAdmin:</h3>";
    echo "<textarea rows='6' cols='100'>";
    echo "USE hospital_clinic_appointment_system;\n";
    echo "DELETE FROM admins WHERE username = 'ilyaas';\n";
    echo "INSERT INTO admins (username, password, email, full_name) VALUES ('ilyaas', '" . $hash . "', 'ilyaas@hospital.com', 'Ilyaas Administrator');";
    echo "</textarea>";
    
    echo "<p><strong>Instructions:</strong></p>";
    echo "<ol>";
    echo "<li>Open phpMyAdmin (usually at http://localhost/phpmyadmin)</li>";
    echo "<li>Select the 'hospital_clinic_appointment_system' database</li>";
    echo "<li>Click on the 'SQL' tab</li>";
    echo "<li>Copy and paste the SQL statements above</li>";
    echo "<li>Click 'Go' to execute</li>";
    echo "</ol>";
    
    echo "<p>After running these statements, you should be able to log in with Username: ilyaas and Password: ilyas8833</p>";
    ?>
</body>
</html>