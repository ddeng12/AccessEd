<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

try {
    $host = 'localhost';
    $dbname = 'webtech_fall2024_david_deng';
    $username = 'david.deng'; 
    $password = 'Pendo248613!';

    echo "<p>Attempting to connect to database...</p>";
    
    $conn = mysqli_connect($host, $username, $password, $dbname);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    echo "<p style='color: green;'>Successfully connected to database!</p>";
    
    // Test a simple query
    echo "<p>Testing simple query...</p>";
    $result = mysqli_query($conn, "SELECT * FROM subjects LIMIT 1");
    
    if ($result) {
        echo "<p style='color: green;'>Query successful!</p>";
        $row = mysqli_fetch_assoc($result);
        echo "<p>Found subject: " . htmlspecialchars($row['subject_name']) . "</p>";
    } else {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
} 