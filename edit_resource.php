<?php
session_start();
include('config.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// Get resource data
$resource_id = $_GET['id'] ?? 0;
$query = "SELECT * FROM resources WHERE resource_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$resource = $stmt->get_result()->fetch_assoc();

if (!$resource) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resource_name = trim($_POST['resource_name']);
    $subject_id = $_POST['subject_id'];
    $description = trim($_POST['description']);
    $resource_type = $_POST['resource_type'];
    
    // Update resource info
    $update_query = "UPDATE resources SET resource_name = ?, subject_id = ?, 
                    description = ?, resource_type = ? WHERE resource_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sissi", $resource_name, $subject_id, $description, 
                            $resource_type, $resource_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_msg'] = "Resource updated successfully";
    } else {
        $_SESSION['error_msg'] = "Update failed";
    }
    
    header('Location: dashboard.php');
    exit();
}

// Get subjects for dropdown
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_result = mysqli_query($conn, $subjects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resource - AccessEd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Edit Resource</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Resource Name</label>
                        <input type="text" class="form-control" name="resource_name" value="<?php echo htmlspecialchars($resource['resource_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select class="form-select" name="subject_id" required>
                            <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                                <option value="<?php echo $subject['subject_id']; ?>" 
                                    <?php echo ($subject['subject_id'] == $resource['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resource Type</label>
                        <select class="form-select" name="resource_type" required>
                            <option value="notes" <?php echo ($resource['resource_type'] == 'notes') ? 'selected' : ''; ?>>Notes</option>
                            <option value="past_paper" <?php echo ($resource['resource_type'] == 'past_paper') ? 'selected' : ''; ?>>Past Paper</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($resource['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current File: <?php echo htmlspecialchars($resource['resource_file']); ?></label>
                        <input type="file" class="form-control" name="resource_file">
                        <small class="text-muted">Leave empty to keep current file</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 