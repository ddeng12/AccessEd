-- Init database
USE webtech_fall2024_david_deng;

-- Core tables
DROP TABLE IF EXISTS admin_activity_log;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS user_activity;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS subjects;

-- Create tables
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(255) NOT NULL
);

-- Default subjects
INSERT INTO subjects (subject_id, subject_name) VALUES 
(1, 'Mathematics'),
(2, 'English'),
(3, 'Swahili'),
(4, 'Biology'),
(5, 'Chemistry');

-- Resource management
CREATE TABLE resources (
    resource_id INT PRIMARY KEY AUTO_INCREMENT,
    resource_name VARCHAR(255) NOT NULL,
    subject_id INT NOT NULL,
    resource_type ENUM('notes', 'past_paper') NOT NULL DEFAULT 'notes',
    description TEXT,
    resource_file VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin system
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- User tracking
CREATE TABLE user_activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    page_name VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action_type VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_user (user_id)
);

-- Feedback system
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category ENUM('General', 'Course Content', 'Website Experience', 
                 'Technical Issue', 'Suggestion') DEFAULT 'General',
    status ENUM('New', 'Read', 'Archived') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin logging
CREATE TABLE admin_activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id)
);

-- Add indexes
CREATE INDEX idx_subject_id ON resources(subject_id);
CREATE INDEX idx_created_at ON resources(created_at);

