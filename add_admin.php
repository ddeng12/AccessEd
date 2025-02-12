<?php
session_start();
include('config.php');

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = strip_tags(trim($_POST['full_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    try {
        // Check if email already exists
        $check_query = "SELECT * FROM admin WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['error_msg'] = "An admin with this email already exists.";
        } else {
            // Hash password and insert new admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO admin (full_name, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sss", $full_name, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success_msg'] = "New admin account created successfully!";
            } else {
                throw new Exception("Error creating admin account");
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    
    header('Location: dashboard.php');
    exit();
}
?> 