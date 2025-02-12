<?php
session_start();
include('config.php'); // Database connection

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { 
    header('Location: admin_login.php');
    exit();
}

// Handle resource deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $resource_id = $_GET['delete'];
    $delete_query = "DELETE FROM resources WHERE resource_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $resource_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Resource deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting resource: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header('Location: manage_resources.php');
    exit();
}

// Fetch resources with subject name
$query_resources = "
    SELECT 
        r.resource_id, 
        r.resource_name, 
        r.resource_file, 
        s.subject_name,
        r.upload_date
    FROM resources r
    JOIN subjects s ON r.subject_id = s.subject_id
    ORDER BY r.upload_date DESC
";
$resources_result = mysqli_query($conn, $query_resources);

// Fetch subjects for the add/edit resource modal
$subjects_query = "SELECT subject_id, subject_name FROM subjects";
$subjects_result = mysqli_query($conn, $subjects_query);

// Log admin activity
$admin_id = $_SESSION['admin_id'] ?? 'unknown';
logUserActivity($admin_id, 'manage_resources.php', 'admin_access');

// User Activity Logging Function
function logUserActivity($user_id, $page_name, $action_type) {
    global $conn;
    $query = $conn->prepare("INSERT INTO user_activity (user_id, page_name, action_type) VALUES (?, ?, ?)");
    $query->bind_param("sss", $user_id, $page_name, $action_type);
    $query->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AccessEd - Manage Resources</title>
    <link rel="stylesheet" href="dashboard.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
                        <span class="fs-4">AccessEd Admin</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="manage_resources.php" class="nav-link active">
                                <i class="fas fa-book me-2"></i>Manage Resources
                            </a>
                        </li>
                        <li>
                            <a href="manage_subjects.php" class="nav-link">
                                <i class="fas fa-book-open me-2"></i>Manage Subjects
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="nav-link">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Manage Resources</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                        <i class="fas fa-plus me-2"></i>Add New Resource
                    </button>
                </div>

                <!-- Flash Message -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Resources Table -->
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>File</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($resource = mysqli_fetch_assoc($resources_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['resource_file']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['upload_date']); ?></td>
                                    <td>
                                        <a href="edit_resource.php?id=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="manage_resources.php?delete=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this resource?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="add_resource.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Resource Name</label>
                            <input type="text" class="form-control" name="resource_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php 
                                mysqli_data_seek($subjects_result, 0);
                                while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                                    <option value="<?php echo $subject['subject_id']; ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resource File</label>
                            <input type="file" class="form-control" name="resource_file" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Resource</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>