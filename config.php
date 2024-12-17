<?php
// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$host = 'localhost';
$dbname = 'webtech_fall2024_david_deng';
$username = 'david.deng'; 
$password = 'Pendo248613!';  

// Create connection - simplest version
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Minimal tracking function
function trackUserActivity($page_name) {
    // Temporarily disable tracking to test basic functionality
    return;
}

// Minimal active users function
function getActiveUsers() {
    return 0;
}

// Basic admin check
function requireAdminLogin() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: admin_login.php');
        exit();
    }
}
?>
