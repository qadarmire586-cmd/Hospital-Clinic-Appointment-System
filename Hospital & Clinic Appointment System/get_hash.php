<!DOCTYPE html>
<html>
<head>
    <title>Generate Password Hash</title>
</head>
<body>
    <h2>Generated Password Hash</h2>
    <?php
    // Generate password hash for ilyas8833
    $password = 'ilyas8833';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<p><strong>Password:</strong> " . $password . "</p>";
    echo "<p><strong>Hash:</strong> " . $hash . "</p>";

    // Create the SQL insert statement
    $sql = "INSERT INTO admins (username, password, email, full_name) VALUES ('ilyaas', '" . $hash . "', 'ilyaas@hospital.com', 'Ilyaas Administrator');";
    echo "<p><strong>SQL Statement:</strong></p>";
    echo "<textarea rows='4' cols='100'>" . $sql . "</textarea>";
    
    echo "<p>Copy the SQL statement above and run it in phpMyAdmin to create your admin user.</p>";
    ?>
</body>
</html>