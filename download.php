<?php
session_start();
include('config.php');

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = 'uploads/' . $filename;
    
    if (file_exists($filepath)) {
        $mime_type = mime_content_type($filepath);
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        exit();
    } else {
        header('HTTP/1.0 404 Not Found');
        echo 'File not found.';
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Invalid request.';
} 