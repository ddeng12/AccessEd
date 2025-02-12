<?php
session_start();
include('db_connection.php');

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);

    // File upload configuration
    $upload_dir = 'uploads/resources/';
    $max_file_size = 10 * 1024 * 1024; // 10 MB
    
    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // File handling
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == UPLOAD_ERR_OK) {
        $file_name = uniqid() . '_' . basename($_FILES['resource_file']['name']);
        $target_path = $upload_dir . $file_name;

        // Validate file type and size
        $file_type = strtolower(pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION));
        if ($file_type != 'pdf') {
            $_SESSION['upload_error'] = 'Only PDF files are allowed.';
            header('Location: dashboard.php#resources');
            exit();
        }

        if ($_FILES['resource_file']['size'] > $max_file_size) {
            $_SESSION['upload_error'] = 'File is too large. Maximum size is 10 MB.';
            header('Location: dashboard.php#resources');
            exit();
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $target_path)) {
            // Insert resource info into database
            $insert_query = "INSERT INTO educational_resources 
                             (title, course_name, file_path, uploaded_at) 
                             VALUES 
                             ('$title', '$course', '$target_path', NOW())";
            
            if (mysqli_query($conn, $insert_query)) {
                $_SESSION['upload_success'] = 'Resource uploaded successfully!';
            } else {
                $_SESSION['upload_error'] = 'Database error: ' . mysqli_error($conn);
            }
        } else {
            $_SESSION['upload_error'] = 'File upload failed.';
        }
    } else {
        $_SESSION['upload_error'] = 'No file uploaded or upload error occurred.';
    }

    header('Location: dashboard.php#resources');
    exit();
}
?>