<?php
include('config.php');

// Get subject ID from URL
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get subject details
$subject_query = "SELECT * FROM subjects WHERE subject_id = ?";
$stmt = $conn->prepare($subject_query);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$subject = $stmt->get_result()->fetch_assoc();

// Check if subject exists
if (!$subject) {
    // Subject not found, redirect to index page
    header('Location: index.php');
    exit();
}

// Get resources for this subject
$resources_query = "SELECT * FROM resources 
                   WHERE subject_id = ? 
                   ORDER BY resource_type, created_at DESC";
$stmt = $conn->prepare($resources_query);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

// Group resources by type
$resources = [
    'notes' => [],
    'past_paper' => []
];

while ($resource = mysqli_fetch_assoc($result)) {
    $resources[$resource['resource_type']][] = $resource;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject['subject_name']); ?> - AccessEd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="course_template.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="course-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($subject['subject_name']); ?></h1>
        </div>
    </div>

    <div class="resource-nav">
        <div class="nav-container">
            <a href="#past-papers" class="resource-tab active" data-target="past-papers">Past Papers</a>
            <a href="#study-materials" class="resource-tab" data-target="study-materials">Study Materials</a>
        </div>
    </div>

    <div class="resources-container">
        <div class="container">
            <!-- Study Materials Section -->
            <div id="study-materials" class="resource-content">
                <?php if (empty($resources['notes'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>Resources in Progress</h3>
                        <p>Our team is working on creating comprehensive study materials for this subject. Please visit again soon!</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($resources['notes'] as $resource): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="resource-card">
                                    <div class="card-body">
                                        <i class="fas fa-book resource-icon"></i>
                                        <h5 class="card-title"><?php echo htmlspecialchars($resource['resource_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    </div>
                                    <div class="card-actions">
                                        <a href="view_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                           class="btn-view">
                                            <i class="fas fa-eye"></i>View
                                        </a>
                                        <a href="download_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                           class="btn-download">
                                            <i class="fas fa-download"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Past Papers Section -->
            <div id="past-papers" class="resource-content active">
                <?php if (empty($resources['past_paper'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>Coming Soon!</h3>
                        <p>We're currently preparing past papers for this subject. Please check back later for updated resources.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($resources['past_paper'] as $resource): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="resource-card">
                                    <div class="card-body">
                                        <i class="fas fa-file-alt resource-icon"></i>
                                        <h5 class="card-title"><?php echo htmlspecialchars($resource['resource_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    </div>
                                    <div class="card-actions">
                                        <a href="view_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                           class="btn-view">
                                            <i class="fas fa-eye"></i>View
                                        </a>
                                        <a href="download_resource.php?id=<?php echo $resource['resource_id']; ?>" 
                                           class="btn-download">
                                            <i class="fas fa-download"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching functionality
        document.querySelectorAll('.resource-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all tabs and contents
                document.querySelectorAll('.resource-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.resource-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(this.dataset.target).classList.add('active');
            });
        });
    </script>
</body>
</html> 