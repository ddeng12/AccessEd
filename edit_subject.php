<?php
session_start();
include('config.php');

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$error = '';
$success = '';

// Get subject ID from URL
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$subject_id) {
    header('Location: dashboard.php#subjects');
    exit();
}

// Get current subject data
$query = "SELECT * FROM subjects WHERE subject_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    header('Location: dashboard.php#subjects');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    
    if (empty($subject_name)) {
        $error = "Subject name is required.";
    } else {
        // Check if subject name already exists (excluding current subject)
        $check_query = "SELECT subject_id FROM subjects WHERE subject_name = ? AND subject_id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $subject_name, $subject_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Subject name already exists.";
        } else {
            // Update subject
            $update_query = "UPDATE subjects SET subject_name = ? WHERE subject_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $subject_name, $subject_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success_msg'] = "Subject updated successfully.";
                header('Location: dashboard.php#subjects');
                exit();
            } else {
                $error = "Error updating subject.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - AccessEd Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Subject</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="subject_name" class="form-label">Subject Name</label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" 
                                       value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php#subjects" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Subject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>