<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');
echo json_encode(['active_users' => getActiveUsers()]); 