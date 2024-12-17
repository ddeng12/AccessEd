<?php
include('config.php');

// Set your admin details
$admin_email = 'dengdavidakuoch@gmail.com';
$admin_password = 'admin123!';
$admin_full_name = 'David Deng';

try {
    // Check if admin already exists
    $check_query = "SELECT * FROM admin WHERE email = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $admin_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial;'>";
        echo "<h2 style='color: #FF4444;'>Admin account already exists!</h2>";
        echo "<p>Please use the existing account or contact support.</p>";
        echo "<a href='admin_login.php' style='display: inline-block; padding: 10px 20px; background: #FFD700; color: #333; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Go to Login</a>";
        echo "</div>";
    } else {
        // Hash the password
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        // Create new admin
        $insert_query = "INSERT INTO admin (full_name, email, password) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("sss", $admin_full_name, $admin_email, $hashed_password);
        
        if ($insert_stmt->execute()) {
            echo "<div style='text-align: center; margin-top: 50px; font-family: Arial;'>";
            echo "<h2 style='color: #28a745;'>Admin account created successfully!</h2>";
            echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px auto; max-width: 400px; border-radius: 10px;'>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($admin_email) . "</p>";
            echo "<p><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</p>";
            echo "</div>";
            echo "<p style='color: #dc3545;'><strong>Important:</strong> Save these credentials and delete this file!</p>";
            echo "<a href='admin_login.php' style='display: inline-block; padding: 10px 20px; background: #FFD700; color: #333; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Go to Login</a>";
            echo "</div>";
        } else {
            throw new Exception("Error creating admin account");
        }
    }
} catch (Exception $e) {
    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial;'>";
    echo "<h2 style='color: #FF4444;'>Error!</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

$conn->close();
?> 