<?php
session_start();
include('config.php');

if (isset($_GET['id'])) {
    $resource_id = (int)$_GET['id'];
    
    // Get resource details
    $query = "SELECT * FROM resources WHERE resource_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $resource = $stmt->get_result()->fetch_assoc();
    
    if ($resource) {
        $file_path = "uploads/" . $resource['resource_file'];
        
        if (file_exists($file_path)) {
            // Log the download
            $user_id = session_id();
            $query = "INSERT INTO user_activity (user_id, page_name, action_type) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $page_name = $resource['resource_name'];
            $action_type = 'download';
            $stmt->bind_param("sss", $user_id, $page_name, $action_type);
            $stmt->execute();
            
            // Set headers for download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($resource['resource_file']) . '"');
            header('Content-Length: ' . filesize($file_path));
            header('Cache-Control: private');
            header('Pragma: public');
            
            // Clear output buffer
            ob_clean();
            flush();
            
            // Output file
            readfile($file_path);
            exit();
        } else {
            die("File not found: " . htmlspecialchars($resource['resource_file']));
        }
    }
}

// If we get here, something went wrong
header('Location: index.php');
exit();
  </rewritten_file> 