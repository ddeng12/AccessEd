<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $subject_id = (int)$_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete associated resources first
        $query = "SELECT resource_file FROM resources WHERE subject_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Delete resource files
        while ($resource = $result->fetch_assoc()) {
            $file_path = 'uploads/' . $resource['resource_file'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete resources from database
        $delete_resources = "DELETE FROM resources WHERE subject_id = ?";
        $stmt = $conn->prepare($delete_resources);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        // Delete subject
        $delete_subject = "DELETE FROM subjects WHERE subject_id = ?";
        $stmt = $conn->prepare($delete_subject);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success_msg'] = "Subject and associated resources deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_msg'] = "Error deleting subject: " . $e->getMessage();
    }
}

header('Location: dashboard.php');
exit();