<?php
session_start();
include('config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get resource data
    $resource_id = $_GET['id'] ?? 0;
    $query = "SELECT r.*, s.subject_name 
              FROM resources r 
              JOIN subjects s ON r.subject_id = s.subject_id 
              WHERE r.resource_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    $resource = $stmt->get_result()->fetch_assoc();

    // Validate resource exists
    if (!$resource) {
        throw new Exception("Resource not found");
    }

    // Set the correct file path using relative path
    $file_path = "uploads/" . $resource['resource_file'];
    
    // Debug info
    error_log("Viewing file: " . realpath($file_path));

    // Check if file exists
    if (!file_exists($file_path)) {
        throw new Exception("File not found: " . htmlspecialchars($resource['resource_file']));
    }

    // Log file details
    error_log("File exists. Size: " . filesize($file_path));

    // Get file mime type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        throw new Exception("Failed to open fileinfo");
    }
    
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    if (!$mime_type) {
        throw new Exception("Could not determine mime type");
    }

    // Clear any existing output
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for viewing/downloading
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="' . basename($resource['resource_file']) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    // Output file in chunks to handle large files
    $handle = fopen($file_path, 'rb');
    if ($handle === false) {
        throw new Exception("Could not open file for reading");
    }

    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
    }
    fclose($handle);
    exit();

} catch (Exception $e) {
    error_log("Error in view_resource.php: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit();
}
?> 