<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    
    if (!empty($subject_name)) {
        // Check if subject already exists
        $check_query = "SELECT subject_id FROM subjects WHERE subject_name = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $subject_name);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['error_msg'] = "Subject already exists.";
        } else {
            // Add new subject
            $insert_query = "INSERT INTO subjects (subject_name) VALUES (?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("s", $subject_name);
            
            if ($insert_stmt->execute()) {
                $new_subject_id = $conn->insert_id;
                $_SESSION['success_msg'] = "Subject added successfully!";
                
                // Note: Removed the logging functionality temporarily
                
            } else {
                $_SESSION['error_msg'] = "Error adding subject.";
            }
        }
    } else {
        $_SESSION['error_msg'] = "Subject name cannot be empty.";
    }
}

header('Location: dashboard.php#subjects-section');
exit();
