<?php
session_start();
include('config.php');

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Add this right after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);

// File upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $base_dir = dirname(__FILE__);  // Get current script directory
        $target_dir = $base_dir . "/uploads/";
        
        // Debug info
        error_log("Base directory: " . $base_dir);
        error_log("Target directory: " . $target_dir);
        error_log("Directory exists: " . (file_exists($target_dir) ? 'yes' : 'no'));
        error_log("Directory writable: " . (is_writable($target_dir) ? 'yes' : 'no'));

        // Don't try to create directory - it should exist
        if (!file_exists($target_dir)) {
            throw new Exception("Uploads directory does not exist. Please create it manually.");
        }

        if (!is_writable($target_dir)) {
            throw new Exception("Uploads directory is not writable. Please check permissions.");
        }

        $resource_name = trim($_POST['resource_name']);
        $subject_id = (int)$_POST['subject_id'];
        $description = trim($_POST['description']);
        $resource_type = $_POST['resource_type'];
        
        // Generate filename
        $file_extension = strtolower(pathinfo($_FILES["resource_file"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Try to move the file
        if (!move_uploaded_file($_FILES["resource_file"]["tmp_name"], $target_file)) {
            error_log("Upload failed. Details:");
            error_log("PHP Error: " . error_get_last()['message']);
            error_log("Target file: " . $target_file);
            error_log("Temp file exists: " . (file_exists($_FILES["resource_file"]["tmp_name"]) ? 'yes' : 'no'));
            throw new Exception("Failed to move uploaded file. Check error logs.");
        }
        
        // Debug form data
        error_log("Adding resource - Name: $resource_name, Subject: $subject_id, Type: $resource_type");
        
        // Database insert
        $query = "INSERT INTO resources (resource_name, subject_id, description, resource_file, resource_type) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sisss", $resource_name, $subject_id, $description, $new_filename, $resource_type);
        
        if (!$stmt->execute()) {
            // If database insert fails, remove the uploaded file
            unlink($target_file);
            throw new Exception("Database error: " . $stmt->error);
        }
        
        $_SESSION['success_msg'] = "Resource uploaded successfully";
        error_log("Resource added successfully to database");
        
        // Redirect back to dashboard
        header('Location: dashboard.php');
        exit();
        
    } catch (Exception $e) {
        error_log("Error in add_resource.php: " . $e->getMessage());
        $_SESSION['error_msg'] = $e->getMessage();
    }
}

// Get subjects for dropdown
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_result = mysqli_query($conn, $subjects_query);

if (!$subjects_result) {
    error_log("Error fetching subjects: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resource - AccessEd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Add New Resource</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error_msg'];
                        unset($_SESSION['error_msg']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_msg'];
                        unset($_SESSION['success_msg']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Resource Name</label>
                        <input type="text" class="form-control" name="resource_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select class="form-select" name="subject_id" required>
                            <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                                <option value="<?php echo $subject['subject_id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resource Type</label>
                        <select class="form-select" name="resource_type" required>
                            <option value="notes">Notes</option>
                            <option value="past_paper">Past Paper</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" class="form-control" name="resource_file" required>
                        <small class="text-muted">Allowed types: PDF, DOC, DOCX, PPT, PPTX, TXT</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Add Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 