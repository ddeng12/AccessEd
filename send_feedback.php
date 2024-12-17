<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = strip_tags(trim($_POST["message"]));
    $category = isset($_POST["category"]) ? $_POST["category"] : 'General';

    $query = "INSERT INTO feedback (name, email, message, category) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $message, $category);
    
    if ($stmt->execute()) {
        // Show success page
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Success</title>
            <meta http-equiv="refresh" content="3;url=index.php">
            <style>
                body { 
                    font-family: Arial, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    background-color: #f8f9fa;
                }
                .message {
                    text-align: center;
                    padding: 2rem;
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .success { color: #28a745; }
            </style>
        </head>
        <body>
            <div class="message">
                <h2 class="success">Thank you for your feedback!</h2>
                <p>Redirecting back to homepage...</p>
            </div>
        </body>
        </html>';
    } else {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <meta http-equiv="refresh" content="3;url=index.php">
            <style>
                body { 
                    font-family: Arial, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    background-color: #f8f9fa;
                }
                .message {
                    text-align: center;
                    padding: 2rem;
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .error { color: #dc3545; }
            </style>
        </head>
        <body>
            <div class="message">
                <h2 class="error">Error submitting feedback</h2>
                <p>Redirecting back to homepage...</p>
            </div>
        </body>
        </html>';
    }
    
    $stmt->close();
    $conn->close();
    exit();
}

header('Location: index.php');
exit();
?>