<?php
session_start();
include('config.php');

// Check admin login and session timeout
requireAdminLogin();

// At the top with other queries
function getUniqueUsersCount() {
    global $conn;
    $query = "SELECT COUNT(DISTINCT user_id) AS total_users FROM user_activity 
              WHERE action_type != 'admin_access' 
              AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Error in getUniqueUsersCount: " . mysqli_error($conn));
        return 0;
    }
    return mysqli_fetch_assoc($result)['total_users'];
}

function getTotalUserActivities() {
    global $conn;
    $query = "SELECT COUNT(*) AS total_activities FROM user_activity 
              WHERE action_type != 'admin_access' 
              AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        error_log("Error in getTotalUserActivities: " . mysqli_error($conn));
        return 0;
    }
    return mysqli_fetch_assoc($result)['total_activities'];
}

// Add this at the start of each page to track activity
trackUserActivity(basename($_SERVER['PHP_SELF']));

// Get statistics
$total_users = getUniqueUsersCount();
$total_activities = getTotalUserActivities();
$query_resources = "SELECT COUNT(*) AS total_resources FROM resources";
$query_subjects = "SELECT COUNT(*) AS total_subjects FROM subjects";
$result_resources = mysqli_query($conn, $query_resources);
$result_subjects = mysqli_query($conn, $query_subjects);
$total_resources = mysqli_fetch_assoc($result_resources)['total_resources'];
$total_subjects = mysqli_fetch_assoc($result_subjects)['total_subjects'];

// Get recent resources
$query_recent = "SELECT r.*, s.subject_name 
                FROM resources r 
                JOIN subjects s ON r.subject_id = s.subject_id 
                ORDER BY r.created_at DESC LIMIT 5";
$recent_resources = mysqli_query($conn, $query_recent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AccessEd - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-header">
                    <h3>AccessEd Admin</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" data-section="dashboard-section">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-section="resources-section">
                            <i class="fas fa-book me-2"></i>Manage Resources
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-section="subjects-section">
                            <i class="fas fa-graduation-cap me-2"></i>Manage Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-section="feedback-section">
                            <i class="fas fa-comments me-2"></i>Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="dashboard-section" style="display: block;">
                    <h1 class="mt-4">Dashboard Overview</h1>
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Users (30 Days)</h5>
                                    <p class="card-text display-4"><?php echo $total_users; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">User Activities</h5>
                                    <p class="card-text display-4"><?php echo $total_activities; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Resources</h5>
                                    <p class="card-text display-4"><?php echo $total_resources; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title">Total Subjects</h5>
                                    <p class="card-text display-4"><?php echo $total_subjects; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active Users</h5>
                                    <p class="card-text display-4"><?php echo getActiveUsers(); ?></p>
                                    <small>In the last 15 minutes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Resources -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Resources</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($resource = mysqli_fetch_assoc($recent_resources)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($resource['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                                       class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resources Section -->
                <div id="resources-section" class="dashboard-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manage Resources</h2>
                        <a href="add_resource.php" class="btn btn-primary add-resource-btn">
                            <i class="fas fa-plus me-2"></i>Add New Resource
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT r.*, s.subject_name 
                                                FROM resources r 
                                                JOIN subjects s ON r.subject_id = s.subject_id 
                                                ORDER BY r.created_at DESC";
                                        $result = mysqli_query($conn, $query);
                                        
                                        while ($resource = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($resource['resource_name']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($resource['created_at'])); ?></td>
                                                <td>
                                                    <a href="edit_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                                       class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                                       class="btn btn-danger btn-sm delete-resource">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects Section -->
                <div id="subjects-section" class="dashboard-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manage Subjects</h2>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                            <i class="fas fa-plus me-2"></i>Add New Subject
                        </button>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Resources Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $subjects_query = "SELECT s.*, COUNT(r.resource_id) as resource_count 
                                             FROM subjects s 
                                             LEFT JOIN resources r ON s.subject_id = r.subject_id 
                                             GROUP BY s.subject_id";
                                        $subjects_result = mysqli_query($conn, $subjects_query);
                                        while ($subject = mysqli_fetch_assoc($subjects_result)): 
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                                <td><?php echo $subject['resource_count']; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm edit-subject" 
                                                            data-id="<?php echo $subject['subject_id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editSubjectModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="delete_subject.php?id=<?php echo $subject['subject_id']; ?>" 
                                                       class="btn btn-danger btn-sm delete-subject"
                                                       onclick="return confirm('Are you sure? This will also delete all resources in this subject.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Section -->
                <div id="feedback-section" class="dashboard-section" style="display: none;">
                    <h2 class="mb-4">Student Feedback</h2>
                    
                    <!-- Feedback Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">New Feedback</h5>
                                    <?php
                                    $new_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM feedback WHERE status = 'New'"))['count'];
                                    ?>
                                    <p class="card-text h2"><?php echo $new_count; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Feedback</h5>
                                    <?php
                                    $total_feedback = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM feedback"))['count'];
                                    ?>
                                    <p class="card-text h2"><?php echo $total_feedback; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">This Month</h5>
                                    <?php
                                    $monthly_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                        "SELECT COUNT(*) as count FROM feedback WHERE MONTH(created_at) = MONTH(CURRENT_DATE())"))['count'];
                                    ?>
                                    <p class="card-text h2"><?php echo $monthly_count; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Feedback</h5>
                            <div>
                                <select class="form-select form-select-sm d-inline-block w-auto" id="feedbackFilter">
                                    <option value="all">All Categories</option>
                                    <option value="General">General</option>
                                    <option value="Course Content">Course Content</option>
                                    <option value="Website Experience">Website Experience</option>
                                    <option value="Technical Issue">Technical Issue</option>
                                    <option value="Suggestion">Suggestion</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $feedback_query = "SELECT * FROM feedback ORDER BY created_at DESC";
                                        $feedback_result = mysqli_query($conn, $feedback_query);
                                        while ($feedback = mysqli_fetch_assoc($feedback_result)): 
                                        ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($feedback['name']); ?>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($feedback['email']); ?></div>
                                                </td>
                                                <td><span class="badge bg-info"><?php echo $feedback['category']; ?></span></td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;">
                                                        <?php echo htmlspecialchars($feedback['message']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $feedback['status'] === 'New' ? 'primary' : 'secondary'; ?>">
                                                        <?php echo $feedback['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info view-feedback" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewFeedbackModal"
                                                            data-id="<?php echo $feedback['feedback_id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($feedback['name']); ?>"
                                                            data-email="<?php echo htmlspecialchars($feedback['email']); ?>"
                                                            data-category="<?php echo htmlspecialchars($feedback['category']); ?>"
                                                            data-message="<?php echo htmlspecialchars($feedback['message']); ?>"
                                                            data-date="<?php echo $feedback['created_at']; ?>"
                                                            data-status="<?php echo $feedback['status']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($feedback['status'] === 'New'): ?>
                                                        <button class="btn btn-sm btn-success mark-read" data-id="<?php echo $feedback['feedback_id']; ?>">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add other sections here -->
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation handling
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const sections = document.querySelectorAll('.dashboard-section');

            function showSection(sectionId) {
                sections.forEach(section => section.style.display = 'none');
                document.getElementById(sectionId).style.display = 'block';
                navLinks.forEach(link => {
                    link.classList.toggle('active', link.dataset.section === sectionId);
                });
            }

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    showSection(this.dataset.section);
                });
            });

            // Delete confirmation
            document.querySelectorAll('.delete-resource').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this resource?')) {
                        e.preventDefault();
                    }
                });
            });

            // Handle feedback modal
            const viewFeedbackModal = document.getElementById('viewFeedbackModal');
            if (viewFeedbackModal) {
                viewFeedbackModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const feedbackId = button.getAttribute('data-id');
                    const name = button.getAttribute('data-name');
                    const email = button.getAttribute('data-email');
                    const category = button.getAttribute('data-category');
                    const message = button.getAttribute('data-message');
                    const date = new Date(button.getAttribute('data-date')).toLocaleString();
                    const status = button.getAttribute('data-status');

                            // Update modal content
                    document.getElementById('feedbackName').textContent = name;
                    document.getElementById('feedbackEmail').textContent = email;
                    document.getElementById('feedbackCategory').textContent = category;
                    document.getElementById('feedbackMessage').textContent = message;
                    document.getElementById('feedbackDate').textContent = date;
                            
                            // Show/hide mark as read button
                            const markAsReadBtn = document.getElementById('markAsReadBtn');
                    if (status === 'New') {
                        markAsReadBtn.style.display = 'block';
                                markAsReadBtn.setAttribute('data-id', feedbackId);
                    } else {
                        markAsReadBtn.style.display = 'none';
                    }
                });
            }

            // Handle mark as read
            const markAsReadBtn = document.getElementById('markAsReadBtn');
            if (markAsReadBtn) {
                markAsReadBtn.addEventListener('click', function() {
                const feedbackId = this.getAttribute('data-id');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('viewFeedbackModal'));
                
                fetch('update_feedback_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${feedbackId}&status=Read`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                            modal.hide();
                        window.location.reload();
                    } else {
                        alert('Error updating feedback status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating feedback status');
                });
            });
            }
        });
    </script>

    <!-- View Feedback Modal -->
    <div class="modal fade" id="viewFeedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <p id="feedbackName"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <p id="feedbackEmail"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Category:</strong>
                        <p id="feedbackCategory"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <p id="feedbackMessage"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong>
                        <p id="feedbackDate"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="markAsReadBtn">Mark as Read</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="add_subject.php" method="POST">
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <script>
        // Update active users count every 60 seconds
        setInterval(function() {
            fetch('get_active_users.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('active-users-count').textContent = data.active_users;
                });
        }, 60000);
    </script>

    <script>
        // Check session every minute
        setInterval(function() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.timeout) {
                        window.location.href = 'admin_login.php?timeout=1';
                    }
                });
        }, 60000); // Check every minute
    </script>
</body>
</html>