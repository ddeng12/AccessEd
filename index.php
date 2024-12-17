<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session before including config
session_start();

// Try including config
try {
    require_once('config.php');
} catch (Exception $e) {
    echo "Error loading configuration: " . $e->getMessage();
    exit();
}

trackUserActivity('index.php');

// Function to get resources by subject
function getResourcesBySubject($subject_id) {
    global $conn;
    $query = "SELECT resource_id, resource_name, resource_file, description 
              FROM resources 
              WHERE subject_id = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $resources = [];
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
    return $resources;
}

// Get all subjects and their resources
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_result = mysqli_query($conn, $subjects_query);

// Subject icons
$icons = [
    'Mathematics' => 'fas fa-calculator',
    'English' => 'fas fa-book',
    'Swahili' => 'fas fa-language',
    'Biology' => 'fas fa-dna',
    'Chemistry' => 'fas fa-flask',
    'default' => 'fas fa-book-open'  // Fallback icon
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AccessEd</title>
    <link rel="stylesheet" href="index.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">AccessEd</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <section id="home" class="welcome-section">
        <div class="welcome-background"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 welcome-content">
                    <h1 class="display-5 mb-4">Welcome to AccessEd</h1>
                    <p class="lead welcome-text mb-4">Empowering students in underserved communities with quality education and opportunities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <h2>Our Mission</h2>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="lead text-secondary animate-on-scroll">At AccessEd, we are dedicated to empowering high school students in underserved communities by providing access to quality educational resources, past papers, revision materials, and entrepreneurial skills training.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <section id="courses" class="py-5 bg-light">
        <div class="container">
            <div class="section-header">
                <h2>Our Courses</h2>
            </div>
            <div class="row">
                <?php 
                while ($subject = mysqli_fetch_assoc($subjects_result)): 
                    // Get the appropriate icon or use default
                    $icon = $icons[$subject['subject_name']] ?? $icons['default'];
                ?>
                    <div class="col-md-4 mb-4 animate-on-scroll">
                        <a href="subject_template.php?id=<?php echo $subject['subject_id']; ?>" class="course-card-link">
                            <div class="course-card">
                                <i class="<?php echo $icon; ?>"></i>
                                <h3 class="h4 mb-3"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                                <p class="text-secondary">Access study materials and past papers.</p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section py-4">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <h2 class="h3 mb-3">Contact Us</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="contact-info">
                        <h3 class="h4 mb-3">Get In Touch</h3>
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@accessed.com">info@accessed.com</a>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:+254792016074">+254 792 016 074</a>
                        </div>
                        <div class="mt-3">
                            <h4 class="h5 mb-2">Follow Us</h4>
                            <div class="social-links">
                                <a href="#" class="text-dark me-3"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="text-dark me-3"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="text-dark me-3"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-muted small">
                                We're committed to supporting education in underserved communities. 
                                Feel free to reach out to us with any questions or support inquiries.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <h5 class="mb-2 h6">AccessEd</h5>
                    <p class="small">Empowering students in underserved communities through quality education and resources.</p>
                </div>
                <div class="col-md-4 mb-2">
                    <h5 class="mb-2 h6">Quick Links</h5>
                    <ul class="list-unstyled small mb-0">
                        <li><a href="#home" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="#about" class="text-white text-decoration-none">About</a></li>
                        <li><a href="#courses" class="text-white text-decoration-none">Courses</a></li>
                        <li><a href="#contact" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-2">
                    <h5 class="mb-2 h6">Connect With Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-2 bg-light">
            <div class="text-center small">
                <p class="mb-0">&copy; 2024 AccessEd. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Feedback Button -->
<button id="feedbackBtn" style="position: fixed; bottom: 30px; right: 30px; background: #FFD700; color: #333; padding: 15px 30px; border: none; border-radius: 50px; cursor: pointer; font-size: 16px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 99999; transition: all 0.3s ease;">
    <i class="fas fa-comments"></i>
    <span>Feedback</span>
</button>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFD700, #FFC107); color: #333; padding: 1.5rem; border: none; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title">
                    <i class="fas fa-comment-dots me-2"></i>
                    Share Your Feedback
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <form action="send_feedback.php" method="POST">
                    <!-- Name -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" required
                            style="border: 2px solid #e9ecef; border-radius: 10px;">
                        <label for="name">Full Name</label>
                    </div>

                    <!-- Email -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" required
                            style="border: 2px solid #e9ecef; border-radius: 10px;">
                        <label for="email">Email Address</label>
                    </div>

                    <!-- Category -->
                    <div class="form-floating mb-3">
                        <select class="form-select" id="category" name="category" required
                            style="border: 2px solid #e9ecef; border-radius: 10px;">
                            <option value="General">General Feedback</option>
                            <option value="Course Content">Course Content</option>
                            <option value="Website Experience">Website Experience</option>
                            <option value="Technical Issue">Technical Issue</option>
                            <option value="Suggestion">Suggestion</option>
                        </select>
                        <label for="category">Feedback Category</label>
                    </div>

                    <!-- Message -->
                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="message" name="message" required
                            style="height: 120px; border: 2px solid #e9ecef; border-radius: 10px;"></textarea>
                        <label for="message">Your Message</label>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" 
                            style="background: #FFD700; border: none; color: #333; padding: 0.75rem 1.5rem; font-weight: 600;">
                            <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="index.js"></script>
    <script src="scroll.js"></script>
    <script>
        
        document.getElementById('feedbackBtn').addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('feedbackModal')).show();
        });
    </script>
</body>
</html>