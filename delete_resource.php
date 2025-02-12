<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $resource_id = (int)$_GET['id'];
    
    // Get file name before deleting
    $query = "SELECT resource_file FROM resources WHERE resource_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    
    // Delete from database
    $delete_query = "DELETE FROM resources WHERE resource_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $resource_id);
    
    if ($stmt->execute()) {
        // Delete file from uploads directory
        if ($resource && isset($resource['resource_file'])) {
            $file_path = 'uploads/' . $resource['resource_file'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_msg'] = "Resource deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting resource: " . $conn->error;
    }
}

header('Location: dashboard.php');
exit();