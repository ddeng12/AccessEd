<?php
session_start();
include('config.php');

// Check if resource ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$resource_id = $_GET['id'];

// Get resource information
$stmt = $conn->prepare("SELECT * FROM resources WHERE resource_id = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$resource = $result->fetch_assoc();
$file_path = 'uploads/' . $resource['resource_file'];

// Check if file exists
if (!file_exists($file_path)) {
    die('File not found.');
}

// Log the download
$user_id = session_id(); // Or use actual user ID if you have a login system
$stmt = $conn->prepare("INSERT INTO user_activity (user_id, page_name, action_type) VALUES (?, 'download', ?)");
$action = "Downloaded " . $resource['resource_name'];
$stmt->bind_param("ss", $user_id, $action);
$stmt->execute();

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($resource['resource_file']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Expires: 0');

// Output file
readfile($file_path);
exit();
?>
  </rewritten_file> 