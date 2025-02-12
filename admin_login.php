<?php
session_start();
include('config.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $query = "SELECT admin_id, password FROM admin WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid email";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-login-container">
        <a href="index.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
        
        <form class="admin-login-form" method="POST" action="">
            <h2><i class="fas fa-lock"></i> Admin Login</h2>
            
            <?php if ($error): ?>
                <div class="admin-login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Email</label>
                <input type="email" id="username" name="username" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-key"></i> Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="admin-login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
</body>
</html>
