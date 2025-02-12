<?php
session_start();
include('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Calculate dashboard statistics
// Get total resources count
$resourceQuery = "SELECT COUNT(*) as total FROM resources";
$resourceResult = $conn->query($resourceQuery);
$totalResources = $resourceResult->fetch_assoc()['total'];

// Get new feedback count (unread feedback)
$feedbackQuery = "SELECT COUNT(*) as total FROM feedback WHERE status = 'New'";
$feedbackResult = $conn->query($feedbackQuery);
$newFeedbackCount = $feedbackResult->fetch_assoc()['total'];

// Get total subjects count
$subjectsQuery = "SELECT COUNT(*) as total FROM subjects";
$subjectsResult = $conn->query($subjectsQuery);
$totalSubjects = $subjectsResult->fetch_assoc()['total'];

// Get total user activity count
$activityQuery = "SELECT COUNT(DISTINCT user_id) as total FROM user_activity";
$activityResult = $conn->query($activityQuery);
$totalUniqueUsers = $activityResult->fetch_assoc()['total'];

// Analytics calculations
// Resources added this month
$resourcesThisMonth = $conn->query("SELECT COUNT(*) as count FROM resources 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetch_assoc()['count'];

// Active users this week
$activeUsersWeek = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM user_activity 
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Most popular subject
$popularSubject = $conn->query("SELECT s.subject_name, COUNT(*) as count 
    FROM resources r 
    JOIN subjects s ON r.subject_id = s.subject_id 
    GROUP BY s.subject_name 
    ORDER BY count DESC 
    LIMIT 1")->fetch_assoc()['subject_name'];

// Get data for charts
$subjectDistribution = $conn->query("SELECT s.subject_name, COUNT(*) as count 
    FROM resources r 
    JOIN subjects s ON r.subject_id = s.subject_id 
    GROUP BY s.subject_name");

$feedbackCategories = $conn->query("SELECT category, COUNT(*) as count 
    FROM feedback 
    GROUP BY category");

// Helper function to calculate change
function calculateChange($metric) {
    // This is a placeholder. You would typically compare current period with previous period
    return '<span class="text-success">+5%</span>';
}

// Initialize variables for statistics
$avgFeedbackRating = "N/A"; // You can calculate this if you have a rating system
$totalDownloads = 0; // You can track this if you have download tracking

// Process subject management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new subject
    if (isset($_POST['add_subject'])) {
        $subject_name = trim($_POST['subject_name']);
        if (!empty($subject_name)) {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
            $stmt->bind_param("s", $subject_name);
            if ($stmt->execute()) {
                // You might want to add success message handling here
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// Handle AJAX requests for subject management
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_subject':
            $subject_id = $_POST['subject_id'];
            $new_name = $_POST['subject_name'];
            $stmt = $conn->prepare("UPDATE subjects SET subject_name = ? WHERE subject_id = ?");
            $stmt->bind_param("si", $new_name, $subject_id);
            echo $stmt->execute() ? 'success' : 'error';
            break;

        case 'delete_subject':
            $subject_id = $_POST['subject_id'];
            $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id = ?");
            $stmt->bind_param("i", $subject_id);
            echo $stmt->execute() ? 'success' : 'error';
            break;
    }
    exit();
}

// Add this with your other action handlers at the top of the file
if (isset($_POST['action']) && $_POST['action'] === 'mark_feedback') {
    $feedback_id = $_POST['feedback_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE feedback_id = ?");
    $stmt->bind_param("si", $status, $feedback_id);
    echo $stmt->execute() ? 'success' : 'error';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="dashboard-nav">
        <div class="nav-brand">
            <i class="fas fa-bars" id="sidebar-toggle"></i>
            Admin Dashboard
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-link home-link">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-menu">
            <a href="#dashboard" class="sidebar-link active" data-section="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard Overview</span>
            </a>
            <a href="#resources" class="sidebar-link" data-section="resources">
                <i class="fas fa-book"></i>
                <span>Resource Management</span>
            </a>
            <a href="#feedback" class="sidebar-link" data-section="feedback">
                <i class="fas fa-comments"></i>
                <span>User Feedback</span>
            </a>
            <a href="#subjects" class="sidebar-link" data-section="subjects">
                <i class="fas fa-graduation-cap"></i>
                <span>Subject Management</span>
            </a>
            <a href="#analytics" class="sidebar-link" data-section="analytics">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Dashboard Overview Section -->
        <section id="dashboard" class="content-section active">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-file-alt"></i>
                            <h3>Total Resources</h3>
                            <p><?php echo $totalResources; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-comments"></i>
                            <h3>New Feedback</h3>
                            <p><?php echo $newFeedbackCount; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Total Subjects</h3>
                            <p><?php echo $totalSubjects; ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3>Unique Users</h3>
                            <p><?php echo $totalUniqueUsers; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- You can add more dashboard widgets here -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Feedback</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $recentFeedbackQuery = "SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5";
                                            $recentFeedbackResult = $conn->query($recentFeedbackQuery);
                                            while ($feedback = $recentFeedbackResult->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>{$feedback['name']}</td>";
                                                echo "<td>{$feedback['category']}</td>";
                                                echo "<td>" . date('M d, Y', strtotime($feedback['created_at'])) . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Resources</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Subject</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $recentResourcesQuery = "SELECT r.*, s.subject_name 
                                                           FROM resources r 
                                                           JOIN subjects s ON r.subject_id = s.subject_id 
                                                           ORDER BY r.created_at DESC LIMIT 5";
                                            $recentResourcesResult = $conn->query($recentResourcesQuery);
                                            while ($resource = $recentResourcesResult->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>{$resource['resource_name']}</td>";
                                                echo "<td>{$resource['subject_name']}</td>";
                                                echo "<td>{$resource['resource_type']}</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resource Management Section -->
        <section id="resources" class="content-section">
            <div class="container-fluid">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Upload New Resource</h4>
                    </div>
                    <div class="card-body">
                        <!-- Upload Resource Form -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="resource_name" class="form-label">Resource Name</label>
                                    <input type="text" class="form-control" id="resource_name" name="resource_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="subject_id" class="form-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id" required>
                                        <?php
                                        // Fetch and display subjects
                                        $query = "SELECT * FROM subjects";
                                        $result = $conn->query($query);
                                        while ($subject = $result->fetch_assoc()) {
                                            echo "<option value='{$subject['subject_id']}'>{$subject['subject_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="resource_type" class="form-label">Resource Type</label>
                                    <select class="form-select" id="resource_type" name="resource_type" required>
                                        <option value="notes">Notes</option>
                                        <option value="past_paper">Past Paper</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="resource_file" class="form-label">File</label>
                                    <input type="file" class="form-control" id="resource_file" name="resource_file" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="upload_resource">Upload Resource</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Existing Resources</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch and display resources
                                    $query = "SELECT r.*, s.subject_name 
                                             FROM resources r 
                                             JOIN subjects s ON r.subject_id = s.subject_id";
                                    $result = $conn->query($query);
                                    while ($resource = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>{$resource['resource_name']}</td>";
                                        echo "<td>{$resource['subject_name']}</td>";
                                        echo "<td>{$resource['resource_type']}</td>";
                                        echo "<td>{$resource['description']}</td>";
                                        echo "<td>
                                                <a href='edit_resource.php?id={$resource['resource_id']}' class='btn btn-sm btn-warning'>Edit</a>
                                                <a href='delete_resource.php?id={$resource['resource_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feedback Section -->
        <section id="feedback" class="content-section">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>User Feedback</h4>
                        <div class="feedback-filters">
                            <button class="btn btn-sm btn-outline-secondary filter-feedback active" data-filter="all">All</button>
                            <button class="btn btn-sm btn-outline-secondary filter-feedback" data-filter="New">Unread</button>
                            <button class="btn btn-sm btn-outline-secondary filter-feedback" data-filter="Read">Read</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Category</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT * FROM feedback ORDER BY created_at DESC";
                                    $result = $conn->query($query);
                                    while ($feedback = $result->fetch_assoc()) {
                                        $statusClass = $feedback['status'] === 'New' ? 'unread' : '';
                                        $statusIcon = $feedback['status'] === 'New' ? 
                                            '<i class="fas fa-envelope"></i>' : 
                                            '<i class="fas fa-envelope-open"></i>';
                                        
                                        echo "<tr class='feedback-row {$statusClass}' data-status='{$feedback['status']}'>";
                                        echo "<td class='status-cell'>{$statusIcon}</td>";
                                        echo "<td>{$feedback['name']}</td>";
                                        echo "<td>{$feedback['email']}</td>";
                                        echo "<td>{$feedback['category']}</td>";
                                        echo "<td>{$feedback['message']}</td>";
                                        echo "<td>" . date('M d, Y', strtotime($feedback['created_at'])) . "</td>";
                                        echo "<td>
                                                <button class='btn btn-sm " . ($feedback['status'] === 'New' ? 'btn-success' : 'btn-secondary') . " toggle-status' 
                                                        data-id='{$feedback['feedback_id']}'
                                                        data-current-status='{$feedback['status']}'>
                                                    " . ($feedback['status'] === 'New' ? 'Mark as Read' : 'Mark as Unread') . "
                                                </button>
                                                <button class='btn btn-sm btn-danger delete-feedback' data-id='{$feedback['feedback_id']}'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Add other sections as needed -->

        <!-- Analytics Section -->
        <section id="analytics" class="content-section">
            <div class="container-fluid">
                <!-- User Activity Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>User Activity Overview</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="userActivityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resource and Feedback Stats -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Resource Distribution by Subject</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="resourceDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Feedback Categories</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="feedbackCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Statistics Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Detailed Statistics</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Metric</th>
                                                <th>Value</th>
                                                <th>Change</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Get statistics for the table
                                            $stats = [
                                                ['Resources Added This Month', $resourcesThisMonth, calculateChange('resources')],
                                                ['Active Users This Week', $activeUsersWeek, calculateChange('users')],
                                                ['Average Feedback Rating', $avgFeedbackRating, calculateChange('feedback')],
                                                ['Most Accessed Subject', $popularSubject, ''],
                                                ['Total Downloads', $totalDownloads, calculateChange('downloads')]
                                            ];

                                            foreach ($stats as $stat) {
                                                echo "<tr>";
                                                echo "<td>{$stat[0]}</td>";
                                                echo "<td>{$stat[1]}</td>";
                                                echo "<td>{$stat[2]}</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Subject Management Section -->
        <section id="subjects" class="content-section">
            <div class="container-fluid">
                <!-- Add New Subject Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Add New Subject</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label for="subject_name" class="form-label">Subject Name</label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" name="add_subject">
                                    <i class="fas fa-plus"></i> Add Subject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Subjects Card -->
                <div class="card">
                    <div class="card-header">
                        <h4>Manage Subjects</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject ID</th>
                                        <th>Subject Name</th>
                                        <th>Total Resources</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch subjects with resource count
                                    $query = "SELECT s.*, COUNT(r.resource_id) as resource_count 
                                             FROM subjects s 
                                             LEFT JOIN resources r ON s.subject_id = r.subject_id 
                                             GROUP BY s.subject_id";
                                    $result = $conn->query($query);
                                    while ($subject = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>{$subject['subject_id']}</td>";
                                        echo "<td>
                                                <span class='subject-name' id='subject-name-{$subject['subject_id']}'>{$subject['subject_name']}</span>
                                                <input type='text' class='form-control d-none edit-subject-input' 
                                                       id='edit-subject-{$subject['subject_id']}' 
                                                       value='{$subject['subject_name']}'>
                                            </td>";
                                        echo "<td>{$subject['resource_count']}</td>";
                                        echo "<td>
                                                <button class='btn btn-sm btn-warning edit-subject-btn' data-id='{$subject['subject_id']}'>
                                                    <i class='fas fa-edit'></i> Edit
                                                </button>
                                                <button class='btn btn-sm btn-success save-subject-btn d-none' data-id='{$subject['subject_id']}'>
                                                    <i class='fas fa-save'></i> Save
                                                </button>
                                                <button class='btn btn-sm btn-danger delete-subject-btn' data-id='{$subject['subject_id']}' 
                                                        data-resource-count='{$subject['resource_count']}'>
                                                    <i class='fas fa-trash'></i> Delete
                                                </button>
                                            </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Add JavaScript for sidebar toggle and section switching
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });

        // Section switching
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSection = this.getAttribute('data-section');
                
                // Update active states
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(targetSection).classList.add('active');
            });
        });

        // User Activity Chart
        new Chart(document.getElementById('userActivityChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'User Activity',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: false,
                    borderColor: '#FFD700',
                    tension: 0.1
                }]
            }
        });

        // Resource Distribution Chart
        new Chart(document.getElementById('resourceDistributionChart'), {
            type: 'pie',
            data: {
                labels: <?php 
                    $labels = [];
                    $data = [];
                    while($row = $subjectDistribution->fetch_assoc()) {
                        $labels[] = $row['subject_name'];
                        $data[] = $row['count'];
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: [
                        '#FFD700',
                        '#FFC800',
                        '#FFB700',
                        '#FFA600',
                        '#FF9500'
                    ]
                }]
            }
        });

        // Feedback Category Chart
        new Chart(document.getElementById('feedbackCategoryChart'), {
            type: 'bar',
            data: {
                labels: <?php 
                    $labels = [];
                    $data = [];
                    while($row = $feedbackCategories->fetch_assoc()) {
                        $labels[] = $row['category'];
                        $data[] = $row['count'];
                    }
                    echo json_encode($labels);
                ?>,
                datasets: [{
                    label: 'Feedback by Category',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: '#FFD700'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Add this to your existing JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle feedback status
            document.querySelectorAll('.toggle-status').forEach(btn => {
                btn.addEventListener('click', function() {
                    const feedbackId = this.dataset.id;
                    const currentStatus = this.dataset.currentStatus;
                    const newStatus = currentStatus === 'New' ? 'Read' : 'New';
                    const row = this.closest('tr');
                    
                    fetch('dashboard.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=mark_feedback&feedback_id=${feedbackId}&status=${newStatus}`
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result === 'success') {
                            // Update UI
                            this.textContent = newStatus === 'New' ? 'Mark as Read' : 'Mark as Unread';
                            this.classList.toggle('btn-success');
                            this.classList.toggle('btn-secondary');
                            this.dataset.currentStatus = newStatus;
                            row.classList.toggle('unread');
                            row.dataset.status = newStatus;
                            
                            // Update status icon
                            const statusCell = row.querySelector('.status-cell');
                            statusCell.innerHTML = newStatus === 'New' ? 
                                '<i class="fas fa-envelope"></i>' : 
                                '<i class="fas fa-envelope-open"></i>';
                        }
                    });
                });
            });

            // Filter feedback
            document.querySelectorAll('.filter-feedback').forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.dataset.filter;
                    
                    // Update active filter button
                    document.querySelectorAll('.filter-feedback').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter rows
                    document.querySelectorAll('.feedback-row').forEach(row => {
                        if (filter === 'all' || row.dataset.status === filter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>