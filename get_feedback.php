<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

// Get feedback data
$feedback_id = $_GET['id'] ?? 0;
$query = "SELECT * FROM feedback WHERE feedback_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $feedback_id);
$stmt->execute();
$feedback = $stmt->get_result()->fetch_assoc();

// Send response
header('Content-Type: application/json');
echo json_encode($feedback ?? ['error' => 'Feedback not found']);
?> 