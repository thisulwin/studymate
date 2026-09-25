CREATE DATABASE IF NOT EXISTS note_platform;
USE note_platform;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-book',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    pdf_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT,
    parent_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

CREATE TABLE comment_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    vote_type TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE saved_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, note_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
);

CREATE TABLE note_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_note_tag (note_id, tag)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@studymate.com', '$2y$12$Q1wAjBa7OhlDGRgvocv5ie801v1sDztjwFMBM1M7nwL6l4xkAOECy', 'admin');

INSERT INTO categories (name, description, icon) VALUES
('Computing & IT', 'Computer Science, Software Engineering, and IT related subjects', 'bi-laptop'),
('Engineering', 'Civil, Mechanical, Electrical, and other engineering disciplines', 'bi-gear'),
('Sciences', 'Physics, Chemistry, Mathematics, and biological sciences', 'bi-atom'),
('Business & Management', 'Business Administration, Accounting, Marketing, and Finance', 'bi-briefcase'),
('Humanities', 'Languages, History, Philosophy, and social sciences', 'bi-book-half');

INSERT INTO departments (category_id, name, description) VALUES
(1, 'Software Engineering', 'Software development, design patterns, and project management'),
(1, 'Computer Science', 'Algorithms, data structures, AI, and machine learning'),
(1, 'Information Technology', 'Networking, databases, and system administration'),
(2, 'Civil Engineering', 'Structural analysis, construction, and project management'),
(2, 'Mechanical Engineering', 'Thermodynamics, fluid mechanics, and manufacturing'),
(3, 'Physics', 'Classical mechanics, quantum physics, and electromagnetism'),
(3, 'Mathematics', 'Calculus, linear algebra, statistics, and discrete math'),
(4, 'Business Administration', 'Management principles, organizational behavior'),
(4, 'Accounting', 'Financial accounting, auditing, and taxation');

INSERT INTO subjects (department_id, name, code) VALUES
(1, 'Web Technologies', 'ICT2209'),
(1, 'Software Design & Architecture', 'ICT3301'),
(1, 'Mobile Application Development', 'ICT3305'),
(2, 'Data Structures & Algorithms', 'CS2201'),
(2, 'Artificial Intelligence', 'CS3302'),
(2, 'Machine Learning', 'CS4401'),
(3, 'Computer Networks', 'ICT2203'),
(3, 'Database Management Systems', 'ICT2205'),
(3, 'Operating Systems', 'ICT3304'),
(4, 'Structural Analysis', 'CE3301'),
(4, 'Surveying', 'CE2201'),
(5, 'Thermodynamics', 'ME3301'),
(5, 'Fluid Mechanics', 'ME3302'),
(6, 'Classical Mechanics', 'PH2201'),
(6, 'Electromagnetism', 'PH3301'),
(7, 'Calculus II', 'MA2201'),
(7, 'Linear Algebra', 'MA2202'),
(8, 'Principles of Management', 'BA2201'),
(9, 'Financial Accounting', 'AC2201');
